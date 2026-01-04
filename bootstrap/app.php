<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
            'teacher' => \App\Http\Middleware\EnsureUserIsTeacher::class,
            'student' => \App\Http\Middleware\EnsureUserIsStudent::class,
        ]);

        // Only apply these middleware to API routes
        $middleware->api([
            \App\Http\Middleware\LogApiActivity::class,
            \App\Http\Middleware\ApiRateLimit::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
