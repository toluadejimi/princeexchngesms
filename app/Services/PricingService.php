<?php

namespace App\Services;

use App\Models\ApiServer;
use App\Models\ServerPricing;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;

class PricingService
{
    /**
     * Get amount to charge (in system currency) for a server/country/service.
     * Uses API price only; customer price = (API USD × rate + margin_amount) × (1 + margin%) for NGN,
     * or raw API USD when display currency is USD. Same formula for both servers.
     */
    public function getPrice(int $serverId, string $countryCode, string $serviceCode): float
    {
        $apiUsd = $this->getApiPriceUsd($serverId, $countryCode, $serviceCode);
        if ($apiUsd <= 0) {
            return 0;
        }
        if (SiteSetting::displayCurrency() === 'NGN' && SiteSetting::usdToNgnRate() > 0) {
            return SiteSetting::usdToNairaTotal($apiUsd);
        }
        return round($apiUsd, 4);
    }

    /**
     * Get API price in USD for a service. Server 1: getPriceForCountry(service, country). Server 2: from getServices.
     */
    public function getApiPriceUsd(int $serverId, string $countryCode, string $serviceCode): float
    {
        $key = "api_price_{$serverId}_{$countryCode}_{$serviceCode}";
        return (float) Cache::remember($key, now()->addMinutes(15), function () use ($serverId, $countryCode, $serviceCode) {
            $server = ApiServer::find($serverId);
            if (!$server) {
                return 0;
            }
            $client = \App\Services\Sms\SmsServerFactory::make($server);
            if ($server->isSmsConfirmed() && method_exists($client, 'getPriceForCountry')) {
                $countryId = (int) $countryCode;
                if ($countryId > 0) {
                    $result = $client->getPriceForCountry($serviceCode, $countryId);
                    return (float) ($result['price'] ?? 0);
                }
            }
            $services = $client->getServices($countryCode);
            foreach ($services as $s) {
                if (($s['code'] ?? '') === $serviceCode) {
                    return (float) ($s['price'] ?? 0);
                }
            }
            return 0;
        });
    }

    public function getBasePrice(int $serverId, string $countryCode, string $serviceCode): float
    {
        $key = "pricing_{$serverId}_{$countryCode}_{$serviceCode}";
        return (float) Cache::remember($key, now()->addMinutes(10), function () use ($serverId, $countryCode, $serviceCode) {
            $p = ServerPricing::where('server_id', $serverId)
                ->where('service_code', $serviceCode)
                ->where(function ($q) use ($countryCode) {
                    $q->where('country_code', $countryCode)->orWhereNull('country_code');
                })
                ->where('active', true)
                ->orderByRaw('country_code IS NOT NULL DESC')
                ->first();
            return $p ? (float) $p->price : 0;
        });
    }

    /**
     * Services with API price in USD. Frontend uses global rate + margin to display.
     */
    public function getServicesWithPrices(int $serverId, ?string $countryCode = null): array
    {
        $server = ApiServer::find($serverId);
        $client = \App\Services\Sms\SmsServerFactory::make($server);
        $countryCode = $countryCode ?? '';
        $services = $client->getServices($countryCode ?: null);
        foreach ($services as &$s) {
            $s['price'] = (float) ($s['price'] ?? 0);
        }
        return $services;
    }
}
