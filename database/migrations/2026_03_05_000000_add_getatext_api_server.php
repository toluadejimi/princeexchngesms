<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::table('api_servers')->where('type', 'getatext')->exists();
        if ($exists) {
            return;
        }

        $key = env('GETATEXT_API_KEY');
        $apiKey = $key ? Crypt::encryptString($key) : Crypt::encryptString('placeholder-set-real-key-in-admin');

        DB::table('api_servers')->insert([
            'name' => 'Server 1',
            'base_url' => env('GETATEXT_BASE_URL', 'https://getatext.com'),
            'api_key' => $apiKey,
            'type' => 'getatext',
            'profit_margin_percent' => 12,
            'status' => false,
            'sort_order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('api_servers')->where('type', 'getatext')->delete();
    }
};
