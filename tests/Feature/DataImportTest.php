<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\agencies;
use App\Models\Branch;
use App\Models\clients;
use App\Models\employees;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class DataImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_preview_and_import_clients_from_csv(): void
    {
        [$manager, $agency] = $this->managerWithAgency();
        $branch = Branch::factory()->create([
            'agency_id' => $agency->id,
            'name' => 'Downtown',
            'code' => 'DT',
        ]);

        clients::factory()->create([
            'agency_id' => $agency->id,
            'email' => 'existing@example.com',
            'name' => 'Existing Client',
            'phone_number' => '0600000000',
        ]);

        $csv = implode("\n", [
            'name,email,phone,branch_code',
            'New Client,new@example.com,0612345678,DT',
            'Updated Client,existing@example.com,0699999999,DT',
            'Bad Branch,bad@example.com,0600000001,NOPE',
        ]);

        $this->actingAs($manager)
            ->post(route('imports.preview', 'clients'), [
                'file' => UploadedFile::fake()->createWithContent('clients.csv', $csv),
            ])
            ->assertOk()
            ->assertSee('New Client')
            ->assertSee('Updated Client')
            ->assertSee('Le code de branche est introuvable');

        $this->actingAs($manager)
            ->post(route('imports.store', 'clients'))
            ->assertRedirect(route('imports.index'));

        $this->assertDatabaseHas('clients', [
            'agency_id' => $agency->id,
            'branch_id' => $branch->id,
            'email' => 'new@example.com',
            'name' => 'New Client',
        ]);

        $this->assertDatabaseHas('clients', [
            'agency_id' => $agency->id,
            'branch_id' => $branch->id,
            'email' => 'existing@example.com',
            'name' => 'Updated Client',
            'phone_number' => '0699999999',
        ]);

        $this->assertDatabaseMissing('clients', [
            'email' => 'bad@example.com',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'agency_id' => $agency->id,
            'action' => 'import.clients.completed',
        ]);

        $log = ActivityLog::where('action', 'import.clients.completed')->firstOrFail();

        $this->assertSame(1, $log->metadata['created']);
        $this->assertSame(1, $log->metadata['updated']);
        $this->assertSame(1, $log->metadata['invalid_rows']);
    }

    public function test_manager_can_import_employees_and_services_from_csv(): void
    {
        [$manager, $agency] = $this->managerWithAgency();
        $branch = Branch::factory()->create([
            'agency_id' => $agency->id,
            'name' => 'Airport',
            'code' => 'AIR',
        ]);

        $employeesCsv = implode("\n", [
            'name,email,phone_number,job_title,salary,branch_code',
            'Sara Washer,sara@example.com,0611111111,Detail Specialist,4200,AIR',
        ]);

        $servicesCsv = implode("\n", [
            'name,description,price,branch_code',
            'Premium Wash,Exterior and interior wash,120,AIR',
        ]);

        $this->actingAs($manager)
            ->post(route('imports.preview', 'employees'), [
                'file' => UploadedFile::fake()->createWithContent('employees.csv', $employeesCsv),
            ])
            ->assertOk()
            ->assertSee('Sara Washer');

        $this->actingAs($manager)
            ->post(route('imports.store', 'employees'))
            ->assertRedirect(route('imports.index'));

        $this->actingAs($manager)
            ->post(route('imports.preview', 'services'), [
                'file' => UploadedFile::fake()->createWithContent('services.csv', $servicesCsv),
            ])
            ->assertOk()
            ->assertSee('Premium Wash');

        $this->actingAs($manager)
            ->post(route('imports.store', 'services'))
            ->assertRedirect(route('imports.index'));

        $this->assertDatabaseHas('employees', [
            'agency_id' => $agency->id,
            'branch_id' => $branch->id,
            'email' => 'sara@example.com',
            'job_title' => 'Detail Specialist',
        ]);

        $this->assertDatabaseHas('services', [
            'agency_id' => $agency->id,
            'branch_id' => $branch->id,
            'name' => 'Premium Wash',
            'price' => 120,
        ]);
    }

    public function test_staff_cannot_access_import_center(): void
    {
        [, $agency] = $this->managerWithAgency();
        $staff = User::factory()->create([
            'role' => 'staff',
            'agency_id' => $agency->id,
        ]);

        employees::factory()->create([
            'agency_id' => $agency->id,
            'user_id' => $staff->id,
            'email' => $staff->email,
        ]);

        $this->actingAs($staff)
            ->get(route('imports.index'))
            ->assertForbidden();
    }

    public function test_reports_can_export_csv(): void
    {
        [$manager, $agency] = $this->managerWithAgency();
        $branch = Branch::factory()->create([
            'agency_id' => $agency->id,
            'name' => 'Downtown',
        ]);

        clients::factory()->create([
            'agency_id' => $agency->id,
            'branch_id' => $branch->id,
            'name' => 'CSV Client',
            'email' => 'csv@example.com',
        ]);

        $response = $this->actingAs($manager)
            ->get(route('reports.csv', 'clients'));

        $response->assertOk();
        $content = $response->streamedContent();

        $this->assertStringContainsString('Nom,Branche,Email', $content);
        $this->assertStringContainsString('"CSV Client",Downtown,csv@example.com', $content);
    }

    private function managerWithAgency(): array
    {
        $manager = User::factory()->create(['role' => 'manager']);
        $agency = agencies::factory()->create();

        $manager->update(['agency_id' => $agency->id]);

        return [$manager->refresh(), $agency];
    }
}
