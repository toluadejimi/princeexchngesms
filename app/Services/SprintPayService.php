<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SprintPayService
{
    protected string $baseUrl = 'https://web.sprintpay.online';

    public function generateVirtualAccount(string $email, string $accountName, string $key): array
    {
        $response = Http::timeout(20)->post($this->baseUrl . '/api/generate-virtual-account', [
            'email' => $email,
            'account_name' => $accountName,
            'key' => $key,
        ]);

        $body = $response->json();
        Log::info('SprintPay generate-virtual-account', ['response' => $body]);

        $status = $body['status'] ?? false;
        $message = $body['message'] ?? 'Unknown error';

        if (!$status || !isset($body['data'])) {
            return ['success' => false, 'message' => $message];
        }

        $data = $body['data'];
        return [
            'success' => true,
            'account_number' => $data['account_number'] ?? $data['account_no'] ?? '',
            'account_name' => $data['account_name'] ?? '',
            'bank_name' => $data['bank_name'] ?? '',
        ];
    }

    /**
     * Build payment URL for instant funding (user is redirected here to pay).
     */
    public function paymentUrl(float $amount, string $ref, string $email): string
    {
        $key = config('services.sprintpay.key');
        return $this->baseUrl . '/pay?' . http_build_query([
            'amount' => (int) round($amount),
            'key' => $key,
            'ref' => $ref,
            'email' => $email,
        ]);
    }
}
