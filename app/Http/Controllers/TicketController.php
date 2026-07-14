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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TicketController extends Controller
{
    private const MANAGER_STATUSES = ['waiting', 'in_progress', 'completed', 'cancelled'];

    private const STAFF_STATUSES = ['waiting', 'in_progress', 'completed'];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $agencyId = $this->requiredAgencyId();
        $branches = $this->branchOptions($agencyId);
        $selectedBranchId = null;
        $query = Ticket::with(['client', 'service', 'employee', 'reservation', 'branch'])
            ->where('agency_id', $agencyId);

        if ($this->userIsStaff()) {
            $query->where('employee_id', $this->currentStaffEmployee()->id);
        } else {
            $selectedBranchId = $this->selectedBranchFilter($request, $agencyId);
            $query->when($selectedBranchId, fn ($query) => $query->where('branch_id', $selectedBranchId));
        }

        if ($request->filled('period')) {
            if ($request->period === 'day') {
                $query->whereDate('created_at', today());
            }

            if ($request->period === 'week') {
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
            }

            if ($request->period === 'month') {
                $query->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
            }
        }

        if ($request->sort === 'oldest') {
            $query->orderBy('created_at', 'asc');
        } else {
            $query->latest();
        }

        $tickets = $query->get();

        return view('tickets.index', compact('tickets', 'branches', 'selectedBranchId'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->requireManagerUser();

        $agencyId = $this->requiredAgencyId();
        $branches = $this->branchOptions($agencyId, false);
        $services = services::with('branch')->where('agency_id', $agencyId)
            ->where(fn ($query) => $query->whereNull('branch_id')->orWhereHas('branch', fn ($branch) => $branch->where('is_active', true)))
            ->get();
        $employees = employees::with('branch')->where('agency_id', $agencyId)
            ->where(fn ($query) => $query->whereNull('branch_id')->orWhereHas('branch', fn ($branch) => $branch->where('is_active', true)))
            ->get();
        $clients = clients::where('agency_id', $agencyId)->orderBy('name')->get();

        return view('tickets.create', compact('services', 'employees', 'branches', 'clients'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->requireManagerUser();

        $agencyId = $this->requiredAgencyId();
        $validated = $request->validate([
            ...$this->clientRules($agencyId),
            'service_id' => ['required', Rule::exists('services', 'id')->where(fn ($query) => $query->where('agency_id', $agencyId))],
            'vehicle_type' => ['required', 'in:car,motorcycle,van'],
            'plate_number' => ['nullable', 'string', 'max:255'],
            'employee_id' => ['nullable', Rule::exists('employees', 'id')->where(fn ($query) => $query->where('agency_id', $agencyId))],
            'branch_id' => ['nullable', $this->activeBranchRule($agencyId)],
        ]);
        $service = services::where('agency_id', $agencyId)->findOrFail($validated['service_id']);
        $employee = ! empty($validated['employee_id'])
            ? employees::where('agency_id', $agencyId)->findOrFail($validated['employee_id'])
            : null;
        $branchId = ($validated['branch_id'] ?? null) ?: $employee?->branch_id ?: $service->branch_id;

        $this->ensureBranchCompatible($branchId, $service, 'service');

        if ($employee) {
            $this->ensureBranchCompatible($branchId, $employee, 'employé');
        }

        DB::transaction(function () use ($request, $agencyId, $validated, $branchId, $service) {
            agencies::whereKey($agencyId)->lockForUpdate()->firstOrFail();
            $this->enforceAgencyPlanLimit(
                'tickets_per_month',
                Ticket::withTrashed()->where('agency_id', $agencyId)
                    ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
                    ->count(),
                'tickets ce mois-ci'
            );

            $client = $this->resolveClient($request, $agencyId);
            $ticket = Ticket::create([
                'service_id' => $validated['service_id'],
                'client_id' => $client->id,
                'agency_id' => $agencyId,
                'branch_id' => $branchId,
                'employee_id' => $validated['employee_id'] ?? null,
                'vehicle_type' => $validated['vehicle_type'],
                'plate_number' => $validated['plate_number'] ?? null,
                'price' => $service->price,
                'status' => TicketStatus::Waiting->value,
            ]);

            $this->logActivity('ticket.created', $ticket);
        });

        return redirect()
            ->route('tickets.index')
            ->with('success', 'Ticket créé avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Ticket $ticket)
    {
        $this->requireVisibleTicket($ticket);
        $ticket->load(['client', 'service', 'employee', 'reservation', 'branch']);

        return view('tickets.show', compact('ticket'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ticket $ticket)
    {
        $this->requireManagerUser();
        $this->requireSameAgency($ticket);

        $agencyId = $this->requiredAgencyId();
        $branches = $this->branchOptions($agencyId);
        $services = services::with('branch')->where('agency_id', $agencyId)->get();
        $employees = employees::with('branch')->where('agency_id', $agencyId)->get();

        return view('tickets.edit', compact('ticket', 'services', 'employees', 'branches'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ticket $ticket)
    {
        $this->requireVisibleTicket($ticket);
        $agencyId = $this->requiredAgencyId();

        if ($this->userIsStaff()) {
            $validated = $request->validate([
                'status' => ['required', Rule::in(self::STAFF_STATUSES)],
            ]);

            $oldStatus = $ticket->status;
            $this->applyTicketTransition($ticket, $validated['status']);

            if ($oldStatus !== $ticket->status) {
                $this->logActivity(
                    'ticket.status_changed',
                    $ticket,
                    [
                        'status' => [
                            'from' => $oldStatus,
                            'to' => $ticket->status,
                        ],
                    ],
                );
            }

            return redirect()->route('tickets.index')->with('success', 'Statut du ticket mis à jour avec succès.');
        }

        $this->requireManagerUser();

        $validated = $request->validate([
            'employee_id' => ['nullable', Rule::exists('employees', 'id')->where(fn ($query) => $query->where('agency_id', $agencyId))],
            'status' => ['required', Rule::in(self::MANAGER_STATUSES)],
        ]);

        $employee = ! empty($validated['employee_id'])
            ? employees::where('agency_id', $agencyId)->findOrFail($validated['employee_id'])
            : null;
        $branchId = $ticket->branch_id ?: $employee?->branch_id;

        if ($employee) {
            $this->ensureBranchCompatible($branchId, $employee, 'employé');
        }

        $before = $ticket->only(['employee_id', 'status', 'branch_id']);

        $this->applyTicketTransition($ticket, $validated['status'], [
            'employee_id' => $request->has('employee_id') ? $validated['employee_id'] : $ticket->employee_id,
            'branch_id' => $branchId,
        ]);

        $changes = $this->activityChanges($before, $ticket->only(['employee_id', 'status', 'branch_id']));

        if ($changes) {
            $this->logActivity('ticket.updated', $ticket, $changes);
        }

        return redirect()->route('tickets.index')->with('success', 'Ticket mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ticket $ticket)
    {
        $this->requireManagerUser();
        $this->requireSameAgency($ticket);

        if ($ticket->reservation_id || ! in_array($ticket->status, [TicketStatus::Cancelled->value, TicketStatus::Completed->value], true)) {
            return back()->with('error', 'Seuls les tickets directs terminés ou annulés peuvent être archivés.');
        }

        $this->logActivity(
            'ticket.deleted',
            $ticket,
            metadata: $ticket->only([
                'service_id',
                'reservation_id',
                'employee_id',
                'vehicle_type',
                'plate_number',
                'status',
                'price',
                'branch_id',
            ])
        );

        $ticket->delete();

        return redirect()
            ->route('tickets.index')
            ->with('success', 'Ticket supprimé avec succès.');
    }

    private function requireVisibleTicket(Ticket $ticket): void
    {
        $this->requireSameAgency($ticket);

        if (! $this->userIsStaff()) {
            return;
        }

        abort_unless(
            (int) $ticket->employee_id === (int) $this->currentStaffEmployee()->id,
            403,
            'Accès non autorisé.'
        );
    }

    private function applyTicketTransition(Ticket $ticket, string $nextStatus, array $extra = []): void
    {
        DB::transaction(function () use ($ticket, $nextStatus, $extra) {
            $lockedTicket = Ticket::whereKey($ticket->id)->lockForUpdate()->firstOrFail();
            $current = TicketStatus::from($lockedTicket->status);
            $next = TicketStatus::from($nextStatus);

            if (! $current->canTransitionTo($next)) {
                throw ValidationException::withMessages([
                    'status' => 'Cette transition de ticket n’est pas autorisée.',
                ]);
            }

            $attributes = ['status' => $next->value, ...$extra];

            if ($next === TicketStatus::InProgress && ! $lockedTicket->started_at) {
                $attributes['started_at'] = now();
            }

            if ($next === TicketStatus::Completed && ! $lockedTicket->completed_at) {
                $attributes['started_at'] ??= $lockedTicket->started_at ?: now();
                $attributes['completed_at'] = now();
            }

            $lockedTicket->update($attributes);

            if ($next === TicketStatus::Cancelled && $lockedTicket->reservation_id) {
                $reservation = reservations::whereKey($lockedTicket->reservation_id)->lockForUpdate()->first();

                if ($reservation && ReservationStatus::from($reservation->status)->canTransitionTo(ReservationStatus::Cancelled)) {
                    $oldReservationStatus = $reservation->status;
                    $reservation->update(['status' => ReservationStatus::Cancelled->value]);
                    $this->logActivity('reservation.cancelled_from_ticket', $reservation, [
                        'status' => [
                            'from' => $oldReservationStatus,
                            'to' => ReservationStatus::Cancelled->value,
                        ],
                    ]);
                }
            }

            $ticket->setRawAttributes($lockedTicket->getAttributes(), true);
        });
    }
}
