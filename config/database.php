<?php

declare(strict_types=1);

/**
 * Database configuration.
 * Values are read from the .env file via the env() helper.
 *
 * @return array<string, string|int>
 */
return [
    'host'     => env('DB_HOST', 'localhost'),
    'port'     => (int) env('DB_PORT', 3306),
    'dbname'   => env('DB_NAME', 'tsilscpx_vanilla_db'),
    'username' => env('DB_USER', 'tsilscpx_chibi_admin'),
    'password' => env('DB_PASS', '9@UPN~I@O]Dw'),
    'charset'  => env('DB_CHARSET', 'utf8mb4'),
];
