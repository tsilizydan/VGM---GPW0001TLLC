<?php

declare(strict_types=1);

/**
 * General application configuration.
 *
 * @return array<string, mixed>
 */
return [
    'name'     => env('APP_NAME', 'Vanilla Group Madagascar'),
    'url'      => env('APP_URL', 'https://vanillagroup-madagascar.tsilizy.com'),
    'env'      => env('APP_ENV', 'production'),
    'debug'    => filter_var(env('APP_DEBUG', false), FILTER_VALIDATE_BOOLEAN),
    'timezone' => env('APP_TIMEZONE', 'Indian/Antananarivo'),
];
