<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

/**
 * Herramienta de Diagnóstico, Reparación y Mantenimiento
 * Consultorio Integral Arenales
 */
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

define('DIAG_START', microtime(true));

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

$action = $_GET['action'] ?? null;
$actionOutput = '';
$actionSuccess = true;

// Helper para borrar archivos recursivamente en un directorio
function cleanDirFiles($dir, $extensions = ['php'])
{
    if (! is_dir($dir)) {
        return 0;
    }
    $count = 0;
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($files as $file) {
        if ($file->isFile()) {
            $ext = strtolower($file->getExtension());
            if (in_array($ext, $extensions) && $file->getFilename() !== '.gitignore') {
                @unlink($file->getRealPath());
                $count++;
            }
        }
    }

    return $count;
}

if ($basePath && $action) {
    try {
        require_once $basePath.'/vendor/autoload.php';
        /** @var Application $app */
        $app = require_once $basePath.'/bootstrap/app.php';
        $kernel = $app->make(Kernel::class);

        if ($action === 'clear_cache') {
            // 1. Borrar archivos físicos en bootstrap/cache
            $deletedBootstrap = cleanDirFiles($basePath.'/bootstrap/cache');
            $deletedViews = cleanDirFiles($basePath.'/storage/framework/views');
            $deletedCache = cleanDirFiles($basePath.'/storage/framework/cache/data');

            // 2. Ejecutar optimize:clear
            $kernel->call('optimize:clear');
            $artisanOut = $kernel->output();

            $actionOutput = "✅ Cachés eliminadas correctamente:\n".
                            "- Archivos eliminados en bootstrap/cache: {$deletedBootstrap}\n".
                            "- Vistas compiladas eliminadas: {$deletedViews}\n".
                            "- Caché de datos eliminada: {$deletedCache}\n\n".
                            "Salida de Artisan:\n".$artisanOut;
        } elseif ($action === 'migrate') {
            $migOut = '';
            try {
                $kernel->call('migrate', ['--force' => true]);
                $migOut = $kernel->output() ?: "Migraciones completadas sin cambios pendientes.\n";
            } catch (Throwable $e) {
                $migOut = 'Error en migraciones: '.$e->getMessage()."\n";
            }

            $actionOutput = "✅ Migraciones de estructura ejecutadas:\n\n".$migOut."\nℹ️ No se modificaron los datos existentes.";
        } elseif ($action === 'seed') {
            $seedOut = '';
            $userOut = '';

            try {
                $kernel->call('db:seed', ['--class' => 'ConsultorioSeeder', '--force' => true]);
                $seedOut = $kernel->output() ?: "Seeds completados.\n";
            } catch (Throwable $e) {
                $seedOut = 'Aviso en seed: '.$e->getMessage()."\n";
            }

            try {
                $kernel->call('app:setup-production-users');
                $userOut = $kernel->output() ?: "Usuarios y médicos de producción configurados.\n";
            } catch (Throwable $e) {
                $userOut = 'Aviso en configuración de usuarios: '.$e->getMessage()."\n";
            }

            $actionOutput = "⚠️ Datos iniciales (Seeds) restablecidos:\n\n".
                            "--- DATOS INICIALES (SEED) ---\n".$seedOut."\n".
                            "--- USUARIOS Y MÉDICOS ---\n".$userOut;
        } elseif ($action === 'optimize') {
            $optSteps = [
                'package:discover' => '1. Descubriendo paquetes de Laravel...',
                'config:cache' => '2. Cacheando configuración (.env)...',
                'route:cache' => '3. Cacheando rutas del sistema...',
                'view:cache' => '4. Pre-compilando vistas Blade...',
                'filament:optimize-panels' => '5. Pre-compilando paneles de gestión...',
            ];
            $results = [];
            foreach ($optSteps as $cmd => $desc) {
                try {
                    $kernel->call($cmd);
                    $out = trim($kernel->output());
                    $results[] = "✔ {$desc}".($out ? "\n  ".str_replace("\n", "\n  ", $out) : '');
                } catch (Throwable $e) {
                    $results[] = "ℹ {$desc} (Omitido: ".$e->getMessage().')';
                }
            }
            $actionOutput = "✅ Proceso de Optimización Finalizado:\n\n".implode("\n\n", $results);
        } elseif ($action === 'test_run') {
            $httpKernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

            // 1. Probar Portada
            $req1 = Request::create('/', 'GET');
            $res1 = $httpKernel->handle($req1);
            $code1 = $res1->getStatusCode();
            $content1 = $res1->getContent();
            $httpKernel->terminate($req1, $res1);

            // 2. Probar Panel
            $req2 = Request::create('/gestion', 'GET');
            $res2 = $httpKernel->handle($req2);
            $code2 = $res2->getStatusCode();
            $content2 = $res2->getContent();
            $httpKernel->terminate($req2, $res2);

            $actionOutput = "=== REPORTE DE PETICIONES HTTP DIRECTAS ===\n\n".
                            "1. PORTADA (GET /)\n".
                            "   - Código HTTP: {$code1}\n".
                            '   - Estado: '.($code1 === 200 ? "✔ ¡CORRECTO (200 OK)!\n" : "❌ Error HTTP {$code1}\n   - Salida:\n   ".substr(strip_tags($content1), 0, 800)."\n\n").
                            "2. PANEL DE GESTIÓN (GET /gestion)\n".
                            "   - Código HTTP: {$code2}\n".
                            '   - Estado: '.($code2 === 200 || $code2 === 302 ? "✔ ¡CORRECTO ({$code2})!\n" : "❌ Error HTTP {$code2}\n   - Salida:\n   ".substr(strip_tags($content2), 0, 800)."\n");
        } elseif ($action === 'enable_debug') {
            $envPath = $basePath.'/.env';
            if (file_exists($envPath)) {
                $envData = file_get_contents($envPath);
                $envData = str_replace('APP_DEBUG=false', 'APP_DEBUG=true', $envData);
                file_put_contents($envPath, $envData);

                // Limpiar cache de config
                @unlink($basePath.'/bootstrap/cache/config.php');
                $actionOutput = '✔ Modo de depuración activado (APP_DEBUG=true). Ahora al ingresar a la portada se mostrará el mensaje de error exacto de Laravel.';
            }
        }
    } catch (Throwable $e) {
        $actionSuccess = false;
        $actionOutput = "❌ Error al ejecutar acción '{$action}':\n".
                        $e->getMessage()."\n\n".
                        'En: '.$e->getFile().':'.$e->getLine()."\n\n".
                        "Trace:\n".$e->getTraceAsString();
    }
}

// Variables de diagnóstico
$checks = [];

// 1. PHP Version
$checks['php_version'] = [
    'title' => 'Versión de PHP',
    'ok' => version_compare(PHP_VERSION, '8.2.0', '>='),
    'detail' => PHP_VERSION.(version_compare(PHP_VERSION, '8.2.0', '>=') ? ' (Compatible)' : ' (Se requiere PHP 8.2 o superior)'),
];

// 2. Extensiones PHP
$requiredExtensions = ['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'tokenizer', 'xml', 'ctype', 'json', 'bcmath', 'curl', 'fileinfo'];
$missingExts = [];
foreach ($requiredExtensions as $ext) {
    if (! extension_loaded($ext)) {
        $missingExts[] = $ext;
    }
}
$checks['php_extensions'] = [
    'title' => 'Extensiones PHP Requeridas',
    'ok' => empty($missingExts),
    'detail' => empty($missingExts) ? 'Todas las extensiones requeridas están instaladas.' : 'Faltan extensiones: '.implode(', ', $missingExts),
];

// 3. Carpeta laravel_app
$checks['base_path'] = [
    'title' => 'Carpeta backend (laravel_app)',
    'ok' => (bool) $basePath,
    'detail' => $basePath ? "Ubicación detectada: {$basePath}" : 'No se encontró laravel_app. Verifique la estructura de carpetas.',
];

// 4. Permisos de escritura
$writeDirs = [
    'storage' => $basePath ? $basePath.'/storage' : null,
    'storage/logs' => $basePath ? $basePath.'/storage/logs' : null,
    'storage/framework/views' => $basePath ? $basePath.'/storage/framework/views' : null,
    'storage/framework/cache' => $basePath ? $basePath.'/storage/framework/cache' : null,
    'storage/framework/sessions' => $basePath ? $basePath.'/storage/framework/sessions' : null,
    'bootstrap/cache' => $basePath ? $basePath.'/bootstrap/cache' : null,
];
$unwritableDirs = [];
foreach ($writeDirs as $name => $p) {
    if ($p) {
        if (! is_dir($p)) {
            @mkdir($p, 0775, true);
        }
        if (! is_writable($p)) {
            $unwritableDirs[] = $name;
        }
    }
}
$checks['permissions'] = [
    'title' => 'Permisos de Escritura (chmod 775 / 755)',
    'ok' => empty($unwritableDirs) && (bool) $basePath,
    'detail' => empty($unwritableDirs) ? 'Todas las carpetas de almacenamiento y caché tienen permisos de escritura.' : 'Las siguientes carpetas no tienen permiso de escritura: '.implode(', ', $unwritableDirs),
];

// 5. Archivo .env
$envFile = $basePath ? $basePath.'/.env' : null;
$envExists = $envFile && file_exists($envFile);
$appKeySet = false;
$dbHost = '127.0.0.1';
$dbPort = '3306';
$dbName = '';
$dbUser = '';
$dbPass = '';

if ($envExists) {
    $envContent = file_get_contents($envFile);
    if (preg_match('/^APP_KEY=(.+)$/m', $envContent, $m)) {
        $key = trim($m[1]);
        if (! empty($key) && $key !== 'base64:COPIA_AQUI_LA_APP_KEY') {
            $appKeySet = true;
        }
    }
    if (preg_match('/^DB_HOST=(.+)$/m', $envContent, $m)) {
        $dbHost = trim($m[1]);
    }
    if (preg_match('/^DB_PORT=(.+)$/m', $envContent, $m)) {
        $dbPort = trim($m[1]);
    }
    if (preg_match('/^DB_DATABASE=(.+)$/m', $envContent, $m)) {
        $dbName = trim($m[1]);
    }
    if (preg_match('/^DB_USERNAME=(.+)$/m', $envContent, $m)) {
        $dbUser = trim($m[1]);
    }
    if (preg_match('/^DB_PASSWORD=(.*)$/m', $envContent, $m)) {
        $dbPass = trim($m[1], " \t\n\r\0\x0B\"'");
    }
}

$checks['env_file'] = [
    'title' => 'Archivo de Configuración (.env)',
    'ok' => $envExists && $appKeySet,
    'detail' => ! $envExists
        ? 'No existe el archivo .env en laravel_app/. Debe crearlo según la guía.'
        : (! $appKeySet ? 'El archivo .env existe pero APP_KEY está vacío o sin configurar.' : 'Archivo .env detectado y APP_KEY configurada.'),
];

// 6. Conexión a Base de Datos MySQL
$dbConnected = false;
$dbTablesCount = 0;
$dbError = null;
$requiredTables = ['users', 'doctors', 'specialties', 'appointments', 'home_pages'];
$missingTables = [];

if ($envExists && ! empty($dbName) && ! empty($dbUser)) {
    try {
        $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";
        $pdo = new PDO($dsn, $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 3,
        ]);
        $dbConnected = true;

        $stmt = $pdo->query('SHOW TABLES');
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $dbTablesCount = count($tables);

        foreach ($requiredTables as $reqTab) {
            if (! in_array($reqTab, $tables)) {
                $missingTables[] = $reqTab;
            }
        }
    } catch (Throwable $e) {
        $dbError = $e->getMessage();
    }
}

$checks['database'] = [
    'title' => 'Conexión a Base de Datos MySQL',
    'ok' => $dbConnected && empty($missingTables) && $dbTablesCount > 0,
    'detail' => $dbConnected
        ? ($dbTablesCount === 0
            ? "⚠️ Conexión exitosa a la base de datos '{$dbName}', pero <strong>está vacía (0 tablas)</strong>. Hacé clic en 'Ejecutar Migraciones' abajo."
            : (! empty($missingTables)
                ? '⚠️ Conexión exitosa, pero faltan tablas clave: '.implode(', ', $missingTables).". Hacé clic en 'Ejecutar Migraciones' abajo."
                : "Conexión exitosa. Se detectaron {$dbTablesCount} tablas creadas correctamente."))
        : 'Error al conectar: '.($dbError ?: 'Credenciales de base de datos incompletas en el archivo .env.'),
];

// 7. Últimos errores en laravel.log
$recentLogs = '';
$logFile = $basePath ? $basePath.'/storage/logs/laravel.log' : null;
if ($logFile && file_exists($logFile)) {
    $lines = file($logFile);
    if ($lines) {
        $recentLines = array_slice($lines, -150);
        $recentLogs = implode('', $recentLines);
    }
}

// 8. Verificar archivos de caché existentes
$cacheFilesFound = [];
if ($basePath && is_dir($basePath.'/bootstrap/cache')) {
    foreach (glob($basePath.'/bootstrap/cache/*.php') as $f) {
        $cacheFilesFound[] = basename($f);
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Diagnóstico y Reparación - Consultorio Arenales</title>
    <style>
        :root {
            --bg-color: #082835;
            --card-bg: #0d3f52;
            --accent: #2dd4bf;
            --accent-hover: #14b8a6;
            --text-color: #f8fafc;
            --text-muted: #94a3b8;
            --border-color: rgba(255, 255, 255, 0.12);
            --ok: #22c55e;
            --warn: #eab308;
            --err: #ef4444;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            padding: 30px 20px;
            line-height: 1.5;
        }
        .container { max-width: 960px; margin: 0 auto; }
        header { margin-bottom: 25px; border-bottom: 1px solid var(--border-color); padding-bottom: 15px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
        h1 { font-size: 1.5rem; color: #fff; display: flex; align-items: center; gap: 8px; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 9999px; font-size: 0.75rem; font-weight: bold; text-transform: uppercase; }
        .badge-ok { background: rgba(34, 197, 94, 0.2); color: var(--ok); border: 1px solid var(--ok); }
        .badge-warn { background: rgba(234, 179, 8, 0.2); color: var(--warn); border: 1px solid var(--warn); }
        .badge-err { background: rgba(239, 68, 68, 0.2); color: var(--err); border: 1px solid var(--err); }
        
        .card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }
        .card h2 { font-size: 1.15rem; margin-bottom: 15px; color: var(--accent); display: flex; align-items: center; gap: 8px; }
        
        .check-item {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            padding: 12px;
            border-radius: 8px;
            background: rgba(0, 0, 0, 0.18);
            margin-bottom: 10px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .check-icon { font-size: 1.4rem; line-height: 1; margin-top: 2px; }
        .check-content { flex: 1; }
        .check-title { font-weight: 600; font-size: 0.95rem; margin-bottom: 2px; }
        .check-detail { font-size: 0.85rem; color: var(--text-muted); }
        
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 12px;
            margin-top: 10px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
            text-align: center;
        }
        .btn-primary { background: var(--accent); color: #082835; }
        .btn-primary:hover { background: var(--accent-hover); }
        .btn-secondary { background: rgba(255, 255, 255, 0.1); color: #fff; border: 1px solid var(--border-color); }
        .btn-secondary:hover { background: rgba(255, 255, 255, 0.2); }
        .btn-danger { background: rgba(239, 68, 68, 0.2); color: #fca5a5; border: 1px solid var(--err); }
        .btn-danger:hover { background: rgba(239, 68, 68, 0.35); }
        .btn-success { background: rgba(34, 197, 94, 0.2); color: #86efac; border: 1px solid var(--ok); }
        .btn-success:hover { background: rgba(34, 197, 94, 0.35); }
        
        pre {
            background: rgba(0, 0, 0, 0.4);
            padding: 15px;
            border-radius: 8px;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.82rem;
            line-height: 1.4;
            overflow-x: auto;
            max-height: 350px;
            border: 1px solid var(--border-color);
            color: #e2e8f0;
            white-space: pre-wrap;
            word-break: break-word;
        }
        .action-result {
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }
        .action-result.ok { background: rgba(34, 197, 94, 0.15); border-color: var(--ok); }
        .action-result.err { background: rgba(239, 68, 68, 0.15); border-color: var(--err); }
    </style>
</head>
<body>

<div class="container">
    <header>
        <h1>🩺 Consultorio Arenales &mdash; Diagnóstico y Reparación</h1>
        <div>
            <a href="/" class="btn btn-secondary" style="padding:6px 12px; font-size:0.8rem;">Ir a la Web</a>
            <a href="/gestion" class="btn btn-secondary" style="padding:6px 12px; font-size:0.8rem;">Ir al Panel</a>
        </div>
    </header>

    <?php if ($action && $actionOutput) { ?>
        <div class="action-result <?= $actionSuccess ? 'ok' : 'err' ?>">
            <h3 style="margin-bottom:8px;"><?= $actionSuccess ? '✅ Resultado de la Operación' : '❌ Error detectado' ?></h3>
            <pre><?= htmlspecialchars($actionOutput) ?></pre>
        </div>
    <?php } ?>

    <!-- BOTONES DE ACCIÓN RÁPIDA -->
    <div class="card">
        <h2>🛠️ Acciones de Reparación Rápida</h2>
        <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:15px;">
            Hacé clic en las siguientes acciones según el diagnóstico para resolver el Error 500:
        </p>
        <div class="actions-grid">
            <a href="?action=clear_cache" class="btn btn-primary">
                🧹 1. Limpiar Todas las Cachés
            </a>
            <a href="?action=migrate" class="btn btn-success" onclick="return confirm('¿Ejecutar migraciones de tablas pendientes? No borrará ni modificará datos existentes.');">
                🗄️ 2. Migrar Tablas (Solo Estructura)
            </a>
            <a href="?action=test_run" class="btn btn-secondary">
                🧪 3. Probar Peticiones HTTP
            </a>
            <a href="?action=optimize" class="btn btn-secondary">
                🚀 4. Optimizar para Producción
            </a>
            <a href="?action=seed" class="btn btn-secondary" style="border: 1px dashed rgba(255,255,255,0.3); font-size:0.8rem;" onclick="return confirm('⚠️ ATENCIÓN: Esto restablecerá los médicos y horarios a los valores iniciales de prueba del seeder. ¿Seguro que deseas continuar?');">
                ⚠️ Restablecer Datos de Prueba (Seed)
            </a>
            <a href="?action=enable_debug" class="btn btn-danger" style="grid-column: 1 / -1;" onclick="return confirm('¿Activar APP_DEBUG para ver el error exacto en pantalla?');">
                🛠️ Ver Error Exacto en Pantalla (Activar APP_DEBUG)
            </a>
        </div>
    </div>

    <!-- ESTADO DEL SISTEMA -->
    <div class="card">
        <h2>🔍 Verificación del Servidor y Entorno</h2>
        <?php foreach ($checks as $key => $check) { ?>
            <div class="check-item">
                <div class="check-icon"><?= $check['ok'] ? '✅' : '❌' ?></div>
                <div class="check-content">
                    <div class="check-title">
                        <?= htmlspecialchars($check['title']) ?>
                        <span class="badge <?= $check['ok'] ? 'badge-ok' : 'badge-err' ?>" style="margin-left:8px;">
                            <?= $check['ok'] ? 'CORRECTO' : 'ATENCIÓN' ?>
                        </span>
                    </div>
                    <div class="check-detail"><?= $check['detail'] ?></div>
                </div>
            </div>
        <?php } ?>
    </div>

    <!-- ARCHIVOS DE CACHÉ ACTUALES -->
    <div class="card">
        <h2>📦 Cachés Detectadas en <code>bootstrap/cache</code></h2>
        <?php if (! empty($cacheFilesFound)) { ?>
            <p style="font-size:0.85rem; color:#fca5a5; margin-bottom:10px;">
                ⚠️ Se encontraron los siguientes archivos de caché activos. Si fueron creados en Windows o antes de configurar la base de datos, causarán Error 500:
            </p>
            <ul style="margin-left:20px; font-size:0.85rem; color:var(--text-muted); margin-bottom:12px;">
                <?php foreach ($cacheFilesFound as $cf) { ?>
                    <li><code><?= htmlspecialchars($cf) ?></code></li>
                <?php } ?>
            </ul>
            <a href="?action=clear_cache" class="btn btn-danger" style="display:inline-flex;">
                🧹 Eliminar todos los archivos de caché
            </a>
        <?php } else { ?>
            <p style="font-size:0.85rem; color:#86efac;">
                ✅ No hay archivos de caché huérfanos. Las rutas y configuración se cargan en tiempo real.
            </p>
        <?php } ?>
    </div>

    <!-- LOGS DE ERROR RECIENTES -->
    <div class="card">
        <h2>📋 Últimos Registros de Error (<code>storage/logs/laravel.log</code>)</h2>
        <?php if (! empty($recentLogs)) { ?>
            <pre><?= htmlspecialchars($recentLogs) ?></pre>
        <?php } else { ?>
            <p style="font-size:0.85rem; color:var(--text-muted);">
                No se encontraron registros de error recientes en <code>storage/logs/laravel.log</code>.
            </p>
        <?php } ?>
    </div>

</div>

</body>
</html>
