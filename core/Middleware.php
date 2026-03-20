<?php

declare(strict_types=1);

namespace Core;

/**
 * Middleware guards.
 *
 * Call inside controller constructors or action methods.
 *
 * Usage:
 *   Middleware::requireAuth();            // redirects to /login if not logged in
 *   Middleware::requireAdmin();           // redirects to /login if not admin
 *   Middleware::requireCsrf($request);    // 419 if POST token missing/invalid
 */
class Middleware
{
    // ── Authentication ────────────────────────────────────────────

    /**
     * Require an authenticated user.
     * Redirects to the login page (preserving intended URL) if not logged in.
     */
    public static function requireAuth(string $loginPath = 'login'): void
    {
        if (!Auth::check()) {
            $intended = urlencode($_SERVER['REQUEST_URI'] ?? '/');
            $locale   = function_exists('current_locale') ? current_locale() : 'fr';
            $base     = rtrim(env('APP_URL', ''), '/');
            Response::redirect("{$base}/{$locale}/{$loginPath}?intended={$intended}", 302);
        }
    }

    // ── Role-based access ─────────────────────────────────────────

    /**
     * Require the logged-in user to have the 'admin' role.
     * Aborts with 403 if authenticated but not admin.
     * Redirects to login if not authenticated at all.
     */
    public static function requireAdmin(): void
    {
        self::requireAuth();

        if (!Auth::hasRole('admin')) {
            Response::abort(403, 'Accès réservé aux administrateurs.');
        }
    }

    /**
     * Require a specific role.
     */
    public static function requireRole(string $role): void
    {
        self::requireAuth();

        if (!Auth::hasRole($role)) {
            Response::abort(403, "Accès réservé aux utilisateurs « {$role} ».");
        }
    }

    // ── CSRF ──────────────────────────────────────────────────────

    /**
     * Validate CSRF token for the current POST request.
     * Returns 419 on failure (renders a styled error page if available).
     */
    public static function requireCsrf(Request $request): void
    {
        if (!in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return;
        }

        $submitted = $_POST['_token'] ?? $request->header('X-CSRF-TOKEN') ?? '';
        $expected  = Session::get('_csrf_token', '');

        if (
            $submitted === ''
            || $expected === ''
            || !hash_equals((string)$expected, (string)$submitted)
        ) {
            http_response_code(419);
            $view = defined('BASE_PATH')
                ? BASE_PATH . '/app/views/errors/419.php'
                : null;

            if ($view && is_file($view)) {
                require $view;
                exit;
            }

            // Fallback
            header('Content-Type: text/html; charset=UTF-8');
            echo '<h1>419 — Token de sécurité expiré</h1>';
            echo '<p>Votre session a expiré. <a href="javascript:history.back()">Retour</a>.</p>';
            exit;
        }

        // Rotate token on every successful POST
        Csrf::regenerate();
    }

    // ── Rate limiting (simple in-session) ────────────────────────

    /**
     * Basic rate limiter using session counters.
     * Aborts with 429 if the limit is exceeded within the time window.
     *
     * @param string $key     Unique action key (e.g. 'login_attempt')
     * @param int    $limit   Max attempts
     * @param int    $windowS Time window in seconds
     */
    public static function rateLimit(string $key, int $limit = 5, int $windowS = 60): void
    {
        $sessionKey = '_rl_' . $key;
        $data       = Session::get($sessionKey, ['count' => 0, 'reset_at' => time() + $windowS]);

        if (!is_array($data)) {
            $data = ['count' => 0, 'reset_at' => time() + $windowS];
        }

        if (time() > $data['reset_at']) {
            $data = ['count' => 0, 'reset_at' => time() + $windowS];
        }

        $data['count']++;
        Session::set($sessionKey, $data);

        if ($data['count'] > $limit) {
            http_response_code(429);
            $retryAfter = max(0, $data['reset_at'] - time());
            header("Retry-After: {$retryAfter}");
            echo "<h1>429 — Trop de requêtes</h1><p>Réessayez dans {$retryAfter} secondes.</p>";
            exit;
        }
    }
}
