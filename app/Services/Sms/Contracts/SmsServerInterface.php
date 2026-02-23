<?php

namespace App\Services\Sms\Contracts;

use App\Models\ApiServer;

interface SmsServerInterface
{
    public function __construct(ApiServer $server);

    /**
     * Get provider account balance (raw, before margin).
     */
    public function getBalance(): float;

    /**
     * Get list of services. Format: [['code' => 'wa', 'name' => 'WhatsApp'], ...]
     * For USA-only server: services for USA. For multi-country: optionally per country.
     */
    public function getServices(?string $countryCode = null): array;

    /**
     * Get available countries. For USA-only returns single USA entry.
     * For multi-country returns list from API or cache.
     */
    public function getCountries(): array;

    /**
     * Order a number. Returns ['order_id' => string, 'phone_number' => string, 'cost' => float] or throws.
     * $options: provider-specific (e.g. 'areas' => '212,718', 'carriers' => 'tmo,vz', 'number' => '...').
     */
    public function orderNumber(string $serviceCode, string $countryCode, ?float $maxPrice = null, array $options = []): array;

    /**
     * Get SMS status/code for an order. Returns ['status' => 'wait'|'ok'|'cancel', 'code' => string|null]
     */
    public function getSms(string $orderId): array;

    /**
     * Cancel a rental and free the number.
     */
    public function cancelNumber(string $orderId): bool;
}
