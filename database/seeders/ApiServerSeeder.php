<?php

namespace Database\Seeders;

use App\Models\ApiServer;
use Illuminate\Database\Seeder;

class ApiServerSeeder extends Seeder
{
    public function run(): void
    {
        ApiServer::create([
            'name' => 'Server 1',
            'base_url' => 'https://api.smsconfirmed.com',
            'api_key' => env('SMSCONFIRMED_API_KEY') ?: 'placeholder-set-real-key-in-admin',
            'type' => 'smsconfirmed',
            'profit_margin_percent' => 10,
            'status' => true,
            'sort_order' => 1,
        ]);

        ApiServer::create([
            'name' => 'Server 2',
            'base_url' => 'https://api.smspool.net',
            'api_key' => env('GLOBAL_SMS_API_KEY') ?: 'placeholder-set-real-key-in-admin',
            'type' => 'multi_country',
            'profit_margin_percent' => 15,
            'status' => true,
            'sort_order' => 2,
        ]);
    }
}
