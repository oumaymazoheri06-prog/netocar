<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $usedKeys = DB::table('agencies')
            ->whereNotNull('license_key')
            ->pluck('license_key')
            ->all();

        DB::table('agencies')
            ->orderBy('id')
            ->get(['id', 'license_key', 'license_status', 'license_expires_at', 'activated_at'])
            ->each(function ($agency) use (&$usedKeys) {
                $key = $agency->license_key ?: $this->generateLicenseKey($usedKeys);

                $usedKeys[] = $key;

                DB::table('agencies')
                    ->where('id', $agency->id)
                    ->update([
                        'license_key' => $key,
                        'license_status' => $agency->license_status ?: 'active',
                        'license_expires_at' => $agency->license_expires_at ?: now()->addYearNoOverflow()->toDateString(),
                        'activated_at' => $agency->activated_at ?: now(),
                    ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }

    private function generateLicenseKey(array $usedKeys): string
    {
        do {
            $key = 'NTO-'.Str::upper(Str::random(4)).'-'.Str::upper(Str::random(4)).'-'.Str::upper(Str::random(4));
        } while (in_array($key, $usedKeys, true));

        return $key;
    }
};
