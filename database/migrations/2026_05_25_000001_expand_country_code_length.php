<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE `rentals` MODIFY `country_code` VARCHAR(64) NOT NULL');
            DB::statement('ALTER TABLE `server_pricing` MODIFY `country_code` VARCHAR(64) NULL');
        } elseif ($driver === 'sqlite') {
            // SQLite tests: column length is not enforced the same way; no-op is fine for local PHPUnit.
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE `rentals` MODIFY `country_code` VARCHAR(10) NOT NULL');
            DB::statement('ALTER TABLE `server_pricing` MODIFY `country_code` VARCHAR(10) NULL');
        }
    }
};
