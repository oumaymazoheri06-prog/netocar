<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->moveLegacyAgencyOwnersToMemberships();

        Schema::table('agencies', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropUnique(['license_key']);
            $table->dropColumn('user_id');
            $table->dropColumn('license_key');
            $table->softDeletes();
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->string('normalized_phone', 20)->nullable()->after('phone_number');
            $table->softDeletes();
        });

        $this->backfillNormalizedPhones();

        Schema::table('clients', function (Blueprint $table) {
            $table->unique(['agency_id', 'normalized_phone']);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('services', function (Blueprint $table) {
            $table->unsignedSmallInteger('duration_minutes')->default(60)->after('price');
            $table->softDeletes();
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->foreignId('employee_id')->nullable()->change();
            $table->unsignedSmallInteger('duration_minutes')->default(60)->after('reservation_date');
            $table->softDeletes();
            $table->foreign('employee_id')->references('id')->on('employees')->nullOnDelete();
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('client_id')->nullable()->after('reservation_id')->constrained('clients')->nullOnDelete();
            $table->timestamp('started_at')->nullable()->after('status');
            $table->timestamp('completed_at')->nullable()->after('started_at');
            $table->softDeletes();
        });

        Schema::table('branches', function (Blueprint $table) {
            $table->time('opening_time')->default('08:00:00')->after('phone_number');
            $table->time('closing_time')->default('18:00:00')->after('opening_time');
            $table->softDeletes();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('amount', 10, 2)->change();
            $table->string('plan')->nullable()->after('agency_id');
            $table->string('billing_period')->default('yearly')->after('plan');
            $table->date('period_starts_at')->nullable()->after('billing_period');
            $table->date('period_ends_at')->nullable()->after('period_starts_at');
            $table->timestamp('processed_at')->nullable()->after('status');
            $table->foreignId('reviewed_by')->nullable()->after('processed_at')->constrained('users')->nullOnDelete();
        });

        DB::table('payments')->orderBy('id')->each(function ($payment) {
            $plan = DB::table('agencies')->where('id', $payment->agency_id)->value('package') ?? 'basic';
            DB::table('payments')->where('id', $payment->id)->update(['plan' => $plan]);
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropColumn(['plan', 'billing_period', 'period_starts_at', 'period_ends_at', 'processed_at']);
            $table->double('amount')->change();
        });

        Schema::table('branches', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['opening_time', 'closing_time']);
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('client_id');
            $table->dropSoftDeletes();
            $table->dropColumn(['started_at', 'completed_at']);
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->dropSoftDeletes();
            $table->dropColumn('duration_minutes');
            $table->foreignId('employee_id')->nullable(false)->change();
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn('duration_minutes');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->dropUnique(['agency_id', 'normalized_phone']);
            $table->dropSoftDeletes();
            $table->dropColumn('normalized_phone');
        });

        Schema::table('agencies', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('license_key')->nullable()->unique();
            $table->dropSoftDeletes();
        });
    }

    private function moveLegacyAgencyOwnersToMemberships(): void
    {
        DB::table('agencies')->whereNotNull('user_id')->orderBy('id')->each(function ($agency) {
            DB::table('users')
                ->where('id', $agency->user_id)
                ->where('role', 'manager')
                ->whereNull('agency_id')
                ->update(['agency_id' => $agency->id]);
        });
    }

    private function backfillNormalizedPhones(): void
    {
        $seen = [];

        DB::table('clients')->orderBy('id')->each(function ($client) use (&$seen) {
            $phone = $this->normalizePhone($client->phone_number);
            $key = $client->agency_id.':'.$phone;

            if ($phone === '' || isset($seen[$key])) {
                return;
            }

            $seen[$key] = true;
            DB::table('clients')->where('id', $client->id)->update(['normalized_phone' => $phone]);
        });
    }

    private function normalizePhone(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone ?? '') ?? '';

        if (str_starts_with($digits, '00212')) {
            $digits = substr($digits, 2);
        }

        if (strlen($digits) === 10 && str_starts_with($digits, '0')) {
            $digits = '212'.substr($digits, 1);
        }

        return $digits;
    }
};
