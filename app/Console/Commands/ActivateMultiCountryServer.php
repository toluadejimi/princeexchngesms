<?php

namespace App\Console\Commands;

use App\Models\ApiServer;
use Illuminate\Console\Command;

class ActivateMultiCountryServer extends Command
{
    protected $signature = 'servers:activate-multi-country';

    protected $description = 'Ensure the Multi-Country (Other Countries) server exists and is active.';

    public function handle(): int
    {
        $server = ApiServer::where('type', 'multi_country')->first();

        if (!$server) {
            $server = ApiServer::create([
                'name' => 'Global Server',
                'base_url' => 'https://api.smspool.net',
                'api_key' => env('GLOBAL_SMS_API_KEY') ?: 'placeholder-set-real-key-in-admin',
                'type' => 'multi_country',
                'profit_margin_percent' => 15,
                'status' => true,
                'sort_order' => 2,
            ]);
            $this->info('Multi-Country server created and set to active.');
        } else {
            $server->update(['status' => true]);
            $this->info('Multi-Country server is now active.');
        }

        $this->line('  Name: ' . $server->name);
        $this->line('  Base URL: ' . $server->base_url);
        $this->line('  Status: ' . ($server->status ? 'Active' : 'Disabled'));

        return self::SUCCESS;
    }
}
