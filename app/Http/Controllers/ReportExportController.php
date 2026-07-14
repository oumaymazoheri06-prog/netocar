<?php

namespace App\Http\Controllers;

use App\Enums\ReservationStatus;
use App\Enums\TicketStatus;
use App\Models\agencies;
use App\Models\Branch;
use App\Models\clients;
use App\Models\employees;
use App\Models\Payment;
use App\Models\reservations;
use App\Models\services;
use App\Models\Ticket;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ReportExportController extends Controller
{
    public function download(Request $request, string $resource)
    {
        $report = $this->buildReport($request, $resource);

        return Pdf::loadView('exports.table-report', $report)
            ->setPaper('a4', $report['orientation'])
            ->download($report['filename']);
    }

    public function print(Request $request, string $resource)
    {
        $report = $this->buildReport($request, $resource);
        $report['preview'] = true;

        return view('exports.table-report', $report);
    }

    public function csv(Request $request, string $resource)
    {
        $report = $this->buildReport($request, $resource);
        $filename = Str::replaceLast('.pdf', '.csv', $report['filename']);

        return response()->streamDownload(function () use ($report) {
            $output = fopen('php://output', 'w');

            fputcsv($output, $report['columns']);

            foreach ($report['rows'] as $row) {
                fputcsv($output, collect($row)->map(fn ($value) => (string) $value)->all());
            }

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function buildReport(Request $request, string $resource): array
    {
        if ($this->userIsStaff() && ! in_array($resource, ['reservations', 'tickets'], true)) {
            abort(403, 'Accès non autorisé.');
        }

        $scope = $request->string('scope')->toString() ?: 'all';
        $monthInput = $request->string('month')->toString();
        $month = $this->resolveMonth($monthInput);
        $scopeLabel = $scope === 'month' ? $month->translatedFormat('F Y') : 'Tous les enregistrements';
        $generatedAt = now()->format('d/m/Y H:i');

        return match ($resource) {
            'agencies' => $this->agencyReport($scope, $month, $scopeLabel, $generatedAt),
            'branches' => $this->branchReport($scope, $month, $scopeLabel, $generatedAt),
            'clients' => $this->clientReport($scope, $month, $scopeLabel, $generatedAt),
            'employees' => $this->employeeReport($scope, $month, $scopeLabel, $generatedAt),
            'reservations' => $this->reservationReport($scope, $month, $scopeLabel, $generatedAt),
            'services' => $this->serviceReport($scope, $month, $scopeLabel, $generatedAt),
            'payments' => $this->paymentReport($scope, $month, $scopeLabel, $generatedAt),
            'tickets' => $this->ticketReport($scope, $month, $scopeLabel, $generatedAt),
            default => abort(404),
        };
    }

    private function agencyReport(string $scope, Carbon $month, string $scopeLabel, string $generatedAt): array
    {
        $this->ensureAdmin();

        $query = agencies::query()->latest();
        $query = $this->applyScope($query, $scope, $month);
        $records = $query->get();

        return $this->reportPayload(
            title: 'Agences',
            subtitle: 'Annuaire des agences et aperçu des plans',
            resource: 'agencies',
            orientation: 'landscape',
            columns: ['ID', 'Nom', 'Adresse', 'Téléphone', 'Email', 'Plan'],
            rows: $records->map(fn (agencies $agency) => [
                $agency->id,
                $agency->name,
                $agency->address,
                $agency->phone_number,
                $agency->email,
                $agency->plan_name,
            ]),
            scopeLabel: $scopeLabel,
            generatedAt: $generatedAt,
        );
    }

    private function branchReport(string $scope, Carbon $month, string $scopeLabel, string $generatedAt): array
    {
        $this->ensureManagerOrAdmin();
        $agencyId = $this->currentAgencyId();

        $query = Branch::query()->where('agency_id', $agencyId)->latest();
        $query = $this->applyScope($query, $scope, $month);
        $records = $query->get();

        return $this->reportPayload(
            title: 'Branches',
            subtitle: 'Sites de l’agence et statut d’activité',
            resource: 'branches',
            orientation: 'landscape',
            columns: ['ID', 'Nom', 'Code', 'Adresse', 'Téléphone', 'Statut'],
            rows: $records->map(fn (Branch $branch) => [
                $branch->id,
                $branch->name,
                $branch->code ?? 'N/D',
                $branch->address ?? 'N/D',
                $branch->phone_number ?? 'N/D',
                $branch->is_active ? 'Active' : 'Inactive',
            ]),
            scopeLabel: $scopeLabel,
            generatedAt: $generatedAt,
        );
    }

    private function clientReport(string $scope, Carbon $month, string $scopeLabel, string $generatedAt): array
    {
        $this->ensureManagerOrAdmin();
        $agencyId = $this->currentAgencyId();

        $query = clients::query()->with('branch')->where('agency_id', $agencyId)->latest();
        $query = $this->applyScope($query, $scope, $month);
        $records = $query->get();

        return $this->reportPayload(
            title: 'Clients',
            subtitle: 'Coordonnées des clients liés à votre agence',
            resource: 'clients',
            orientation: 'portrait',
            columns: ['ID', 'Nom', 'Branche', 'Email', 'Téléphone'],
            rows: $records->map(fn (clients $client) => [
                $client->id,
                $client->name,
                $client->branch?->name ?? 'Toute l’agence',
                $client->email,
                $client->phone,
            ]),
            scopeLabel: $scopeLabel,
            generatedAt: $generatedAt,
        );
    }

    private function employeeReport(string $scope, Carbon $month, string $scopeLabel, string $generatedAt): array
    {
        $this->ensureManagerOrAdmin();
        $agencyId = $this->currentAgencyId();

        $query = employees::query()->with('branch')->where('agency_id', $agencyId)->latest();
        $query = $this->applyScope($query, $scope, $month);
        $records = $query->get();

        return $this->reportPayload(
            title: 'Employés',
            subtitle: 'Membres de l’équipe et postes',
            resource: 'employees',
            orientation: 'landscape',
            columns: ['ID', 'Nom', 'Branche', 'Email', 'Poste', 'Salaire', 'Téléphone'],
            rows: $records->map(fn (employees $employee) => [
                $employee->id,
                $employee->name,
                $employee->branch?->name ?? 'Toute l’agence',
                $employee->email,
                $employee->job_title,
                $employee->salary,
                $employee->phone_number,
            ]),
            scopeLabel: $scopeLabel,
            generatedAt: $generatedAt,
        );
    }

    private function reservationReport(string $scope, Carbon $month, string $scopeLabel, string $generatedAt): array
    {
        $agencyId = $this->currentAgencyId();

        $query = reservations::query()
            ->with(['client', 'service', 'employee', 'branch'])
            ->where('agency_id', $agencyId)
            ->latest();

        if ($this->userIsStaff()) {
            $query->where('employee_id', $this->currentStaffEmployee()->id);
        }

        $query = $this->applyScope($query, $scope, $month, 'reservation_date');
        $records = $query->get();

        return $this->reportPayload(
            title: 'Réservations',
            subtitle: 'Réservations et affectations de service',
            resource: 'reservations',
            orientation: 'landscape',
            columns: ['ID', 'Branche', 'Client', 'Service', 'Employé', 'Véhicule', 'Immatriculation', 'Date', 'Statut'],
            rows: $records->map(fn (reservations $reservation) => [
                $reservation->id,
                $reservation->branch?->name ?? 'Toute l’agence',
                $reservation->client?->name ?? 'N/D',
                $reservation->service?->name ?? 'N/D',
                $reservation->employee?->name ?? 'Non assigné',
                $this->vehicleLabel($reservation->vehicle_type),
                $reservation->plate_number ?? 'N/D',
                $reservation->reservation_date?->format('d/m/Y H:i'),
                $this->reservationStatusLabel($reservation->status),
            ]),
            scopeLabel: $scopeLabel,
            generatedAt: $generatedAt,
        );
    }

    private function serviceReport(string $scope, Carbon $month, string $scopeLabel, string $generatedAt): array
    {
        $this->ensureManagerOrAdmin();
        $agencyId = $this->currentAgencyId();

        $query = services::query()->with(['agency', 'branch'])->where('agency_id', $agencyId)->latest();
        $query = $this->applyScope($query, $scope, $month);
        $records = $query->get();

        return $this->reportPayload(
            title: 'Services',
            subtitle: 'Services proposés et tarifs',
            resource: 'services',
            orientation: 'landscape',
            columns: ['ID', 'Branche', 'Nom', 'Description', 'Prix', 'Photo'],
            rows: $records->map(fn (services $service) => [
                $service->id,
                $service->branch?->name ?? 'Toute l’agence',
                $service->name,
                Str::limit($service->description, 90),
                $service->price,
                $service->photo ? 'Jointe' : 'Aucune',
            ]),
            scopeLabel: $scopeLabel,
            generatedAt: $generatedAt,
        );
    }

    private function paymentReport(string $scope, Carbon $month, string $scopeLabel, string $generatedAt): array
    {
        abort_if($this->userIsStaff(), 403, 'Accès non autorisé.');

        $query = Payment::query()->with('agency')->latest();

        if (! $this->userIsAdmin()) {
            $query->where('agency_id', $this->currentAgencyId());
        }

        $query = $this->applyScope($query, $scope, $month);
        $records = $query->get();

        return $this->reportPayload(
            title: 'Paiements',
            subtitle: 'Paiements annuels et modes de règlement',
            resource: 'payments',
            orientation: 'landscape',
            columns: ['ID', 'Agence', 'Plan', 'Montant', 'Reçu', 'Mode', 'Statut', 'Créé le'],
            rows: $records->map(fn (Payment $payment) => [
                $payment->id,
                $payment->agency?->name ?? 'N/D',
                data_get(config('netocar.plans'), "{$payment->plan}.label", ucfirst((string) $payment->plan)),
                'MAD '.number_format($payment->amount, 2),
                $payment->receipt_photo ? 'Joint' : 'Aucun',
                ucfirst($payment->payment_method),
                $this->paymentStatusLabel($payment->status),
                $payment->created_at?->format('d/m/Y'),
            ]),
            scopeLabel: $scopeLabel,
            generatedAt: $generatedAt,
        );
    }

    private function ticketReport(string $scope, Carbon $month, string $scopeLabel, string $generatedAt): array
    {
        $agencyId = $this->currentAgencyId();

        $query = Ticket::query()
            ->with(['service', 'employee', 'reservation', 'branch'])
            ->where('agency_id', $agencyId)
            ->latest();

        if ($this->userIsStaff()) {
            $query->where('employee_id', $this->currentStaffEmployee()->id);
        }

        $query = $this->applyScope($query, $scope, $month);
        $records = $query->get();

        return $this->reportPayload(
            title: 'Tickets',
            subtitle: 'File des tickets et suivi de progression',
            resource: 'tickets',
            orientation: 'landscape',
            columns: ['ID', 'Branche', 'Service', 'Employé', 'Véhicule', 'Immatriculation', 'Statut', 'Prix', 'Créé le', 'Terminé le'],
            rows: $records->map(fn (Ticket $ticket) => [
                $ticket->id,
                $ticket->branch?->name ?? 'Toute l’agence',
                $ticket->service?->name ?? 'N/D',
                $ticket->employee?->name ?? 'Non assigné',
                $this->vehicleLabel($ticket->vehicle_type),
                $ticket->plate_number ?? 'N/D',
                $this->ticketStatusLabel($ticket->status),
                'MAD '.number_format($ticket->price, 2),
                $ticket->created_at?->format('d/m/Y'),
                $ticket->completed_at?->format('d/m/Y H:i') ?? 'N/D',
            ]),
            scopeLabel: $scopeLabel,
            generatedAt: $generatedAt,
        );
    }

    private function reportPayload(
        string $title,
        string $subtitle,
        string $resource,
        string $orientation,
        array $columns,
        Collection $rows,
        string $scopeLabel,
        string $generatedAt,
    ): array {
        return [
            'title' => $title,
            'subtitle' => $subtitle,
            'resource' => $resource,
            'orientation' => $orientation,
            'columns' => $columns,
            'rows' => $rows,
            'scopeLabel' => $scopeLabel,
            'generatedAt' => $generatedAt,
            'filename' => Str::slug($resource.'-'.$scopeLabel).'.pdf',
            'preview' => false,
        ];
    }

    private function applyScope($query, string $scope, Carbon $month, string $dateColumn = 'created_at')
    {
        if ($scope !== 'month') {
            return $query;
        }

        return $query->whereYear($dateColumn, $month->year)
            ->whereMonth($dateColumn, $month->month);
    }

    private function currentAgencyId(): int
    {
        $agencyId = auth()->user()?->agency?->id;

        abort_unless($agencyId, 403, 'Vous n’êtes rattaché à aucune agence.');

        return (int) $agencyId;
    }

    private function ensureAdmin(): void
    {
        abort_unless(auth()->user()?->role === 'admin', 403);
    }

    private function ensureManagerOrAdmin(): void
    {
        abort_unless(in_array(auth()->user()?->role, ['admin', 'manager'], true), 403);
    }

    private function resolveMonth(?string $monthInput): Carbon
    {
        if (! $monthInput) {
            return Carbon::now();
        }

        try {
            return Carbon::createFromFormat('Y-m', $monthInput)->startOfMonth();
        } catch (\Throwable) {
            return Carbon::now();
        }
    }

    private function vehicleLabel(?string $value): string
    {
        return [
            'car' => 'Voiture',
            'motorcycle' => 'Moto',
            'van' => 'Utilitaire',
        ][$value] ?? 'N/D';
    }

    private function reservationStatusLabel(?string $value): string
    {
        return ReservationStatus::tryFrom((string) $value)?->label() ?? (string) $value;
    }

    private function ticketStatusLabel(?string $value): string
    {
        return TicketStatus::tryFrom((string) $value)?->label() ?? (string) $value;
    }

    private function paymentStatusLabel(?string $value): string
    {
        return [
            'pending' => 'En attente',
            'completed' => 'Payé',
            'failed' => 'Échoué',
        ][$value] ?? (string) $value;
    }
}
