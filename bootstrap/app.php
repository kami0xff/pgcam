<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust Cloudflare Tunnel as a reverse proxy so Laravel reads
        // X-Forwarded-Proto, X-Forwarded-For, etc. correctly
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'set.locale' => \App\Http\Middleware\SetLocale::class,
            'detect.locale' => \App\Http\Middleware\DetectLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
