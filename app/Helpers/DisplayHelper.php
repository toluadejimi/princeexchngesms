<?php

namespace App\Helpers;

class DisplayHelper
{
    protected static array $countryNames = [
        'US' => 'United States',
        'GB' => 'United Kingdom',
        'NG' => 'Nigeria',
        'CA' => 'Canada',
        'AU' => 'Australia',
        'DE' => 'Germany',
        'FR' => 'France',
        'IN' => 'India',
        'KE' => 'Kenya',
        'GH' => 'Ghana',
        'ZA' => 'South Africa',
        'EG' => 'Egypt',
        'PK' => 'Pakistan',
        'BD' => 'Bangladesh',
        'PH' => 'Philippines',
        'ID' => 'Indonesia',
        'MY' => 'Malaysia',
        'VN' => 'Vietnam',
        'BR' => 'Brazil',
        'MX' => 'Mexico',
    ];

    protected static array $serviceNames = [
        'wa' => 'WhatsApp',
        'go' => 'Google',
        'tg' => 'Telegram',
        'ds' => 'Discord',
        'fb' => 'Facebook',
        'am' => 'Amazon',
        'tw' => 'Twitter',
        'ig' => 'Instagram',
        '2redbeans' => '2RedBeans',
        'whatsapp' => 'WhatsApp',
        'telegram' => 'Telegram',
        'google' => 'Google',
        'discord' => 'Discord',
        'facebook' => 'Facebook',
        'instagram' => 'Instagram',
        'twitter' => 'Twitter',
        'tiktok' => 'TikTok',
        'amazon' => 'Amazon',
    ];

    public static function countryCodeToName(string $code): string
    {
        $code = strtoupper($code);
        return self::$countryNames[$code] ?? $code;
    }

    public static function serviceCodeToName(string $code): string
    {
        $key = strtolower($code);
        return self::$serviceNames[$key] ?? ucfirst($code);
    }

    /**
     * Format phone number in US style: (XXX) XXX-XXXX or +1 (XXX) XXX-XXXX
     */
    public static function formatPhoneNumber(?string $number): string
    {
        if ($number === null || $number === '') {
            return '—';
        }
        $digits = preg_replace('/\D/', '', $number);
        if (strlen($digits) === 10) {
            return '(' . substr($digits, 0, 3) . ') ' . substr($digits, 3, 3) . '-' . substr($digits, 6, 4);
        }
        if (strlen($digits) === 11 && $digits[0] === '1') {
            return '+1 (' . substr($digits, 1, 3) . ') ' . substr($digits, 4, 3) . '-' . substr($digits, 7, 4);
        }
        if (strlen($digits) > 11) {
            return '+' . $digits;
        }
        return $number;
    }
}
