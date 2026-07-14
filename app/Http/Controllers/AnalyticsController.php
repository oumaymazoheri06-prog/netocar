<?php

namespace App\Http\Controllers;

use App\Models\agencies;
use App\Models\Branch;
use App\Models\clients;
use App\Models\employees;
use App\Models\Payment;
use App\Models\reservations;
use App\Models\services;
use App\Models\Ticket;
use Illuminate\Support\Collection;

class AnalyticsController extends Controller
{
    public function index()
    {
        if ($this->userIsAdmin()) {
            return $this->adminAnalytics();
        }

        $this->requireManagerUser();

        return $this->managerAnalytics();
    }

    private function managerAnalytics()
    {
        $agency = $this->currentAgency();

        $branches = Branch::where('agency_id', $agency->id)
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();
        $services = services::where('agency_id', $agency->id)->get();
        $employees = employees::with('branch')->where('agency_id', $agency->id)->get();
        $tickets = Ticket::with(['service', 'employee', 'branch'])
            ->where('agency_id', $agency->id)
            ->get();
        $reservations = reservations::with(['client', 'service', 'employee', 'branch'])
            ->where('agency_id', $agency->id)
            ->get();

        $completedTickets = $tickets->where('status', 'completed');
        $confirmedReservations = $reservations->where('status', 'confirmed');
        $cancelledReservations = $reservations->where('status', 'cancelled');

        $totalRevenue = $completedTickets->sum('price');

        $revenueByService = $services
            ->map(function (services $service) use ($completedTickets, $confirmedReservations) {
                $serviceTickets = $completedTickets->where('service_id', $service->id);
                $serviceReservations = $confirmedReservations->where('service_id', $service->id);
                $ticketRevenue = (float) $serviceTickets->sum('price');

                return [
                    'name' => $service->name,
                    'ticket_count' => $serviceTickets->count(),
                    'reservation_count' => $serviceReservations->count(),
                    'revenue' => $ticketRevenue,
                ];
            })
            ->sortByDesc('revenue')
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
                    'in_progress' => $employeeTickets->where('status', 'in_progress')->count(),
                    'waiting' => $employeeTickets->where('status', 'waiting')->count(),
                    'cancelled' => $employeeTickets->where('status', 'cancelled')->count(),
                    'reservations' => $reservations->where('employee_id', $employee->id)->count(),
                    'revenue' => $completedEmployeeTickets->sum('price'),
                    'average_minutes' => $this->averageTicketMinutes($completedEmployeeTickets),
                ];
            })
            ->sortByDesc(fn (array $employee) => [$employee['completed'], $employee['revenue']])
            ->values();

        $branchPerformance = $branches
            ->map(function (Branch $branch) use ($tickets, $completedTickets, $reservations) {
                $branchTickets = $tickets->where('branch_id', $branch->id);
                $branchCompletedTickets = $completedTickets->where('branch_id', $branch->id);
                $branchReservations = $reservations->where('branch_id', $branch->id);

                return [
                    'name' => $branch->name,
                    'status' => $branch->is_active ? 'Active' : 'Inactive',
                    'tickets' => $branchTickets->count(),
                    'reservations' => $branchReservations->count(),
                    'cancelled' => $branchReservations->where('status', 'cancelled')->count(),
                    'revenue' => $branchCompletedTickets->sum('price'),
                ];
            });

        if ($tickets->whereNull('branch_id')->isNotEmpty() || $reservations->whereNull('branch_id')->isNotEmpty()) {
            $unassignedTickets = $tickets->whereNull('branch_id');
            $unassignedCompletedTickets = $completedTickets->whereNull('branch_id');
            $unassignedReservations = $reservations->whereNull('branch_id');

            $branchPerformance->push([
                'name' => 'Toute l’agence',
                'status' => 'Non assigné',
                'tickets' => $unassignedTickets->count(),
                'reservations' => $unassignedReservations->count(),
                'cancelled' => $unassignedReservations->where('status', 'cancelled')->count(),
                'revenue' => $unassignedCompletedTickets->sum('price'),
            ]);
        }

        $branchPerformance = $branchPerformance
            ->sortByDesc('revenue')
            ->values();

        $busiestDays = $this->busiestDays($tickets, $reservations);
        $averageTicketMinutes = $this->averageTicketMinutes($completedTickets);
        $activeCustomersCount = clients::where('agency_id', $agency->id)
            ->whereHas('reservation', fn ($query) => $query->where('status', '!=', 'cancelled'))
            ->count();
        $recurringCustomersQuery = clients::withCount([
            'reservation' => fn ($query) => $query->where('status', '!=', 'cancelled'),
        ])
            ->where('agency_id', $agency->id)
            ->orderByDesc('reservation_count')
            ->get()
            ->filter(fn (clients $client) => $client->reservation_count >= 2);
        $recurringCustomersCount = $recurringCustomersQuery->count();
        $recurringCustomers = $recurringCustomersQuery
            ->take(10)
            ->values();

        return view('analytics.index', [
            'mode' => 'manager',
            'agency' => $agency,
            'totalRevenue' => $totalRevenue,
            'completedTicketsCount' => $completedTickets->count(),
            'confirmedReservationsCount' => $confirmedReservations->count(),
            'averageTicketMinutes' => $averageTicketMinutes,
            'cancelledReservationsCount' => $cancelledReservations->count(),
            'cancellationRate' => $reservations->count() > 0
                ? round(($cancelledReservations->count() / $reservations->count()) * 100, 1)
                : 0,
            'recentCancelledReservations' => $cancelledReservations
                ->sortByDesc('updated_at')
                ->take(8)
                ->values(),
            'branchPerformance' => $branchPerformance,
            'maxBranchRevenue' => max(1, (float) $branchPerformance->max('revenue')),
            'revenueByService' => $revenueByService,
            'maxServiceRevenue' => max(1, (float) $revenueByService->max('revenue')),
            'employeePerformance' => $employeePerformance,
            'busiestDays' => $busiestDays,
            'maxBusiestDayTotal' => max(1, (int) $busiestDays->max('total')),
            'recurringCustomers' => $recurringCustomers,
            'activeCustomersCount' => $activeCustomersCount,
            'recurringRate' => $activeCustomersCount > 0
                ? round(($recurringCustomersCount / $activeCustomersCount) * 100, 1)
                : 0,
        ]);
    }

    private function adminAnalytics()
    {
        $agencies = agencies::with(['payments' => fn ($query) => $query->latest()])->orderBy('name')->get();
        $unpaidAgencies = $agencies->reject(fn (agencies $agency) => $agency->hasActiveLicense())->values();
        $paidAgenciesCount = $agencies->count() - $unpaidAgencies->count();
        $paymentStatusCounts = collect(['pending', 'completed', 'failed'])
            ->mapWithKeys(fn (string $status) => [$status => Payment::where('status', $status)->count()]);

        return view('analytics.index', [
            'mode' => 'admin',
            'agenciesCount' => $agencies->count(),
            'paidAgenciesCount' => $paidAgenciesCount,
            'unpaidAgencies' => $unpaidAgencies,
            'unpaidAgenciesCount' => $unpaidAgencies->count(),
            'unpaidAmountTotal' => $unpaidAgencies->sum(fn (agencies $agency) => $agency->plan_amount),
            'paymentStatusCounts' => $paymentStatusCounts,
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
            $ticketCount = $tickets->filter(fn (Ticket $ticket) => ! $ticket->reservation_id && $ticket->created_at?->format('l') === $day)->count();
            $reservationCount = $reservations->filter(fn (reservations $reservation) => $reservation->status !== 'cancelled' && $reservation->reservation_date?->format('l') === $day)->count();

            return [
                'label' => $label,
                'tickets' => $ticketCount,
                'reservations' => $reservationCount,
                'total' => $ticketCount + $reservationCount,
            ];
        });
    }
}
