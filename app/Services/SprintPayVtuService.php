<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SprintPayVtuService
{
    private const PURCHASE_PATHS = [
        'airtime' => '/merchant/vas/buy-ng-airtime',
        'data' => '/merchant/vas/buy-data',
        'cable' => '/merchant/vas/buy-cable',
        'electricity' => '/merchant/vas/buy-electricity',
    ];

    public function enabled(): bool
    {
        return (string) SiteSetting::get('vtu_enabled', '0') === '1';
    }

    public function configured(): bool
    {
        return $this->webkey() !== '' && $this->secret() !== '';
    }

    public function webkey(): string
    {
        return (string) (SiteSetting::get('vtu_sprintpay_key') ?: config('services.sprintpay.vtu_key', ''));
    }

    public function secret(): string
    {
        return (string) (SiteSetting::getEncrypted('vtu_sprintpay_secret') ?: config('services.sprintpay.vtu_secret', ''));
    }

    public function baseUrl(): string
    {
        return rtrim((string) SiteSetting::get('vtu_sprintpay_base_url', config('services.sprintpay.vtu_base_url', 'https://web.sprintpay.online/api')), '/');
    }

    /**
     * Public SprintPay catalog endpoints are safe to proxy to the customer UI.
     *
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public function catalog(string $path, array $query = []): array
    {
        $path = '/'.ltrim($path, '/');
        $url = $this->baseUrl().$path;

        $response = Http::acceptJson()
            ->connectTimeout(10)
            ->timeout(30)
            ->get($url, $query);

        $body = $response->json();
        Log::info('SprintPay VTU catalog response', [
            'path' => $path,
            'status' => $response->status(),
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException('Unable to load VTU products. Please try again.');
        }

        return is_array($body) ? $body : [];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{success: bool, message: string, data: array<string, mixed>}
     */
    public function purchase(string $type, array $payload): array
    {
        if (! $this->enabled()) {
            throw new \RuntimeException('VTU service is currently disabled.');
        }
        if (! $this->configured()) {
            throw new \RuntimeException('VTU service is not configured.');
        }

        $path = self::PURCHASE_PATHS[$type] ?? null;
        if (! $path) {
            throw new \RuntimeException('Unsupported VTU product.');
        }

        $payload['key'] = $this->webkey();
        $url = $this->baseUrl().$path;

        $response = Http::acceptJson()
            ->asJson()
            ->withToken($this->secret())
            ->connectTimeout(15)
            ->timeout(60)
            ->post($url, $payload);

        $data = $response->json();
        $data = is_array($data) ? $data : [];

        Log::info('SprintPay VTU purchase response', [
            'type' => $type,
            'status' => $response->status(),
            'success' => $response->successful(),
            'response' => $data,
        ]);

        if (! $response->successful()) {
            return [
                'success' => false,
                'message' => $this->messageFromResponse($data, 'Provider request failed. Please try again.'),
                'data' => $data,
            ];
        }

        return [
            'success' => $this->looksSuccessful($data),
            'message' => $this->messageFromResponse($data, 'Purchase submitted.'),
            'data' => $data,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function looksSuccessful(array $data): bool
    {
        foreach (['success', 'status'] as $key) {
            if (($data[$key] ?? null) === true) {
                return true;
            }
        }

        $status = strtolower((string) ($data['status'] ?? $data['code'] ?? $data['responseCode'] ?? ''));

        return in_array($status, ['success', 'successful', 'completed', 'delivered', '200', '00', '000'], true);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function messageFromResponse(array $data, string $default): string
    {
        foreach (['message', 'response_description', 'description', 'error', 'detail'] as $key) {
            $value = $data[$key] ?? null;
            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return $default;
    }
}
