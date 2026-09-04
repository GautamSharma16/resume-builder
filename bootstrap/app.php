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
        $middleware->web(append: [
            \App\Http\Middleware\CanonicalHost::class,
            \App\Http\Middleware\SecurityAndCacheHeaders::class,
            \App\Http\Middleware\TrackVisitor::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'razorpay/webhook',
        ]);

        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureAdminRole::class,
            'user' => \App\Http\Middleware\EnsureUserRole::class,
            'company' => \App\Http\Middleware\EnsureCompanyRole::class,
            'role' => \App\Http\Middleware\EnsureRole::class,
            'subscription' => \App\Http\Middleware\EnsureActiveSubscription::class,
            'download.access' => \App\Http\Middleware\EnsureDownloadAccess::class,
            'permission' => \App\Http\Middleware\PermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
