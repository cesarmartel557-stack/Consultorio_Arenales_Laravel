<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Localizar automáticamente la carpeta raíz de Laravel
$possiblePaths = [
    __DIR__ . '/..',
    __DIR__ . '/../laravel_app',
    __DIR__ . '/../../laravel_app',
    __DIR__ . '/../../',
];

$basePath = null;
foreach ($possiblePaths as $path) {
    if (file_exists($path . '/vendor/autoload.php') && file_exists($path . '/bootstrap/app.php')) {
        $basePath = realpath($path);
        break;
    }
}

if (! $basePath) {
    http_response_code(500);
    echo '<h1>HTTP 500 - Error de configuración</h1>';
    echo '<p>No se pudo localizar la carpeta <code>laravel_app</code> (falta <code>vendor/autoload.php</code> o <code>bootstrap/app.php</code>).</p>';
    echo '<p>Verifique que <strong>deploy.zip</strong> esté descomprimido en <code>/laravel_app/</code> y <strong>public_html.zip</strong> en su carpeta pública.</p>';
    exit(1);
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $basePath . '/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $basePath . '/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $basePath . '/bootstrap/app.php';

$app->handleRequest(Request::capture());
