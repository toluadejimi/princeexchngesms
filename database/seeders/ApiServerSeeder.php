<?php

namespace Database\Seeders;

use App\Models\ApiServer;
use App\Models\Country;
use Illuminate\Database\Seeder;

class ApiServerSeeder extends Seeder
{
    public function run(): void
    {
        $usa = ApiServer::create([
            'name' => 'DaisySMS USA',
            'base_url' => 'https://daisysms.com',
            'api_key' => env('DAISYSMS_API_KEY') ?: 'placeholder-set-real-key-in-admin',
            'type' => 'usa_only',
            'profit_margin_percent' => 10,
            'status' => true,
            'sort_order' => 1,
        ]);
        Country::create([
            'server_id' => $usa->id,
            'name' => 'United States',
            'code' => 'US',
            'provider_country_id' => '187',
            'active' => true,
        ]);

        $global = ApiServer::create([
            'name' => 'Global Server',
            'base_url' => 'https://api.smspool.net',
            'api_key' => env('GLOBAL_SMS_API_KEY') ?: 'placeholder-set-real-key-in-admin',
            'type' => 'multi_country',
            'profit_margin_percent' => 15,
            'status' => true,
            'sort_order' => 2,
        ]);
    }
}
