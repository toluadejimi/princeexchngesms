<?php

namespace App\Services\Sms;

use App\Models\ApiServer;
use App\Models\ApiRequestLog;
use App\Services\Sms\Contracts\SmsServerInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * SMSCONFIRMED API – multi-country SMS (Server 1).
 * Base URL: https://api.smsconfirmed.com/stubs/handler_api.php
 * Auth: api_key in query string. All actions via GET.
 */
class SmsConfirmedService implements SmsServerInterface
{
    protected ApiServer $server;

    protected string $baseUrl = 'https://api.smsconfirmed.com';

    public function __construct(ApiServer $server)
    {
        $this->server = $server;
        if (!empty($server->base_url)) {
            $this->baseUrl = rtrim($server->base_url, '/');
        }
    }

    protected function handlerUrl(): string
    {
        return str_contains($this->baseUrl, 'handler_api')
            ? $this->baseUrl
            : $this->baseUrl . '/stubs/handler_api.php';
    }

    protected function getApiKey(): string
    {
        return $this->server->getDecryptedApiKey();
    }

    protected function get(string $action, array $params = [], string $logAction = 'request'): string
    {
        $params['api_key'] = $this->getApiKey();
        $params['action'] = $action;
        $url = $this->handlerUrl() . '?' . http_build_query($params);
        $start = microtime(true);
        $response = Http::timeout(15)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (compatible; SMSRental/1.0; +https://github.com)',
                'Accept' => 'text/plain, application/json, */*',
            ])
            ->get($url);
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
            $body = trim((string) $response->body());
            $msg = 'Provider API failed: HTTP ' . $response->status();
            if ($body !== '' && preg_match('/^(NO_BALANCE|BAD_KEY|BLOCKED|NO_NUMBERS|CHANNELS_LIMIT)/', $body)) {
                $msg = $body;
            } elseif ($body !== '') {
                $msg .= '. Response: ' . substr($body, 0, 150);
            }
            throw new \RuntimeException($msg);
        }

        $body = trim($response->body());
        if (str_starts_with($body, 'BAD_KEY')) {
            throw new \RuntimeException('Invalid API key');
        }
        if (str_starts_with($body, 'BLOCKED')) {
            throw new \RuntimeException('Account blocked');
        }

        return $body;
    }

    public function getBalance(): float
    {
        $body = $this->get('getBalance', [], 'getBalance');
        if (!str_starts_with($body, 'ACCESS_BALANCE:')) {
            throw new \RuntimeException('Provider getBalance unexpected: ' . $body);
        }
        return (float) substr($body, strlen('ACCESS_BALANCE:'));
    }

    /**
     * getPrices(service, country?) – raw API response. Used for price lookup.
     */
    public function getPrices(string $serviceCode, ?int $countryId = null): array
    {
        $params = ['service' => $serviceCode];
        if ($countryId !== null && $countryId > 0) {
            $params['country'] = $countryId;
        }
        $body = $this->get('getPrices', $params, 'getPrices');
        if (preg_match('/^(BAD_|NO_|ERROR_|WRONG_)/', $body)) {
            return [];
        }
        $data = json_decode($body, true);
        return is_array($data) ? $data : [];
    }

    /**
     * Get estimated price for a country + service (for price endpoint). One getPrices(service, country) call.
     * Returns ['price' => float, 'success_rate' => 0] to match Server 2 format.
     */
    public function getPriceForCountry(string $serviceCode, int $countryId): array
    {
        $pricesByCountry = $this->getPrices($serviceCode, $countryId);
        $price = 0.0;
        $countryKey = (string) $countryId;
        if (isset($pricesByCountry[$countryKey][$serviceCode]['Price'])) {
            $price = (float) $pricesByCountry[$countryKey][$serviceCode]['Price'];
        } elseif (isset($pricesByCountry[$countryId][$serviceCode]['Price'])) {
            $price = (float) $pricesByCountry[$countryId][$serviceCode]['Price'];
        } elseif (!empty($pricesByCountry)) {
            $first = reset($pricesByCountry);
            if (is_array($first) && isset($first[$serviceCode]['Price'])) {
                $price = (float) $first[$serviceCode]['Price'];
            }
        }
        return ['price' => $price, 'success_rate' => 0];
    }

    /**
     * Services list only (no prices). Like Server 2 – price shown after user selects country + service via price endpoint.
     */
    public function getServices(?string $countryCode = null): array
    {
        $cacheKey = 'smsconfirmed_services_' . $this->server->id;
        return Cache::remember($cacheKey, now()->addMinutes(30), function () {
            $body = $this->get('getServicesList', [], 'getServicesList');
            $data = json_decode($body, true);
            if (!is_array($data) || empty($data['services'])) {
                return [];
            }
            $services = [];
            foreach ($data['services'] as $item) {
                $code = $item['code'] ?? '';
                if ($code === '') {
                    continue;
                }
                $services[] = [
                    'code' => $code,
                    'name' => $item['name'] ?? ucfirst($code),
                    'price' => 0,
                ];
            }
            return $services;
        });
    }

    /**
     * getOperators(country) – list of operator codes for a country. Response: {"status":"success","countryOperators":{"187":["att","tmobile","verizon","sprint"]}}
     */
    public function getOperators(?int $countryId = null): array
    {
        $params = [];
        if ($countryId !== null && $countryId > 0) {
            $params['country'] = $countryId;
        }
        $body = $this->get('getOperators', $params, 'getOperators');
        $data = json_decode($body, true);
        if (!is_array($data) || empty($data['countryOperators'])) {
            return [];
        }
        $operators = [];
        $list = $data['countryOperators'];
        if (!is_array($list)) {
            return [];
        }
        $codes = [];
        if ($countryId !== null && $countryId > 0 && isset($list[(string) $countryId])) {
            $codes = $list[(string) $countryId];
        } else {
            $first = reset($list);
            $codes = is_array($first) ? $first : [];
        }
        $names = [
            'att' => 'AT&T', 'tmobile' => 'T-Mobile', 'verizon' => 'Verizon', 'sprint' => 'Sprint',
            'lycamobile' => 'Lycamobile', 'tigo' => 'Tigo', 'orange' => 'Orange', 'vodafone' => 'Vodafone',
        ];
        foreach ($codes as $code) {
            $operators[] = [
                'id' => $code,
                'name' => $names[$code] ?? ucfirst($code),
            ];
        }
        return $operators;
    }

    /**
     * Get countries from API. Merges lists from all services (tg, wa, go, fb) so USA and others
     * are included even if only one service returns them.
     */
    public function getCountries(): array
    {
        $cacheKey = 'smsconfirmed_countries_' . $this->server->id;
        return Cache::remember($cacheKey, now()->addHour(), function () {
            $byCode = [];
            $services = ['tg', 'wa', 'go', 'fb'];
            foreach ($services as $service) {
                try {
                    $body = $this->get('getCountries', ['service' => $service], 'getCountries');
                    $data = json_decode($body, true);
                    if (!is_array($data) || empty($data['countries'])) {
                        continue;
                    }
                    foreach ($data['countries'] as $item) {
                        $code = (string) ($item['code'] ?? '');
                        $name = (string) ($item['name'] ?? 'Country ' . $code);
                        if ($code !== '' && !isset($byCode[$code])) {
                            $byCode[$code] = [
                                'code' => $code,
                                'name' => $name,
                                'provider_id' => $code,
                            ];
                        }
                    }
                } catch (\Throwable $e) {
                    continue;
                }
            }
            $countries = array_values($byCode);
            if (empty($countries)) {
                return [];
            }
            usort($countries, fn ($a, $b) => strcasecmp($a['name'], $b['name']));
            return $countries;
        });
    }

    public function orderNumber(string $serviceCode, string $countryCode, ?float $maxPrice = null, array $options = []): array
    {
        $params = [
            'service' => $serviceCode,
            'country' => (int) $countryCode ?: 187,
        ];
        if ($maxPrice !== null && $maxPrice > 0) {
            $params['maxPrice'] = $maxPrice;
        }
        if (!empty($options['operator'])) {
            $params['operator'] = $options['operator'];
        }
        if (!empty($options['phoneException'])) {
            $params['phoneException'] = $options['phoneException'];
        }
        if (!empty($options['MAX_SUCCESS'])) {
            $params['MAX_SUCCESS'] = '1';
        }

        $body = $this->get('getNumber', $params, 'getNumber');

        if (str_starts_with($body, 'NO_BALANCE')) {
            throw new \RuntimeException('Insufficient balance on provider.');
        }
        if (str_starts_with($body, 'CHANNELS_LIMIT')) {
            throw new \RuntimeException('Too many active rentals on provider.');
        }
        if (str_starts_with($body, 'NO_NUMBERS')) {
            throw new \RuntimeException('No numbers available for this service/country.');
        }
        if (str_starts_with($body, 'WRONG_MAX_PRICE')) {
            $min = substr($body, strlen('WRONG_MAX_PRICE:'));
            throw new \RuntimeException('Price exceeded; minimum is ' . $min);
        }
        if (str_starts_with($body, 'BAD_SERVICE') || str_starts_with($body, 'NO_ACTIVATION')) {
            throw new \RuntimeException('Order failed: ' . $body);
        }
        if (!str_starts_with($body, 'ACCESS_NUMBER:')) {
            throw new \RuntimeException('Order failed: ' . substr($body, 0, 100));
        }

        $parts = explode(':', trim(substr($body, strlen('ACCESS_NUMBER:'))));
        $orderId = $parts[0] ?? '';
        $phone = $parts[1] ?? '';

        return [
            'order_id' => (string) $orderId,
            'phone_number' => (string) $phone,
            'cost' => $maxPrice ?? 0.50,
        ];
    }

    /**
     * getActiveActivations – list of current active activations with smsCodes/smsTexts.
     * Used to periodically check for codes without calling getStatus per order.
     */
    public function getActiveActivations(): array
    {
        $body = $this->get('getActiveActivations', [], 'getActiveActivations');
        $data = json_decode($body, true);
        if (!is_array($data) || !isset($data['activations'])) {
            return [];
        }
        return is_array($data['activations']) ? $data['activations'] : [];
    }

    /**
     * Check activation status and get SMS code via getStatus (id=activation id).
     * Responses: STATUS_OK:code, STATUS_WAIT_RETRY:code, STATUS_WAIT_CODE, STATUS_CANCEL, NO_ACTIVATION.
     */
    public function getSms(string $orderId): array
    {
        $body = trim($this->get('getStatus', ['id' => $orderId], 'getStatus'));
        if (str_starts_with($body, 'STATUS_OK:')) {
            $code = trim(substr($body, strlen('STATUS_OK:')));
            return ['status' => 'ok', 'code' => $code];
        }
        if (str_starts_with($body, 'STATUS_WAIT_RETRY:')) {
            $code = trim(substr($body, strlen('STATUS_WAIT_RETRY:')));
            return ['status' => 'wait', 'code' => $code !== '' ? $code : null];
        }
        if (str_starts_with($body, 'STATUS_WAIT_CODE')) {
            return ['status' => 'wait', 'code' => null];
        }
        if (str_starts_with($body, 'STATUS_CANCEL') || str_starts_with($body, 'NO_ACTIVATION')) {
            return ['status' => 'cancel', 'code' => null];
        }
        Log::warning('SmsConfirmed getStatus unexpected response', [
            'order_id' => $orderId,
            'response_preview' => strlen($body) > 200 ? substr($body, 0, 200) . '…' : $body,
        ]);
        return ['status' => 'wait', 'code' => null];
    }

    public function cancelNumber(string $orderId): bool
    {
        $body = $this->get('setStatus', ['id' => $orderId, 'status' => 8], 'setStatus');
        return str_starts_with($body, 'ACCESS_CANCEL');
    }
}
