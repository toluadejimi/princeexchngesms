<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::table('site_settings')->where('key', 'naira_margin_amount')->exists();
        if (!$exists) {
            DB::table('site_settings')->insert(['key' => 'naira_margin_amount', 'value' => '0']);
        }
    }

    public function down(): void
    {
        DB::table('site_settings')->where('key', 'naira_margin_amount')->delete();
    }
};
