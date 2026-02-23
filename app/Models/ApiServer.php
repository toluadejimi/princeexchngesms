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

    public function isUsaOnly(): bool
    {
        return $this->type === 'usa_only';
    }

    public function isMultiCountry(): bool
    {
        return $this->type === 'multi_country';
    }

    /** Customer-facing label (no provider name). */
    public function getDisplayNameAttribute(): string
    {
        return $this->type === 'usa_only' ? 'USA' : 'Other Countries';
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}
