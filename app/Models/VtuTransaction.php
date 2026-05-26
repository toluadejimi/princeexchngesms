<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VtuTransaction extends Model
{
    public const TYPE_AIRTIME = 'airtime';

    public const TYPE_DATA = 'data';

    public const TYPE_CABLE = 'cable';

    public const TYPE_ELECTRICITY = 'electricity';

    public const STATUS_PENDING = 'pending';

    public const STATUS_SUCCESSFUL = 'successful';

    public const STATUS_FAILED = 'failed';

    public const STATUS_REFUNDED = 'refunded';

    protected $fillable = [
        'user_id',
        'type',
        'status',
        'reference',
        'provider_reference',
        'service_id',
        'variation_code',
        'recipient',
        'amount',
        'wallet_debit',
        'customer_name',
        'token',
        'message',
        'request_payload',
        'response_payload',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:4',
            'wallet_debit' => 'decimal:4',
            'request_payload' => 'array',
            'response_payload' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isSuccessful(): bool
    {
        return $this->status === self::STATUS_SUCCESSFUL;
    }
}
