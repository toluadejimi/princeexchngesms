<?php

namespace App\Jobs;

use App\Models\Rental;
use App\Services\RentalService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PollRentalSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Rental $rental
    ) {}

    public function handle(RentalService $rentalService): void
    {
        $rentalService->checkAndUpdateSms($this->rental);
    }
}
