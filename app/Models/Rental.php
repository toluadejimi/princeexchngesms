<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rental extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'user_id',
        'server_id',
        'country_code',
        'service_code',
        'phone_number',
        'order_id',
        'cost',
        'status',
        'sms_code',
        'sms_messages',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'cost' => 'decimal:4',
            'expires_at' => 'datetime',
            'sms_messages' => 'array',
        ];
    }

    /**
     * All received SMS codes (newest last). Each item: ['code' => string, 'received_at' => string].
     */
    public function getSmsMessagesList(): array
    {
        $list = $this->sms_messages ?? [];
        if (!is_array($list)) {
            return [];
        }
        if ($this->sms_code && empty($list)) {
            return [['code' => $this->sms_code, 'received_at' => $this->updated_at?->toIso8601String() ?? '']];
        }
        return $list;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(ApiServer::class, 'server_id');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_ACTIVE]);
    }

    public function scopeCompleted($query)
    {
        return $query->whereIn('status', [self::STATUS_COMPLETED, self::STATUS_CANCELLED, self::STATUS_EXPIRED]);
    }

    public function isActive(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_ACTIVE], true);
    }

    /** For Server 1: cancel is allowed only 10 minutes after creation. Returns that timestamp or null (cancel always allowed). */
    public function cancelAllowedAt(): ?\Carbon\Carbon
    {
        if (!$this->relationLoaded('server')) {
            $this->load('server');
        }
        if ($this->server && $this->server->isSmsConfirmed() && $this->created_at) {
            return $this->created_at->copy()->addMinutes(10);
        }
        return null;
    }

    /** Whether the user is allowed to cancel this rental (respects 10-min rule for Server 1). */
    public function isCancelAllowed(): bool
    {
        $at = $this->cancelAllowedAt();
        return $at === null || now()->gte($at);
    }

    /**
     * Display name for the service: for multi-country (SMSPool) resolves from provider list by service_code (ID).
     */
    public function getServiceDisplayName(): string
    {
        $code = $this->service_code ?? '';
        if ($code === '') {
            return '—';
        }
        if ($this->server && $this->server->isMultiCountry()) {
            try {
                $client = \App\Services\Sms\SmsServerFactory::make($this->server);
                if (method_exists($client, 'getServices')) {
                    $services = $client->getServices($this->country_code);
                    foreach ($services as $s) {
                        $c = $s['code'] ?? $s['id'] ?? null;
                        if ((string) $c === (string) $code) {
                            return $s['name'] ?? ucfirst((string) $code);
                        }
                    }
                }
            } catch (\Throwable) {
                // fall through to default
            }
        }
        return \App\Helpers\DisplayHelper::serviceCodeToName($code);
    }

    /**
     * Display name for the country. For Server 1 (SmsConfirmed) and Server 2 (SMSPool), resolves from
     * provider list by country_code (Server 1 may store numeric id e.g. 187, Server 2 stores ISO code).
     */
    public function getCountryDisplayName(): string
    {
        $code = (string) ($this->country_code ?? '');
        if ($code === '') {
            return '—';
        }
        if ($this->server) {
            try {
                $client = \App\Services\Sms\SmsServerFactory::make($this->server);
                if (method_exists($client, 'getCountries')) {
                    $countries = $client->getCountries();
                    $codeUpper = strtoupper($code);
                    foreach ($countries as $c) {
                        $itemCode = (string) ($c['code'] ?? '');
                        $itemId = (string) ($c['provider_id'] ?? $c['id'] ?? $c['ID'] ?? '');
                        if ($itemCode === $code || strtoupper($itemCode) === $codeUpper || $itemId === $code) {
                            return (string) ($c['name'] ?? $c['country'] ?? $c['country_name'] ?? \App\Helpers\DisplayHelper::countryCodeToName($code));
                        }
                    }
                }
            } catch (\Throwable) {
                // fall through to default
            }
        }
        return \App\Helpers\DisplayHelper::countryCodeToName(strtoupper($code));
    }
}
