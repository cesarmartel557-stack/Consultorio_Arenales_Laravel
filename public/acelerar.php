<?php

use Illuminate\Contracts\Console\Kernel;

// Script para acelerar y optimizar Laravel y Filament en producción
define('LARAVEL_START', microtime(true));

$autoload = __DIR__.'/../laravel_app/vendor/autoload.php';
$bootstrap = __DIR__.'/../laravel_app/bootstrap/app.php';

if (! file_exists($autoload) || ! file_exists($bootstrap)) {
    // Fallback if public_html is in another relative depth
    $autoload = __DIR__.'/../../laravel_app/vendor/autoload.php';
    $bootstrap = __DIR__.'/../../laravel_app/bootstrap/app.php';
}

if (! file_exists($autoload)) {
    exit('Error: No se encontró la carpeta /laravel_app/');
}

require $autoload;
$app = require_once $bootstrap;

$kernel = $app->make(Kernel::class);

echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Optimización</title><style>body{font-family:sans-serif;padding:30px;background:#0d3f52;color:#fff;}pre{background:rgba(0,0,0,0.3);padding:15px;border-radius:8px;font-size:14px;overflow-x:auto;}h1{color:#4ade80;}</style></head><body>";
echo '<h1>🚀 Optimizando Consultorio Arenales...</h1>';

$commands = [
    'optimize:clear' => '1. Limpiando cachés viejas...',
    'config:cache' => '2. Cacheando configuración (.env)...',
    'route:cache' => '3. Cacheando todas las rutas...',
    'view:cache' => '4. Pre-compilando vistas Blade...',
    'filament:optimize' => '5. Pre-compilando panel Filament y recursos...',
    'filament:assets' => '6. Publicando assets estáticos de Filament...',
];

foreach ($commands as $cmd => $desc) {
    echo "<p><strong>{$desc}</strong></p><pre>";
    try {
        $kernel->call($cmd);
        echo htmlspecialchars($kernel->output() ?: "Completado con éxito.\n");
    } catch (Throwable $e) {
        echo 'Aviso: '.htmlspecialchars($e->getMessage())."\n";
    }
    echo '</pre>';
}

$time = round((microtime(true) - LARAVEL_START) * 1000, 2);
echo "<h2 style='color:#4ade80;'>✅ ¡Listo! Sistema optimizado al 100% en {$time} ms.</h2>";
echo '<p><em>Por seguridad, una vez verificado podés eliminar este archivo acelerar.php</em></p>';
echo '</body></html>';
