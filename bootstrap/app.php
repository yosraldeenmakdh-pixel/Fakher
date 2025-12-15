<?php

use App\Http\Middleware\CheckUserActivity;
use App\Http\Middleware\CheckUserExists;
use App\Http\Middleware\CheckUserRole;
use App\Http\Middleware\CorsProtection;
use App\Http\Middleware\Guest;
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

        $middleware->use([
            CorsProtection::class,
        ]);

        $middleware->alias([
            'guest'=>Guest::class ,
            'checkUserRole' => CheckUserRole::class,
        ]);


    })
    ->withExceptions(function (Exceptions $exceptions): void {

    })->create();
