<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
echo "VERCEL ENV: " . (isset($_ENV['VERCEL']) ? 'YES' : 'NO') . "<br>";
echo "VERCEL SERVER: " . (isset($_SERVER['VERCEL']) ? 'YES' : 'NO') . "<br>";


// Prepare storage directories in /tmp for Vercel serverless environment
if (isset($_ENV['VERCEL']) || isset($_SERVER['VERCEL'])) {
    $storageDirs = [
        '/tmp/storage/framework/views',
        '/tmp/storage/framework/cache/data',
        '/tmp/storage/framework/sessions',
        '/tmp/storage/logs',
        '/tmp/bootstrap/cache',
    ];

    foreach ($storageDirs as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    putenv('APP_SERVICES_CACHE=/tmp/bootstrap/cache/services.php');
    putenv('APP_PACKAGES_CACHE=/tmp/bootstrap/cache/packages.php');
    putenv('APP_CONFIG_CACHE=/tmp/bootstrap/cache/config.php');
    putenv('APP_ROUTES_CACHE=/tmp/bootstrap/cache/routes.php');
    putenv('APP_EVENTS_CACHE=/tmp/bootstrap/cache/events.php');
    putenv('VIEW_COMPILED_PATH=/tmp/storage/framework/views');
}

try {
    require __DIR__ . '/../public/index.php';
} catch (\Throwable $e) {
    echo "<h1>FATAL ERROR CAUGHT IN API/INDEX.PHP</h1>";
    echo "<pre>" . (string) $e . "</pre>";
}
