<?php

namespace App\Services;

use App\Models\ApiServer;
use App\Models\Rental;
use App\Services\Sms\SmsServerFactory;
use Illuminate\Support\Facades\DB;

class RentalService
{
    public function __construct(
        protected WalletService $wallet,
        protected PricingService $pricing
    ) {}

    /**
     * Create a new rental: validate, charge wallet, order number from provider, save rental.
     * @param array $options Provider-specific (e.g. areas, carriers, number for DaisySMS USA).
     */
    public function createRental(int $userId, int $serverId, string $serviceCode, string $countryCode, array $options = []): Rental
    {
        $user = \App\Models\User::findOrFail($userId);
        $server = ApiServer::active()->findOrFail($serverId);

        if ($server->isUsaOnly()) {
            $countryCode = 'US';
        }

        $cost = $this->pricing->getPrice($serverId, $countryCode, $serviceCode);
        if ($cost <= 0) {
            throw new \RuntimeException('Pricing not configured for this service/country.');
        }
        if (!empty($options['areas']) || !empty($options['carriers'])) {
            $cost = round($cost * 1.2, 4);
        }

        $apiUsd = $this->pricing->getApiPriceUsd($serverId, $countryCode, $serviceCode);
        $maxPriceUsd = $apiUsd > 0 ? $apiUsd * 1.5 : 5.0;
        if (!empty($options['areas']) || !empty($options['carriers'])) {
            $maxPriceUsd = round($maxPriceUsd * 1.2, 2);
        }

        $rental = new Rental([
            'user_id' => $userId,
            'server_id' => $serverId,
            'country_code' => $countryCode,
            'service_code' => $serviceCode,
            'cost' => $cost,
            'status' => Rental::STATUS_PENDING,
        ]);

        return DB::transaction(function () use ($user, $server, $rental, $serviceCode, $countryCode, $cost, $maxPriceUsd, $options) {
            $this->wallet->chargeForRental($user, $cost, $rental);
            $rental->save();

            try {
                $client = SmsServerFactory::make($server);
                $result = $client->orderNumber($serviceCode, $countryCode === 'US' ? '187' : $countryCode, $maxPriceUsd, $options);
            } catch (\Throwable $e) {
                $this->wallet->refundForRental($user, $cost, $rental, 'order_failed: ' . $e->getMessage());
                $rental->update(['status' => Rental::STATUS_CANCELLED]);
                throw $e;
            }

            $rental->update([
                'order_id' => $result['order_id'],
                'phone_number' => $result['phone_number'],
                'status' => Rental::STATUS_ACTIVE,
                'expires_at' => now()->addMinutes(15),
            ]);

            return $rental->fresh();
        });
    }

    public function cancelRental(Rental $rental): void
    {
        if (!$rental->isActive()) {
            return;
        }
        $server = $rental->server;
        $client = SmsServerFactory::make($server);
        try {
            $client->cancelNumber($rental->order_id);
        } catch (\Throwable) {
            // log but continue to refund
        }
        $this->wallet->refundForRental($rental->user, (float) $rental->cost, $rental, 'user_cancelled');
        $rental->update(['status' => Rental::STATUS_CANCELLED]);
    }

    /**
     * Expire an active rental (cancel with provider + refund). Call when expires_at has passed.
     */
    public function expireRental(Rental $rental): void
    {
        if (!in_array($rental->status, [Rental::STATUS_ACTIVE, Rental::STATUS_PENDING], true)) {
            return;
        }
        $server = $rental->server;
        $client = SmsServerFactory::make($server);
        try {
            $client->cancelNumber($rental->order_id);
        } catch (\Throwable) {
            // log but continue to refund
        }
        $this->wallet->refundForRental($rental->user, (float) $rental->cost, $rental, 'expired');
        $rental->update(['status' => Rental::STATUS_EXPIRED]);
    }

    /** Expire all overdue active rentals for a user (e.g. on dashboard load). */
    public function expireOverdueRentalsForUser(int $userId): int
    {
        $count = 0;
        Rental::where('user_id', $userId)
            ->whereIn('status', [Rental::STATUS_ACTIVE, Rental::STATUS_PENDING])
            ->where('expires_at', '<', now())
            ->each(function (Rental $rental) use (&$count) {
                $this->expireRental($rental);
                $count++;
            });
        return $count;
    }

    public function checkAndUpdateSms(Rental $rental): void
    {
        if (!in_array($rental->status, [Rental::STATUS_ACTIVE, Rental::STATUS_COMPLETED], true)) {
            return;
        }
        if ($rental->expires_at && $rental->expires_at->isPast()) {
            $this->expireRental($rental);
            return;
        }
        $client = SmsServerFactory::make($rental->server);
        $result = $client->getSms($rental->order_id);
        if ($result['status'] === 'ok' && $result['code']) {
            $code = is_string($result['code']) ? trim($result['code']) : (string) $result['code'];
            $messages = $rental->sms_messages ?? [];
            if (!is_array($messages)) {
                $messages = [];
            }
            $last = end($messages);
            $lastCode = is_array($last) ? ($last['code'] ?? '') : '';
            if ($code !== '' && $code !== $lastCode) {
                $messages[] = ['code' => $code, 'received_at' => now()->toIso8601String()];
            }
            $rental->update([
                'sms_code' => $code,
                'sms_messages' => $messages,
                'status' => Rental::STATUS_COMPLETED,
            ]);
        } elseif ($result['status'] === 'cancel') {
            $rental->update(['status' => Rental::STATUS_CANCELLED]);
        }
    }
}
