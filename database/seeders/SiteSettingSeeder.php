<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'display_currency' => 'NGN',
            'usd_to_ngn_rate' => '0',
            'naira_margin_percent' => '0',
            'naira_margin_amount' => '0',
        ];
        foreach ($defaults as $key => $value) {
            if (SiteSetting::find($key) === null) {
                SiteSetting::set($key, $value);
            }
        }
    }
}
