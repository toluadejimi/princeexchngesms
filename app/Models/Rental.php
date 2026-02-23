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
}
