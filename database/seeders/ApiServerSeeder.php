<?php

namespace Database\Seeders;

use App\Models\ApiServer;
use Illuminate\Database\Seeder;

class ApiServerSeeder extends Seeder
{
    public function run(): void
    {
        ApiServer::firstOrCreate(
            ['type' => 'getatext'],
            [
                'name' => 'Server 1',
                'base_url' => env('GETATEXT_BASE_URL', 'https://getatext.com'),
                'api_key' => env('GETATEXT_API_KEY') ?: 'placeholder-set-real-key-in-admin',
                'profit_margin_percent' => 12,
                'status' => false,
                'sort_order' => 1,
            ]
        );

        ApiServer::firstOrCreate(
            ['type' => 'multi_country'],
            [
                'name' => 'Server 2',
                'base_url' => 'https://api.smspool.net',
                'api_key' => env('GLOBAL_SMS_API_KEY') ?: 'placeholder-set-real-key-in-admin',
                'profit_margin_percent' => 15,
                'status' => true,
                'sort_order' => 2,
            ]
        );

        ApiServer::firstOrCreate(
            ['type' => 'fivesim'],
            [
                'name' => 'Server 3',
                'base_url' => env('FIVESIM_BASE_URL', 'https://5sim.net'),
                'api_key' => env('FIVESIM_API_KEY') ?: 'placeholder-set-real-key-in-admin',
                'profit_margin_percent' => 12,
                'status' => false,
                'sort_order' => 3,
            ]
        );
    }
}
