<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->unique(['agency_id', 'email']);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->unique(['agency_id', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropUnique(['agency_id', 'email']);
            $table->unique('email');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropUnique(['agency_id', 'email']);
            $table->unique('email');
        });
    }
};
