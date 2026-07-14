<?php

namespace App\Http\Controllers;

use App\Models\agencies;
use App\Models\Branch;
use App\Models\clients;
use App\Models\employees;
use App\Models\services;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DataImportController extends Controller
{
    private const MAX_ROWS = 1000;

    private array $branchCache = [];

    public function index()
    {
        $this->requireManagerUser();

        return view('imports.index', [
            'resources' => $this->resources(),
            'preview' => null,
            'selectedResource' => null,
        ]);
    }

    public function template(string $resource)
    {
        $this->requireManagerUser();
        $config = $this->resourceConfig($resource);

        return $this->csvDownload(
            $resource.'-import-template.csv',
            [$config['headers'], $config['example']]
        );
    }

    public function preview(Request $request, string $resource)
    {
        $this->requireManagerUser();
        $config = $this->resourceConfig($resource);

        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $agencyId = $this->requiredAgencyId();
        $parsedRows = $this->parseCsv($validated['file']);
        $previewRows = $this->previewRows($resource, $parsedRows, $agencyId);
        $summary = $this->summary($previewRows);

        $preview = [
            'resource' => $resource,
            'label' => $config['label'],
            'filename' => $validated['file']->getClientOriginalName(),
            'rows' => $previewRows,
            'summary' => $summary,
            'columns' => $config['preview_columns'],
        ];

        session()->put($this->sessionKey($resource), $preview);

        return view('imports.index', [
            'resources' => $this->resources(),
            'preview' => $preview,
            'selectedResource' => $resource,
        ]);
    }

    public function store(string $resource)
    {
        $this->requireManagerUser();
        $this->resourceConfig($resource);

        $preview = session($this->sessionKey($resource));

        if (! $preview) {
            return redirect()
                ->route('imports.index')
                ->with('error', 'Importez puis prévisualisez un fichier CSV avant de lancer l’import.');
        }

        $agency = $this->currentAgency();
        $validRows = collect($preview['rows'])
            ->filter(fn (array $row) => $row['valid'])
            ->pluck('data')
            ->values();

        $result = [
            'created' => 0,
            'updated' => 0,
        ];

        DB::transaction(function () use ($resource, $agency, $validRows, &$result) {
            foreach ($validRows as $data) {
                $action = match ($resource) {
                    'clients' => $this->importClient($agency, $data),
                    'employees' => $this->importEmployee($agency, $data),
                    'services' => $this->importService($agency, $data),
                };

                $result[$action]++;
            }
        });

        $this->logActivity(
            'import.'.$resource.'.completed',
            $agency,
            metadata: [
                'filename' => $preview['filename'],
                'total_rows' => $preview['summary']['total'],
                'valid_rows' => $preview['summary']['valid'],
                'invalid_rows' => $preview['summary']['invalid'],
                'created' => $result['created'],
                'updated' => $result['updated'],
            ],
        );

        session()->forget($this->sessionKey($resource));

        return redirect()
            ->route('imports.index')
            ->with('success', "Import {$preview['label']} terminé : {$result['created']} créé(s), {$result['updated']} mis à jour.");
    }

    private function parseCsv(UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'r');

        if (! $handle) {
            throw ValidationException::withMessages([
                'file' => 'Le fichier CSV n’a pas pu être ouvert.',
            ]);
        }

        $headers = fgetcsv($handle);

        if (! $headers) {
            fclose($handle);

            throw ValidationException::withMessages([
                'file' => 'Le fichier CSV doit contenir une ligne d’en-tête.',
            ]);
        }

        $headers = collect($headers)
            ->map(fn ($header) => $this->normalizeHeader((string) $header))
            ->all();

        $rows = [];
        $rowNumber = 1;

        while (($values = fgetcsv($handle)) !== false) {
            $rowNumber++;

            if ($this->rowIsEmpty($values)) {
                continue;
            }

            if (count($rows) >= self::MAX_ROWS) {
                fclose($handle);

                throw ValidationException::withMessages([
                    'file' => 'Le fichier CSV est trop volumineux. Importez au maximum '.self::MAX_ROWS.' lignes à la fois.',
                ]);
            }

            $values = array_pad($values, count($headers), null);
            $rows[] = [
                'row_number' => $rowNumber,
                'values' => array_combine($headers, array_slice($values, 0, count($headers))),
            ];
        }

        fclose($handle);

        return $rows;
    }

    private function previewRows(string $resource, array $rows, int $agencyId): array
    {
        $seen = [];

        return collect($rows)
            ->map(function (array $row) use ($resource, $agencyId, &$seen) {
                [$data, $errors] = match ($resource) {
                    'clients' => $this->clientData($row['values'], $agencyId),
                    'employees' => $this->employeeData($row['values'], $agencyId),
                    'services' => $this->serviceData($row['values'], $agencyId),
                };

                $importKey = $data['import_key'] ?? null;

                if ($importKey && isset($seen[$importKey])) {
                    $errors[] = 'Ligne en double dans ce fichier.';
                }

                if ($importKey) {
                    $seen[$importKey] = true;
                }

                $valid = $errors === [];
                $action = $valid ? $this->importAction($resource, $data, $agencyId) : 'skip';

                unset($data['import_key']);

                return [
                    'row_number' => $row['row_number'],
                    'valid' => $valid,
                    'action' => $action,
                    'errors' => $errors,
                    'data' => $data,
                ];
            })
            ->all();
    }

    private function clientData(array $row, int $agencyId): array
    {
        [$branchId, $branchLabel, $branchError] = $this->resolveBranch($row, $agencyId);

        $data = [
            'name' => $this->value($row, ['name', 'client_name', 'full_name']),
            'email' => ($email = $this->value($row, ['email', 'client_email'])) ? Str::lower($email) : null,
            'phone_number' => $this->value($row, ['phone', 'phone_number', 'mobile']),
            'branch_id' => $branchId,
            'branch_label' => $branchLabel,
        ];

        $errors = $this->validateRow($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone_number' => ['required', 'string', 'max:30'],
        ]);

        if ($branchError) {
            $errors[] = $branchError;
        }

        $data['normalized_phone'] = clients::normalizePhone($data['phone_number']);

        $data['import_key'] = 'client:'.$data['normalized_phone'];

        return [$data, $errors];
    }

    private function employeeData(array $row, int $agencyId): array
    {
        [$branchId, $branchLabel, $branchError] = $this->resolveBranch($row, $agencyId);

        $data = [
            'name' => $this->value($row, ['name', 'employee_name', 'full_name']),
            'email' => Str::lower((string) $this->value($row, ['email', 'employee_email'])),
            'phone_number' => $this->value($row, ['phone_number', 'phone', 'mobile']),
            'job_title' => $this->value($row, ['job_title', 'position', 'role']),
            'salary' => $this->value($row, ['salary', 'monthly_salary']),
            'branch_id' => $branchId,
            'branch_label' => $branchLabel,
        ];

        $errors = $this->validateRow($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone_number' => ['required', 'string', 'max:20'],
            'job_title' => ['required', 'string', 'max:255'],
            'salary' => ['required', 'numeric', 'min:0'],
        ]);

        if ($branchError) {
            $errors[] = $branchError;
        }

        $data['import_key'] = 'employee:'.$data['email'];

        return [$data, $errors];
    }

    private function serviceData(array $row, int $agencyId): array
    {
        [$branchId, $branchLabel, $branchError] = $this->resolveBranch($row, $agencyId);

        $data = [
            'name' => $this->value($row, ['name', 'service_name']),
            'description' => $this->value($row, ['description', 'details']),
            'price' => $this->value($row, ['price', 'amount']),
            'duration_minutes' => $this->value($row, ['duration_minutes', 'duration']) ?? '60',
            'branch_id' => $branchId,
            'branch_label' => $branchLabel,
        ];

        $errors = $this->validateRow($data, [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:555'],
            'price' => ['required', 'numeric', 'min:0'],
            'duration_minutes' => ['required', 'integer', 'min:15', 'max:480'],
        ]);

        if ($branchError) {
            $errors[] = $branchError;
        }

        $data['import_key'] = 'service:'.($branchId ?? 'agency').':'.Str::lower((string) $data['name']);

        return [$data, $errors];
    }

    private function validateRow(array $data, array $rules): array
    {
        $validator = Validator::make($data, $rules);

        if ($validator->passes()) {
            return [];
        }

        return $validator->errors()->all();
    }

    private function importClient(agencies $agency, array $data): string
    {
        $this->ensureBranchStillBelongsToAgency($agency->id, $data['branch_id'] ?? null);

        $client = clients::where('agency_id', $agency->id)
            ->where(function ($query) use ($data) {
                $query->where('normalized_phone', $data['normalized_phone']);

                if ($data['email']) {
                    $query->orWhere('email', $data['email']);
                }
            })
            ->first();

        if ($client) {
            $before = $client->only(['name', 'email', 'phone_number', 'normalized_phone', 'branch_id']);
            $client->update([
                'branch_id' => $data['branch_id'] ?? null,
                'name' => $data['name'],
                'phone_number' => $data['phone_number'],
                ...($data['email'] ? ['email' => $data['email']] : []),
            ]);

            $changes = $this->activityChanges($before, $client->only(array_keys($before)));
            if ($changes) {
                $this->logActivity('client.updated_from_import', $client, $changes);
            }

            return 'updated';
        }

        $this->enforceAgencyPlanLimit(
            'clients',
            clients::where('agency_id', $agency->id)->count(),
            'clients'
        );

        $client = clients::create([
            'agency_id' => $agency->id,
            'branch_id' => $data['branch_id'] ?? null,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone_number' => $data['phone_number'],
            'normalized_phone' => $data['normalized_phone'],
        ]);

        $this->logActivity('client.created_from_import', $client);

        return 'created';
    }

    private function importEmployee(agencies $agency, array $data): string
    {
        $this->ensureBranchStillBelongsToAgency($agency->id, $data['branch_id'] ?? null);

        $employee = employees::where('agency_id', $agency->id)
            ->where('email', $data['email'])
            ->first();

        if ($employee) {
            $before = $employee->only(['name', 'phone_number', 'job_title', 'salary', 'branch_id']);
            $employee->update([
                'branch_id' => $data['branch_id'] ?? null,
                'name' => $data['name'],
                'phone_number' => $data['phone_number'],
                'job_title' => $data['job_title'],
                'salary' => $data['salary'],
            ]);

            $changes = $this->activityChanges($before, $employee->only(array_keys($before)));
            if ($changes) {
                $this->logActivity('employee.updated_from_import', $employee, $changes);
            }

            return 'updated';
        }

        $this->enforceAgencyPlanLimit(
            'employees',
            employees::where('agency_id', $agency->id)->count(),
            'employees'
        );

        $employee = employees::create([
            'agency_id' => $agency->id,
            'branch_id' => $data['branch_id'] ?? null,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone_number' => $data['phone_number'],
            'job_title' => $data['job_title'],
            'salary' => $data['salary'],
        ]);

        $this->logActivity('employee.created_from_import', $employee);

        return 'created';
    }

    private function importService(agencies $agency, array $data): string
    {
        $this->ensureBranchStillBelongsToAgency($agency->id, $data['branch_id'] ?? null);

        $query = services::where('agency_id', $agency->id)
            ->where('name', $data['name']);

        $data['branch_id']
            ? $query->where('branch_id', $data['branch_id'])
            : $query->whereNull('branch_id');

        $service = $query->first();

        if ($service) {
            $before = $service->only(['description', 'price', 'duration_minutes']);
            $service->update([
                'description' => $data['description'],
                'price' => $data['price'],
                'duration_minutes' => $data['duration_minutes'],
            ]);

            $changes = $this->activityChanges($before, $service->only(array_keys($before)));
            if ($changes) {
                $this->logActivity('service.updated_from_import', $service, $changes);
            }

            return 'updated';
        }

        $this->enforceAgencyPlanLimit(
            'services',
            services::where('agency_id', $agency->id)->count(),
            'services'
        );

        $service = services::create([
            'agency_id' => $agency->id,
            'branch_id' => $data['branch_id'] ?? null,
            'name' => $data['name'],
            'description' => $data['description'],
            'price' => $data['price'],
            'duration_minutes' => $data['duration_minutes'],
        ]);

        $this->logActivity('service.created_from_import', $service);

        return 'created';
    }

    private function importAction(string $resource, array $data, int $agencyId): string
    {
        return match ($resource) {
            'clients' => clients::where('agency_id', $agencyId)
                ->where(fn ($query) => $query->where('normalized_phone', $data['normalized_phone'])
                    ->when($data['email'], fn ($query) => $query->orWhere('email', $data['email'])))
                ->exists() ? 'update' : 'create',
            'employees' => employees::where('agency_id', $agencyId)->where('email', $data['email'])->exists() ? 'update' : 'create',
            'services' => $this->serviceExists($agencyId, $data) ? 'update' : 'create',
        };
    }

    private function serviceExists(int $agencyId, array $data): bool
    {
        $query = services::where('agency_id', $agencyId)
            ->where('name', $data['name']);

        return $data['branch_id']
            ? $query->where('branch_id', $data['branch_id'])->exists()
            : $query->whereNull('branch_id')->exists();
    }

    private function resolveBranch(array $row, int $agencyId): array
    {
        $branchId = $this->value($row, ['branch_id']);
        $branchCode = $this->value($row, ['branch_code', 'site_code', 'location_code']);
        $branchName = $this->value($row, ['branch_name', 'branch', 'site', 'location']);

        if (! $branchId && ! $branchCode && ! $branchName) {
            return [null, 'Toute l’agence', null];
        }

        $branches = $this->branchesForImport($agencyId);

        if ($branchId) {
            $branch = $branches->firstWhere('id', (int) $branchId);

            return $branch
                ? [(int) $branch->id, $branch->name, null]
                : [null, 'Branche inconnue', 'L’identifiant de branche ne correspond pas à cette agence.'];
        }

        if ($branchCode) {
            $branch = $branches->first(fn (Branch $branch) => Str::lower((string) $branch->code) === Str::lower($branchCode));

            return $branch
                ? [(int) $branch->id, $branch->name, null]
                : [null, 'Branche inconnue', 'Le code de branche est introuvable pour cette agence.'];
        }

        $agencyWideNames = ['agency-wide', 'agency wide', 'all', 'none', ''];

        if (in_array(Str::lower((string) $branchName), $agencyWideNames, true)) {
            return [null, 'Toute l’agence', null];
        }

        $branch = $branches->first(fn (Branch $branch) => Str::lower($branch->name) === Str::lower((string) $branchName));

        return $branch
            ? [(int) $branch->id, $branch->name, null]
            : [null, 'Branche inconnue', 'Le nom de branche est introuvable pour cette agence.'];
    }

    private function ensureBranchStillBelongsToAgency(int $agencyId, ?int $branchId): void
    {
        if (! $branchId) {
            return;
        }

        if (Branch::where('agency_id', $agencyId)->where('is_active', true)->whereKey($branchId)->exists()) {
            return;
        }

        throw ValidationException::withMessages([
            'branch_id' => 'L’une des branches prévisualisées n’appartient plus à cette agence.',
        ]);
    }

    private function branchesForImport(int $agencyId)
    {
        if (! isset($this->branchCache[$agencyId])) {
            $this->branchCache[$agencyId] = Branch::where('agency_id', $agencyId)->where('is_active', true)->get();
        }

        return $this->branchCache[$agencyId];
    }

    private function value(array $row, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = $row[$key] ?? null;

            if ($value === null) {
                continue;
            }

            $value = trim((string) $value);

            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    private function summary(array $rows): array
    {
        $collection = collect($rows);

        return [
            'total' => $collection->count(),
            'valid' => $collection->where('valid', true)->count(),
            'invalid' => $collection->where('valid', false)->count(),
            'create' => $collection->where('action', 'create')->count(),
            'update' => $collection->where('action', 'update')->count(),
        ];
    }

    private function rowIsEmpty(array $values): bool
    {
        return collect($values)
            ->every(fn ($value) => trim((string) $value) === '');
    }

    private function normalizeHeader(string $header): string
    {
        $header = preg_replace('/^\xEF\xBB\xBF/', '', $header);

        return Str::of($header)
            ->lower()
            ->trim()
            ->replace([' ', '-', '.'], '_')
            ->replaceMatches('/[^a-z0-9_]/', '')
            ->toString();
    }

    private function csvDownload(string $filename, array $rows)
    {
        return response()->streamDownload(function () use ($rows) {
            $output = fopen('php://output', 'w');

            foreach ($rows as $row) {
                fputcsv($output, $row);
            }

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function resourceConfig(string $resource): array
    {
        $config = $this->resources()[$resource] ?? null;

        abort_unless($config, 404);

        return $config;
    }

    private function resources(): array
    {
        return [
            'clients' => [
                'label' => 'Clients',
                'description' => 'Importez les fiches clients et associez-les aux branches.',
                'headers' => ['name', 'email', 'phone', 'branch_code', 'branch_name'],
                'example' => ['Nadia Amrani', 'nadia@example.com', '0612345678', 'DT', 'Downtown'],
                'preview_columns' => ['name', 'email', 'phone_number', 'branch_label'],
            ],
            'employees' => [
                'label' => 'Employés',
                'description' => 'Importez les profils employés sans créer de comptes de connexion.',
                'headers' => ['name', 'email', 'phone_number', 'job_title', 'salary', 'branch_code', 'branch_name'],
                'example' => ['Youssef Bennani', 'youssef@example.com', '0611111111', 'Spécialiste lavage', '4500', 'DT', 'Centre-ville'],
                'preview_columns' => ['name', 'email', 'phone_number', 'job_title', 'salary', 'branch_label'],
            ],
            'services' => [
                'label' => 'Services',
                'description' => 'Importez le catalogue de services avec des prix par branche.',
                'headers' => ['name', 'description', 'price', 'duration_minutes', 'branch_code', 'branch_name'],
                'example' => ['Lavage premium', 'Lavage extérieur et intérieur', '120', '60', 'DT', 'Centre-ville'],
                'preview_columns' => ['name', 'description', 'price', 'duration_minutes', 'branch_label'],
            ],
        ];
    }

    private function sessionKey(string $resource): string
    {
        return 'imports.preview.'.$resource;
    }
}
