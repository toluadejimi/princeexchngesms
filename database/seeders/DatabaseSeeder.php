<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'name' => 'Admin',
            'is_admin' => true,
            'wallet_balance' => 100,
        ]);

        User::factory()->create([
            'email' => 'user@example.com',
            'name' => 'Test User',
            'wallet_balance' => 50,
        ]);

        $this->call(ApiServerSeeder::class);
        $this->call(SiteSettingSeeder::class);
    }
}
