<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class ApiServer extends Model
{
    private const ACTIVE_CACHE_KEY = 'api_servers_active_sorted';

    protected $fillable = [
        'name',
        'base_url',
        'api_key',
        'type',
        'profit_margin_percent',
        'status',
        'sort_order',
    ];

    protected $hidden = ['api_key'];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'profit_margin_percent' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::ACTIVE_CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::ACTIVE_CACHE_KEY));
    }

    /**
     * Active servers are used on every customer dashboard/request form render.
     *
     * @return Collection<int, self>
     */
    public static function activeCached(): Collection
    {
        return Cache::remember(
            self::ACTIVE_CACHE_KEY,
            now()->addMinutes(10),
            fn () => self::active()->orderBy('sort_order')->get()
        );
    }

    public function getDecryptedApiKey(): string
    {
        try {
            return Crypt::decryptString($this->api_key);
        } catch (\Throwable) {
            return (string) $this->getRawOriginal('api_key');
        }
    }

    public function setApiKeyAttribute(?string $value): void
    {
        $this->attributes['api_key'] = $value ? Crypt::encryptString($value) : null;
    }

    public function countries(): HasMany
    {
        return $this->hasMany(Country::class, 'server_id');
    }

    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class, 'server_id');
    }

    public function pricing(): HasMany
    {
        return $this->hasMany(ServerPricing::class, 'server_id');
    }

    public function isSmsConfirmed(): bool
    {
        return $this->type === 'smsconfirmed';
    }

    public function isMultiCountry(): bool
    {
        return $this->type === 'multi_country';
    }

    public function isGetatext(): bool
    {
        return $this->type === 'getatext';
    }

    public function isFiveSim(): bool
    {
        return $this->type === 'fivesim';
    }

    /** Customer-facing label only (no provider branding). */
    public function getDisplayNameAttribute(): string
    {
        if ($this->type === 'getatext') {
            return 'Server 1';
        }
        if ($this->type === 'multi_country') {
            return 'Server 2';
        }
        if ($this->type === 'fivesim') {
            return 'Server 3';
        }
        if ($this->type === 'smsconfirmed') {
            return $this->name ?: 'Server';
        }

        return $this->name ?: ('Server '.($this->sort_order ?: 1));
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}
