<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('api_servers')->where('type', 'getatext')->update([
            'sort_order' => 1,
            'name' => 'Server 1',
        ]);

        DB::table('api_servers')->where('type', 'multi_country')->update([
            'sort_order' => 2,
            'name' => 'Server 2',
        ]);

        $exists = DB::table('api_servers')->where('type', 'fivesim')->exists();
        if (! $exists) {
            $key = env('FIVESIM_API_KEY');
            $apiKey = $key ? Crypt::encryptString($key) : Crypt::encryptString('placeholder-set-real-key-in-admin');

            DB::table('api_servers')->insert([
                'name' => 'Server 3',
                'base_url' => rtrim((string) env('FIVESIM_BASE_URL', 'https://5sim.net'), '/'),
                'api_key' => $apiKey,
                'type' => 'fivesim',
                'profit_margin_percent' => 12,
                'status' => false,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('api_servers')->where('type', 'fivesim')->update([
                'sort_order' => 3,
                'name' => 'Server 3',
            ]);
        }

        // Legacy integration: keep row for old rentals; keep out of default active list unless re-enabled in admin.
        DB::table('api_servers')->where('type', 'smsconfirmed')->update([
            'status' => false,
            'sort_order' => 90,
        ]);
    }

    public function down(): void
    {
        DB::table('api_servers')->where('type', 'fivesim')->delete();
        DB::table('api_servers')->where('type', 'getatext')->update(['sort_order' => 3, 'name' => 'Server 3']);
        DB::table('api_servers')->where('type', 'multi_country')->update(['sort_order' => 2, 'name' => 'Server 2']);
    }
};
