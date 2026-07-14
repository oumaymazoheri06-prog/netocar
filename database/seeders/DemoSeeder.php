<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\agencies;
use App\Models\Branch;
use App\Models\clients;
use App\Models\employees;
use App\Models\Payment;
use App\Models\reservations;
use App\Models\services;
use App\Models\Ticket;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $password = config('netocar.demo.password', 'Demo@2026!');
            $managerEmail = config('netocar.demo.manager_email', 'demo.manager@netocar.test');
            $staffEmail = config('netocar.demo.staff_email', 'demo.staff@netocar.test');

            $agency = agencies::withTrashed()->updateOrCreate(
                ['email' => 'demo.agency@netocar.test'],
                [
                    'name' => config('netocar.demo.agency_name', 'NetoCar Demo Center'),
                    'address' => 'Boulevard Mohammed V, Casablanca',
                    'phone_number' => '0600000000',
                    'package' => 'standard',
                    'license_status' => 'active',
                    'license_expires_at' => now()->addYearNoOverflow()->toDateString(),
                    'activated_at' => now(),
                ],
            );

            if ($agency->trashed()) {
                $agency->restore();
            }

            $this->clearDemoAgency($agency);

            $manager = User::updateOrCreate(
                ['email' => $managerEmail],
                [
                    'name' => 'NetoCar Demo Manager',
                    'password' => $password,
                    'role' => 'manager',
                    'agency_id' => $agency->id,
                    'email_verified_at' => now(),
                ],
            );

            $staffUser = User::updateOrCreate(
                ['email' => $staffEmail],
                [
                    'name' => 'NetoCar Demo Staff',
                    'password' => $password,
                    'role' => 'staff',
                    'agency_id' => $agency->id,
                    'email_verified_at' => now(),
                ],
            );

            $centreVille = Branch::create([
                'agency_id' => $agency->id,
                'name' => 'Centre-ville',
                'code' => 'CASA',
                'address' => 'Rue Ibn Tachfine, Casablanca',
                'phone_number' => '0522000001',
                'opening_time' => '08:00',
                'closing_time' => '19:00',
                'simultaneous_capacity' => 4,
                'is_active' => true,
            ]);

            $maarif = Branch::create([
                'agency_id' => $agency->id,
                'name' => 'Maarif',
                'code' => 'MRF',
                'address' => 'Avenue Bir Anzarane, Casablanca',
                'phone_number' => '0522000002',
                'opening_time' => '09:00',
                'closing_time' => '20:00',
                'simultaneous_capacity' => 3,
                'is_active' => true,
            ]);

            $employees = collect([
                [
                    'user_id' => $staffUser->id,
                    'branch_id' => $centreVille->id,
                    'name' => 'Youssef Bennani',
                    'email' => $staffEmail,
                    'phone_number' => '0611111111',
                    'job_title' => 'Chef equipe lavage',
                    'salary' => 4800,
                ],
                [
                    'branch_id' => $centreVille->id,
                    'name' => 'Imane Rachidi',
                    'email' => 'demo.imane@netocar.test',
                    'phone_number' => '0622222222',
                    'job_title' => 'Specialiste interieur',
                    'salary' => 4200,
                ],
                [
                    'branch_id' => $maarif->id,
                    'name' => 'Omar El Fassi',
                    'email' => 'demo.omar@netocar.test',
                    'phone_number' => '0633333333',
                    'job_title' => 'Detailing premium',
                    'salary' => 5200,
                ],
                [
                    'branch_id' => $maarif->id,
                    'name' => 'Nora Amrani',
                    'email' => 'demo.nora@netocar.test',
                    'phone_number' => '0644444444',
                    'job_title' => 'Accueil client',
                    'salary' => 3900,
                ],
            ])->map(fn (array $data) => employees::create([
                'agency_id' => $agency->id,
                ...$data,
            ]));

            $services = collect([
                ['branch_id' => $centreVille->id, 'name' => 'Lavage express', 'description' => 'Exterieur rapide avec sechage manuel.', 'price' => 60, 'duration_minutes' => 30],
                ['branch_id' => $centreVille->id, 'name' => 'Lavage complet', 'description' => 'Interieur, exterieur et finition pneus.', 'price' => 120, 'duration_minutes' => 60],
                ['branch_id' => $maarif->id, 'name' => 'Detailing premium', 'description' => 'Nettoyage approfondi et soin carrosserie.', 'price' => 280, 'duration_minutes' => 120],
                ['branch_id' => $maarif->id, 'name' => 'Polish carrosserie', 'description' => 'Correction legere et brillance durable.', 'price' => 420, 'duration_minutes' => 180],
                ['branch_id' => null, 'name' => 'Nettoyage moteur', 'description' => 'Degraissage controle et protection moteur.', 'price' => 150, 'duration_minutes' => 75],
            ])->map(fn (array $data) => services::create([
                'agency_id' => $agency->id,
                ...$data,
            ]));

            $clients = collect([
                ['name' => 'Sara El Idrissi', 'email' => 'demo.sara@netocar.test', 'phone_number' => '0612345678', 'branch_id' => $centreVille->id],
                ['name' => 'Mehdi Alaoui', 'email' => 'demo.mehdi@netocar.test', 'phone_number' => '0623456789', 'branch_id' => $centreVille->id],
                ['name' => 'Nadia Amrani', 'email' => 'demo.nadia@netocar.test', 'phone_number' => '0634567890', 'branch_id' => $maarif->id],
                ['name' => 'Karim Berrada', 'email' => 'demo.karim@netocar.test', 'phone_number' => '0645678901', 'branch_id' => $maarif->id],
                ['name' => 'Hajar Mansouri', 'email' => 'demo.hajar@netocar.test', 'phone_number' => '0656789012', 'branch_id' => null],
                ['name' => 'Anas Tazi', 'email' => 'demo.anas@netocar.test', 'phone_number' => '0667890123', 'branch_id' => $centreVille->id],
            ])->map(fn (array $data) => clients::create([
                'agency_id' => $agency->id,
                ...$data,
            ]));

            $this->createReservationWithTicket(
                $agency,
                $centreVille,
                $clients[0],
                $services[1],
                $employees[0],
                now()->addHours(2),
                'confirmed',
                'waiting',
                'car',
                '12345-A-6',
            );

            $this->createReservationWithTicket(
                $agency,
                $centreVille,
                $clients[1],
                $services[0],
                $employees[1],
                now()->addHours(4),
                'confirmed',
                'in_progress',
                'car',
                '22110-B-6',
                startedAt: now()->subMinutes(18),
            );

            $this->createReservationWithTicket(
                $agency,
                $maarif,
                $clients[2],
                $services[2],
                $employees[2],
                now()->subDays(1)->setTime(11, 0),
                'confirmed',
                'completed',
                'van',
                '77889-C-6',
                startedAt: now()->subDays(1)->setTime(11, 0),
                completedAt: now()->subDays(1)->setTime(13, 5),
            );

            reservations::create([
                'agency_id' => $agency->id,
                'branch_id' => $maarif->id,
                'client_id' => $clients[3]->id,
                'service_id' => $services[3]->id,
                'employee_id' => $employees[2]->id,
                'vehicle_type' => 'car',
                'plate_number' => '88990-D-6',
                'reservation_date' => now()->addDay()->setTime(10, 30),
                'duration_minutes' => $services[3]->duration_minutes,
                'status' => 'pending',
            ]);

            Ticket::create([
                'agency_id' => $agency->id,
                'branch_id' => $centreVille->id,
                'client_id' => $clients[4]->id,
                'service_id' => $services[4]->id,
                'employee_id' => $employees[0]->id,
                'vehicle_type' => 'car',
                'plate_number' => '44556-E-6',
                'status' => 'completed',
                'started_at' => now()->subHours(4),
                'completed_at' => now()->subHours(3),
                'price' => $services[4]->price,
            ]);

            Payment::create([
                'agency_id' => $agency->id,
                'plan' => $agency->package,
                'billing_period' => 'yearly',
                'period_starts_at' => now()->subMonth()->toDateString(),
                'period_ends_at' => now()->addYearNoOverflow()->toDateString(),
                'amount' => $agency->plan_amount,
                'status' => 'completed',
                'processed_at' => now()->subMonth(),
                'reviewed_by' => $manager->id,
                'payment_method' => 'virement',
                'receipt_photo' => null,
            ]);
        });
    }

    private function clearDemoAgency(agencies $agency): void
    {
        ActivityLog::where('agency_id', $agency->id)->delete();
        Payment::where('agency_id', $agency->id)->delete();
        Ticket::withTrashed()->where('agency_id', $agency->id)->forceDelete();
        reservations::withTrashed()->where('agency_id', $agency->id)->forceDelete();
        services::withTrashed()->where('agency_id', $agency->id)->forceDelete();
        clients::withTrashed()->where('agency_id', $agency->id)->forceDelete();
        employees::withTrashed()->where('agency_id', $agency->id)->forceDelete();
        Branch::withTrashed()->where('agency_id', $agency->id)->forceDelete();
    }

    private function createReservationWithTicket(
        agencies $agency,
        Branch $branch,
        clients $client,
        services $service,
        employees $employee,
        CarbonInterface $date,
        string $reservationStatus,
        string $ticketStatus,
        string $vehicleType,
        string $plateNumber,
        ?CarbonInterface $startedAt = null,
        ?CarbonInterface $completedAt = null,
    ): void {
        $reservation = reservations::create([
            'agency_id' => $agency->id,
            'branch_id' => $branch->id,
            'client_id' => $client->id,
            'service_id' => $service->id,
            'employee_id' => $employee->id,
            'vehicle_type' => $vehicleType,
            'plate_number' => $plateNumber,
            'reservation_date' => $date,
            'duration_minutes' => $service->duration_minutes,
            'status' => $reservationStatus,
        ]);

        Ticket::create([
            'agency_id' => $agency->id,
            'branch_id' => $branch->id,
            'reservation_id' => $reservation->id,
            'client_id' => $client->id,
            'service_id' => $service->id,
            'employee_id' => $employee->id,
            'vehicle_type' => $vehicleType,
            'plate_number' => $plateNumber,
            'status' => $ticketStatus,
            'started_at' => $startedAt,
            'completed_at' => $completedAt,
            'price' => $service->price,
        ]);
    }
}
