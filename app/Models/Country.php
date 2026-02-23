<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Country extends Model
{
    protected $fillable = [
        'server_id',
        'name',
        'code',
        'provider_country_id',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(ApiServer::class, 'server_id');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
