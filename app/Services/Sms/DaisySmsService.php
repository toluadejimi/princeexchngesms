<?php

namespace App\Services\Sms;

use App\Models\ApiServer;
use App\Models\ApiRequestLog;
use App\Services\Sms\Contracts\SmsServerInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;

/**
 * DaisySMS USA API – sms-activate compatible.
 * Base URL: https://daisysms.com
 * Auth: api_key in query string. All actions via GET to /stubs/handler_api.php
 */
class DaisySmsService implements SmsServerInterface
{
    protected ApiServer $server;
    protected string $baseUrl = 'https://daisysms.com';

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

    /**
     * GET request to handler_api.php. Returns raw response for header access when needed.
     */
    protected function get(string $action, array $params = [], string $logAction = 'request'): Response
    {
        $params['api_key'] = $this->getApiKey();
        $params['action'] = $action;
        $url = $this->baseUrl . '/stubs/handler_api.php?' . http_build_query($params);
        $start = microtime(true);
        $response = Http::timeout(15)->get($url);
        $duration = (microtime(true) - $start) * 1000;

        ApiRequestLog::create([
            'server_id' => $this->server->id,
            'action' => $logAction,
            'method' => 'GET',
            'url' => $this->baseUrl . '/stubs/handler_api.php',
            'status_code' => $response->status(),
            'response_body' => substr((string) $response->body(), 0, 2000),
            'duration_ms' => round($duration, 2),
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException('DaisySMS API request failed: HTTP ' . $response->status());
        }

        $body = trim($response->body());
        if (str_starts_with($body, 'BAD_KEY')) {
            throw new \RuntimeException('DaisySMS: Invalid API key');
        }

        return $response;
    }

    protected function request(string $action, array $params = [], string $logAction = 'request'): string
    {
        return trim($this->get($action, $params, $logAction)->body());
    }

    public function getBalance(): float
    {
        $body = $this->request('getBalance', [], 'getBalance');
        if (!str_starts_with($body, 'ACCESS_BALANCE:')) {
            throw new \RuntimeException('DaisySMS getBalance unexpected: ' . $body);
        }
        return (float) substr($body, strlen('ACCESS_BALANCE:'));
    }

    public function getServices(?string $countryCode = null): array
    {
        $cacheKey = 'daisysms_services_' . $this->server->id;
        return Cache::remember($cacheKey, now()->addMinutes(15), function () {
            $services = $this->parseServicesFromGetPricesVerification();
            if (empty($services)) {
                $services = $this->parseServicesFromGetPrices();
            }
            if (empty($services)) {
                return $this->getServicesFromPricing();
            }
            return $services;
        });
    }

    /** getPricesVerification returns: service => country => data. USA = 187. */
    protected function parseServicesFromGetPricesVerification(): array
    {
        try {
            $body = $this->request('getPricesVerification', [], 'getPricesVerification');
            $data = json_decode($body, true);
        } catch (\Throwable) {
            return [];
        }
        if (!is_array($data)) {
            return [];
        }
        $services = [];
        foreach ($data as $serviceCode => $countries) {
            if (!is_array($countries)) {
                continue;
            }
            foreach ($countries as $countryId => $info) {
                if (!is_array($info) || ($countryId !== 187 && $countryId !== '187')) {
                    continue;
                }
                $price = $info['cost'] ?? $info['price'] ?? 0;
                $services[] = [
                    'code' => $serviceCode,
                    'name' => $this->serviceCodeToName($serviceCode),
                    'price' => (float) $price,
                ];
                break;
            }
        }
        return $services;
    }

    /** getPrices returns: country => service => data. USA = 187. */
    protected function parseServicesFromGetPrices(): array
    {
        try {
            $body = $this->request('getPrices', [], 'getPrices');
            $data = json_decode($body, true);
        } catch (\Throwable) {
            return [];
        }
        if (!is_array($data)) {
            return [];
        }
        $usa = $data[187] ?? $data['187'] ?? null;
        if (!is_array($usa)) {
            return [];
        }
        $services = [];
        foreach ($usa as $serviceCode => $info) {
            if (!is_array($info)) {
                continue;
            }
            $price = $info['cost'] ?? $info['price'] ?? 0;
            $services[] = [
                'code' => $serviceCode,
                'name' => $this->serviceCodeToName($serviceCode),
                'price' => (float) $price,
            ];
        }
        return $services;
    }

    protected function getServicesFromPricing(): array
    {
        $rows = \App\Models\ServerPricing::where('server_id', $this->server->id)
            ->where('active', true)
            ->get();
        $services = [];
        foreach ($rows as $p) {
            $services[] = [
                'code' => $p->service_code,
                'name' => $this->serviceCodeToName($p->service_code),
                'price' => (float) $p->price,
            ];
        }
        return $services;
    }

    protected function serviceCodeToName(string $code): string
    {
        $names = [
            'wa' => 'WhatsApp', 'go' => 'Google', 'tg' => 'Telegram', 'ds' => 'Discord',
            'fb' => 'Facebook', 'am' => 'Amazon', 'tw' => 'Twitter', 'ig' => 'Instagram',
        ];
        return $names[$code] ?? ucfirst($code);
    }

    public function getCountries(): array
    {
        return [
            ['code' => 'US', 'name' => 'United States', 'provider_id' => '187'],
        ];
    }

    public function orderNumber(string $serviceCode, string $countryCode, ?float $maxPrice = null, array $options = []): array
    {
        $params = [
            'service' => $serviceCode,
            'max_price' => $maxPrice ?? 5.0,
        ];
        if (!empty($options['areas'])) {
            $params['areas'] = is_array($options['areas']) ? implode(',', $options['areas']) : $options['areas'];
        }
        if (!empty($options['carriers'])) {
            $params['carriers'] = is_array($options['carriers']) ? implode(',', $options['carriers']) : $options['carriers'];
        }
        if (!empty($options['number'])) {
            $params['number'] = preg_replace('/\D/', '', $options['number']);
        }
        $response = $this->get('getNumber', $params, 'getNumber');
        $body = trim($response->body());

        if (str_starts_with($body, 'NO_NUMBERS')) {
            throw new \RuntimeException('No numbers available for this service.');
        }
        if (str_starts_with($body, 'NO_MONEY')) {
            throw new \RuntimeException('Insufficient balance on provider.');
        }
        if (str_starts_with($body, 'MAX_PRICE_EXCEEDED')) {
            throw new \RuntimeException('Price exceeded your maximum.');
        }
        if (str_starts_with($body, 'TOO_MANY_ACTIVE_RENTALS')) {
            throw new \RuntimeException('Too many active rentals on provider.');
        }
        if (!str_starts_with($body, 'ACCESS_NUMBER:')) {
            throw new \RuntimeException('DaisySMS order failed: ' . $body);
        }

        $parts = explode(':', trim(substr($body, strlen('ACCESS_NUMBER:'))));
        $orderId = $parts[0] ?? '';
        $phone = $parts[1] ?? '';

        $cost = $this->getPriceFromOrder($serviceCode, 'US');
        $xPrice = $response->header('X-Price');
        if ($xPrice !== null && $xPrice !== '' && is_numeric($xPrice)) {
            $cost = (float) $xPrice;
        }

        return [
            'order_id' => $orderId,
            'phone_number' => $phone,
            'cost' => $cost,
        ];
    }

    protected function getPriceFromOrder(string $serviceCode, string $countryCode): float
    {
        $pricing = \App\Models\ServerPricing::where('server_id', $this->server->id)
            ->where('service_code', $serviceCode)
            ->where(function ($q) use ($countryCode) {
                $q->where('country_code', $countryCode)->orWhereNull('country_code');
            })
            ->where('active', true)
            ->first();
        return $pricing ? (float) $pricing->price : 0.50;
    }

    public function getSms(string $orderId): array
    {
        $body = $this->request('getStatus', ['id' => $orderId], 'getStatus');
        if (str_starts_with($body, 'STATUS_OK:')) {
            $code = trim(substr($body, strlen('STATUS_OK:')));
            return ['status' => 'ok', 'code' => $code];
        }
        if (str_starts_with($body, 'STATUS_WAIT_CODE')) {
            return ['status' => 'wait', 'code' => null];
        }
        if (str_starts_with($body, 'STATUS_CANCEL')) {
            return ['status' => 'cancel', 'code' => null];
        }
        if (str_starts_with($body, 'NO_ACTIVATION')) {
            return ['status' => 'cancel', 'code' => null];
        }
        return ['status' => 'wait', 'code' => null];
    }

    public function cancelNumber(string $orderId): bool
    {
        $body = $this->request('setStatus', ['id' => $orderId, 'status' => 8], 'cancel');
        return str_starts_with($body, 'ACCESS_CANCEL');
    }
}
