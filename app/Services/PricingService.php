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
     * When display currency is NGN: returns Naira total (API USD × rate + margin).
     * When USD: returns USD (API price + margin).
     */
    public function getPrice(int $serverId, string $countryCode, string $serviceCode): float
    {
        $apiUsd = $this->getApiPriceUsd($serverId, $countryCode, $serviceCode);
        if ($apiUsd > 0 && SiteSetting::displayCurrency() === 'NGN' && SiteSetting::usdToNgnRate() > 0) {
            return SiteSetting::usdToNairaTotal($apiUsd);
        }
        if ($apiUsd > 0) {
            $server = ApiServer::find($serverId);
            $margin = $server && $server->profit_margin_percent > 0
                ? (1 + (float) $server->profit_margin_percent / 100)
                : 1;
            return round($apiUsd * $margin, 4);
        }
        $base = $this->getBasePrice($serverId, $countryCode, $serviceCode);
        $server = ApiServer::find($serverId);
        if ($server && $server->profit_margin_percent > 0) {
            $base = $base * (1 + (float) $server->profit_margin_percent / 100);
        }
        return round($base, 4);
    }

    /**
     * Get API price in USD for a service (from provider's getServices). Cached.
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
     * Services with prices from API (USD). Customer sees (USD × cover rate) + margin_ngn in Naira on frontend.
     */
    public function getServicesWithPrices(int $serverId, ?string $countryCode = null): array
    {
        $server = ApiServer::find($serverId);
        $client = \App\Services\Sms\SmsServerFactory::make($server);
        $services = $client->getServices($countryCode ?? 'US');
        $countryCode = $countryCode ?? 'US';
        foreach ($services as &$s) {
            $apiUsd = (float) ($s['price'] ?? 0);
            if ($apiUsd <= 0) {
                $s['price'] = $this->getPrice($serverId, $countryCode, $s['code']);
            } else {
                $s['price'] = $apiUsd;
            }
        }
        return $services;
    }
}
