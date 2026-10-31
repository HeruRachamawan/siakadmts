<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\SetAcademicYearContext;
use Laravel\Sanctum\SanctumServiceProvider;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: [
            __DIR__.'/../routes/api/auth.php',
            __DIR__.'/../routes/api/admin.php',
            __DIR__.'/../routes/api/teacher.php',
            __DIR__.'/../routes/api/student.php',
            __DIR__.'/../routes/api/public.php',
        ],
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withProviders([
        SanctumServiceProvider::class,
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias(['role' => RoleMiddleware::class]);
        $middleware->api(append: [
            SetAcademicYearContext::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
