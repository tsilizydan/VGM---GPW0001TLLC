<?php

declare(strict_types=1);

namespace Core;

/**
 * Application bootstrap.
 *
 * Responsibilities:
 *  1. Load the .env file into $_ENV
 *  2. Register the PSR-4 autoloader (BEFORE anything else)
 *  3. Apply app-level settings (timezone, error reporting)
 *  4. Resolve locale from URL prefix (/fr/, /en/, /es/)
 *  5. Load route definitions
 *  6. Dispatch the request
 *
 * Boot order is CRITICAL on Namecheap/LiteSpeed:
 *   loadEnv()  →  registerAutoloader()  →  configure()  →  new Router()
 *   The autoloader MUST register before configure() because configure()
 *   uses Core\Session, Core\Cache, and Core\Assets which need autoloading.
 */
class Application
{
    private Router $router;

    public function __construct()
    {
        $this->loadEnv();
        $this->registerAutoloader();
        $this->configure();

        $this->router = new Router();
    }

    /**
     * Boot the application and dispatch the current HTTP request.
     */
    public static function run(): void
    {
        $app = new static();
        $app->resolveLocale();
        $app->loadRoutes();
        $app->dispatch();
    }

    // -----------------------------------------------------------------------
    // Private boot steps
    // -----------------------------------------------------------------------

    /**
     * Parse the .env file and populate $_ENV / putenv().
     */
    private function loadEnv(): void
    {
        $envFile = BASE_PATH . DIRECTORY_SEPARATOR . '.env';

        if (!is_file($envFile)) {
            return;
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);

            if (str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value, " \t\n\r\0\x0B\"'");

            if (!array_key_exists($key, $_ENV)) {
                $_ENV[$key] = $value;
                putenv("{$key}={$value}");
            }
        }
    }

    /**
     * Bootstrap the custom PSR-4 autoloader.
     * MUST be called before configure() so Core\Cache, Core\Assets, etc. are available.
     */
    private function registerAutoloader(): void
    {
        require_once BASE_PATH . '/core/Autoloader.php';

        $loader = new Autoloader();
        $loader->addNamespace('Core', BASE_PATH . '/core');
        $loader->addNamespace('App',  BASE_PATH . '/app');
        $loader->register();

        require_once BASE_PATH . '/core/helpers.php';
    }

    /**
     * Apply PHP runtime settings and start the session.
     */
    private function configure(): void
    {
        // Respect FORCE_DEBUG: never downgrade error visibility when it's on
        $forceDebug = defined('FORCE_DEBUG') && FORCE_DEBUG === true;
        $debug      = $forceDebug || filter_var(env('APP_DEBUG', false), FILTER_VALIDATE_BOOLEAN);
        $tz         = env('APP_TIMEZONE', 'UTC');

        error_reporting($debug ? E_ALL : (E_ALL & ~E_DEPRECATED & ~E_STRICT));
        ini_set('display_errors', $debug ? '1' : '0');
        ini_set('log_errors', '1');
        ini_set('error_log', BASE_PATH . '/storage/logs/error.log');

        date_default_timezone_set($tz);

        // Start session (Session::start has its own try/catch + fallback)
        Session::start();

        // File-based cache
        try {
            Cache::init(BASE_PATH . '/storage/cache');
        } catch (\Throwable $e) {
            error_log('[Cache] Init failed: ' . $e->getMessage());
        }

        // Asset pipeline (minify in production)
        try {
            $isProd = env('APP_ENV', 'local') === 'production';
            Assets::init(
                publicDir:  BASE_PATH . '/public',
                cacheDir:   BASE_PATH . '/public/assets/cache',
                baseUrl:    rtrim(env('APP_URL', ''), '/'),
                production: $isProd
            );
        } catch (\Throwable $e) {
            error_log('[Assets] Init failed: ' . $e->getMessage());
        }
    }

    // -----------------------------------------------------------------------
    // Locale resolution
    // -----------------------------------------------------------------------

    /**
     * Detect locale from the URL prefix and strip it from REQUEST_URI.
     */
    private function resolveLocale(): void
    {
        try {
            $supported = Lang::supportedLocales();
            $uri       = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/');
            $uri       = '/' . ltrim($uri, '/');

            // Skip locale logic entirely for system/asset paths
            if (RouteGuard::isBypassPath($uri)) {
                Lang::setLocale('fr');
                return;
            }

            // 1. Valid locale prefix: /fr/shop, /en/, /es ...
            if (preg_match('#^/(' . implode('|', $supported) . ')(/.*)?$#', $uri, $m)) {
                $locale    = $m[1];
                $remainder = ($m[2] ?? '') ?: '/';

                Lang::setLocale($locale);
                Session::set('locale', $locale);

                // Persist in cookie for cross-session memory (1 year)
                if (!headers_sent()) {
                    setcookie('locale', $locale, time() + 31536000, '/', '', false, false);
                }

                // Strip prefix — router sees only the path segment after /{locale}
                $_SERVER['REQUEST_URI'] = $remainder;
                return;
            }

            // 2. Bare root / → detect language and redirect
            $detectedLocale = RouteGuard::detectLanguage();

            if ($uri === '/') {
                $this->redirectToLocale($detectedLocale);
            }

            // 3. Any path without a locale prefix → redirect, preserving the path
            $this->redirectToLocale($detectedLocale, $uri);
        } catch (\Throwable $e) {
            // If locale resolution crashes, default to 'fr' and continue
            error_log('[resolveLocale] Error: ' . $e->getMessage());
            Lang::setLocale('fr');
        }
    }

    /**
     * Issue a 302 redirect to /{locale}{path} and exit.
     */
    private function redirectToLocale(string $locale, string $path = '/'): never
    {
        $base = rtrim(env('APP_URL', ''), '/');
        $path = '/' . ltrim($path, '/');
        header('Location: ' . $base . '/' . $locale . $path, true, 302);
        exit;
    }

    // -----------------------------------------------------------------------
    // Routing
    // -----------------------------------------------------------------------

    /**
     * Include the web route definitions file.
     */
    private function loadRoutes(): void
    {
        $router = $this->router;
        require base_path('routes/web.php');
    }

    /**
     * Create a Request and let the Router dispatch it.
     */
    private function dispatch(): void
    {
        $request = new Request();
        try {
            $this->router->dispatch($request);
        } catch (\Throwable $e) {
            error_log((string) $e);

            // In forced-debug mode, re-throw so index.php renders the raw stack trace
            if (defined('FORCE_DEBUG') && FORCE_DEBUG === true) {
                throw $e;
            }

            $code   = $e->getCode();
            $isHttp = in_array($code, [400, 403, 404, 405, 422, 500], true);
            $status = $isHttp ? $code : 500;
            Response::abort($status);
        }
    }
}
