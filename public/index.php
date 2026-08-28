<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Localizar automáticamente la carpeta raíz de Laravel
$possiblePaths = [
    __DIR__.'/..',
    __DIR__.'/../laravel_app',
    __DIR__.'/../../laravel_app',
    __DIR__.'/../../',
];

$basePath = null;
foreach ($possiblePaths as $path) {
    if (file_exists($path.'/vendor/autoload.php') && file_exists($path.'/bootstrap/app.php')) {
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
if (file_exists($maintenance = $basePath.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $basePath.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
try {
    /** @var Application $app */
    $app = require_once $basePath.'/bootstrap/app.php';
    $app->handleRequest(Request::capture());
} catch (Throwable $e) {
    http_response_code(500);
    echo '<!DOCTYPE html><html lang="es"><head><meta charset="utf-8"><title>Error 500</title>';
    echo '<style>body{font-family:system-ui,-apple-system,sans-serif;background:#082835;color:#fff;padding:40px;line-height:1.5;}pre{background:rgba(0,0,0,0.4);padding:15px;border-radius:8px;overflow-x:auto;color:#e2e8f0;border:1px solid rgba(255,255,255,0.1);font-size:13px;}</style></head><body>';
    echo '<h1 style="color:#f87171;">⚠️ Error 500 en Laravel</h1>';
    echo '<p style="font-size:1.1rem;margin:15px 0;"><strong>Mensaje:</strong> '.htmlspecialchars($e->getMessage()).'</p>';
    echo '<p style="color:#94a3b8;"><strong>En:</strong> '.htmlspecialchars($e->getFile()).':'.$e->getLine().'</p>';
    echo '<h3 style="margin-top:25px;margin-bottom:10px;color:#2dd4bf;">Stack Trace:</h3>';
    echo '<pre>'.htmlspecialchars($e->getTraceAsString()).'</pre>';
    echo '</body></html>';
}
