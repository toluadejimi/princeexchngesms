<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MaintenanceController extends Controller
{
    public function migrate(): RedirectResponse
    {
        $lock = Cache::lock('admin-run-migrations', 120);

        if (! $lock->get()) {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'A migration command is already running. Please wait and try again.');
        }

        try {
            $exitCode = Artisan::call('migrate', [
                '--force' => true,
            ]);

            $output = trim(Artisan::output());
            Log::info('Admin ran database migrations', [
                'exit_code' => $exitCode,
                'output' => $output,
            ]);

            if ($exitCode !== 0) {
                return redirect()
                    ->route('admin.dashboard')
                    ->with('error', 'Migration failed. Check the output below.')
                    ->with('artisan_output', $output !== '' ? $output : 'No output returned.');
            }

            return redirect()
                ->route('admin.dashboard')
                ->with('success', 'Migrations completed successfully.')
                ->with('artisan_output', $output !== '' ? $output : 'Nothing to migrate.');
        } catch (\Throwable $e) {
            Log::error('Admin migration command failed', [
                'message' => $e->getMessage(),
                'exception' => $e::class,
            ]);

            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'Migration failed: '.$e->getMessage());
        } finally {
            optional($lock)->release();
        }
    }
}
