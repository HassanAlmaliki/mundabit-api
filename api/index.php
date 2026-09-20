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

    if (empty(getenv('SESSION_DRIVER'))) {
        putenv('SESSION_DRIVER=database');
        $_ENV['SESSION_DRIVER'] = 'database';
        $_SERVER['SESSION_DRIVER'] = 'database';
    }

    if (empty(getenv('SESSION_LIFETIME')) || (int) getenv('SESSION_LIFETIME') <= 0) {
        putenv('SESSION_LIFETIME=120');
        $_ENV['SESSION_LIFETIME'] = '120';
        $_SERVER['SESSION_LIFETIME'] = '120';
    }

    if (empty(getenv('SESSION_COOKIE'))) {
        putenv('SESSION_COOKIE=mundabit_session');
        $_ENV['SESSION_COOKIE'] = 'mundabit_session';
        $_SERVER['SESSION_COOKIE'] = 'mundabit_session';
    }

    if (empty(getenv('APP_NAME'))) {
        putenv('APP_NAME=mundabit');
        $_ENV['APP_NAME'] = 'mundabit';
        $_SERVER['APP_NAME'] = 'mundabit';
    }

    if (empty(getenv('BCRYPT_ROUNDS')) || (int) getenv('BCRYPT_ROUNDS') <= 0) {
        putenv('BCRYPT_ROUNDS=12');
        $_ENV['BCRYPT_ROUNDS'] = '12';
        $_SERVER['BCRYPT_ROUNDS'] = '12';
    }

    $_SERVER['HTTPS'] = 'on';
    $_SERVER['SERVER_PORT'] = '443';
}

// Direct static file serving fallback for serverless environment
$publicPath = realpath(__DIR__ . '/../public');
$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$filePath = $publicPath ? realpath($publicPath . $requestUri) : false;

if (
    $filePath &&
    $publicPath &&
    str_starts_with($filePath, $publicPath) &&
    is_file($filePath) &&
    $requestUri !== '/index.php'
) {
    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    $mimeTypes = [
        'css'   => 'text/css; charset=utf-8',
        'js'    => 'application/javascript; charset=utf-8',
        'mjs'   => 'application/javascript; charset=utf-8',
        'json'  => 'application/json',
        'woff2' => 'font/woff2',
        'woff'  => 'font/woff',
        'ttf'   => 'font/ttf',
        'svg'   => 'image/svg+xml',
        'png'   => 'image/png',
        'jpg'   => 'image/jpeg',
        'jpeg'  => 'image/jpeg',
        'gif'   => 'image/gif',
        'ico'   => 'image/x-icon',
        'webp'  => 'image/webp',
    ];

    $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';
    header('Content-Type: ' . $mimeType);
    header('Access-Control-Allow-Origin: *');
    header('Cache-Control: public, max-age=31536000, immutable');
    header('Content-Length: ' . filesize($filePath));
    readfile($filePath);
    exit(0);
}

try {
    require __DIR__ . '/../public/index.php';
} catch (\Throwable $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo "<h1>Exception in api/index.php</h1>";
    echo "<pre>" . htmlspecialchars((string) $e) . "</pre>";
}
