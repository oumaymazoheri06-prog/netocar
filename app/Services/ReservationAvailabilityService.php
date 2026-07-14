<?php

namespace App\Services;

use App\Enums\ReservationStatus;
use App\Models\Branch;
use App\Models\employees;
use App\Models\reservations;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class ReservationAvailabilityService
{
    /**
     * This method must run inside a database transaction. Locking the branch
     * serializes bookings for the same site and closes the double-booking race.
     */
    public function assertAvailable(
        int $agencyId,
        ?int $branchId,
        ?int $employeeId,
        string $reservationDate,
        int $durationMinutes,
        ?int $ignoreReservationId = null,
        string $status = ReservationStatus::Pending->value,
    ): void {
        if ($status === ReservationStatus::Cancelled->value) {
            return;
        }

        $branch = null;

        if ($branchId) {
            $branch = Branch::where('agency_id', $agencyId)
                ->where('is_active', true)
                ->whereKey($branchId)
                ->lockForUpdate()
                ->first();

            if (! $branch) {
                throw ValidationException::withMessages([
                    'branch_id' => 'La branche sélectionnée est inactive ou inaccessible.',
                ]);
            }
        }

        if ($employeeId) {
            $employee = employees::where('agency_id', $agencyId)
                ->whereKey($employeeId)
                ->lockForUpdate()
                ->first();

            if (! $employee) {
                throw ValidationException::withMessages([
                    'employee_id' => 'Cet employé est inactif ou inaccessible.',
                ]);
            }
        }

        if (! $branch && ! $employeeId) {
            throw ValidationException::withMessages([
                'branch_id' => 'Sélectionnez une branche pour une réservation sans employé.',
            ]);
        }

        $startsAt = Carbon::parse($reservationDate);
        $endsAt = $startsAt->copy()->addMinutes($durationMinutes);
        if ($branch) {
            $opensAt = $startsAt->copy()->setTimeFromTimeString($branch->opening_time ?: '08:00:00');
            $closesAt = $startsAt->copy()->setTimeFromTimeString($branch->closing_time ?: '18:00:00');

            if ($startsAt->lt($opensAt) || $endsAt->gt($closesAt)) {
                throw ValidationException::withMessages([
                    'reservation_date' => "Le créneau doit être compris entre {$opensAt->format('H:i')} et {$closesAt->format('H:i')}.",
                ]);
            }
        }

        $overlapping = reservations::query()
            ->where('agency_id', $agencyId)
            ->when(
                $branchId,
                fn ($query) => $query->where('branch_id', $branchId),
                fn ($query) => $query->where('employee_id', $employeeId),
            )
            ->whereIn('status', [ReservationStatus::Pending->value, ReservationStatus::Confirmed->value])
            ->when($ignoreReservationId, fn ($query) => $query->whereKeyNot($ignoreReservationId))
            ->whereBetween('reservation_date', [$startsAt->copy()->subDay(), $endsAt])
            ->lockForUpdate()
            ->get()
            ->filter(function (reservations $reservation) use ($startsAt, $endsAt) {
                $existingStart = $reservation->reservation_date;
                $existingEnd = $existingStart->copy()->addMinutes($reservation->duration_minutes ?: 60);

                return $existingStart->lt($endsAt) && $existingEnd->gt($startsAt);
            });

        if ($employeeId && $overlapping->contains(fn (reservations $reservation) => (int) $reservation->employee_id === $employeeId)) {
            throw ValidationException::withMessages([
                'reservation_date' => 'Cet employé possède déjà une réservation sur ce créneau.',
            ]);
        }

        if ($branch && $overlapping->count() >= $branch->simultaneous_capacity) {
            throw ValidationException::withMessages([
                'reservation_date' => "La capacité de la branche ({$branch->simultaneous_capacity}) est atteinte sur ce créneau.",
            ]);
        }
    }
}
