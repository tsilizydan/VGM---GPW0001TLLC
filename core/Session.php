<?php

declare(strict_types=1);

namespace Core;

/**
 * Session manager.
 *
 * Wraps PHP sessions with a clean API.
 * Call Session::start() once, early in the application bootstrap.
 */
class Session
{
    private static bool $started = false;

    // -----------------------------------------------------------------------
    // Lifecycle
    // -----------------------------------------------------------------------

    /**
     * Start (or resume) the session with secure defaults.
     */
    public static function start(): void
    {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            self::$started = true;
            return;
        }

        // Secure session configuration
        ini_set('session.use_strict_mode',     '1');
        ini_set('session.use_only_cookies',    '1');
        ini_set('session.cookie_httponly',     '1');
        ini_set('session.cookie_samesite',     'Lax');

        // Use HTTPS-only cookies in production
        $isSecure = (env('APP_ENV', 'local') === 'production');
        ini_set('session.cookie_secure', $isSecure ? '1' : '0');

        // Session lifetime in minutes (default 120)
        $lifetime = (int) env('SESSION_LIFETIME', 120) * 60;
        ini_set('session.gc_maxlifetime', (string) $lifetime);
        ini_set('session.cookie_lifetime', '0'); // expire on browser close

        // Store sessions inside project storage to avoid shared-host conflicts
        $savePath = base_path('storage/sessions');
        if (!is_dir($savePath)) {
            mkdir($savePath, 0755, true);
        }
        session_save_path($savePath);

        session_name('vgm_session');
        session_start();

        self::$started = true;
    }

    // -----------------------------------------------------------------------
    // Accessors
    // -----------------------------------------------------------------------

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function all(): array
    {
        return $_SESSION ?? [];
    }

    // -----------------------------------------------------------------------
    // Flash messages (survive exactly one redirect)
    // -----------------------------------------------------------------------

    /**
     * Store a flash value (available until the NEXT get call).
     */
    public static function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    /**
     * Retrieve and immediately remove a flash value.
     */
    public static function getFlash(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }

    public static function hasFlash(string $key): bool
    {
        return isset($_SESSION['_flash'][$key]);
    }

    // -----------------------------------------------------------------------
    // Security
    // -----------------------------------------------------------------------

    /**
     * Regenerate the session ID to prevent session fixation.
     */
    public static function regenerate(bool $deleteOld = true): void
    {
        session_regenerate_id($deleteOld);
    }

    /**
     * Destroy the session completely (logout).
     */
    public static function destroy(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
        self::$started = false;
    }
}
