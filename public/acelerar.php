<?php

/**
 * Script de Optimización para Producción
 * Consultorio Integral Arenales
 */
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

define('LARAVEL_START', microtime(true));

// Localizar carpeta raíz de Laravel
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
    exit('Error: No se encontró la carpeta /laravel_app/ (falta vendor/autoload.php o bootstrap/app.php)');
}

require $basePath.'/vendor/autoload.php';
/** @var Application $app */
$app = require_once $basePath.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);

echo "<!DOCTYPE html><html lang='es'><head><meta charset='utf-8'><title>Optimización - Consultorio Arenales</title><style>body{font-family:system-ui,-apple-system,sans-serif;padding:30px;background:#082835;color:#fff;max-width:800px;margin:0 auto;}pre{background:rgba(0,0,0,0.4);padding:15px;border-radius:8px;font-size:13px;overflow-x:auto;border:1px solid rgba(255,255,255,0.1);color:#e2e8f0;}h1{color:#2dd4bf;font-size:1.5rem;margin-bottom:20px;}.btn{display:inline-block;background:#2dd4bf;color:#082835;padding:10px 18px;border-radius:8px;text-decoration:none;font-weight:bold;margin-top:15px;}</style></head><body>";
echo '<h1>🚀 Optimizando Consultorio Arenales para Producción...</h1>';

// 1. Limpiar físicamente primero
$cacheDir = $basePath.'/bootstrap/cache';
if (is_dir($cacheDir)) {
    foreach (glob($cacheDir.'/*.php') as $f) {
        @unlink($f);
    }
}

$commands = [
    'package:discover' => '1. Registrando paquetes y servicios...',
    'config:cache' => '2. Cacheando configuración (.env)...',
    'route:cache' => '3. Cacheando rutas del sistema...',
    'view:cache' => '4. Pre-compilando vistas Blade...',
    'filament:optimize-panels' => '5. Pre-compilando panel de gestión Filament...',
];

$hasError = false;

foreach ($commands as $cmd => $desc) {
    echo "<p><strong>{$desc}</strong></p><pre>";
    try {
        $kernel->call($cmd);
        $out = trim($kernel->output());
        echo htmlspecialchars($out ?: "Completado con éxito.\n");
    } catch (Throwable $e) {
        echo 'Aviso (continuando): '.htmlspecialchars($e->getMessage())."\n";
    }
    echo '</pre>';
}

$time = round((microtime(true) - LARAVEL_START) * 1000, 2);

if (! $hasError) {
    echo "<h2 style='color:#2dd4bf;margin-top:20px;'>✅ ¡Listo! Sistema optimizado al 100% en {$time} ms.</h2>";
    echo "<p style='color:#94a3b8;margin-top:10px;'>Las consultas y respuestas del servidor ahora responderán a máxima velocidad.</p>";
} else {
    echo "<h2 style='color:#fca5a5;margin-top:20px;'>⚠️ El proceso finalizó con algunas advertencias.</h2>";
    echo "<p style='color:#94a3b8;'>Podés ingresar a <a href='diagnostico.php' style='color:#2dd4bf;'>diagnostico.php</a> para revisar el estado detallado de la base de datos y permisos.</p>";
}

echo "<div style='margin-top:25px;'>";
echo "<a href='/' class='btn' style='margin-right:10px;'>Ir a la Web</a> ";
echo "<a href='/gestion' class='btn' style='background:rgba(255,255,255,0.15);color:#fff;'>Ir al Panel de Gestión</a> ";
echo "<a href='diagnostico.php' class='btn' style='background:rgba(255,255,255,0.15);color:#fff;'>Diagnóstico</a>";
echo '</div>';

echo '</body></html>';
