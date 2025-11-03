<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/admin/index.route.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin.auth' => \App\Http\Middleware\AdminAuthMiddleware::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            'admin/courses',
            'admin/courses/*',
            'admin/lesson',
            'admin/quizz',
            'admin/quizz/{quizId}/questions',
            'admin/questions/{questionId}/answers',
            'admin/questions/{questionId}',
            'admin/answers/{answerId}',
            'admin/questions/{questionId}/true-answer'
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

return $app;
