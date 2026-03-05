<?php

namespace App\Services\Sms;

use App\Models\ApiServer;
use App\Services\Sms\Contracts\SmsServerInterface;

class SmsServerFactory
{
    public static function make(ApiServer|int $server): SmsServerInterface
    {
        $model = $server instanceof ApiServer ? $server : ApiServer::findOrFail($server);
        return match ($model->type) {
            'smsconfirmed' => new SmsConfirmedService($model),
            'multi_country' => new MultiCountrySmsService($model),
            default => throw new \InvalidArgumentException('Unknown SMS server type: ' . $model->type),
        };
    }
}
