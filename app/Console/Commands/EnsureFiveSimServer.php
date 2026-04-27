<?php

namespace App\Console\Commands;

use App\Models\ApiServer;
use Illuminate\Console\Command;

/**
 * Server row for type `fivesim` (customer-facing: Server 3). Run if migration was skipped.
 */
class EnsureFiveSimServer extends Command
{
    protected $signature = 'servers:ensure-fivesim';

    protected $description = 'Create the Server 3 row in api_servers if it is missing (5SIM integration)';

    public function handle(): int
    {
        $server = ApiServer::firstOrCreate(
            ['type' => 'fivesim'],
            [
                'name' => 'Server 3',
                'base_url' => rtrim((string) env('FIVESIM_BASE_URL', 'https://5sim.net'), '/'),
                'api_key' => env('FIVESIM_API_KEY') ?: 'placeholder-set-real-key-in-admin',
                'profit_margin_percent' => 12,
                'status' => false,
                'sort_order' => 3,
            ]
        );

        if ($server->wasRecentlyCreated) {
            $this->info('Created Server 3 (id '.$server->id.'). Enable it and set the API token in Admin → Servers.');
        } else {
            $this->info('Server 3 row already exists (id '.$server->id.').');
        }

        return self::SUCCESS;
    }
}
