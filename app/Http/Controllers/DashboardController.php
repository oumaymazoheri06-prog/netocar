<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\clients;
use App\Models\employees;
use App\Models\reservations;
use App\Models\services;
use App\Models\Ticket;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    public function __invoke()
    {
        if (auth()->user()->role === 'admin') {
            return view('dashboard', [
                'agenciesCount' => \App\Models\agencies::count(),
                'managersCount' => \App\Models\User::where('role', 'manager')->count(),
                'usersCount' => \App\Models\User::count(),
                'basicAgenciesCount' => \App\Models\agencies::where('package', 'basic')->count(),
                'standardAgenciesCount' => \App\Models\agencies::where('package', 'standard')->count(),
                'premiumAgenciesCount' => \App\Models\agencies::where('package', 'premium')->count(),
                'recentAgencies' => \App\Models\agencies::with('user')
                    ->latest()
                    ->take(6)
                    ->get(),
                'recentManagers' => \App\Models\User::with('agency')
                    ->where('role', 'manager')
                    ->latest()
                    ->take(6)
                    ->get(),
            ]);
        }

        if (auth()->user()->role === 'staff') {
            return $this->staffDashboard();
        }

        if (auth()->user()->role !== 'manager') {
            abort(403, 'Accès non autorisé.');
        }

        $agency = auth()->user()->agency;

        if (! $agency) {
            abort(403, 'Aucune agence n’est liée à ce manager.');
        }

        $nextYearlyFee = $agency->plan_amount;

        $branches = Branch::where('agency_id', $agency->id)
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();
        $services = services::with('branch')
            ->where('agency_id', $agency->id)
            ->get();
        $employees = employees::with('branch')
            ->where('agency_id', $agency->id)
            ->get();
        $tickets = Ticket::with(['client', 'service', 'employee', 'branch'])
            ->where('agency_id', $agency->id)
            ->get();
        $reservations = reservations::with(['client', 'service', 'employee', 'branch'])
            ->where('agency_id', $agency->id)
            ->get();

        $completedTickets = $tickets->where('status', 'completed');
        $confirmedReservations = $reservations->where('status', 'confirmed');
        $cancelledReservations = $reservations->where('status', 'cancelled');
        $activeTicketsCount = $tickets->whereIn('status', ['waiting', 'in_progress'])->count();
        $todayReservationsCount = $reservations
            ->filter(fn (reservations $reservation) => $reservation->reservation_date?->isSameDay(today()))
            ->count();

        $periodRevenue = function ($start, $end) use ($agency) {
            return Ticket::where('agency_id', $agency->id)
                ->where('status', 'completed')
                ->whereBetween('completed_at', [$start, $end])
                ->sum('price');
        };

        $dailyRevenue = collect(range(6, 0))->map(function ($daysAgo) use ($periodRevenue) {
            $date = now()->subDays($daysAgo);

            return [
                'label' => $date->translatedFormat('d M'),
                'total' => $periodRevenue($date->copy()->startOfDay(), $date->copy()->endOfDay()),
            ];
        });

        $monthlyRevenue = collect(range(11, 0))->map(function ($monthsAgo) use ($periodRevenue) {
            $date = now()->subMonthsNoOverflow($monthsAgo);

            return [
                'label' => $date->translatedFormat('M Y'),
                'total' => $periodRevenue($date->copy()->startOfMonth(), $date->copy()->endOfMonth()),
            ];
        });

        $yearlyRevenue = collect(range(4, 0))->map(function ($yearsAgo) use ($periodRevenue) {
            $date = now()->subYearsNoOverflow($yearsAgo);

            return [
                'label' => $date->format('Y'),
                'total' => $periodRevenue($date->copy()->startOfYear(), $date->copy()->endOfYear()),
            ];
        });

        $todayIncome = $periodRevenue(now()->startOfDay(), now()->endOfDay());
        $monthIncome = $periodRevenue(now()->startOfMonth(), now()->endOfMonth());
        $yearIncome = $periodRevenue(now()->startOfYear(), now()->endOfYear());
        $previousMonthIncome = $periodRevenue(
            now()->subMonthNoOverflow()->startOfMonth(),
            now()->subMonthNoOverflow()->endOfMonth()
        );

        $ticketStatusBreakdown = collect([
            'waiting' => 'En attente',
            'in_progress' => 'En cours',
            'completed' => 'Terminés',
            'cancelled' => 'Annulés',
        ])->map(function (string $label, string $status) use ($tickets) {
            return [
                'status' => $status,
                'label' => $label,
                'count' => $tickets->where('status', $status)->count(),
            ];
        })->values();

        $ticketStatusTotal = max(1, $ticketStatusBreakdown->sum('count'));

        $branchPerformance = $branches
            ->map(function (Branch $branch) use ($tickets, $completedTickets, $reservations) {
                $branchTickets = $tickets->where('branch_id', $branch->id);
                $branchCompletedTickets = $completedTickets->where('branch_id', $branch->id);
                $branchReservations = $reservations->where('branch_id', $branch->id);
                $activeLoad = $branchTickets->whereIn('status', ['waiting', 'in_progress'])->count();

                return [
                    'name' => $branch->name,
                    'status' => $branch->is_active ? 'Active' : 'Inactive',
                    'capacity' => max(1, (int) $branch->simultaneous_capacity),
                    'active_load' => $activeLoad,
                    'load_rate' => min(100, round(($activeLoad / max(1, (int) $branch->simultaneous_capacity)) * 100)),
                    'tickets' => $branchTickets->count(),
                    'reservations' => $branchReservations->count(),
                    'cancelled' => $branchReservations->where('status', 'cancelled')->count(),
                    'revenue' => (float) $branchCompletedTickets->sum('price'),
                ];
            });

        if ($tickets->whereNull('branch_id')->isNotEmpty() || $reservations->whereNull('branch_id')->isNotEmpty()) {
            $unassignedTickets = $tickets->whereNull('branch_id');
            $unassignedReservations = $reservations->whereNull('branch_id');

            $branchPerformance->push([
                'name' => 'Toute l’agence',
                'status' => 'Non assigné',
                'capacity' => null,
                'active_load' => $unassignedTickets->whereIn('status', ['waiting', 'in_progress'])->count(),
                'load_rate' => 0,
                'tickets' => $unassignedTickets->count(),
                'reservations' => $unassignedReservations->count(),
                'cancelled' => $unassignedReservations->where('status', 'cancelled')->count(),
                'revenue' => (float) $completedTickets->whereNull('branch_id')->sum('price'),
            ]);
        }

        $branchPerformance = $branchPerformance
            ->sortByDesc(fn (array $branch) => ($branch['revenue'] * 100000) + $branch['tickets'])
            ->values();

        $revenueByService = $services
            ->map(function (services $service) use ($completedTickets, $confirmedReservations) {
                $serviceTickets = $completedTickets->where('service_id', $service->id);
                $serviceReservations = $confirmedReservations->where('service_id', $service->id);

                return [
                    'name' => $service->name,
                    'branch' => $service->branch?->name ?? 'Toute l’agence',
                    'ticket_count' => $serviceTickets->count(),
                    'reservation_count' => $serviceReservations->count(),
                    'demand' => $serviceTickets->count() + $serviceReservations->count(),
                    'revenue' => (float) $serviceTickets->sum('price'),
                ];
            })
            ->sortByDesc(fn (array $service) => ($service['revenue'] * 100000) + $service['demand'])
            ->take(6)
            ->values();

        $employeePerformance = $employees
            ->map(function (employees $employee) use ($tickets, $reservations) {
                $employeeTickets = $tickets->where('employee_id', $employee->id);
                $completedEmployeeTickets = $employeeTickets->where('status', 'completed');

                return [
                    'name' => $employee->name,
                    'job_title' => $employee->job_title,
                    'branch' => $employee->branch?->name ?? 'Toute l’agence',
                    'completed' => $completedEmployeeTickets->count(),
                    'active' => $employeeTickets->whereIn('status', ['waiting', 'in_progress'])->count(),
                    'reservations' => $reservations->where('employee_id', $employee->id)->count(),
                    'revenue' => (float) $completedEmployeeTickets->sum('price'),
                    'average_minutes' => $this->averageTicketMinutes($completedEmployeeTickets),
                ];
            })
            ->sortByDesc(fn (array $employee) => ($employee['completed'] * 100000) + $employee['revenue'])
            ->take(6)
            ->values();

        $busiestDays = $this->busiestDays($tickets, $reservations);
        $busiestDay = $busiestDays->sortByDesc('total')->first();
        $averageTicketMinutes = $this->averageTicketMinutes($completedTickets);
        $activeCustomersCount = clients::where('agency_id', $agency->id)
            ->whereHas('reservation', fn ($query) => $query->where('status', '!=', 'cancelled'))
            ->count();
        $recurringCustomersCount = clients::withCount([
            'reservation' => fn ($query) => $query->where('status', '!=', 'cancelled'),
        ])
            ->where('agency_id', $agency->id)
            ->get()
            ->filter(fn (clients $client) => $client->reservation_count >= 2)
            ->count();
        $recurringRate = $activeCustomersCount > 0
            ? round(($recurringCustomersCount / $activeCustomersCount) * 100, 1)
            : 0;
        $cancellationRate = $reservations->count() > 0
            ? round(($cancelledReservations->count() / $reservations->count()) * 100, 1)
            : 0;
        $completionRate = $tickets->count() > 0
            ? round(($completedTickets->count() / $tickets->count()) * 100, 1)
            : 0;

        return view('dashboard.manager', [
            'agency' => $agency,
            'planLabel' => config("netocar.plans.{$agency->package}.label", ucfirst($agency->package)),
            'nextYearlyFee' => $nextYearlyFee,
            'nextBillingDate' => $agency->license_expires_at?->translatedFormat('d F Y') ?? now()->addYearNoOverflow()->translatedFormat('d F Y'),
            'branchesCount' => $branches->count(),
            'clientsCount' => clients::where('agency_id', $agency->id)->count(),
            'employeesCount' => $employees->count(),
            'servicesCount' => $services->count(),
            'reservationsCount' => $reservations->count(),
            'pendingReservationsCount' => $reservations->where('status', 'pending')->count(),
            'recentReservations' => reservations::with(['client', 'service', 'employee', 'branch'])
                ->where('agency_id', $agency->id)
                ->latest()
                ->take(5)
                ->get(),
            'recentServices' => services::with('branch')
                ->where('agency_id', $agency->id)
                ->latest()
                ->take(5)
                ->get(),
            'recentTickets' => Ticket::with(['client', 'service', 'employee', 'branch'])
                ->where('agency_id', $agency->id)
                ->latest()
                ->take(6)
                ->get(),
            'todayIncome' => $todayIncome,
            'monthIncome' => $monthIncome,
            'yearIncome' => $yearIncome,
            'previousMonthIncome' => $previousMonthIncome,
            'monthlyRevenueTrend' => $this->percentageTrend($monthIncome, $previousMonthIncome),
            'dailyRevenue' => $dailyRevenue,
            'monthlyRevenue' => $monthlyRevenue,
            'yearlyRevenue' => $yearlyRevenue,
            'completedTicketsCount' => $completedTickets->count(),
            'confirmedReservationsCount' => $confirmedReservations->count(),
            'cancelledReservationsCount' => $cancelledReservations->count(),
            'activeTicketsCount' => $activeTicketsCount,
            'todayReservationsCount' => $todayReservationsCount,
            'ticketStatusBreakdown' => $ticketStatusBreakdown,
            'ticketStatusTotal' => $ticketStatusTotal,
            'branchPerformance' => $branchPerformance,
            'maxBranchRevenue' => max(1, (float) $branchPerformance->max('revenue')),
            'maxBranchTickets' => max(1, (int) $branchPerformance->max('tickets')),
            'revenueByService' => $revenueByService,
            'maxServiceRevenue' => max(1, (float) $revenueByService->max('revenue')),
            'employeePerformance' => $employeePerformance,
            'busiestDays' => $busiestDays,
            'maxBusiestDayTotal' => max(1, (int) $busiestDays->max('total')),
            'busiestDay' => $busiestDay,
            'averageTicketMinutes' => $averageTicketMinutes,
            'activeCustomersCount' => $activeCustomersCount,
            'recurringCustomersCount' => $recurringCustomersCount,
            'recurringRate' => $recurringRate,
            'cancellationRate' => $cancellationRate,
            'completionRate' => $completionRate,
        ]);
    }

    private function staffDashboard()
    {
        $agency = $this->currentAgency();
        $employee = $this->currentStaffEmployee();

        $assignedTicketQuery = Ticket::where('agency_id', $agency->id)
            ->where('employee_id', $employee->id);

        $activeTickets = (clone $assignedTicketQuery)
            ->with(['service', 'reservation'])
            ->whereIn('status', ['waiting', 'in_progress'])
            ->latest()
            ->take(8)
            ->get();

        $upcomingReservations = reservations::with(['client', 'service', 'ticket'])
            ->where('agency_id', $agency->id)
            ->where('employee_id', $employee->id)
            ->where('reservation_date', '>=', now()->startOfDay())
            ->whereIn('status', ['pending', 'confirmed'])
            ->orderBy('reservation_date')
            ->take(6)
            ->get();

        return view('dashboard.staff', [
            'agency' => $agency,
            'employee' => $employee,
            'activeTickets' => $activeTickets,
            'upcomingReservations' => $upcomingReservations,
            'waitingTicketsCount' => (clone $assignedTicketQuery)->where('status', 'waiting')->count(),
            'inProgressTicketsCount' => (clone $assignedTicketQuery)->where('status', 'in_progress')->count(),
            'completedTodayCount' => (clone $assignedTicketQuery)
                ->where('status', 'completed')
                ->whereDate('completed_at', today())
                ->count(),
            'assignedReservationsCount' => reservations::where('agency_id', $agency->id)
                ->where('employee_id', $employee->id)
                ->whereIn('status', ['pending', 'confirmed'])
                ->count(),
        ]);
    }

    private function averageTicketMinutes(Collection $tickets): ?int
    {
        $durations = $tickets
            ->filter(fn (Ticket $ticket) => $ticket->started_at && $ticket->completed_at)
            ->map(fn (Ticket $ticket) => $ticket->started_at->diffInMinutes($ticket->completed_at));

        if ($durations->isEmpty()) {
            return null;
        }

        return (int) round($durations->avg());
    }

    private function busiestDays(Collection $tickets, Collection $reservations): Collection
    {
        $days = collect([
            'Monday' => 'Lundi',
            'Tuesday' => 'Mardi',
            'Wednesday' => 'Mercredi',
            'Thursday' => 'Jeudi',
            'Friday' => 'Vendredi',
            'Saturday' => 'Samedi',
            'Sunday' => 'Dimanche',
        ]);

        return $days->map(function (string $label, string $day) use ($tickets, $reservations) {
            $ticketCount = $tickets
                ->filter(fn (Ticket $ticket) => ! $ticket->reservation_id && $ticket->created_at?->format('l') === $day)
                ->count();
            $reservationCount = $reservations
                ->filter(fn (reservations $reservation) => $reservation->status !== 'cancelled' && $reservation->reservation_date?->format('l') === $day)
                ->count();

            return [
                'label' => $label,
                'tickets' => $ticketCount,
                'reservations' => $reservationCount,
                'total' => $ticketCount + $reservationCount,
            ];
        })->values();
    }

    private function percentageTrend(float|int $current, float|int $previous): array
    {
        if ((float) $previous === 0.0) {
            return [
                'label' => (float) $current > 0.0 ? '+100%' : '0%',
                'direction' => (float) $current > 0.0 ? 'up' : 'neutral',
            ];
        }

        $change = (($current - $previous) / $previous) * 100;

        return [
            'label' => ($change > 0 ? '+' : '').number_format($change, 1, ',', ' ').'%',
            'direction' => $change > 0 ? 'up' : ($change < 0 ? 'down' : 'neutral'),
        ];
    }
}
