<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;
use Core\Lang;

/**
 * Category model — handles the categories + category_translations tables.
 */
class Category extends Model
{
    // ── Read ────────────────────────────────────────────────────

    /**
     * All active categories with translation for the current locale.
     *
     * @return list<array<string,mixed>>
     */
    public static function all(): array
    {
        $locale = Lang::getLocale();

        return self::query(
            "SELECT c.id, c.slug, c.sort_order,
                    COALESCE(ct.name, ct_fr.name, c.slug) AS name,
                    COALESCE(ct.description, ct_fr.description) AS description
             FROM categories c
             LEFT JOIN category_translations ct
                    ON ct.category_id = c.id AND ct.locale = ?
             LEFT JOIN category_translations ct_fr
                    ON ct_fr.category_id = c.id AND ct_fr.locale = 'fr'
             ORDER BY c.sort_order, c.id",
            [$locale]
        );
    }

    /**
     * Find a single category by ID (with all locale translations).
     *
     * @return array<string,mixed>|null
     */
    public static function find(int $id): ?array
    {
        $cat = self::queryOne('SELECT * FROM categories WHERE id = ?', [$id]);
        if (!$cat) return null;

        $cat['translations'] = self::translations($id);
        return $cat;
    }

    /**
     * Find a category by slug (with current-locale name).
     *
     * @return array<string,mixed>|null
     */
    public static function findBySlug(string $slug): ?array
    {
        $locale = Lang::getLocale();
        return self::queryOne(
            "SELECT c.id, c.slug, c.sort_order,
                    COALESCE(ct.name, ct_fr.name, c.slug) AS name
             FROM categories c
             LEFT JOIN category_translations ct
                    ON ct.category_id = c.id AND ct.locale = ?
             LEFT JOIN category_translations ct_fr
                    ON ct_fr.category_id = c.id AND ct_fr.locale = 'fr'
             WHERE c.slug = ?",
            [$locale, $slug]
        );
    }

    /**
     * All translations for a category (keyed by locale).
     *
     * @return array<string, array<string,string>>  locale → {name, description}
     */
    public static function translations(int $categoryId): array
    {
        $rows = self::query(
            'SELECT locale, name, description FROM category_translations WHERE category_id = ?',
            [$categoryId]
        );
        $result = [];
        foreach ($rows as $row) {
            $result[$row['locale']] = ['name' => $row['name'], 'description' => $row['description'] ?? ''];
        }
        return $result;
    }

    // ── Write ───────────────────────────────────────────────────

    /**
     * @param array<string,mixed> $data  slug, sort_order, parent_id(optional)
     */
    public static function create(array $data): int
    {
        self::execute(
            'INSERT INTO categories (slug, parent_id, sort_order) VALUES (?, ?, ?)',
            [$data['slug'], $data['parent_id'] ?? null, $data['sort_order'] ?? 0]
        );
        return (int) self::lastInsertId();
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function update(int $id, array $data): void
    {
        self::execute(
            'UPDATE categories SET slug=?, parent_id=?, sort_order=? WHERE id=?',
            [$data['slug'], $data['parent_id'] ?? null, $data['sort_order'] ?? 0, $id]
        );
    }

    /**
     * Upsert a single locale translation for a category.
     */
    public static function saveTranslation(int $categoryId, string $locale, string $name, string $description = ''): void
    {
        self::execute(
            'INSERT INTO category_translations (category_id, locale, name, description)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE name=VALUES(name), description=VALUES(description)',
            [$categoryId, $locale, $name, $description]
        );
    }

    public static function delete(int $id): void
    {
        self::execute('DELETE FROM categories WHERE id = ?', [$id]);
    }

    // ── Helpers ─────────────────────────────────────────────────

    /**
     * Generate a URL-safe slug from a string.
     */
    public static function slugify(string $str): string
    {
        $str = mb_strtolower($str, 'UTF-8');
        $str = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $str) ?: $str;
        $str = preg_replace('/[^a-z0-9]+/', '-', $str) ?? $str;
        return trim($str, '-');
    }
}
