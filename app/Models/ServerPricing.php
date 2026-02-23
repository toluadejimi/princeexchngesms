<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServerPricing extends Model
{
    protected $table = 'server_pricing';

    protected $fillable = [
        'server_id',
        'country_code',
        'service_code',
        'price',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:4',
            'active' => 'boolean',
        ];
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(ApiServer::class, 'server_id');
    }
}
