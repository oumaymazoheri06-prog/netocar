<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\ActivityLog;
use App\Models\agencies;
use App\Models\Branch;
use App\Models\clients;
use App\Models\employees;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

abstract class Controller
{
    protected function currentAgency(): agencies
    {
        $agency = auth()->user()?->agency;

        abort_unless($agency, 403, 'Vous n’êtes rattaché à aucune agence.');

        return $agency;
    }

    protected function requiredAgencyId(): int
    {
        return (int) $this->currentAgency()->id;
    }

    protected function userIsAdmin(): bool
    {
        return auth()->user()?->role === UserRole::Admin->value;
    }

    protected function userIsManager(): bool
    {
        return auth()->user()?->role === UserRole::Manager->value;
    }

    protected function userIsStaff(): bool
    {
        return auth()->user()?->role === UserRole::Staff->value;
    }

    protected function requireAdminUser(): void
    {
        abort_unless($this->userIsAdmin(), 403, 'Accès non autorisé.');
    }

    protected function requireManagerUser(): void
    {
        abort_unless($this->userIsManager(), 403, 'Accès non autorisé.');
    }

    protected function currentStaffEmployee(): employees
    {
        $user = auth()->user();
        $agency = $this->currentAgency();
        $employee = $user?->staffEmployee;

        abort_unless(
            $this->userIsStaff() && $employee && (int) $employee->agency_id === (int) $agency->id,
            403,
            'Aucun profil employé n’est lié à ce compte personnel.'
        );

        return $employee;
    }

    protected function requireSameAgency(Model $model): void
    {
        abort_unless(
            (int) $model->getAttribute('agency_id') === $this->requiredAgencyId(),
            403,
            'Accès non autorisé.'
        );
    }

    protected function requireVisibleAgencyRecord(Model $model): void
    {
        if ($this->userIsAdmin()) {
            return;
        }

        $this->requireSameAgency($model);
    }

    protected function branchOptions(int $agencyId, bool $includeInactive = true)
    {
        return Branch::where('agency_id', $agencyId)
            ->when(! $includeInactive, fn ($query) => $query->where('is_active', true))
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();
    }

    protected function selectedBranchFilter(Request $request, int $agencyId): ?int
    {
        $branchId = $request->integer('branch_id') ?: null;

        if ($branchId && ! Branch::where('agency_id', $agencyId)->whereKey($branchId)->exists()) {
            abort(404);
        }

        return $branchId;
    }

    protected function ensureBranchCompatible(?int $branchId, Model $record, string $label): void
    {
        $recordBranchId = $record->getAttribute('branch_id');

        if (! $branchId || ! $recordBranchId || (int) $recordBranchId === (int) $branchId) {
            return;
        }

        throw ValidationException::withMessages([
            'branch_id' => "L’élément sélectionné ({$label}) appartient à une autre branche.",
        ]);
    }

    protected function enforceAgencyPlanLimit(string $limitKey, int $currentCount, string $label): void
    {
        $agency = $this->currentAgency();
        $limit = data_get($agency->plan_limits, $limitKey);

        if ($limit === null || $currentCount < (int) $limit) {
            return;
        }

        throw ValidationException::withMessages([
            'plan' => "Votre plan {$agency->plan_name} autorise {$limit} {$label}. Passez à un plan supérieur pour en ajouter davantage.",
        ]);
    }

    protected function activeBranchRule(int $agencyId)
    {
        return Rule::exists('branches', 'id')->where(
            fn ($query) => $query->where('agency_id', $agencyId)->where('is_active', true)
        );
    }

    protected function clientRules(int $agencyId): array
    {
        return [
            'client_id' => [
                'nullable',
                Rule::exists('clients', 'id')->where(
                    fn ($query) => $query->where('agency_id', $agencyId)->whereNull('deleted_at')
                ),
            ],
            'client_name' => 'required_without:client_id|nullable|string|max:255',
            'client_phone' => ['required_without:client_id', 'nullable', 'string', 'max:30', 'regex:/\d/'],
            'client_email' => 'nullable|email|max:255',
        ];
    }

    protected function resolveClient(Request $request, int $agencyId): clients
    {
        $normalizedPhone = clients::normalizePhone($request->string('client_phone')->toString());
        $client = null;

        if ($request->filled('client_id')) {
            $candidate = clients::where('agency_id', $agencyId)->findOrFail($request->integer('client_id'));

            if (! $normalizedPhone || $candidate->normalized_phone === $normalizedPhone) {
                $client = $candidate;
            }
        }

        if (! $client && $normalizedPhone) {
            $client = clients::where('agency_id', $agencyId)
                ->where('normalized_phone', $normalizedPhone)
                ->first();
        }

        if ($client) {
            return $client;
        }

        $email = $request->filled('client_email')
            ? strtolower(trim($request->string('client_email')->toString()))
            : null;

        if ($email && clients::where('agency_id', $agencyId)->where('email', $email)->exists()) {
            throw ValidationException::withMessages([
                'client_email' => 'Cette adresse email appartient déjà à un autre client.',
            ]);
        }

        $this->enforceAgencyPlanLimit(
            'clients',
            clients::where('agency_id', $agencyId)->count(),
            'clients'
        );

        try {
            $client = clients::create([
                'agency_id' => $agencyId,
                'branch_id' => null,
                'name' => trim($request->string('client_name')->toString()),
                'email' => $email,
                'phone_number' => trim($request->string('client_phone')->toString()),
                'normalized_phone' => $normalizedPhone,
            ]);
        } catch (\Illuminate\Database\UniqueConstraintViolationException) {
            $client = clients::where('agency_id', $agencyId)
                ->where('normalized_phone', $normalizedPhone)
                ->firstOrFail();
        }

        if ($client->wasRecentlyCreated) {
            $this->logActivity('client.created', $client);
        }

        return $client;
    }

    protected function activityChanges(array $before, array $after): array
    {
        $changes = [];

        foreach ($after as $field => $afterValue) {
            $beforeValue = $before[$field] ?? null;

            if ($beforeValue == $afterValue) {
                continue;
            }

            $changes[$field] = [
                'from' => $this->activityValue($beforeValue),
                'to' => $this->activityValue($afterValue),
            ];
        }

        return $changes;
    }

    protected function logActivity(
        string $action,
        Model $subject,
        array $changes = [],
        ?int $agencyId = null,
        ?string $subjectLabel = null,
        array $metadata = []
    ): void {
        $user = auth()->user();
        $agencyId ??= $this->activityAgencyId($subject);
        $agencyName = $this->activityAgencyName($subject, $agencyId);

        ActivityLog::create([
            'agency_id' => $agencyId,
            'agency_name' => $agencyName,
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'user_role' => $user?->role,
            'action' => $action,
            'subject_type' => class_basename($subject),
            'subject_id' => $subject->getKey(),
            'subject_label' => $subjectLabel ?? $this->activitySubjectLabel($subject),
            'changes' => $changes ?: null,
            'metadata' => $metadata ? $this->activityValue($metadata) : null,
            'ip_address' => request()?->ip(),
            'user_agent' => Str::limit((string) request()?->userAgent(), 500, ''),
        ]);
    }

    private function activityAgencyId(Model $subject): ?int
    {
        if ($subject instanceof agencies) {
            return (int) $subject->id;
        }

        $agencyId = $subject->getAttribute('agency_id') ?? auth()->user()?->agency_id;

        return $agencyId ? (int) $agencyId : null;
    }

    private function activityAgencyName(Model $subject, ?int $agencyId): ?string
    {
        if ($subject instanceof agencies) {
            return $subject->name;
        }

        if ($subject->relationLoaded('agency') && $subject->getRelation('agency')) {
            return $subject->getRelation('agency')->name;
        }

        if (! $agencyId) {
            return null;
        }

        return agencies::find($agencyId)?->name;
    }

    private function activitySubjectLabel(Model $subject): string
    {
        $type = Str::headline(class_basename($subject));
        $identifier = $subject->getKey() ? "#{$subject->getKey()}" : '';

        foreach (['name', 'email', 'plate_number', 'status'] as $attribute) {
            $value = $subject->getAttribute($attribute);

            if ($value) {
                return trim("{$type} {$identifier} - {$value}");
            }
        }

        return trim("{$type} {$identifier}");
    }

    private function activityValue(mixed $value): mixed
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if ($value instanceof Model) {
            return $value->getKey();
        }

        if (is_array($value)) {
            return collect($value)
                ->map(fn ($item) => $this->activityValue($item))
                ->all();
        }

        return $value;
    }
}
