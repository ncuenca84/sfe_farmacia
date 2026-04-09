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
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'emisor.activo' => \App\Http\Middleware\EmisorActivo::class,
            'whmcs.auth' => \App\Http\Middleware\WhmcsApiAuth::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(function (\App\Exceptions\LimiteComprobanteException $e) {
            return back()->with('error', $e->getMessage());
        });

        $exceptions->renderable(function (\App\Exceptions\PlanVencidoException $e) {
            return back()->with('error', $e->getMessage());
        });
    })->create();
