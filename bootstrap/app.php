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
        $middleware->validateCsrfTokens(except: [
            '/midtrans/notification' // URI yang dikecualikan dari CSRF
        ]);
        $middleware->alias([
            'feature' => \App\Http\Middleware\CheckFeatureIsEnabled::class,
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'subscribed' => \App\Http\Middleware\CheckSubscription::class,
            'setActiveOutlet' => \App\Http\Middleware\SetActiveOutlet::class,
        ]);
        $middleware->web(append: [
            \App\Http\Middleware\SetActiveOutlet::class, // ⬅️ aktif di semua route web
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();