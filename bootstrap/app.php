<?php

use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            Route::middleware(['api', 'throttle:120,1'])
                ->prefix('v1/integration')
                ->name('integration.open.')
                ->group(base_path('routes/integration.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'affiliate.auth' => \App\Http\Middleware\AuthenticateAffiliate::class,
            'integration.caller_host' => \App\Http\Middleware\VerifyIntegrationCallerHost::class,
            'integration.list' => \App\Http\Middleware\VerifyIntegrationListToken::class,
            'integration.order_signature' => \App\Http\Middleware\VerifyIntegrationOrderSignature::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            '/payment/callback'
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
