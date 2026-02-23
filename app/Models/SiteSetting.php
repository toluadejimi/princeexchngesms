<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    public $timestamps = false;
    protected $keyType = 'string';
    protected $fillable = ['key', 'value'];

    public static function get(string $key, mixed $default = null): mixed
    {
        $cacheKey = 'site_setting_' . $key;
        return Cache::remember($cacheKey, now()->addHour(), function () use ($key, $default) {
            $row = static::find($key);
            return $row !== null ? $row->value : $default;
        });
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value === null ? null : (string) $value]);
        Cache::forget('site_setting_' . $key);
    }

    /** Site name (used in nav and page title) */
    public static function siteName(): string
    {
        return (string) static::get('site_name', config('app.name', 'SMS Rental'));
    }

    /** Logo path relative to storage/app/public (e.g. site/logo.png). Empty if not set. */
    public static function logoPath(): string
    {
        return (string) static::get('site_logo', '');
    }

    /** Full URL to logo for use in img src, or null if not set. Uses asset() to avoid Storage/Flysystem (no fileinfo dependency). */
    public static function logoUrl(): ?string
    {
        $path = static::logoPath();
        return $path ? asset('storage/' . ltrim($path, '/')) : null;
    }

    /** Favicon path relative to storage/app/public. Empty if not set. */
    public static function faviconPath(): string
    {
        return (string) static::get('site_favicon', '');
    }

    /** Full URL to favicon for use in link href, or null if not set. Uses asset() to avoid Storage/Flysystem (no fileinfo dependency). */
    public static function faviconUrl(): ?string
    {
        $path = static::faviconPath();
        return $path ? asset('storage/' . ltrim($path, '/')) : null;
    }

    /** Display currency: USD or NGN (system charges and wallet use this) */
    public static function displayCurrency(): string
    {
        return (string) static::get('display_currency', 'NGN');
    }

    /** Symbol for wallet/charges: ₦ or $ */
    public static function walletSymbol(): string
    {
        return static::displayCurrency() === 'NGN' ? '₦' : '$';
    }

    /** Format wallet amount for display (no decimals for Naira) */
    public static function formatWalletAmount(float $amount): string
    {
        $decimals = static::displayCurrency() === 'NGN' ? 0 : 2;
        return static::walletSymbol() . number_format($amount, $decimals);
    }

    /** USD to Naira rate (e.g. 1500) */
    public static function usdToNgnRate(): float
    {
        return (float) static::get('usd_to_ngn_rate', 0);
    }

    /** Extra margin % applied when showing Naira price (e.g. 5) */
    public static function nairaMarginPercent(): float
    {
        return (float) static::get('naira_margin_percent', 0);
    }

    /** Fixed margin in Naira added to (USD × rate) for customer display and charge */
    public static function nairaMarginAmount(): float
    {
        return (float) static::get('naira_margin_amount', 0);
    }

    /** Telegram link URL for floating icon (e.g. https://t.me/yourchannel). Empty to hide. */
    public static function telegramUrl(): string
    {
        return (string) static::get('telegram_url', '');
    }

    /**
     * Convert API USD price to customer display. Uses usdToNairaTotal when NGN.
     * Returns ['amount' => number, 'currency' => 'NGN'|'USD', 'symbol' => '₦'|'$'].
     */
    public static function formatPrice(float $usdPrice): array
    {
        $currency = static::displayCurrency();
        if ($currency === 'NGN' && static::usdToNgnRate() > 0) {
            return ['amount' => static::usdToNairaTotal($usdPrice), 'currency' => 'NGN', 'symbol' => '₦'];
        }
        return ['amount' => $usdPrice, 'currency' => 'USD', 'symbol' => '$'];
    }

    /**
     * Customer-facing Naira total: (usd × rate) × (1 + margin%) + margin_ngn.
     */
    public static function usdToNairaTotal(float $usdPrice): float
    {
        $rate = static::usdToNgnRate();
        if ($rate <= 0) {
            return 0;
        }
        $ngn = $usdPrice * $rate;
        $ngn *= (1 + static::nairaMarginPercent() / 100);
        $ngn += static::nairaMarginAmount();
        return round($ngn, 0);
    }

    /**
     * Charge in USD for wallet so display Naira matches: api_usd × (1 + margin%) + (margin_ngn / rate).
     */
    public static function chargeUsdFromApiPrice(float $apiUsd): float
    {
        $rate = static::usdToNgnRate();
        $percent = static::nairaMarginPercent();
        $marginNgn = static::nairaMarginAmount();
        $usd = $percent ? $apiUsd * (1 + $percent / 100) : $apiUsd;
        if ($rate > 0 && $marginNgn > 0) {
            $usd += $marginNgn / $rate;
        }
        return round($usd, 4);
    }
}
