<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Hash Driver
    |--------------------------------------------------------------------------
    |
    | This option controls the default hash driver that will be used to hash
    | passwords for your application. By default, the bcrypt algorithm is
    | used; however, you remain free to modify this option if you wish.
    |
    | Supported: "bcrypt", "argon", "argon2id"
    |
    */

    'driver' => env('HASH_DRIVER') ?: 'bcrypt',

    /*
    |--------------------------------------------------------------------------
    | Bcrypt Options
    |--------------------------------------------------------------------------
    |
    | Here you may specify the configuration options for when the bcrypt
    | algorithm is used. The cost will be used to generate the hash with
    | the algorithm, and the higher the value the longer it will take.
    |
    */

    'bcrypt' => [
        'rounds' => ((int) env('BCRYPT_ROUNDS')) > 0 ? (int) env('BCRYPT_ROUNDS') : 12,
        'verify' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Argon Options
    |--------------------------------------------------------------------------
    |
    | Here you may specify the configuration options for when the Argon
    | algorithm is used. You may configure the memory, threads, and time
    | cost that will be used to generate the hashes for your application.
    |
    */

    'argon' => [
        'memory' => 65536,
        'threads' => 1,
        'time' => 4,
        'verify' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Rehash On Login
    |--------------------------------------------------------------------------
    |
    | When set to true, this option instructs the authentication system to
    | automatically rehash the user's password if the cost configuration
    | has changed since the user's password was originally hashed.
    |
    */

    'rehash_on_login' => false,

];
