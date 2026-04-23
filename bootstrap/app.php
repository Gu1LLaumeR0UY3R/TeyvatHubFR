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
        $middleware->alias([
            'admin'     => \App\Http\Middleware\AdminMiddleware::class,
            'admin.can' => \App\Http\Middleware\AdminCanMiddleware::class,
            '2fa.user'  => \App\Http\Middleware\EnsureUserTwoFactorIsVerified::class,
            '2fa.admin' => \App\Http\Middleware\EnsureAdminTwoFactorIsVerified::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
