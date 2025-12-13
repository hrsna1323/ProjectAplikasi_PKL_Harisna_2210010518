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
        // Add SanitizeInput middleware to web group for all form submissions
        $middleware->web(append: [
            \App\Http\Middleware\SanitizeInput::class,
        ]);
        
        $middleware->alias([
            'auth.custom' => \App\Http\Middleware\AuthCustom::class,
            'admin' => \App\Http\Middleware\AdminOnly::class,
            'operator' => \App\Http\Middleware\OperatorOnly::class,
            'publisher' => \App\Http\Middleware\PublisherOnly::class,
            'sanitize' => \App\Http\Middleware\SanitizeInput::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
