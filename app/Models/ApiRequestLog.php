<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiRequestLog extends Model
{
    protected $fillable = [
        'server_id',
        'action',
        'method',
        'url',
        'status_code',
        'response_body',
        'error',
        'duration_ms',
    ];

    protected function casts(): array
    {
        return [
            'duration_ms' => 'float',
        ];
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(ApiServer::class, 'server_id');
    }
}
