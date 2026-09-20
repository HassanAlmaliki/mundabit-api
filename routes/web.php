<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin/login');
});

Route::get('/debug-hash', function () {
    try {
        $phpHash = password_hash('password123', PASSWORD_BCRYPT);
        $laravelHash = \Illuminate\Support\Facades\Hash::make('password123');
        return response()->json([
            'status' => 'success',
            'phpHash' => $phpHash,
            'laravelHash' => $laravelHash,
            'php_version' => PHP_VERSION,
            'bcrypt_rounds_env' => getenv('BCRYPT_ROUNDS'),
            'hashing_config' => config('hashing'),
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'class' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);
    }
});
