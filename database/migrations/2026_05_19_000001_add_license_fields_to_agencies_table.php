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
        Schema::table('agencies', function (Blueprint $table) {
            $table->string('license_key')->nullable()->unique()->after('package');
            $table->enum('license_status', ['active', 'suspended'])->default('active')->after('license_key');
            $table->date('license_expires_at')->nullable()->after('license_status');
            $table->timestamp('activated_at')->nullable()->after('license_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agencies', function (Blueprint $table) {
            $table->dropColumn([
                'license_key',
                'license_status',
                'license_expires_at',
                'activated_at',
            ]);
        });
    }
};
