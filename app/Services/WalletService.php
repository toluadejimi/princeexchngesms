<?php

namespace App\Services;

use App\Models\Rental;
use App\Models\SiteSetting;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

class WalletService
{
    /**
     * Deduct balance for a rental. Uses DB lock to prevent double charge.
     * Amount is in system currency (Naira or USD). Returns true on success.
     */
    public function chargeForRental(User $user, float $amount, Rental $rental): bool
    {
        return DB::transaction(function () use ($user, $amount, $rental) {
            $user = User::where('id', $user->id)->lockForUpdate()->firstOrFail();
            $balance = (float) $user->wallet_balance;
            if ($balance < $amount) {
                throw new \RuntimeException('Insufficient wallet balance. Required: ' . SiteSetting::formatWalletAmount($amount));
            }
            $newBalance = $balance - $amount;
            $user->update(['wallet_balance' => $newBalance]);
            WalletTransaction::create([
                'user_id' => $user->id,
                'type' => WalletTransaction::TYPE_RENTAL_CHARGE,
                'amount' => -$amount,
                'balance_after' => $newBalance,
                'reference_type' => 'rental',
                'reference_id' => $rental->id,
                'meta' => ['rental_id' => $rental->id],
            ]);
            return true;
        });
    }

    /**
     * Refund user when rental is cancelled or order fails.
     */
    public function refundForRental(User $user, float $amount, Rental $rental, string $reason = 'cancelled'): bool
    {
        return DB::transaction(function () use ($user, $amount, $rental, $reason) {
            $user = User::where('id', $user->id)->lockForUpdate()->firstOrFail();
            $newBalance = (float) $user->wallet_balance + $amount;
            $user->update(['wallet_balance' => $newBalance]);
            WalletTransaction::create([
                'user_id' => $user->id,
                'type' => WalletTransaction::TYPE_REFUND,
                'amount' => $amount,
                'balance_after' => $newBalance,
                'reference_type' => 'rental',
                'reference_id' => $rental->id,
                'meta' => ['reason' => $reason, 'rental_id' => $rental->id],
            ]);
            return true;
        });
    }

    /**
     * Admin deposit or adjustment.
     */
    public function adjust(User $user, float $amount, string $type = WalletTransaction::TYPE_ADMIN_ADJUSTMENT, ?array $meta = null): bool
    {
        return DB::transaction(function () use ($user, $amount, $type, $meta) {
            $user = User::where('id', $user->id)->lockForUpdate()->firstOrFail();
            $newBalance = (float) $user->wallet_balance + $amount;
            if ($newBalance < 0) {
                throw new \RuntimeException('Resulting balance cannot be negative.');
            }
            $user->update(['wallet_balance' => $newBalance]);
            WalletTransaction::create([
                'user_id' => $user->id,
                'type' => $type,
                'amount' => $amount,
                'balance_after' => $newBalance,
                'reference_type' => null,
                'reference_id' => null,
                'meta' => $meta,
            ]);
            return true;
        });
    }

    public function getBalance(User $user): float
    {
        return (float) $user->wallet_balance;
    }
}
