<?php

declare(strict_types=1);

namespace Core;

/**
 * Lang — DB-driven internationalisation engine.
 *
 * Usage:
 *   Lang::setLocale('en');
 *   Lang::get('nav.shop');             // → 'Shop'
 *   Lang::get('alert.added_to_cart', ['name' => 'Vanilla']); // → 'Vanilla added to cart.'
 *
 * Supported locales: fr (default), en, es
 * Fallback chain:    requested locale → fr → raw key
 */
class Lang
{
    /** In-memory cache per request: locale → [key → value] */
    private static array $cache = [];

    /** Currently active locale */
    private static string $locale = 'fr';

    /** Valid locale codes */
    private static array $supported = ['fr', 'en', 'es'];

    // -----------------------------------------------------------------------
    // Locale management
    // -----------------------------------------------------------------------

    public static function setLocale(string $locale): void
    {
        if (in_array($locale, self::$supported, true)) {
            self::$locale = $locale;
        }
    }

    public static function getLocale(): string
    {
        return self::$locale;
    }

    /** @return list<string> */
    public static function supportedLocales(): array
    {
        return self::$supported;
    }

    public static function isSupported(string $locale): bool
    {
        return in_array($locale, self::$supported, true);
    }

    // -----------------------------------------------------------------------
    // Translation retrieval
    // -----------------------------------------------------------------------

    /**
     * Get a translated string.
     *
     * @param array<string, string> $replace  Replaces :placeholders in the value
     */
    public static function get(string $key, array $replace = []): string
    {
        $locale = self::$locale;
        $value  = self::lookupKey($locale, $key)
               ?? self::lookupKey('fr', $key)
               ?? $key;

        // Replace :placeholder → replacement value
        foreach ($replace as $placeholder => $val) {
            $value = str_replace(':' . $placeholder, $val, $value);
        }

        return $value;
    }

    /**
     * Return all translations for a locale (used by admin editor).
     *
     * @return array<string, string>  key → value
     */
    public static function all(string $locale): array
    {
        self::loadLocale($locale);
        return self::$cache[$locale] ?? [];
    }

    // -----------------------------------------------------------------------
    // Internal helpers
    // -----------------------------------------------------------------------

    private static function lookupKey(string $locale, string $key): ?string
    {
        self::loadLocale($locale);
        return self::$cache[$locale][$key] ?? null;
    }

    /** Load all translations for a locale into the in-memory cache. */
    private static function loadLocale(string $locale): void
    {
        if (isset(self::$cache[$locale])) {
            return; // Already loaded this request
        }

        self::$cache[$locale] = [];

        // Guard: need PDO available (may not be during boot)
        if (!class_exists(\Core\Model::class)) {
            return;
        }

        try {
            $rows = \Core\Model::rawQuery(
                'SELECT `key`, value FROM translations WHERE locale = ?',
                [$locale]
            );
            foreach ($rows as $row) {
                self::$cache[$locale][$row['key']] = $row['value'];
            }
        } catch (\Throwable) {
            // DB not yet set up — silently degrade to key fallback
        }
    }

    /**
     * Clear the in-memory cache (useful after admin edits or in tests).
     */
    public static function clearCache(?string $locale = null): void
    {
        if ($locale === null) {
            self::$cache = [];
        } else {
            unset(self::$cache[$locale]);
        }
    }
}
