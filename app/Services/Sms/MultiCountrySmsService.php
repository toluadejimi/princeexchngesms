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
            // SMSPool returns raw array: [{"ID":1,"name":"1688","favourite":0}, ...]
            $list = null;
            if (is_array($data)) {
                if (isset($data['data']) && is_array($data['data'])) {
                    $list = $data['data'];
                } elseif (isset($data['services']) && is_array($data['services'])) {
                    $list = $data['services'];
                } elseif (array_keys($data) === range(0, count($data) - 1)) {
                    $list = $data;
                }
            }
            if (!is_array($list)) {
                return [];
            }
            $services = [];
            foreach ($list as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $id = $item['ID'] ?? $item['id'] ?? $item['service_id'] ?? $item['short_name'] ?? '';
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
        $result = Cache::remember($cacheKey, now()->addHour(), function () {
            try {
                $data = $this->post('/country/retrieve_all', [], 'getCountries');
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('SMSPool getCountries: API request failed', [
                    'server_id' => $this->server->id,
                    'server_name' => $this->server->name,
                    'base_url' => $this->server->base_url,
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                ]);
                return [];
            }
            // SMSPool returns raw array: [{"ID":1,"name":"United States","short_name":"US","cc":"1","region":"..."}, ...]
            $list = null;
            if (is_array($data)) {
                if (isset($data['data']) && is_array($data['data'])) {
                    $list = $data['data'];
                } elseif (isset($data['countries']) && is_array($data['countries'])) {
                    $list = $data['countries'];
                } elseif (isset($data['result']) && is_array($data['result'])) {
                    $list = $data['result'];
                } elseif (array_keys($data) === range(0, count($data) - 1)) {
                    $list = $data; // raw sequential array
                }
            }
            if (!is_array($list)) {
                \Illuminate\Support\Facades\Log::warning('SMSPool getCountries: response is not a list', [
                    'server_id' => $this->server->id,
                    'response_keys' => is_array($data) ? array_keys($data) : gettype($data),
                    'list_type' => gettype($list),
                    'response_sample' => is_array($data) ? json_encode(array_slice($data, 0, 2)) : substr((string) $data, 0, 500),
                ]);
                return [];
            }
            $countries = [];
            foreach ($list as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $id = $item['ID'] ?? $item['id'] ?? $item['country_id'] ?? $item['countryId'] ?? '';
                $code = strtoupper((string) ($item['short_name'] ?? $item['iso'] ?? $item['iso2'] ?? $item['code'] ?? $item['country_code'] ?? (string) $id));
                $name = $item['name'] ?? $item['country'] ?? $item['country_name'] ?? 'Country ' . $code;
                if ($code === '' || $code === '0') {
                    continue;
                }
                $countries[] = [
                    'code' => $code,
                    'name' => (string) $name,
                    'provider_id' => (string) $id,
                ];
            }
            if (empty($countries)) {
                \Illuminate\Support\Facades\Log::warning('SMSPool getCountries: parsed 0 countries from list', [
                    'server_id' => $this->server->id,
                    'list_count' => count($list),
                    'first_item_sample' => isset($list[0]) ? json_encode($list[0]) : 'n/a',
                ]);
            }
            return $countries;
        });
        if (empty($result)) {
            Cache::forget($cacheKey);
        }
        return $result;
    }

    /**
     * Get available pools. SMSPool returns [{"ID":3,"name":"Charlie"}, ...].
     */
    public function getPools(): array
    {
        $cacheKey = 'smspool_pools_' . $this->server->id;
        return Cache::remember($cacheKey, now()->addHour(), function () {
            try {
                $data = $this->post('/pool/retrieve_all', [], 'getPools');
            } catch (\Throwable $e) {
                return [];
            }
            $list = null;
            if (is_array($data)) {
                if (isset($data['data']) && is_array($data['data'])) {
                    $list = $data['data'];
                } elseif (isset($data['pools']) && is_array($data['pools'])) {
                    $list = $data['pools'];
                } elseif (array_keys($data) === range(0, count($data) - 1)) {
                    $list = $data;
                }
            }
            if (!is_array($list)) {
                return [];
            }
            $pools = [];
            foreach ($list as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $id = $item['ID'] ?? $item['id'] ?? '';
                $name = $item['name'] ?? 'Pool ' . $id;
                if ($id !== '' && $id !== null) {
                    $pools[] = ['id' => (string) $id, 'name' => (string) $name];
                }
            }
            return $pools;
        });
    }

    /**
     * Get price for country + service + optional pool. SMSPool POST /request/price.
     * Params: country = country_id, service = service_id, pool = pool_id (optional).
     * Returns ['price' => float, 'success_rate' => int].
     */
    public function getPrice(int $countryId, int $serviceId, ?int $poolId = null): array
    {
        $form = [
            'country' => $countryId,
            'service' => $serviceId,
        ];
        if ($poolId !== null && $poolId > 0) {
            $form['pool'] = $poolId;
        }
        $data = $this->post('/request/price', $form, 'getPrice');
        $price = (float) ($data['price'] ?? 0);
        $successRate = isset($data['success_rate']) ? (int) $data['success_rate'] : 0;
        return ['price' => $price, 'success_rate' => $successRate];
    }

    public function orderNumber(string $serviceCode, string $countryCode, ?float $maxPrice = null, array $options = []): array
    {
        // SMSPool /purchase/sms expects numeric country and service IDs (same as /request/price)
        $countryParam = isset($options['country_id']) && $options['country_id'] !== ''
            ? (int) $options['country_id']
            : $countryCode;
        $serviceParam = is_numeric($serviceCode) ? (int) $serviceCode : $serviceCode;

        $form = [
            'country' => $countryParam,
            'service' => $serviceParam,
        ];
        if ($maxPrice !== null && $maxPrice > 0) {
            $form['max_price'] = $maxPrice;
        }
        if (!empty($options['pool_id'])) {
            $form['pool'] = $options['pool_id'];
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

    // ---------- SMS management (SMSPool Postman collection) ----------

    /** Active orders – POST /request/active */
    public function getActiveOrders(): array
    {
        $data = $this->post('/request/active', [], 'activeOrders');
        $list = $data['orders'] ?? $data['data'] ?? $data['result'] ?? $data;
        return is_array($list) ? $list : [];
    }

    /** Cancel all SMS – POST /sms/cancel_all */
    public function cancelAll(): array
    {
        return $this->post('/sms/cancel_all', [], 'cancelAll');
    }

    /** Clear SMS cache – POST /sms/clear_cache */
    public function clearSmsCache(): array
    {
        return $this->post('/sms/clear_cache', [], 'clearCache');
    }

    /** Activate SMS – POST /sms/activate. Params: orderid */
    public function activateSms(string $orderId): array
    {
        return $this->post('/sms/activate', ['orderid' => $orderId], 'activate');
    }

    /** Reactivate SMS – POST /sms/reactivate. Params: orderid */
    public function reactivateSms(string $orderId): array
    {
        return $this->post('/sms/reactivate', ['orderid' => $orderId], 'reactivate');
    }

    /** Archive all orders – POST /request/archive */
    public function archiveAll(): array
    {
        return $this->post('/request/archive', [], 'archiveAll');
    }

    /** Check resend – POST /sms/check_resend. Params: orderid */
    public function checkResend(string $orderId): array
    {
        return $this->post('/sms/check_resend', ['orderid' => $orderId], 'checkResend');
    }

    /** Resend – POST /sms/resend. Params: orderid */
    public function resendSms(string $orderId): array
    {
        return $this->post('/sms/resend', ['orderid' => $orderId], 'resend');
    }

    /** One-time SMS stock – POST /sms/stock. Optional: country, service */
    public function getSmsStock(?int $countryId = null, ?int $serviceId = null): array
    {
        $form = [];
        if ($countryId !== null) {
            $form['country'] = $countryId;
        }
        if ($serviceId !== null) {
            $form['service'] = $serviceId;
        }
        return $this->post('/sms/stock', $form, 'stock');
    }

    /** One-time stock for all services – POST /sms/all_stock */
    public function getAllSmsStock(): array
    {
        return $this->post('/sms/all_stock', [], 'allStock');
    }

    /** Order history – POST /request/history */
    public function getOrderHistory(): array
    {
        $data = $this->post('/request/history', [], 'history');
        $list = $data['orders'] ?? $data['data'] ?? $data['result'] ?? $data;
        return is_array($list) ? $list : [];
    }

    /** Request available areacodes – POST /request/areacodes. Optional: country */
    public function getAreacodes(?int $countryId = null): array
    {
        $form = [];
        if ($countryId !== null) {
            $form['country'] = $countryId;
        }
        $data = $this->post('/request/areacodes', $form, 'areacodes');
        $list = $data['areacodes'] ?? $data['data'] ?? $data['result'] ?? $data;
        return is_array($list) ? $list : [];
    }
}
