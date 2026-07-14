<?php

namespace App\Http\Controllers;

use App\Enums\ReservationStatus;
use App\Enums\TicketStatus;
use App\Models\agencies;
use App\Models\clients;
use App\Models\employees;
use App\Models\reservations;
use App\Models\services;
use App\Models\Ticket;
use App\Services\ReservationAvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ReservationsController extends Controller
{
    public function __construct(private readonly ReservationAvailabilityService $availability) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $agencyId = $this->requiredAgencyId();
        $branches = $this->branchOptions($agencyId, false);
        $selectedBranchId = null;

        $query = reservations::with(['client', 'service', 'employee', 'ticket'])
            ->with('branch')
            ->where('agency_id', $agencyId);

        if ($this->userIsStaff()) {
            $query->where('employee_id', $this->currentStaffEmployee()->id);
        } else {
            $selectedBranchId = $this->selectedBranchFilter($request, $agencyId);
            $query->when($selectedBranchId, fn ($query) => $query->where('branch_id', $selectedBranchId));
        }

        $reservations = $query->latest('reservation_date')->get();

        return view('reservations.index', compact('reservations', 'branches', 'selectedBranchId'));

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->requireManagerUser();

        $agencyId = $this->requiredAgencyId();

        $branches = $this->branchOptions($agencyId);
        $clients = clients::with('branch')->where('agency_id', $agencyId)->get();
        $employees = employees::with('branch')->where('agency_id', $agencyId)
            ->where(fn ($query) => $query->whereNull('branch_id')->orWhereHas('branch', fn ($branch) => $branch->where('is_active', true)))
            ->get();
        $services = services::with('branch')->where('agency_id', $agencyId)
            ->where(fn ($query) => $query->whereNull('branch_id')->orWhereHas('branch', fn ($branch) => $branch->where('is_active', true)))
            ->get();

        return view('reservations.create', compact('clients', 'employees', 'services', 'branches'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->requireManagerUser();

        $agencyId = $this->requiredAgencyId();

        $request->validate([
            ...$this->clientRules($agencyId),
            'service_id' => ['required', Rule::exists('services', 'id')->where(fn ($query) => $query->where('agency_id', $agencyId))],
            'employee_id' => ['nullable', Rule::exists('employees', 'id')->where(fn ($query) => $query->where('agency_id', $agencyId)->whereNull('deleted_at'))],
            'vehicle_type' => 'required|in:car,motorcycle,van',
            'plate_number' => 'nullable|string|max:255',
            'reservation_date' => 'required|date|after_or_equal:now',
            'branch_id' => ['nullable', $this->activeBranchRule($agencyId)],
        ]);

        $service = services::where('agency_id', $agencyId)->findOrFail($request->service_id);
        $employee = $request->filled('employee_id')
            ? employees::where('agency_id', $agencyId)->findOrFail($request->employee_id)
            : null;
        $branchId = $request->integer('branch_id') ?: $employee?->branch_id ?: $service->branch_id;

        $this->ensureBranchCompatible($branchId, $service, 'service');
        if ($employee) {
            $this->ensureBranchCompatible($branchId, $employee, 'employé');
        }

        DB::transaction(function () use ($request, $agencyId, $branchId, $service) {
            agencies::whereKey($agencyId)->lockForUpdate()->firstOrFail();
            $this->availability->assertAvailable(
                $agencyId,
                $branchId,
                $request->integer('employee_id') ?: null,
                $request->reservation_date,
                $service->duration_minutes,
            );
            $reservationMonth = Carbon::parse($request->reservation_date);
            $this->enforceAgencyPlanLimit(
                'reservations_per_month',
                reservations::withTrashed()->where('agency_id', $agencyId)
                    ->whereBetween('reservation_date', [$reservationMonth->copy()->startOfMonth(), $reservationMonth->copy()->endOfMonth()])
                    ->count(),
                'réservations ce mois-ci'
            );

            $client = $this->resolveClient($request, $agencyId);

            $reservation = reservations::create([
                'client_id' => $client->id,
                'service_id' => $request->service_id,
                'employee_id' => $request->employee_id,
                'vehicle_type' => $request->vehicle_type,
                'plate_number' => $request->plate_number,
                'reservation_date' => $request->reservation_date,
                'duration_minutes' => $service->duration_minutes,
                'agency_id' => $agencyId,
                'branch_id' => $branchId,
                'status' => 'pending',
            ]);

            $this->logActivity('reservation.created', $reservation);
        });

        return redirect()->route('reservations.index')
            ->with('success', 'Réservation créée avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(reservations $reservation)
    {
        $this->requireVisibleReservation($reservation);
        $reservation->load(['client', 'service', 'employee', 'ticket', 'branch']);

        return view('reservations.show', ['reservations' => $reservation]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(reservations $reservation)
    {
        $this->requireManagerUser();
        $this->requireSameAgency($reservation);

        $agencyId = $this->requiredAgencyId();
        $branches = $this->branchOptions($agencyId, false);
        $clients = clients::with('branch')->where('agency_id', $agencyId)->get();
        $employees = employees::with('branch')->where('agency_id', $agencyId)
            ->where(fn ($query) => $query->whereNull('branch_id')->orWhereHas('branch', fn ($branch) => $branch->where('is_active', true)))
            ->get();
        $services = services::with('branch')->where('agency_id', $agencyId)
            ->where(fn ($query) => $query->whereNull('branch_id')->orWhereHas('branch', fn ($branch) => $branch->where('is_active', true)))
            ->get();

        return view('reservations.edit', [
            'reservation' => $reservation,
            'clients' => $clients,
            'employees' => $employees,
            'services' => $services,
            'branches' => $branches,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, reservations $reservation)
    {
        $this->requireManagerUser();
        $this->requireSameAgency($reservation);
        $agencyId = $this->requiredAgencyId();

        $request->validate([
            ...$this->clientRules($agencyId),
            'service_id' => ['required', Rule::exists('services', 'id')->where(fn ($query) => $query->where('agency_id', $agencyId))],
            'employee_id' => ['nullable', Rule::exists('employees', 'id')->where(fn ($query) => $query->where('agency_id', $agencyId)->whereNull('deleted_at'))],
            'vehicle_type' => 'required|in:car,motorcycle,van',
            'plate_number' => 'nullable|string|max:255',
            'reservation_date' => 'required|date',
            'status' => 'required|in:pending,confirmed,cancelled',
            'branch_id' => ['nullable', $this->activeBranchRule($agencyId)],
        ]);

        $service = services::where('agency_id', $agencyId)->findOrFail($request->service_id);
        $employee = $request->filled('employee_id')
            ? employees::where('agency_id', $agencyId)->findOrFail($request->employee_id)
            : null;
        $branchId = $request->integer('branch_id') ?: $employee?->branch_id ?: $service->branch_id;

        $this->ensureBranchCompatible($branchId, $service, 'service');
        if ($employee) {
            $this->ensureBranchCompatible($branchId, $employee, 'employé');
        }

        $before = $reservation->only([
            'client_id',
            'service_id',
            'employee_id',
            'vehicle_type',
            'plate_number',
            'reservation_date',
            'status',
            'branch_id',
        ]);
        $createdTicket = null;

        DB::transaction(function () use ($request, $reservation, $service, $branchId, $agencyId, &$createdTicket) {
            agencies::whereKey($agencyId)->lockForUpdate()->firstOrFail();
            $lockedReservation = reservations::whereKey($reservation->id)->lockForUpdate()->firstOrFail();
            $this->ensureReservationTransition($lockedReservation->status, $request->status);
            $this->enforceReservationMonthLimitOnMove($lockedReservation, $request->reservation_date, $agencyId);
            $this->availability->assertAvailable(
                $agencyId,
                $branchId,
                $request->integer('employee_id') ?: null,
                $request->reservation_date,
                $service->duration_minutes,
                $lockedReservation->id,
                $request->status,
            );

            $client = $this->resolveClient($request, $agencyId);
            $existingTicket = Ticket::withTrashed()->where('reservation_id', $lockedReservation->id)->lockForUpdate()->first();
            $willCreateTicket = $request->status === ReservationStatus::Confirmed->value && ! $existingTicket;
            $operationalChanges = $this->reservationOperationalChanges($lockedReservation, $request, $branchId);

            if ($existingTicket && in_array($existingTicket->status, [TicketStatus::Completed->value, TicketStatus::Cancelled->value], true) && $operationalChanges) {
                throw ValidationException::withMessages([
                    'status' => 'Cette réservation possède un ticket terminé ou annulé et ne peut plus être modifiée.',
                ]);
            }

            if ($request->status === ReservationStatus::Cancelled->value && $existingTicket?->status === TicketStatus::Completed->value) {
                throw ValidationException::withMessages([
                    'status' => 'Une prestation terminée ne peut plus être annulée.',
                ]);
            }

            $lockedReservation->update([
                'client_id' => $client->id,
                'service_id' => $request->service_id,
                'employee_id' => $request->employee_id,
                'vehicle_type' => $request->vehicle_type,
                'plate_number' => $request->plate_number,
                'reservation_date' => $request->reservation_date,
                'duration_minutes' => $service->duration_minutes,
                'status' => $request->status,
                'branch_id' => $branchId,
            ]);

            if ($willCreateTicket) {
                $this->enforceAgencyPlanLimit(
                    'tickets_per_month',
                    Ticket::withTrashed()->where('agency_id', $agencyId)
                        ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
                        ->count(),
                    'tickets ce mois-ci'
                );

                $createdTicket = Ticket::create([
                    'service_id' => $service->id,
                    'agency_id' => $lockedReservation->agency_id,
                    'branch_id' => $branchId,
                    'reservation_id' => $lockedReservation->id,
                    'client_id' => $client->id,
                    'employee_id' => $request->employee_id,
                    'vehicle_type' => $request->vehicle_type,
                    'plate_number' => $request->plate_number,
                    'status' => 'waiting',
                    'price' => $service->price,
                ]);
            } elseif ($existingTicket && ! $existingTicket->trashed()) {
                $this->synchronizeTicketWithReservation($existingTicket, $lockedReservation, $service, $client->id);
            }
        });

        $reservation->refresh();

        $changes = $this->activityChanges($before, $reservation->only([
            'client_id',
            'service_id',
            'employee_id',
            'vehicle_type',
            'plate_number',
            'reservation_date',
            'status',
            'branch_id',
        ]));

        if ($changes) {
            $this->logActivity('reservation.updated', $reservation, $changes);
        }

        if ($createdTicket) {
            $this->logActivity(
                'ticket.created_from_reservation',
                $createdTicket,
                metadata: ['reservation_id' => $reservation->id],
            );
        }

        return redirect()->route('reservations.index')
            ->with('success', 'Réservation mise à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(reservations $reservation)
    {
        $this->requireManagerUser();
        $this->requireSameAgency($reservation);

        $linkedTicket = $reservation->ticket;
        if ($linkedTicket && ! $linkedTicket->trashed()
            && in_array($linkedTicket->status, [TicketStatus::Waiting->value, TicketStatus::InProgress->value], true)) {
            return back()->with('error', 'Cette réservation possède un ticket actif. Annulez ou terminez le ticket avant de l’archiver.');
        }

        $this->logActivity(
            'reservation.deleted',
            $reservation,
            metadata: $reservation->only([
                'client_id',
                'service_id',
                'employee_id',
                'vehicle_type',
                'plate_number',
                'reservation_date',
                'status',
                'branch_id',
            ])
        );

        $reservation->delete();

        return redirect()->route('reservations.index')
            ->with('success', 'Réservation archivée avec succès.');

    }

    private function requireVisibleReservation(reservations $reservation): void
    {
        $this->requireSameAgency($reservation);

        if (! $this->userIsStaff()) {
            return;
        }

        abort_unless(
            (int) $reservation->employee_id === (int) $this->currentStaffEmployee()->id,
            403,
            'Accès non autorisé.'
        );
    }

    private function ensureReservationTransition(string $current, string $next): void
    {
        if (! ReservationStatus::from($current)->canTransitionTo(ReservationStatus::from($next))) {
            throw ValidationException::withMessages([
                'status' => 'Cette transition de statut n’est pas autorisée.',
            ]);
        }
    }

    private function enforceReservationMonthLimitOnMove(reservations $reservation, string $newDate, int $agencyId): void
    {
        $currentMonth = $reservation->reservation_date->format('Y-m');
        $targetMonth = Carbon::parse($newDate);

        if ($currentMonth === $targetMonth->format('Y-m')) {
            return;
        }

        $this->enforceAgencyPlanLimit(
            'reservations_per_month',
            reservations::withTrashed()
                ->where('agency_id', $agencyId)
                ->whereBetween('reservation_date', [$targetMonth->copy()->startOfMonth(), $targetMonth->copy()->endOfMonth()])
                ->count(),
            'réservations ce mois-ci'
        );
    }

    private function reservationOperationalChanges(reservations $reservation, Request $request, ?int $branchId): bool
    {
        return (int) $reservation->service_id !== $request->integer('service_id')
            || (int) $reservation->employee_id !== ($request->integer('employee_id') ?: 0)
            || (string) $reservation->vehicle_type !== (string) $request->vehicle_type
            || (string) $reservation->plate_number !== (string) $request->plate_number
            || (int) $reservation->branch_id !== (int) $branchId;
    }

    private function synchronizeTicketWithReservation(Ticket $ticket, reservations $reservation, services $service, int $clientId): void
    {
        $before = $ticket->only(['client_id', 'service_id', 'employee_id', 'branch_id', 'vehicle_type', 'plate_number', 'price', 'status']);
        $attributes = [
            'client_id' => $clientId,
            'service_id' => $service->id,
            'employee_id' => $reservation->employee_id,
            'branch_id' => $reservation->branch_id,
            'vehicle_type' => $reservation->vehicle_type,
            'plate_number' => $reservation->plate_number,
            'price' => $service->price,
        ];

        if ($reservation->status === ReservationStatus::Cancelled->value
            && in_array($ticket->status, [TicketStatus::Waiting->value, TicketStatus::InProgress->value], true)) {
            $attributes['status'] = TicketStatus::Cancelled->value;
        }

        $ticket->update($attributes);
        $changes = $this->activityChanges($before, $ticket->only(array_keys($before)));

        if ($changes) {
            $this->logActivity('ticket.synchronized_from_reservation', $ticket, $changes);
        }
    }
}
