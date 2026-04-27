<?php

namespace App\Services\Sms;

use App\Models\ApiRequestLog;
use App\Models\ApiServer;
use App\Services\Sms\Contracts\SmsServerInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Worldwide activation API (internal type: fivesim). Auth: Bearer token on base URL from admin.
 */
class FiveSimSmsService implements SmsServerInterface
{
    protected ApiServer $server;

    protected string $baseUrl = 'https://5sim.net';

    public function __construct(ApiServer $server)
    {
        $this->server = $server;
        if (! empty($server->base_url)) {
            $this->baseUrl = rtrim($server->base_url, '/');
        }
    }

    protected function getToken(): string
    {
        return $this->server->getDecryptedApiKey();
    }

    protected function guestHeaders(): array
    {
        return [
            'Accept' => 'application/json',
        ];
    }

    protected function authHeaders(): array
    {
        return [
            'Authorization' => 'Bearer '.$this->getToken(),
            'Accept' => 'application/json',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getJson(string $path, array $query = [], bool $auth = false, string $logAction = 'get'): array
    {
        $url = $this->baseUrl.$path;
        $start = microtime(true);
        $headers = $auth ? $this->authHeaders() : $this->guestHeaders();
        $response = Http::timeout(30)->withHeaders($headers)->get($url, $query);
        $duration = (microtime(true) - $start) * 1000;

        if (config('app.log_api_requests', false)) {
            ApiRequestLog::create([
                'server_id' => $this->server->id,
                'action' => $logAction,
                'method' => 'GET',
                'url' => $url,
                'status_code' => $response->status(),
                'response_body' => substr((string) $response->body(), 0, 2000),
                'duration_ms' => round($duration, 2),
            ]);
        }

        if (! $response->successful()) {
            throw new \RuntimeException('Network error, please try again.');
        }

        $data = $response->json();

        return is_array($data) ? $data : [];
    }

    public function getBalance(): float
    {
        $data = $this->getJson('/v1/user/profile', [], true, 'balance');

        return (float) ($data['balance'] ?? 0);
    }

    /**
     * @return array<int, array{code: string, name: string, price: float}>
     */
    public function getServices(?string $countryCode = null): array
    {
        $country = $countryCode ? strtolower(trim($countryCode)) : '';
        if ($country === '') {
            return [];
        }

        $cacheKey = 'fivesim_products_'.$this->server->id.'_'.$country;

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($country) {
            try {
                $data = $this->getJson('/v1/guest/products/'.rawurlencode($country).'/any', [], false, 'products');
            } catch (\Throwable $e) {
                Log::warning('Worldwide slot getServices failed', [
                    'server_id' => $this->server->id,
                    'country' => $country,
                    'message' => $e->getMessage(),
                ]);

                return [];
            }

            $services = [];
            foreach ($data as $productKey => $meta) {
                if (! is_string($productKey) || ! is_array($meta)) {
                    continue;
                }
                $category = strtoupper((string) ($meta['Category'] ?? $meta['category'] ?? ''));
                if ($category === 'HOSTING') {
                    continue;
                }
                $price = (float) ($meta['Price'] ?? $meta['price'] ?? 0);
                $qty = (int) ($meta['Qty'] ?? $meta['qty'] ?? 0);
                if ($qty <= 0 && $price <= 0) {
                    continue;
                }
                $name = ucfirst(str_replace('_', ' ', $productKey));
                $services[] = [
                    'code' => $productKey,
                    'name' => $name,
                    'price' => round($price, 4),
                ];
            }

            return $services;
        });
    }

    /**
     * @return array<int, array{code: string, name: string, provider_id: string}>
     */
    public function getCountries(): array
    {
        $cacheKey = 'fivesim_countries_'.$this->server->id;

        return Cache::remember($cacheKey, now()->addMinutes(30), function () {
            try {
                $data = $this->getJson('/v1/guest/countries', [], true, 'countries');
            } catch (\Throwable $e) {
                Log::warning('Worldwide slot getCountries failed', [
                    'server_id' => $this->server->id,
                    'message' => $e->getMessage(),
                ]);

                return [];
            }

            $countries = [];
            foreach ($data as $slug => $meta) {
                if (! is_string($slug) || ! is_array($meta)) {
                    continue;
                }
                $name = (string) ($meta['text_en'] ?? ucfirst($slug));
                $countries[] = [
                    'code' => $slug,
                    'name' => $name,
                    'provider_id' => $slug,
                ];
            }

            return $countries;
        });
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array{order_id: string, phone_number: string, cost: float, expires_at?: string|null}
     */
    public function orderNumber(string $serviceCode, string $countryCode, ?float $maxPrice = null, array $options = []): array
    {
        $country = strtolower(trim($countryCode));
        $product = strtolower(trim($serviceCode));
        $operator = 'any';
        if (! empty($options['pool_id'])) {
            $operator = strtolower(trim((string) $options['pool_id']));
        }

        $query = [];
        if ($maxPrice !== null && $maxPrice > 0) {
            $query['maxPrice'] = (string) round($maxPrice, 2);
        }

        $path = '/v1/user/buy/activation/'.rawurlencode($country).'/'.rawurlencode($operator).'/'.rawurlencode($product);
        $url = $this->baseUrl.$path;
        $start = microtime(true);
        $response = Http::timeout(45)->withHeaders($this->authHeaders())->get($url, $query);
        $duration = (microtime(true) - $start) * 1000;

        if (config('app.log_api_requests', false)) {
            ApiRequestLog::create([
                'server_id' => $this->server->id,
                'action' => 'order',
                'method' => 'GET',
                'url' => $url,
                'status_code' => $response->status(),
                'response_body' => substr((string) $response->body(), 0, 2000),
                'duration_ms' => round($duration, 2),
            ]);
        }

        if (! $response->successful()) {
            throw new \RuntimeException('Network error, please try again.');
        }

        $data = $response->json();
        if (! is_array($data)) {
            throw new \RuntimeException('Network error, please try again.');
        }

        $orderId = $data['id'] ?? null;
        $phone = $data['phone'] ?? '';
        if ($orderId === null || $phone === '') {
            Log::warning('Worldwide slot order unexpected response', ['keys' => array_keys($data)]);

            throw new \RuntimeException('Unable to rent a number right now. Please try again.');
        }

        $priceRaw = (float) ($data['price'] ?? 0);
        $costUsd = $priceRaw > 0 ? round($priceRaw, 4) : 0.0;

        return [
            'order_id' => (string) $orderId,
            'phone_number' => (string) $phone,
            'cost' => $costUsd,
            'expires_at' => isset($data['expires']) ? (string) $data['expires'] : null,
        ];
    }

    /**
     * @return array{status: string, code: ?string}
     */
    public function getSms(string $orderId): array
    {
        $data = $this->getJson('/v1/user/check/'.rawurlencode($orderId), [], true, 'check');

        $status = strtoupper((string) ($data['status'] ?? ''));
        $smsList = $data['sms'] ?? [];
        $code = null;
        if (is_array($smsList) && $smsList !== []) {
            $last = end($smsList);
            if (is_array($last)) {
                $code = isset($last['code']) ? (string) $last['code'] : null;
            }
        }

        if (in_array($status, ['CANCELED', 'CANCELLED', 'BANNED', 'TIMEOUT', 'EXPIRED'], true)) {
            return ['status' => 'cancel', 'code' => null];
        }

        if ($code !== null && $code !== '') {
            return ['status' => 'ok', 'code' => $code];
        }

        if (in_array($status, ['RECEIVED', 'FINISHED'], true)) {
            return ['status' => 'ok', 'code' => $code];
        }

        return ['status' => 'wait', 'code' => null];
    }

    public function cancelNumber(string $orderId): bool
    {
        try {
            $this->getJson('/v1/user/cancel/'.rawurlencode($orderId), [], true, 'cancel');

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
