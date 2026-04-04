<?php

namespace App\Support;

use Illuminate\Database\ConnectionException;
use Illuminate\Database\QueryException;
use Throwable;

final class CustomerFacing
{
    public const DEFAULT_MESSAGE = 'Something went wrong. Please try again in a moment.';

    /** Safe text for JSON/HTML shown to end users (never SQL or stack details). */
    public static function exceptionMessage(Throwable $e, bool $allowRuntimeDetail = true): string
    {
        if ($e instanceof QueryException || $e instanceof ConnectionException) {
            return self::DEFAULT_MESSAGE;
        }

        if ($allowRuntimeDetail && $e instanceof \RuntimeException) {
            $msg = $e->getMessage();

            return $msg !== '' ? $msg : self::DEFAULT_MESSAGE;
        }

        return self::DEFAULT_MESSAGE;
    }
}
