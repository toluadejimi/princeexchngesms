<?php

namespace App\Services\Sms;

use App\Models\ApiServer;
use App\Models\ApiRequestLog;
use App\Services\Sms\Contracts\SmsServerInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * SMSPool API – multi-country SMS (Other Countries server).
 * Base URL: https://api.smspool.net
 * Auth: POST form-data with key=api_key on every request.
 */
class MultiCountrySmsService implements SmsServerInterface
{
    protected ApiServer $server;

    public function __construct(ApiServer $server)
    {
        $this->server = $server;
    }

    protected function getApiKey(): string
    {
        return $this->server->getDecryptedApiKey();
    }

    protected function post(string $path, array $form = [], string $logAction = 'request'): array
    {
        $form['key'] = $this->getApiKey();
        $url = rtrim($this->server->base_url, '/') . $path;
        $start = microtime(true);
        $response = Http::asForm()->timeout(15)->post($url, $form);

        $duration = (microtime(true) - $start) * 1000;
        ApiRequestLog::create([
            'server_id' => $this->server->id,
            'action' => $logAction,
            'method' => 'POST',
            'url' => $url,
            'status_code' => $response->status(),
            'response_body' => substr((string) $response->body(), 0, 2000),
            'duration_ms' => round($duration, 2),
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException('SMSPool API failed: HTTP ' . $response->status());
        }

        $body = $response->json();
        return is_array($body) ? $body : [];
    }

    public function getBalance(): float
    {
        $data = $this->post('/request/balance', [], 'getBalance');
        return (float) ($data['balance'] ?? $data['credits'] ?? 0);
    }

    public function getServices(?string $countryCode = null): array
    {
        $cacheKey = 'smspool_services_' . $this->server->id;
        return Cache::remember($cacheKey, now()->addMinutes(15), function () {
            $data = $this->post('/service/retrieve_all', [], 'getServices');
            $list = $data['data'] ?? $data['services'] ?? $data;
            if (!is_array($list)) {
                return [];
            }
            $services = [];
            foreach ($list as $item) {
                $id = $item['id'] ?? $item['service_id'] ?? $item['short_name'] ?? '';
                $services[] = [
                    'code' => (string) $id,
                    'name' => $item['name'] ?? $item['short_name'] ?? $item['service'] ?? 'Service ' . $id,
                    'price' => (float) ($item['price'] ?? $item['cost'] ?? 0),
                ];
            }
            return $services;
        });
    }

    public function getCountries(): array
    {
        $cacheKey = 'smspool_countries_' . $this->server->id;
        return Cache::remember($cacheKey, now()->addHour(), function () {
            $data = $this->post('/country/retrieve_all', [], 'getCountries');
            $list = $data['data'] ?? $data['countries'] ?? $data;
            if (!is_array($list)) {
                return [];
            }
            $countries = [];
            foreach ($list as $item) {
                $id = $item['id'] ?? $item['country_id'] ?? '';
                $code = $item['iso'] ?? $item['code'] ?? $item['country_code'] ?? (string) $id;
                $countries[] = [
                    'code' => (string) $code,
                    'name' => $item['name'] ?? $item['country'] ?? 'Country ' . $code,
                    'provider_id' => (string) $id,
                ];
            }
            return $countries;
        });
    }

    public function orderNumber(string $serviceCode, string $countryCode, ?float $maxPrice = null, array $options = []): array
    {
        $form = [
            'country' => $countryCode,
            'service' => $serviceCode,
        ];
        if ($maxPrice !== null && $maxPrice > 0) {
            $form['max_price'] = $maxPrice;
        }
        $data = $this->post('/purchase/sms', $form, 'order');

        if (isset($data['success']) && (int) $data['success'] === 0) {
            throw new \RuntimeException($data['message'] ?? 'Order failed');
        }

        $orderId = $data['orderid'] ?? $data['order_id'] ?? $data['order_code'] ?? null;
        $phone = $data['phonenumber'] ?? $data['phone_number'] ?? $data['number'] ?? $data['phone'] ?? '';
        $cost = (float) ($data['cost'] ?? $data['price'] ?? 0);

        if (!$orderId || !$phone) {
            throw new \RuntimeException('Order failed: ' . json_encode($data));
        }

        return [
            'order_id' => (string) $orderId,
            'phone_number' => (string) $phone,
            'cost' => $cost,
        ];
    }

    public function getSms(string $orderId): array
    {
        $data = $this->post('/sms/check', ['orderid' => $orderId], 'getStatus');

        $status = strtolower((string) ($data['status'] ?? ''));
        $code = $data['sms'] ?? $data['code'] ?? $data['full_code'] ?? null;

        if (in_array($status, ['completed', 'received', 'success', '1'], true) || !empty($code)) {
            return ['status' => 'ok', 'code' => $code ? (string) $code : null];
        }
        if (in_array($status, ['cancelled', 'cancel', 'expired', 'refunded'], true)) {
            return ['status' => 'cancel', 'code' => null];
        }

        return ['status' => 'wait', 'code' => null];
    }

    public function cancelNumber(string $orderId): bool
    {
        $data = $this->post('/sms/cancel', ['orderid' => $orderId], 'cancel');
        return (int) ($data['success'] ?? 0) === 1;
    }
}
