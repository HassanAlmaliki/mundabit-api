<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin/login');
});

Route::get('/debug-hash', function () {
    $hasher = app('hash')->driver('bcrypt');
    $reflection = new ReflectionClass($hasher);
    $roundsProp = $reflection->getProperty('rounds');
    $rounds = $roundsProp->getValue($hasher);

    $actualError = null;
    try {
        password_hash('test', PASSWORD_BCRYPT, ['cost' => $rounds]);
    } catch (\Throwable $e) {
        $actualError = [
            'msg' => $e->getMessage(),
            'class' => get_class($e),
        ];
    }

    return response()->json([
        'hashing_config' => config('hashing'),
        'rounds_in_hasher' => $rounds,
        'cost_test_error' => $actualError,
    ]);
});
