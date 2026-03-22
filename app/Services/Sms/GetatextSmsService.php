<?php

namespace App\Services\Sms;

use App\Models\ApiRequestLog;
use App\Models\ApiServer;
use App\Services\Sms\Contracts\SmsServerInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Getatext API (Server 3) – USA-focused SMS rentals.
 * Base: https://getatext.com
 * Auth: `Auth: YOUR_API_KEY` header on all requests.
 *
 * @see https://getatext.com API docs
 */
class GetatextSmsService implements SmsServerInterface
{
    protected ApiServer $server;

    protected string $baseUrl = 'https://getatext.com';

    public function __construct(ApiServer $server)
    {
        $this->server = $server;
        if (!empty($server->base_url)) {
            $this->baseUrl = rtrim($server->base_url, '/');
        }
    }

    protected function getApiKey(): string
    {
        return $this->server->getDecryptedApiKey();
    }

    protected function authHeaders(): array
    {
        return [
            'Auth' => $this->getApiKey(),
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    /** GET requests: omit Content-Type — some APIs/proxies reject GET with a body content type. */
    protected function authHeadersForGet(): array
    {
        return [
            'Auth' => $this->getApiKey(),
            'Accept' => 'application/json',
            'User-Agent' => 'Mozilla/5.0 (compatible; PrinceExchangeSMS/1.0; +https://github.com)',
        ];
    }

    /**
     * @param  array<string, mixed>|null  $json  Request body for POST
     * @return array<string, mixed>
     */
    protected function post(string $path, ?array $json = null, string $logAction = 'post'): array
    {
        $url = $this->baseUrl . $path;
        $start = microtime(true);
        $response = Http::timeout(45)->withHeaders($this->authHeaders())->post($url, $json ?? []);
        $duration = (microtime(true) - $start) * 1000;

        if (config('app.log_api_requests', false)) {
            ApiRequestLog::create([
                'server_id' => $this->server->id,
                'action' => $logAction,
                'method' => 'POST',
                'url' => $url,
                'status_code' => $response->status(),
                'response_body' => substr((string) $response->body(), 0, 2000),
                'duration_ms' => round($duration, 2),
            ]);
        }

        $body = $response->body();
        $data = json_decode($body, true);

        if (!$response->successful()) {
            $msg = $this->errorMessageFromBody(is_array($data) ? $data : null, $body) ?? ('HTTP ' . $response->status());
            throw new \RuntimeException($msg);
        }

        if (!is_array($data)) {
            throw new \RuntimeException('Invalid JSON from Getatext API');
        }

        if (isset($data['errors']) && $data['errors'] !== null && $data['errors'] !== '' && (string) $data['errors'] !== 'null') {
            $err = is_string($data['errors']) ? $data['errors'] : json_encode($data['errors']);
            throw new \RuntimeException($err);
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    protected function get(string $path, string $logAction = 'get'): array
    {
        $url = $this->baseUrl . $path;
        $start = microtime(true);
        $response = Http::timeout(30)->withHeaders($this->authHeadersForGet())->get($url);
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

        if (!$response->successful()) {
            $body = $response->body();
            $decoded = json_decode($body, true);
            $msg = $this->errorMessageFromBody(is_array($decoded) ? $decoded : null, $body) ?? ('HTTP ' . $response->status());

            throw new \RuntimeException($msg);
        }

        $body = $response->body();
        $trim = trim($body);
        if ($trim === '') {
            throw new \RuntimeException('Empty response from Getatext API');
        }

        $data = json_decode($body, true);
        if (!is_array($data)) {
            throw new \RuntimeException('Invalid JSON from Getatext API: ' . substr($trim, 0, 120));
        }

        // List at root [{...}, ...] — valid; object with errors — check below
        if (isset($data['errors']) && $data['errors'] !== null && $data['errors'] !== '' && (string) $data['errors'] !== 'null') {
            $err = is_string($data['errors']) ? $data['errors'] : json_encode($data['errors']);
            throw new \RuntimeException($err);
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>|null  $data
     */
    private function errorMessageFromBody(?array $data, string $rawBody): ?string
    {
        if (is_array($data) && isset($data['errors']) && $data['errors'] !== null && $data['errors'] !== 'null') {
            return is_string($data['errors']) ? $data['errors'] : json_encode($data['errors']);
        }
        $trim = trim($rawBody);

        return $trim !== '' ? substr($trim, 0, 300) : null;
    }

    public function getBalance(): float
    {
        $data = $this->get('/api/v1/balance', 'balance');
        $bal = $data['balance'] ?? null;

        return $bal !== null ? (float) $bal : 0.0;
    }

    /**
     * @return array<int, array{code: string, name: string, price: float}>
     */
    public function getServices(?string $countryCode = null): array
    {
        $cacheKey = 'getatext_services_' . $this->server->id;

        try {
            return Cache::remember($cacheKey, now()->addMinutes(30), function () {
                $raw = $this->get('/api/v1/prices-info', 'prices-info');
                $services = $this->extractServicesFromPricesPayload($raw);
                if (empty($services)) {
                    Log::warning('Getatext prices-info returned no parseable services', [
                        'server_id' => $this->server->id,
                        'keys' => is_array($raw) ? array_keys($raw) : [],
                    ]);
                }

                return $services;
            });
        } catch (\Throwable $e) {
            Log::error('Getatext getServices failed', [
                'server_id' => $this->server->id,
                'message' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Parse /api/v1/prices-info (and legacy getPrices-style) payloads into a flat service list.
     *
     * @param  array<mixed>  $raw
     * @return array<int, array{code: string, name: string, price: float}>
     */
    private function extractServicesFromPricesPayload(array $raw): array
    {
        $services = [];

        // Unwrap common envelopes: { "data": [...] }, { "data": { ... } }, etc.
        foreach (['data', 'services', 'result', 'items', 'prices', 'list'] as $key) {
            if (!isset($raw[$key]) || !is_array($raw[$key])) {
                continue;
            }
            $inner = $raw[$key];
            if (array_is_list($inner)) {
                $raw = $inner;
                break;
            }
            if (isset($inner['api_name']) || isset($inner['service_name'])) {
                $raw = $inner;
                break;
            }
        }

        // Single doc-style object: { "api_name": "whatsapp", "service_name": "...", "price": "0.55" }
        if (isset($raw['api_name']) || isset($raw['service_name'])) {
            $code = (string) ($raw['api_name'] ?? $raw['service'] ?? $raw['code'] ?? '');
            if ($code !== '') {
                $price = (float) ($raw['price'] ?? $raw['cost'] ?? 0);

                return [[
                    'code' => $code,
                    'name' => (string) ($raw['service_name'] ?? $raw['name'] ?? ucfirst($code)),
                    'price' => $price,
                ]];
            }
        }

        // Legacy getPrices-style: [ { "whatsapp": { "cost": 0.55, "count": ... } }, { "telegram": { ... } } ]
        if (array_is_list($raw)) {
            foreach ($raw as $item) {
                if (!is_array($item)) {
                    continue;
                }
                foreach ($item as $code => $meta) {
                    if (!is_string($code) && !is_int($code)) {
                        continue;
                    }
                    $codeStr = (string) $code;
                    if (!is_array($meta)) {
                        continue;
                    }
                    if (!isset($meta['cost']) && !isset($meta['price']) && !isset($meta['count']) && !isset($meta['physicalCount'])) {
                        continue;
                    }
                    $price = (float) ($meta['cost'] ?? $meta['price'] ?? 0);
                    $services[] = [
                        'code' => $codeStr,
                        'name' => (string) ($meta['service_name'] ?? ucfirst($codeStr)),
                        'price' => $price,
                    ];
                }
            }
        }

        // Top-level map: { "whatsapp": { "cost": ... }, "telegram": { ... } } (no list)
        if (!array_is_list($raw) && !isset($raw['status'])) {
            foreach ($raw as $code => $meta) {
                if (!is_string($code) && !is_int($code)) {
                    continue;
                }
                $codeStr = (string) $code;
                if (!is_array($meta)) {
                    continue;
                }
                if (in_array($codeStr, ['data', 'services', 'errors', 'status', 'message'], true)) {
                    continue;
                }
                if (!isset($meta['cost']) && !isset($meta['price']) && !isset($meta['count']) && !isset($meta['physicalCount']) && !isset($meta['api_name'])) {
                    continue;
                }
                $price = (float) ($meta['cost'] ?? $meta['price'] ?? 0);
                $services[] = [
                    'code' => $meta['api_name'] ?? $codeStr,
                    'name' => (string) ($meta['service_name'] ?? $meta['name'] ?? ucfirst((string) ($meta['api_name'] ?? $codeStr))),
                    'price' => $price,
                ];
            }
        }

        if (!empty($services)) {
            return array_values($services);
        }

        // Row list: [ { "api_name", "service_name", "price" }, ... ]
        $rows = $this->normalizePricesInfoRows($raw);
        foreach ($rows as $item) {
            if (!is_array($item)) {
                continue;
            }
            $code = (string) ($item['api_name'] ?? $item['service'] ?? $item['code'] ?? $item['slug'] ?? '');
            if ($code === '') {
                continue;
            }
            $price = (float) ($item['price'] ?? $item['cost'] ?? 0);
            $name = (string) ($item['service_name'] ?? $item['name'] ?? ucfirst($code));
            $services[] = [
                'code' => $code,
                'name' => $name,
                'price' => $price,
            ];
        }

        return $services;
    }

    /**
     * @param  array<mixed>  $raw
     * @return array<int, array<string, mixed>>
     */
    private function normalizePricesInfoRows(array $raw): array
    {
        if (isset($raw['data']) && is_array($raw['data'])) {
            return array_values(array_filter($raw['data'], 'is_array'));
        }
        if (isset($raw['services']) && is_array($raw['services'])) {
            return array_values(array_filter($raw['services'], 'is_array'));
        }
        if (isset($raw['result']) && is_array($raw['result'])) {
            return array_values(array_filter($raw['result'], 'is_array'));
        }
        if (isset($raw['items']) && is_array($raw['items'])) {
            return array_values(array_filter($raw['items'], 'is_array'));
        }
        if (isset($raw['api_name']) || isset($raw['service_name'])) {
            return [$raw];
        }
        if (array_is_list($raw)) {
            return array_values(array_filter($raw, 'is_array'));
        }
        foreach ($raw as $v) {
            if (is_array($v) && (isset($v['api_name']) || isset($v['service_name']))) {
                return array_values(array_filter($raw, 'is_array'));
            }
        }

        return [];
    }

    /**
     * USA-only (provider country id 187).
     *
     * @return array<int, array{code: string, name: string, provider_id: string}>
     */
    public function getCountries(): array
    {
        return [
            ['code' => '187', 'name' => 'United States', 'provider_id' => '187'],
        ];
    }

    /**
     * Estimated price for pricing endpoint (same shape as SMSConfirmed).
     *
     * @return array{price: float, success_rate: int}
     */
    public function getPriceForCountry(string $serviceCode, int $countryId): array
    {
        $services = $this->getServices(null);
        foreach ($services as $s) {
            if (($s['code'] ?? '') === $serviceCode) {
                return ['price' => (float) ($s['price'] ?? 0), 'success_rate' => 0];
            }
        }

        return ['price' => 0.0, 'success_rate' => 0];
    }

    /**
     * Operators for optional pool dropdown (USA).
     *
     * @return array<int, array{id: string, name: string}>
     */
    public function getOperators(?int $countryId = null): array
    {
        return [
            ['id' => 'at&t', 'name' => 'AT&T'],
            ['id' => 'tmobile', 'name' => 'T-Mobile'],
            ['id' => 'verizon', 'name' => 'Verizon'],
        ];
    }

    /**
     * @param  array<string, mixed>  $options  areas, carriers, number, operator (from pool)
     * @return array{order_id: string, phone_number: string, cost: float, expires_at?: string|null}
     */
    public function orderNumber(string $serviceCode, string $countryCode, ?float $maxPrice = null, array $options = []): array
    {
        $payload = [
            'service' => $serviceCode,
        ];

        if ($maxPrice !== null && $maxPrice > 0) {
            $payload['max_price'] = round($maxPrice, 2);
        }

        if (!empty($options['operator'])) {
            $payload['carrier'] = $this->normalizeCarrierCode((string) $options['operator']);
            $payload['keep_carrier'] = true;
        } elseif (!empty($options['carriers'])) {
            $mapped = $this->mapCarriersParamToCarrier((string) $options['carriers']);
            if ($mapped !== null) {
                $payload['carrier'] = $mapped;
                $payload['keep_carrier'] = true;
            }
        }

        if (!empty($options['areas'])) {
            $payload['area_codes'] = preg_replace('/\s+/', '', (string) $options['areas']);
            $payload['lock_area_code'] = true;
        }

        $data = $this->post('/api/v1/rent-a-number', $payload, 'rent-a-number');

        // Some responses nest payload under "data" or use alternate keys
        if (isset($data['data']) && is_array($data['data'])) {
            $data = array_merge($data, $data['data']);
        }

        $status = strtolower((string) ($data['status'] ?? ''));
        if ($status !== '' && $status !== 'success') {
            $err = $data['errors'] ?? $data['message'] ?? $data['error'] ?? json_encode($data);
            $errStr = is_string($err) ? $err : json_encode($err);
            throw new \RuntimeException('Getatext: ' . $errStr);
        }

        $orderId = (string) ($data['id'] ?? $data['rental_id'] ?? $data['order_id'] ?? '');
        $phone = (string) ($data['number'] ?? $data['phone'] ?? $data['phone_number'] ?? '');
        if ($orderId === '' || $phone === '') {
            Log::warning('Getatext rent-a-number unexpected shape', ['keys' => array_keys($data), 'server_id' => $this->server->id]);
            throw new \RuntimeException('Order failed: missing id or number in provider response.');
        }

        $price = isset($data['price']) ? (float) $data['price'] : ($maxPrice ?? 0.5);
        $endTime = $data['end_time'] ?? $data['expires_at'] ?? null;

        $out = [
            'order_id' => $orderId,
            'phone_number' => $phone,
            'cost' => $price,
        ];
        if (is_string($endTime) && $endTime !== '') {
            $out['expires_at'] = $endTime;
        }

        return $out;
    }

    private function normalizeCarrierCode(string $code): string
    {
        $c = strtolower(trim($code));

        return match ($c) {
            'att', 'at&t' => 'at&t',
            'tmo', 'tmobile', 't-mobile' => 'tmobile',
            'vz', 'verizon' => 'verizon',
            default => $code,
        };
    }

    private function mapCarriersParamToCarrier(string $carriers): ?string
    {
        $parts = array_filter(array_map('trim', explode(',', strtolower($carriers))));
        $first = $parts[0] ?? '';

        return match ($first) {
            'tmo', 'tmobile' => 'tmobile',
            'vz', 'verizon' => 'verizon',
            'att', 'at&t' => 'at&t',
            default => $first !== '' ? $this->normalizeCarrierCode($first) : null,
        };
    }

    /**
     * @return array{status: 'ok'|'wait'|'cancel', code: string|null}
     */
    public function getSms(string $orderId): array
    {
        $body = trim($orderId);
        if ($body === '') {
            return ['status' => 'cancel', 'code' => null];
        }

        try {
            $data = $this->post('/api/v1/rental-status', ['id' => (int) $orderId], 'rental-status');
        } catch (\Throwable $e) {
            Log::warning('Getatext rental-status failed', ['order_id' => $orderId, 'message' => $e->getMessage()]);

            return ['status' => 'wait', 'code' => null];
        }

        $code = $data['code'] ?? null;
        if ($code !== null && $code !== '') {
            $codeStr = is_string($code) ? trim($code) : (string) $code;

            return ['status' => 'ok', 'code' => $codeStr];
        }

        $status = strtolower((string) ($data['status'] ?? ''));

        if (in_array($status, ['cancelled', 'canceled', 'expired', 'completed'], true)) {
            return ['status' => 'cancel', 'code' => null];
        }

        return ['status' => 'wait', 'code' => null];
    }

    public function cancelNumber(string $orderId): bool
    {
        try {
            $data = $this->post('/api/v1/cancel-rental', ['id' => (int) $orderId], 'cancel-rental');
            $st = strtolower((string) ($data['status'] ?? ''));

            return $st === 'cancelled' || $st === 'canceled';
        } catch (\Throwable $e) {
            Log::warning('Getatext cancel failed', ['order_id' => $orderId, 'message' => $e->getMessage()]);

            return false;
        }
    }
}
