<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class ApiServer extends Model
{
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

        return $this->name ?: ('Server ' . ($this->sort_order ?: 1));
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}
