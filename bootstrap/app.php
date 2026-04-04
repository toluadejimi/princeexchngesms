<?php

use App\Support\CustomerFacing;
use Illuminate\Database\ConnectionException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias(['admin' => \App\Http\Middleware\EnsureAdmin::class]);
        $middleware->appendToGroup('web', [\App\Http\Middleware\EnsureUserNotBlocked::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, Request $request) {
            if (! $request->expectsJson()) {
                return null;
            }

            if ($e instanceof HttpExceptionInterface) {
                return null;
            }

            if ($e instanceof QueryException || $e instanceof ConnectionException) {
                Log::error('Database error (sanitized for client)', [
                    'exception' => $e::class,
                    'message' => $e->getMessage(),
                ]);

                return response()->json([
                    'message' => CustomerFacing::DEFAULT_MESSAGE,
                ], 500);
            }

            return null;
        });
    })->create();
