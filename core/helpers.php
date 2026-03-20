<?php

declare(strict_types=1);

/**
 * Global helper functions.
 * Loaded automatically during the application bootstrap.
 */

// -----------------------------------------------------------------------
// Environment
// -----------------------------------------------------------------------

function env(string $key, mixed $default = null): mixed
{
    $value = $_ENV[$key] ?? getenv($key);

    if ($value === false || $value === null || $value === '') {
        return $default;
    }

    return match (strtolower((string) $value)) {
        'true',  '(true)'  => true,
        'false', '(false)' => false,
        'null',  '(null)'  => null,
        'empty', '(empty)' => '',
        default             => $value,
    };
}

// -----------------------------------------------------------------------
// Paths
// -----------------------------------------------------------------------

function base_path(string $path = ''): string
{
    return BASE_PATH . ($path !== '' ? DIRECTORY_SEPARATOR . ltrim($path, '/\\') : '');
}

function view_path(string $path = ''): string
{
    return base_path('app/views') . ($path !== '' ? DIRECTORY_SEPARATOR . $path : '');
}

function storage_path(string $path = ''): string
{
    return base_path('storage') . ($path !== '' ? DIRECTORY_SEPARATOR . $path : '');
}

// -----------------------------------------------------------------------
// URLs & Assets
// -----------------------------------------------------------------------

function asset(string $path): string
{
    if (class_exists(\Core\Assets::class)) {
        return \Core\Assets::url($path);
    }
    $base = rtrim(env('APP_URL', ''), '/');
    return $base . '/assets/' . ltrim($path, '/');
}

function url(string $path = ''): string
{
    $base = rtrim(env('APP_URL', ''), '/');
    return $base . '/' . ltrim($path, '/');
}

// -----------------------------------------------------------------------
// Security / Output
// -----------------------------------------------------------------------

/**
 * HTML-escape a value for safe output.
 */
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// -----------------------------------------------------------------------
// CSRF helpers
// -----------------------------------------------------------------------

/**
 * Output the hidden CSRF token input field.
 * Use inside every HTML form: <?= csrf_field() ?>
 */
function csrf_field(): string
{
    return \Core\Csrf::field();
}

/**
 * Return the raw CSRF token string.
 */
function csrf_token(): string
{
    return \Core\Csrf::token();
}

// -----------------------------------------------------------------------
// Authentication helpers
// -----------------------------------------------------------------------

/**
 * Return the authenticated user array, or null.
 *
 * @return array<string, mixed>|null
 */
function auth(): ?array
{
    return \Core\Auth::user();
}

// -----------------------------------------------------------------------
// Form helpers
// -----------------------------------------------------------------------

/**
 * Retrieve an old (previously submitted) form value from the session.
 * Used to repopulate form fields after a validation failure.
 */
function old(string $key, string $default = ''): string
{
    $old = \Core\Session::getFlash('_old_input');
    if (is_array($old) && isset($old[$key])) {
        return e((string) $old[$key]);
    }
    return e($default);
}

// -----------------------------------------------------------------------
// Debug
// -----------------------------------------------------------------------

function dd(mixed ...$values): never
{
    foreach ($values as $value) {
        echo '<pre>' . e(print_r($value, true)) . '</pre>';
    }
    exit;
}

// -----------------------------------------------------------------------
// Internationalisation (i18n)
// -----------------------------------------------------------------------

/**
 * Translate a key using the active locale.
 *
 * @param string               $key      Dot-notation key, e.g. 'nav.shop'
 * @param array<string,string> $replace  :placeholder => replacement pairs
 */
function t(string $key, array $replace = []): string
{
    return \Core\Lang::get($key, $replace);
}

/**
 * Return the currently active locale code ('fr', 'en', or 'es').
 */
function current_locale(): string
{
    return \Core\Lang::getLocale();
}

/**
 * Generate a locale-prefixed URL.
 *
 * @param string      $path    The path after the locale, e.g. 'shop'
 * @param string|null $locale  Override locale; uses current locale if null
 *
 * Examples:
 *   locale_url('shop')       → /fr/shop
 *   locale_url('shop', 'en') → /en/shop
 *   locale_url('')           → /fr/
 */
function locale_url(string $path = '', ?string $locale = null): string
{
    $locale = $locale ?? current_locale();
    $base   = rtrim(env('APP_URL', ''), '/');
    $path   = ltrim($path, '/');
    return $base . '/' . $locale . ($path !== '' ? '/' . $path : '/');
}

/**
 * Generate a locale-prefixed URL for the same path in a different locale.
 * Useful in the language switcher to generate /en/current-page.
 *
 * @param string $locale Target locale ('fr', 'en', 'es')
 * @param string $path   Override the path; defaults to current REQUEST_URI
 */
function switch_locale_url(string $locale, string $path = ''): string
{
    if ($path === '') {
        $path = $_SERVER['REQUEST_URI'] ?? '/';
        // Strip existing locale prefix if present (already stripped by Application)
        // REQUEST_URI at this point is already /{path} without locale prefix
    }
    return locale_url($path, $locale);
}
