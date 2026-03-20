<?php

declare(strict_types=1);

namespace Core;

/**
 * Application bootstrap.
 *
 * Responsibilities:
 *  1. Load the .env file into $_ENV
 *  2. Apply app-level settings (timezone, error reporting)
 *  3. Register the PSR-4 autoloader
 *  4. Resolve locale from URL prefix (/fr/, /en/, /es/)
 *  5. Load route definitions
 *  6. Dispatch the request
 */
class Application
{
    private Router $router;

    public function __construct()
    {
        $this->loadEnv();
        $this->configure();
        $this->registerAutoloader();

        $this->router = new Router();
    }

    /**
     * Boot the application and dispatch the current HTTP request.
     */
    public static function run(): void
    {
        $app = new static();
        $app->resolveLocale();   // ← i18n step before routing
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
     * Apply PHP runtime settings and start the session.
     */
    private function configure(): void
    {
        $debug = filter_var(env('APP_DEBUG', false), FILTER_VALIDATE_BOOLEAN);
        $tz    = env('APP_TIMEZONE', 'UTC');

        error_reporting($debug ? E_ALL : 0);
        ini_set('display_errors', $debug ? '1' : '0');
        ini_set('log_errors', '1');
        ini_set('error_log', BASE_PATH . '/storage/logs/error.log');

        date_default_timezone_set($tz);

        require_once BASE_PATH . '/core/Session.php';
        \Core\Session::start();

        // File-based cache
        Cache::init(BASE_PATH . '/storage/cache');

        // Asset pipeline (minify in production)
        $isProd = env('APP_ENV', 'local') === 'production';
        Assets::init(
            publicDir:  BASE_PATH . '/public',
            cacheDir:   BASE_PATH . '/public/assets/cache',
            baseUrl:    rtrim(env('APP_URL', ''), '/'),
            production: $isProd
        );
    }

    /**
     * Bootstrap the custom PSR-4 autoloader.
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

    // -----------------------------------------------------------------------
    // Locale resolution
    // -----------------------------------------------------------------------

    /**
     * Detect locale from the URL prefix and strip it from REQUEST_URI.
     *
     * Patterns handled:
     *   /fr/shop  → locale=fr, PATH_INFO=/shop
     *   /en/      → locale=en, PATH_INFO=/
     *   /         → redirect to /{browser_lang}/
     *   /shop     → (no prefix) → redirect to /{locale}/shop
     */
    private function resolveLocale(): void
    {
        $supported = Lang::supportedLocales(); // ['fr','en','es']
        $uri       = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/');
        $uri       = '/' . ltrim($uri, '/');

        // Strip query string from URI (already done by parse_url above)

        // Bypass locale resolution for system routes (sitemap, robots)
        $bypassPaths = ['/sitemap.xml', '/robots.txt'];
        if (in_array($uri, $bypassPaths, true)) {
            // Don't redirect — let them pass through to the router as-is
            Lang::setLocale('fr');
            return;
        }

        // 1. Recognised locale prefix  e.g. /fr/shop or /en
        if (preg_match('#^/(' . implode('|', $supported) . ')(/.*)?$#', $uri, $m)) {
            $locale    = $m[1];
            $remainder = ($m[2] ?? '') ?: '/';

            Lang::setLocale($locale);
            Session::set('locale', $locale);

            // Let the Router see only the path after the locale prefix
            $_SERVER['REQUEST_URI'] = $remainder;
            return;
        }

        // 2. Bare root /  → redirect to preferred locale
        if ($uri === '/') {
            $saved = Session::get('locale');
            if ($saved && in_array($saved, $supported, true)) {
                $this->redirectToLocale($saved);
            }
            $this->redirectToLocale($this->detectBrowserLocale($supported));
        }

        // 3. Any other path without prefix → redirect preserving path
        $saved = Session::get('locale') ?? 'fr';
        if (!in_array($saved, $supported, true)) {
            $saved = $this->detectBrowserLocale($supported);
        }
        $this->redirectToLocale($saved, $uri);
    }

    /**
     * Detect the best supported locale from the Accept-Language header.
     *
     * @param list<string> $supported
     */
    private function detectBrowserLocale(array $supported): string
    {
        $header = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
        preg_match_all('/([a-z]{2})(?:-[a-z]{2})?/i', $header, $m);
        foreach ($m[1] as $tag) {
            $tag = strtolower($tag);
            if (in_array($tag, $supported, true)) {
                return $tag;
            }
        }
        return 'fr';
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
            $code    = $e->getCode();
            $isHttp  = in_array($code, [400, 403, 404, 405, 422, 500], true);
            $status  = $isHttp ? $code : 500;
            error_log((string) $e);
            Response::abort($status);
        }
    }
}
