<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;
use Core\Lang;

/**
 * Recipe model.
 *
 * Recipes are content pieces (e.g. "Crème brûlée à la vanille") linked to products.
 * They drive organic SEO + soft product upsells.
 */
class Recipe extends Model
{
    // ── Read ─────────────────────────────────────────────────────

    /**
     * Published recipes that use a given product.
     *
     * @return list<array<string,mixed>>
     */
    public static function forProduct(int $productId, int $limit = 3): array
    {
        $locale = Lang::getLocale();
        return self::query(
            "SELECT r.id, r.slug, r.cover_image, r.prep_time, r.cook_time, r.servings, r.difficulty,
                    COALESCE(rt.title, rt_fr.title, r.slug) AS title,
                    COALESCE(rt.intro, rt_fr.intro)         AS intro
             FROM recipes r
             JOIN recipe_products rp ON rp.recipe_id = r.id
             LEFT JOIN recipe_translations rt
                    ON rt.recipe_id = r.id AND rt.locale = ?
             LEFT JOIN recipe_translations rt_fr
                    ON rt_fr.recipe_id = r.id AND rt_fr.locale = 'fr'
             WHERE rp.product_id = ? AND r.status = 'published'
             ORDER BY r.sort_order
             LIMIT ?",
            [$locale, $productId, $limit]
        );
    }

    /**
     * Paginated public recipe listing.
     *
     * @return array{data:list<array>, total:int, pages:int, page:int}
     */
    public static function paginate(array $filters = []): array
    {
        $locale  = Lang::getLocale();
        $perPage = max(1, (int)($filters['per_page'] ?? 12));
        $page    = max(1, (int)($filters['page'] ?? 1));
        $offset  = ($page - 1) * $perPage;

        $total = (int)(self::queryOne(
            "SELECT COUNT(*) AS cnt FROM recipes WHERE status = 'published'"
        )['cnt'] ?? 0);

        $data = self::query(
            "SELECT r.id, r.slug, r.cover_image, r.prep_time, r.cook_time, r.servings, r.difficulty,
                    COALESCE(rt.title, rt_fr.title, r.slug) AS title,
                    COALESCE(rt.intro, rt_fr.intro)         AS intro
             FROM recipes r
             LEFT JOIN recipe_translations rt
                    ON rt.recipe_id = r.id AND rt.locale = ?
             LEFT JOIN recipe_translations rt_fr
                    ON rt_fr.recipe_id = r.id AND rt_fr.locale = 'fr'
             WHERE r.status = 'published'
             ORDER BY r.sort_order, r.id
             LIMIT ? OFFSET ?",
            [$locale, $perPage, $offset]
        );

        return [
            'data'  => $data,
            'total' => $total,
            'pages' => (int)ceil($total / $perPage),
            'page'  => $page,
        ];
    }

    /**
     * Single recipe by slug (with full content + linked products).
     *
     * @return array<string,mixed>|null
     */
    public static function findBySlug(string $slug): ?array
    {
        $locale = Lang::getLocale();
        $recipe = self::queryOne(
            "SELECT r.*,
                    COALESCE(rt.title, rt_fr.title, r.slug)         AS title,
                    COALESCE(rt.intro, rt_fr.intro)                 AS intro,
                    COALESCE(rt.ingredients, rt_fr.ingredients)     AS ingredients,
                    COALESCE(rt.steps, rt_fr.steps)                 AS steps
             FROM recipes r
             LEFT JOIN recipe_translations rt
                    ON rt.recipe_id = r.id AND rt.locale = ?
             LEFT JOIN recipe_translations rt_fr
                    ON rt_fr.recipe_id = r.id AND rt_fr.locale = 'fr'
             WHERE r.slug = ? AND r.status = 'published'",
            [$locale, $slug]
        );
        if (!$recipe) return null;

        $recipe['products'] = self::linkedProducts((int)$recipe['id']);
        return $recipe;
    }

    /**
     * Admin: find by ID (any status, full translations).
     *
     * @return array<string,mixed>|null
     */
    public static function find(int $id): ?array
    {
        $recipe = self::queryOne('SELECT * FROM recipes WHERE id=?', [$id]);
        if (!$recipe) return null;

        $recipe['translations'] = self::translationsFor($id);
        $recipe['products']     = self::linkedProducts($id);
        return $recipe;
    }

    /** @return list<array<string,mixed>> Admin list */
    public static function all(): array
    {
        $locale = Lang::getLocale();
        return self::query(
            "SELECT r.id, r.slug, r.status, r.difficulty, r.created_at,
                    COALESCE(rt.title, rt_fr.title, r.slug) AS title
             FROM recipes r
             LEFT JOIN recipe_translations rt ON rt.recipe_id = r.id AND rt.locale = ?
             LEFT JOIN recipe_translations rt_fr ON rt_fr.recipe_id = r.id AND rt_fr.locale = 'fr'
             ORDER BY r.sort_order, r.id",
            [$locale]
        );
    }

    // ── Write ─────────────────────────────────────────────────────

    public static function create(array $data): int
    {
        self::execute(
            'INSERT INTO recipes (slug, cover_image, prep_time, cook_time, servings, difficulty, status, sort_order)
             VALUES (?,?,?,?,?,?,?,?)',
            [$data['slug'], $data['cover_image'] ?? null, $data['prep_time'] ?? null,
             $data['cook_time'] ?? null, $data['servings'] ?? null,
             $data['difficulty'] ?? 'easy', $data['status'] ?? 'draft', $data['sort_order'] ?? 0]
        );
        return (int)self::lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        self::execute(
            'UPDATE recipes SET slug=?,cover_image=?,prep_time=?,cook_time=?,servings=?,difficulty=?,status=?,sort_order=? WHERE id=?',
            [$data['slug'], $data['cover_image'] ?? null, $data['prep_time'] ?? null,
             $data['cook_time'] ?? null, $data['servings'] ?? null,
             $data['difficulty'] ?? 'easy', $data['status'] ?? 'draft', $data['sort_order'] ?? 0, $id]
        );
    }

    public static function delete(int $id): void
    {
        self::execute('DELETE FROM recipes WHERE id=?', [$id]);
    }

    public static function saveTranslation(int $recipeId, string $locale, array $data): void
    {
        self::execute(
            'INSERT INTO recipe_translations (recipe_id, locale, title, intro, ingredients, steps)
             VALUES (?,?,?,?,?,?)
             ON DUPLICATE KEY UPDATE title=VALUES(title), intro=VALUES(intro),
                 ingredients=VALUES(ingredients), steps=VALUES(steps)',
            [$recipeId, $locale, $data['title'] ?? '', $data['intro'] ?? '',
             $data['ingredients'] ?? '', $data['steps'] ?? '']
        );
    }

    /**
     * Sync linked products for a recipe.
     *
     * @param list<int> $productIds
     */
    public static function syncProducts(int $recipeId, array $productIds): void
    {
        self::execute('DELETE FROM recipe_products WHERE recipe_id=?', [$recipeId]);
        foreach (array_unique(array_filter($productIds)) as $pid) {
            self::execute(
                'INSERT IGNORE INTO recipe_products (recipe_id, product_id) VALUES (?,?)',
                [$recipeId, (int)$pid]
            );
        }
    }

    // ── Helpers ────────────────────────────────────────────────────

    /** @return list<array<string,mixed>> */
    public static function linkedProducts(int $recipeId): array
    {
        $locale = Lang::getLocale();
        return self::query(
            "SELECT p.id, p.slug, p.price,
                    COALESCE(pt.name, pt_fr.name, p.slug) AS name,
                    img.path AS image
             FROM recipe_products rp
             JOIN products p ON p.id = rp.product_id
             LEFT JOIN product_translations pt ON pt.product_id = p.id AND pt.locale = ?
             LEFT JOIN product_translations pt_fr ON pt_fr.product_id = p.id AND pt_fr.locale = 'fr'
             LEFT JOIN product_images img ON img.product_id = p.id AND img.is_primary = 1
             WHERE rp.recipe_id = ? AND p.status = 'active'",
            [$locale, $recipeId]
        );
    }

    /** @return array<string, array<string,string>> */
    private static function translationsFor(int $recipeId): array
    {
        $rows   = self::query('SELECT * FROM recipe_translations WHERE recipe_id=?', [$recipeId]);
        $result = [];
        foreach ($rows as $row) {
            $result[$row['locale']] = [
                'title'       => $row['title'] ?? '',
                'intro'       => $row['intro'] ?? '',
                'ingredients' => $row['ingredients'] ?? '',
                'steps'       => $row['steps'] ?? '',
            ];
        }
        return $result;
    }

    public static function slugify(string $str): string
    {
        $str = mb_strtolower($str, 'UTF-8');
        $str = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $str) ?: $str;
        return trim(preg_replace('/[^a-z0-9]+/', '-', $str) ?? $str, '-');
    }

    public static function difficultyLabel(string $d): string
    {
        return match($d) { 'easy' => 'Facile', 'hard' => 'Difficile', default => 'Moyen' };
    }
}
