<?php

namespace App\Services\Sms;

use App\Models\ApiRequestLog;
use App\Models\ApiServer;
use App\Services\Sms\Contracts\SmsServerInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
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
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    protected function getJson(string $path, array $query = [], bool $auth = false, string $logAction = 'get', array $context = []): array
    {
        $url = $this->baseUrl.$path;
        $headers = $auth ? $this->authHeaders() : $this->guestHeaders();

        return $this->requestGet($url, $headers, $query, $logAction, (int) config('services.fivesim.timeout', 45), $context);
    }

    /**
     * @param  array<string, string>  $headers
     * @param  array<string, mixed>  $query
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    protected function requestGet(
        string $url,
        array $headers,
        array $query,
        string $logAction,
        int $timeoutSeconds,
        array $context = []
    ): array {
        $start = microtime(true);

        try {
            $response = Http::withHeaders($headers)
                ->connectTimeout((int) config('services.fivesim.connect_timeout', 15))
                ->timeout($timeoutSeconds)
                ->get($url, $query);

            $durationMs = (microtime(true) - $start) * 1000;
            $status = $response->status();
            $rawBody = (string) $response->body();

            $this->recordHttpLog($logAction, $url, $status, $rawBody, null, $durationMs, $context);

            if (! $response->successful()) {
                throw new \RuntimeException($this->customerMessageFromResponse($response));
            }

            $data = $response->json();

            return is_array($data) ? $data : [];
        } catch (ConnectionException $e) {
            $durationMs = (microtime(true) - $start) * 1000;
            $this->recordHttpLog($logAction, $url, 0, null, $e->getMessage(), $durationMs, $context);

            throw new \RuntimeException('Network error, please try again.');
        } catch (RequestException $e) {
            $durationMs = (microtime(true) - $start) * 1000;
            $httpResponse = $e->response;
            $status = $httpResponse ? $httpResponse->status() : 0;
            $rawBody = $httpResponse ? (string) $httpResponse->body() : null;
            $this->recordHttpLog($logAction, $url, $status, $rawBody, $e->getMessage(), $durationMs, $context);

            throw new \RuntimeException(
                $httpResponse
                    ? $this->customerMessageFromResponse($httpResponse)
                    : 'Network error, please try again.'
            );
        }
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    protected function recordHttpLog(
        string $action,
        string $url,
        int $statusCode,
        ?string $responseBody,
        ?string $error,
        float $durationMs,
        array $extra = []
    ): void {
        $bodySample = $responseBody !== null ? substr($responseBody, 0, 2000) : null;
        $failed = $error !== null || $statusCode === 0 || $statusCode >= 400;

        $context = array_merge([
            'provider' => 'fivesim',
            'action' => $action,
            'server_id' => $this->server->id,
            'server_name' => $this->server->name,
            'url' => $url,
            'status_code' => $statusCode ?: null,
            'duration_ms' => round($durationMs, 2),
            'connect_timeout' => (int) config('services.fivesim.connect_timeout', 15),
            'timeout' => (int) config('services.fivesim.timeout', 45),
        ], $extra);

        if ($error !== null) {
            $context['curl_error'] = $error;
        }
        if ($bodySample !== null && $bodySample !== '') {
            $context['response_body'] = $bodySample;
        }

        if ($failed) {
            Log::error('FiveSim HTTP request failed', $context);
        } elseif (config('app.log_api_requests', false)) {
            Log::info('FiveSim HTTP response', $context);
        }

        if ($failed || config('app.log_api_requests', false)) {
            try {
                ApiRequestLog::create([
                    'server_id' => $this->server->id,
                    'action' => $action,
                    'method' => 'GET',
                    'url' => $url,
                    'status_code' => $statusCode > 0 ? $statusCode : null,
                    'response_body' => $bodySample,
                    'error' => $error !== null
                        ? substr($error, 0, 1000)
                        : ($statusCode >= 400 ? 'HTTP '.$statusCode : null),
                    'duration_ms' => round($durationMs, 2),
                ]);
            } catch (\Throwable $logException) {
                Log::warning('FiveSim: could not write api_request_logs row', [
                    'message' => $logException->getMessage(),
                ]);
            }
        }
    }

    protected function customerMessageFromResponse(Response $response): string
    {
        $status = $response->status();
        $rawBody = (string) $response->body();
        if ($rawBody !== '') {
            $json = $response->json();
            if (is_array($json)) {
                foreach (['message', 'error', 'detail', 'msg', 'reason'] as $key) {
                    $value = $json[$key] ?? null;
                    if (is_string($value) && trim($value) !== '') {
                        return 'Unable to rent a number: '.trim($value);
                    }
                }
            }
        }

        if ($status === 401 || $status === 403) {
            return 'Provider authentication failed. Check the API key in Admin → Servers.';
        }
        if ($status === 402) {
            return 'Provider balance is too low. Please try again later.';
        }
        if ($status === 404) {
            return 'Number not available for this country/service. Try another option.';
        }
        if ($status >= 500) {
            return 'Provider is temporarily unavailable. Please try again.';
        }

        return $status > 0
            ? 'Provider returned HTTP '.$status.'. Please try again.'
            : 'Network error, please try again.';
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

        $data = $this->requestGet($url, $this->authHeaders(), $query, 'order', (int) config('services.fivesim.timeout', 45), [
            'country' => $country,
            'product' => $product,
            'operator' => $operator,
            'max_price' => $maxPrice,
        ]);

        $orderId = $data['id'] ?? null;
        $phone = $data['phone'] ?? '';
        if ($orderId === null || $phone === '') {
            Log::warning('FiveSim order unexpected response', [
                'server_id' => $this->server->id,
                'country' => $country,
                'product' => $product,
                'operator' => $operator,
                'response_keys' => array_keys($data),
                'response_sample' => json_encode(array_slice($data, 0, 8)),
            ]);

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
