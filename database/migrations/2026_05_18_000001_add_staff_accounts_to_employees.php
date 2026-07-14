<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->nullable()
                ->after('agency_id')
                ->unique()
                ->constrained('users')
                ->nullOnDelete();
        });

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'manager', 'staff') NOT NULL DEFAULT 'manager'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::table('users')->where('role', 'staff')->update(['role' => 'manager']);
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'manager') NOT NULL DEFAULT 'manager'");
        }
    }
};
