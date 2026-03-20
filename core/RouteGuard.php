<?php

declare(strict_types=1);

namespace Core;

/**
 * RouteGuard — Controlled, language-aware redirection system.
 *
 * Provides the 5 core functions for safe homepage and 404 handling:
 *
 *   RouteGuard::isHomepageRequest()      → true if requesting / or /{locale}/
 *   RouteGuard::isValidRoute($uri)       → true if the URI matches a registered route
 *   RouteGuard::redirectToHomepage($lang)→ safe redirect to /{lang}/ with no loops
 *   RouteGuard::handleInvalidRoute()     → redirect to /{lang}/not-found (clean 404 URL)
 *   RouteGuard::detectLanguage()         → best locale from session → cookie → Accept-Language → 'fr'
 */
class RouteGuard
{
    /** Supported locales — kept in sync with Lang::supportedLocales() */
    private const SUPPORTED = ['fr', 'en', 'es'];

    /** Paths that bypass locale & redirect logic entirely */
    private const BYPASS_PATHS = ['/sitemap.xml', '/robots.txt', '/favicon.ico'];

    // ── Public API ────────────────────────────────────────────────

    /**
     * Returns true if the raw REQUEST_URI is a homepage request:
     *   /          → true
     *   /fr/       → true (after locale strip, Router sees /)
     *   /en        → true
     */
    public static function isHomepageRequest(): bool
    {
        $uri = self::rawUri();
        // root
        if ($uri === '/') {
            return true;
        }
        // locale root: /fr or /fr/
        foreach (self::SUPPORTED as $locale) {
            if ($uri === "/{$locale}" || $uri === "/{$locale}/") {
                return true;
            }
        }
        return false;
    }

    /**
     * Check whether the given locale-stripped URI matches any registered GET route.
     *
     * Pass in the Router (obtained via DI or static registry) and the stripped URI.
     * This is used by the Router's own 404 handler — you normally don't call this directly.
     *
     * @param  list<array{regex: string}> $routes  GET routes from the router
     */
    public static function isValidRoute(array $routes, string $uri): bool
    {
        foreach ($routes as $route) {
            if (preg_match($route['regex'], $uri)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Redirect to the localized homepage /{lang}/.
     *
     * Guards against redirect loops: if we're already on /{lang}/, serve a 404
     * instead of looping forever.
     *
     * @param string|null $lang  Locale to redirect to. Null → detectLanguage()
     */
    public static function redirectToHomepage(?string $lang = null): never
    {
        $lang = $lang ?? self::detectLanguage();
        $base = rtrim(env('APP_URL', ''), '/');

        // Loop guard — if we're already at the homepage, do not redirect again
        $current = self::rawUri();
        if ($current === "/{$lang}" || $current === "/{$lang}/") {
            // Already there — just render 404 to prevent infinite loop
            Response::abort(404);
        }

        header("Location: {$base}/{$lang}/", true, 302);
        exit;
    }

    /**
     * Handle an invalid (unmatched) route.
     *
     * Redirects to /{lang}/not-found so the URL changes to something meaningful.
     * Falls back to inline 404 page if we're already on the not-found URL (loop guard).
     */
    public static function handleInvalidRoute(): never
    {
        $lang    = self::detectLanguage();
        $base    = rtrim(env('APP_URL', ''), '/');
        $current = self::rawUri();

        // If we're already on the not-found page, render inline to stop any loop
        if (
            $current === "/{$lang}/not-found"
            || $current === '/not-found'
        ) {
            Response::abort(404);
        }

        // Redirect to the friendly 404 URL
        header("Location: {$base}/{$lang}/not-found", true, 302);
        exit;
    }

    /**
     * Detect the best locale for the current user.
     *
     * Priority:
     *   1. Session: 'locale' key (previously resolved by Application)
     *   2. Cookie:  'locale' key
     *   3. HTTP Accept-Language header
     *   4. Default: 'fr'
     */
    public static function detectLanguage(): string
    {
        // 1. Session
        if (session_status() === PHP_SESSION_ACTIVE) {
            $saved = $_SESSION['_s']['locale'] ?? null;
            if ($saved && in_array($saved, self::SUPPORTED, true)) {
                return $saved;
            }
        }

        // 2. Cookie
        $cookie = $_COOKIE['locale'] ?? null;
        if ($cookie && in_array($cookie, self::SUPPORTED, true)) {
            return $cookie;
        }

        // 3. Accept-Language header
        return self::detectFromHeader();
    }

    /**
     * True if the URI is a system bypass path (sitemap, robots, favicon).
     */
    public static function isBypassPath(string $uri): bool
    {
        return in_array($uri, self::BYPASS_PATHS, true);
    }

    // ── Private helpers ───────────────────────────────────────────

    /**
     * Return the raw decoded URI path (without query string).
     */
    private static function rawUri(): string
    {
        $raw = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
        return '/' . ltrim(rawurldecode($raw), '/');
    }

    /**
     * Parse Accept-Language header and return the best supported locale.
     */
    private static function detectFromHeader(): string
    {
        $header = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
        if (!$header) {
            return 'fr';
        }

        // Parse weighted preferences: fr-CH,fr;q=0.9,en;q=0.7
        preg_match_all(
            '/([a-z]{2})(?:-[a-zA-Z]{2})?(?:;q=([0-9.]+))?/i',
            $header,
            $m,
            PREG_SET_ORDER
        );

        // Sort by quality weight descending
        usort($m, fn($a, $b) => (float)($b[2] ?? 1.0) <=> (float)($a[2] ?? 1.0));

        foreach ($m as $match) {
            $tag = strtolower($match[1]);
            if (in_array($tag, self::SUPPORTED, true)) {
                return $tag;
            }
        }

        return 'fr';
    }
}
