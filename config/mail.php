<?php

declare(strict_types=1);

/**
 * Mail configuration.
 * Values are loaded from .env — configure SMTP for reliable delivery.
 *
 * @return array<string, mixed>
 */
return [
    'driver'     => env('MAIL_DRIVER', 'mail'),   // 'mail' (PHP mail()) or 'smtp'
    'host'       => env('MAIL_HOST', 'localhost'),
    'port'       => (int) env('MAIL_PORT', 587),
    'username'   => env('MAIL_USERNAME', ''),
    'password'   => env('MAIL_PASSWORD', ''),
    'encryption' => env('MAIL_ENCRYPTION', 'tls'), // 'tls' | 'ssl' | ''
    'from_email' => env('MAIL_FROM', 'noreply@vanillagroupe.mg'),
    'from_name'  => env('MAIL_FROM_NAME', 'Vanilla Groupe Madagascar'),
];
