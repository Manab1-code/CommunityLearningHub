<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // ✅ your middleware alias
        $middleware->alias([
            'api_token' => \App\Http\Middleware\ApiTokenAuth::class,
            'session_token' => \App\Http\Middleware\SetUserFromSessionToken::class,
            'admin' => \App\Http\Middleware\EnsureAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // ✅ IMPORTANT: keep this so Laravel binds ExceptionHandler correctly
    })
    ->create();
