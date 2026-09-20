<?php

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

    if (empty(getenv('APP_MAINTENANCE_DRIVER'))) {
        putenv('APP_MAINTENANCE_DRIVER=file');
        $_ENV['APP_MAINTENANCE_DRIVER'] = 'file';
        $_SERVER['APP_MAINTENANCE_DRIVER'] = 'file';
    }

    if (empty(getenv('APP_KEY'))) {
        putenv('APP_KEY=base64:+XsiCF9dUlCq/2oDq1Ay704c9RLnsKJMoC0MU3IfWN0=');
        $_ENV['APP_KEY'] = 'base64:+XsiCF9dUlCq/2oDq1Ay704c9RLnsKJMoC0MU3IfWN0=';
        $_SERVER['APP_KEY'] = 'base64:+XsiCF9dUlCq/2oDq1Ay704c9RLnsKJMoC0MU3IfWN0=';
    }

    if (empty(getenv('APP_LOCALE'))) {
        putenv('APP_LOCALE=ar');
        $_ENV['APP_LOCALE'] = 'ar';
        $_SERVER['APP_LOCALE'] = 'ar';
    }
}

try {
    require __DIR__ . '/../public/index.php';
} catch (\Throwable $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo "<h1>Exception in api/index.php</h1>";
    echo "<pre>" . htmlspecialchars((string) $e) . "</pre>";
}
