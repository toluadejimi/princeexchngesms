<?php

namespace App\Services;

use App\Models\User;
use App\Models\VtuTransaction;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class VtuPurchaseService
{
    public function __construct(
        private WalletService $wallet,
        private SprintPayVtuService $sprintPay
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function purchase(User $user, string $type, float $amount, array $payload): VtuTransaction
    {
        if ($amount <= 0) {
            throw new \RuntimeException('Enter a valid amount.');
        }

        $reference = 'VTU'.now()->format('ymdHis').Str::upper(Str::random(6));
        $payload['request_id'] = $reference;

        $transaction = VtuTransaction::create([
            'user_id' => $user->id,
            'type' => $type,
            'status' => VtuTransaction::STATUS_PENDING,
            'reference' => $reference,
            'service_id' => Arr::get($payload, 'service_id'),
            'variation_code' => Arr::get($payload, 'variation_code') ?: Arr::get($payload, 'variation'),
            'recipient' => (string) (Arr::get($payload, 'phone') ?: Arr::get($payload, 'billersCode') ?: Arr::get($payload, 'meter_number') ?: Arr::get($payload, 'smartcard_number')),
            'amount' => $amount,
            'wallet_debit' => $amount,
            'request_payload' => Arr::except($payload, ['key']),
        ]);

        $this->wallet->chargeForVtu($user, $amount, $transaction);

        try {
            $result = $this->sprintPay->purchase($type, $payload);
        } catch (\Throwable $e) {
            $this->wallet->refundForVtu($user, $amount, $transaction, 'provider_exception');
            $transaction->update([
                'status' => VtuTransaction::STATUS_REFUNDED,
                'message' => $e->getMessage(),
            ]);

            throw $e;
        }

        if (! $result['success']) {
            $this->wallet->refundForVtu($user, $amount, $transaction, 'provider_failed');
            $transaction->update([
                'status' => VtuTransaction::STATUS_REFUNDED,
                'message' => $result['message'],
                'response_payload' => $result['data'],
            ]);

            throw new \RuntimeException($result['message']);
        }

        $data = $result['data'];
        $transaction->update([
            'status' => VtuTransaction::STATUS_SUCCESSFUL,
            'provider_reference' => $this->providerReference($data),
            'customer_name' => $this->customerName($data),
            'token' => $this->token($data),
            'message' => $result['message'],
            'response_payload' => $data,
        ]);

        return $transaction->fresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function providerReference(array $data): ?string
    {
        foreach (['reference', 'request_id', 'transaction_id', 'transactionId', 'order_id', 'id'] as $key) {
            $value = Arr::get($data, $key);
            if (is_scalar($value) && (string) $value !== '') {
                return (string) $value;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function customerName(array $data): ?string
    {
        foreach (['customer_name', 'customerName', 'name', 'Customer_Name', 'content.Customer_Name'] as $key) {
            $value = Arr::get($data, $key);
            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function token(array $data): ?string
    {
        foreach (['token', 'Token', 'meter_token', 'purchased_code', 'content.token', 'content.Token'] as $key) {
            $value = Arr::get($data, $key);
            if (is_scalar($value) && (string) $value !== '') {
                return (string) $value;
            }
        }

        return null;
    }
}
