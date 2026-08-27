<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'gestion/logout',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

$publicPath = realpath(__DIR__.'/../../public_html/turnos')
    ?: realpath(__DIR__.'/../../public_html')
    ?: (file_exists(dirname(__DIR__).'/../public_html/turnos') ? dirname(__DIR__).'/../public_html/turnos' : null)
    ?: (file_exists(dirname(__DIR__).'/../public_html') ? dirname(__DIR__).'/../public_html' : null)
    ?: (dirname(__DIR__).'/public');

$app->usePublicPath($publicPath);

return $app;
