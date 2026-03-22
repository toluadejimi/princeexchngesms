<?php

namespace App\Console\Commands;

use App\Models\ApiServer;
use Illuminate\Console\Command;

/**
 * Server 3 (Getatext) is normally added by migration `2026_03_05_000000_add_getatext_api_server`.
 * Run this on production if that migration was skipped so Admin → Servers lists Server 3.
 */
class EnsureGetatextServer extends Command
{
    protected $signature = 'servers:ensure-getatext';

    protected $description = 'Create the Getatext (Server 3) row in api_servers if it is missing';

    public function handle(): int
    {
        $server = ApiServer::firstOrCreate(
            ['type' => 'getatext'],
            [
                'name' => 'Server 3',
                'base_url' => rtrim((string) env('GETATEXT_BASE_URL', 'https://getatext.com'), '/'),
                'api_key' => env('GETATEXT_API_KEY') ?: 'placeholder-set-real-key-in-admin',
                'profit_margin_percent' => 12,
                'status' => false,
                'sort_order' => 3,
            ]
        );

        if ($server->wasRecentlyCreated) {
            $this->info('Created Server 3 (Getatext, id '.$server->id.'). Enable it and set the API key in Admin → Servers.');
        } else {
            $this->info('Server 3 (Getatext) already exists (id '.$server->id.').');
        }

        return self::SUCCESS;
    }
}
