<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

/**
 * Translation model — thin DB layer for the translations table.
 */
class Translation extends Model
{
    /**
     * Fetch every key→value pair for a locale.
     *
     * @return array<string, string>
     */
    public static function all(string $locale): array
    {
        $rows = self::query(
            'SELECT `key`, value FROM translations WHERE locale = ? ORDER BY `key`',
            [$locale]
        );

        $out = [];
        foreach ($rows as $row) {
            $out[$row['key']] = $row['value'];
        }
        return $out;
    }

    /**
     * Get a single translation.
     */
    public static function get(string $locale, string $key): ?string
    {
        $row = self::queryOne(
            'SELECT value FROM translations WHERE locale = ? AND `key` = ? LIMIT 1',
            [$locale, $key]
        );
        return $row['value'] ?? null;
    }

    /**
     * Insert or update a single translation.
     */
    public static function upsert(string $locale, string $key, string $value): void
    {
        self::execute(
            'INSERT INTO translations (locale, `key`, value)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE value = VALUES(value)',
            [$locale, $key, $value]
        );
    }

    /**
     * All unique keys that exist for the canonical locale (fr).
     * Used to build the admin editor table rows.
     *
     * @return list<string>
     */
    public static function allKeys(): array
    {
        $rows = self::query(
            "SELECT `key` FROM translations WHERE locale = 'fr' ORDER BY `key`",
            []
        );
        return array_column($rows, 'key');
    }

    /**
     * Return a pivot table: key → [fr => ..., en => ..., es => ...]
     * Used by the admin editor.
     *
     * @return array<string, array<string, string>>
     */
    public static function pivot(): array
    {
        $rows = self::query(
            'SELECT locale, `key`, value FROM translations ORDER BY `key`, locale',
            []
        );

        $pivot = [];
        foreach ($rows as $row) {
            $pivot[$row['key']][$row['locale']] = $row['value'];
        }
        return $pivot;
    }
}
