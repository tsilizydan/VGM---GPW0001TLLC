<?php

declare(strict_types=1);

namespace Core;

/**
 * Asset pipeline: CSS/JS minification + cache-busting fingerprints.
 *
 * Usage in views:
 *   <link rel="stylesheet" href="<?= Assets::css('css/app.css') ?>">
 *   <script src="<?= Assets::js('js/app.js') ?>"></script>
 *
 * In production (APP_ENV=production), files are minified and cached.
 * In dev, original files are served with a mtime query string.
 */
class Assets
{
    private static string $publicDir  = '';
    private static string $cacheDir   = '';
    private static string $baseUrl    = '';
    private static bool   $production = false;

    public static function init(string $publicDir, string $cacheDir, string $baseUrl, bool $production = false): void
    {
        self::$publicDir  = rtrim($publicDir, '/\\');
        self::$cacheDir   = rtrim($cacheDir, '/\\');
        self::$baseUrl    = rtrim($baseUrl, '/');
        self::$production = $production;

        if ($production && !is_dir(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0775, true);
        }
    }

    // ── Public API ────────────────────────────────────────────────

    /**
     * Return a versioned URL for a CSS file.
     * In production: minify + cache + fingerprint.
     * In dev: add ?mtime query string.
     */
    public static function css(string $path): string
    {
        return self::process($path, 'css');
    }

    /**
     * Return a versioned URL for a JS file.
     */
    public static function js(string $path): string
    {
        return self::process($path, 'js');
    }

    /**
     * Return a simple versioned URL for any asset (images, fonts…).
     * Doc root = project root, so web URL must include /public/assets/
     */
    public static function url(string $path): string
    {
        $path = ltrim($path, '/');
        $absPath = self::$publicDir . '/assets/' . $path;
        $mtime   = is_file($absPath) ? filemtime($absPath) : time();
        return self::$baseUrl . '/public/assets/' . $path . '?v=' . $mtime;
    }

    // ── Bundle multiple files ─────────────────────────────────────

    /**
     * Minify + concatenate multiple CSS files into one cached bundle.
     * Returns the URL to the bundled file.
     *
     * @param list<string> $paths  Paths relative to public dir
     */
    public static function cssBundle(array $paths, string $bundleName = 'bundle'): string
    {
        return self::bundle($paths, 'css', $bundleName);
    }

    /**
     * Minify + concatenate multiple JS files into one cached bundle.
     *
     * @param list<string> $paths  Paths relative to public dir
     */
    public static function jsBundle(array $paths, string $bundleName = 'bundle'): string
    {
        return self::bundle($paths, 'js', $bundleName);
    }

    // ── Minification ─────────────────────────────────────────────

    /**
     * Minify CSS: strip comments, collapse whitespace.
     */
    public static function minifyCSS(string $css): string
    {
        // Remove block comments /* … */
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css) ?? $css;
        // Remove line breaks, tabs, multiple spaces
        $css = str_replace(["\r\n", "\r", "\n", "\t"], ' ', $css);
        // Collapse multiple spaces
        $css = preg_replace('/\s{2,}/', ' ', $css) ?? $css;
        // Remove space around selectors, braces, colons, semicolons
        $css = preg_replace('/\s*([{};:,>+~])\s*/', '$1', $css) ?? $css;
        // Remove last semicolons before closing brace
        $css = str_replace(';}', '}', $css);
        return trim($css);
    }

    /**
     * Minify JavaScript: strip single-line comments, collapse whitespace.
     * Note: This is a simple minifier — for production use JShrink is better.
     * Safe for plain JS without complex regex/string edge cases.
     */
    public static function minifyJS(string $js): string
    {
        // Remove single-line comments (// …) — skip URLs (http://)
        $js = preg_replace('/(?<!:|\\\)\/\/[^\n]*/', '', $js) ?? $js;
        // Remove block comments /* … */ (non-greedy)
        $js = preg_replace('/\/\*[\s\S]*?\*\//', '', $js) ?? $js;
        // Collapse blank lines and leading/trailing whitespace per line
        $lines = array_map('trim', explode("\n", $js));
        $lines = array_filter($lines, fn($l) => $l !== '');
        $js    = implode("\n", $lines);
        // Collapse multiple spaces (not inside strings — simplified)
        $js = preg_replace('/[ \t]{2,}/', ' ', $js) ?? $js;
        return trim($js);
    }

    // ── Internal ─────────────────────────────────────────────────

    private static function process(string $path, string $type): string
    {
        $path    = ltrim($path, '/');
        $absPath = self::$publicDir . '/assets/' . $path;

        if (!is_file($absPath)) {
            // File not found — return path anyway (browser will 404)
            return self::$baseUrl . '/public/assets/' . $path;
        }

        if (!self::$production) {
            // Dev: serve original with mtime query string for cache busting
            return self::$baseUrl . '/public/assets/' . $path . '?v=' . filemtime($absPath);
        }

        // Production: minify → write to cache dir → return versioned URL
        $mtime    = filemtime($absPath);
        $cacheKey = $type . '_' . md5($path . $mtime);
        $cacheFile = self::$cacheDir . '/' . $cacheKey . '.' . $type;
        $cacheUrl  = self::$baseUrl . '/public/assets/cache/' . $cacheKey . '.' . $type;

        if (!is_file($cacheFile)) {
            $source = file_get_contents($absPath) ?: '';
            $minified = $type === 'css' ? self::minifyCSS($source) : self::minifyJS($source);
            file_put_contents($cacheFile, $minified, LOCK_EX);
        }

        return $cacheUrl;
    }

    private static function bundle(array $paths, string $type, string $bundleName): string
    {
        if (!self::$production) {
            return self::process($paths[0] ?? '', $type);
        }

        // Compute combined mtime hash
        $mtimes = [];
        foreach ($paths as $p) {
            $f = self::$publicDir . '/assets/' . ltrim($p, '/');
            $mtimes[] = is_file($f) ? filemtime($f) : 0;
        }

        $cacheKey  = $type . '_bundle_' . md5($bundleName . implode('|', $mtimes));
        $cacheFile = self::$cacheDir . '/' . $cacheKey . '.' . $type;
        $cacheUrl  = self::$baseUrl . '/public/assets/cache/' . $cacheKey . '.' . $type;

        if (!is_file($cacheFile)) {
            $combined = '';
            foreach ($paths as $p) {
                $f = self::$publicDir . '/assets/' . ltrim($p, '/');
                if (is_file($f)) {
                    $combined .= file_get_contents($f) . "\n";
                }
            }
            $minified = $type === 'css' ? self::minifyCSS($combined) : self::minifyJS($combined);
            file_put_contents($cacheFile, $minified, LOCK_EX);
        }

        return $cacheUrl;
    }
}
