<?php

namespace App\Console\Commands;

use App\Models\ApiServer;
use Illuminate\Console\Command;

/**
 * Primary US-numbers row (`getatext`) is normally added by migration `2026_03_05_000000_add_getatext_api_server`.
 * Customer label: Server 1.
 */
class EnsureGetatextServer extends Command
{
    protected $signature = 'servers:ensure-getatext';

    protected $description = 'Create the getatext api_servers row if it is missing (shown as Server 1)';

    public function handle(): int
    {
        $server = ApiServer::firstOrCreate(
            ['type' => 'getatext'],
            [
                'name' => 'Server 1',
                'base_url' => rtrim((string) env('GETATEXT_BASE_URL', 'https://getatext.com'), '/'),
                'api_key' => env('GETATEXT_API_KEY') ?: 'placeholder-set-real-key-in-admin',
                'profit_margin_percent' => 12,
                'status' => false,
                'sort_order' => 1,
            ]
        );

        if ($server->wasRecentlyCreated) {
            $this->info('Created Server 1 row (getatext, id '.$server->id.'). Enable it and set the API key in Admin → Servers.');
        } else {
            $this->info('Server 1 (getatext) already exists (id '.$server->id.').');
        }

        return self::SUCCESS;
    }
}
