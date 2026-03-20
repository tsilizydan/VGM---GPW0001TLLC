<?php

declare(strict_types=1);

namespace Core;

/**
 * File-based cache with TTL support.
 *
 * Usage:
 *   Cache::remember('key', 300, fn() => expensiveQuery());
 *   Cache::forget('key');
 *   Cache::flush();
 */
class Cache
{
    private static string $dir = '';

    // ── Bootstrap ────────────────────────────────────────────────

    public static function init(string $cacheDir): void
    {
        self::$dir = rtrim($cacheDir, '/\\');
        if (!is_dir(self::$dir)) {
            mkdir(self::$dir, 0775, true);
        }
    }

    // ── Core API ─────────────────────────────────────────────────

    /**
     * Get a cached value or compute + store it.
     *
     * @template T
     * @param callable(): T $callback
     * @return T
     */
    public static function remember(string $key, int $ttlSeconds, callable $callback): mixed
    {
        $file = self::path($key);

        if (is_file($file)) {
            $raw = file_get_contents($file);
            if ($raw !== false) {
                $entry = unserialize($raw);
                if (is_array($entry) && isset($entry['expires'], $entry['data'])) {
                    if ($entry['expires'] === 0 || $entry['expires'] > time()) {
                        return $entry['data'];
                    }
                }
            }
            @unlink($file); // expired
        }

        $data = $callback();
        self::set($key, $data, $ttlSeconds);
        return $data;
    }

    /**
     * Store a value in the cache.
     */
    public static function set(string $key, mixed $value, int $ttlSeconds = 300): void
    {
        $entry = [
            'expires' => $ttlSeconds === 0 ? 0 : time() + $ttlSeconds,
            'data'    => $value,
        ];
        file_put_contents(self::path($key), serialize($entry), LOCK_EX);
    }

    /**
     * Get a cached value, or null if missing/expired.
     */
    public static function get(string $key): mixed
    {
        $file = self::path($key);
        if (!is_file($file)) return null;

        $raw   = file_get_contents($file);
        if ($raw === false) return null;

        $entry = unserialize($raw);
        if (!is_array($entry) || !isset($entry['expires'], $entry['data'])) return null;
        if ($entry['expires'] !== 0 && $entry['expires'] <= time()) {
            @unlink($file);
            return null;
        }
        return $entry['data'];
    }

    /**
     * Delete a specific cache entry.
     */
    public static function forget(string $key): void
    {
        $file = self::path($key);
        if (is_file($file)) @unlink($file);
    }

    /**
     * Delete all cache entries (or entries matching a tag/prefix).
     */
    public static function flush(string $prefix = ''): void
    {
        if (!is_dir(self::$dir)) return;

        foreach (glob(self::$dir . '/*.cache') ?: [] as $file) {
            if ($prefix === '' || str_starts_with(basename($file), self::sanitize($prefix))) {
                @unlink($file);
            }
        }
    }

    /**
     * Flush all product-related cache entries (call after product mutations).
     */
    public static function flushProducts(): void
    {
        self::flush('product_');
    }

    /**
     * Flush all recipe/bundle cache entries.
     */
    public static function flushContent(): void
    {
        self::flush('recipe_');
        self::flush('bundle_');
    }

    // ── Internal ─────────────────────────────────────────────────

    private static function path(string $key): string
    {
        return self::$dir . '/' . self::sanitize($key) . '.cache';
    }

    private static function sanitize(string $key): string
    {
        // Convert key to a safe filename using sha256 prefix for uniqueness
        $safe = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $key);
        // Append short hash to prevent collisions on long keys
        return substr($safe, 0, 64) . '_' . substr(hash('sha256', $key), 0, 8);
    }
}
