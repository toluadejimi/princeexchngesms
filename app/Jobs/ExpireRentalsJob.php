<?php

namespace App\Jobs;

use App\Models\Rental;
use App\Services\RentalService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExpireRentalsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(RentalService $rentalService): void
    {
        $overdue = Rental::whereIn('status', [Rental::STATUS_PENDING, Rental::STATUS_ACTIVE])
            ->where('expires_at', '<', now())
            ->get();
        foreach ($overdue as $rental) {
            try {
                $rentalService->expireRental($rental);
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }
}
