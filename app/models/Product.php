<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;
use Core\Lang;
use Core\Cache;

/**
 * Product model.
 *
 * Handles products, product_translations, product_images, product_variations.
 */
class Product extends Model
{
    // ── Read — Lists ────────────────────────────────────────────

    /**
     * Paginated list of products with primary image + locale-aware name.
     *
     * @param array{
     *   category?:string,
     *   status?:string,
     *   featured?:bool,
     *   search?:string,
     *   sort?:string,
     *   page?:int,
     *   per_page?:int
     * } $filters
     *
     * @return array{data:list<array>, total:int, pages:int, page:int}
     */
    public static function paginate(array $filters = []): array
    {
        $locale  = Lang::getLocale();
        $perPage = max(1, (int) ($filters['per_page'] ?? 12));
        $page    = max(1, (int) ($filters['page'] ?? 1));
        $offset  = ($page - 1) * $perPage;

        // Cache for 60s (shop listing — price/stock may change frequently)
        $cacheKey = 'product_paginate_' . $locale . '_' . md5(serialize($filters));
        return Cache::remember($cacheKey, 60, function () use ($locale, $perPage, $page, $offset, $filters) {

        [$where, $bindings] = self::buildWhere($filters, $locale);

        $sortMap = [
            'name_asc'   => 'pt.name ASC',
            'price_asc'  => 'p.price ASC',
            'price_desc' => 'p.price DESC',
            'newest'     => 'p.created_at DESC',
        ];
        $sort = $sortMap[$filters['sort'] ?? ''] ?? 'p.sort_order ASC, p.id ASC';

        $total = (int) (self::queryOne(
            "SELECT COUNT(*) AS cnt FROM products p
             LEFT JOIN category_translations ct_cat
                    ON ct_cat.category_id = p.category_id AND ct_cat.locale = 'fr'
             LEFT JOIN product_translations pt
                    ON pt.product_id = p.id AND pt.locale = ?
             $where",
            [$locale, ...$bindings]
        )['cnt'] ?? 0);

        $data = self::query(
            "SELECT p.id, p.slug, p.price, p.compare_price, p.stock, p.status,
                    p.featured, p.category_id, p.sku, p.created_at,
                    COALESCE(pt.name, pt_fr.name, p.slug) AS name,
                    COALESCE(pt.description, pt_fr.description)  AS description,
                    img.path AS primary_image, img.alt AS image_alt,
                    COALESCE(ct.name, ct_fr.name, '') AS category_name, ct_cat.name AS category_slug_name,
                    cats.slug AS category_slug
             FROM products p
             LEFT JOIN product_translations pt
                    ON pt.product_id = p.id AND pt.locale = ?
             LEFT JOIN product_translations pt_fr
                    ON pt_fr.product_id = p.id AND pt_fr.locale = 'fr'
             LEFT JOIN product_images img
                    ON img.product_id = p.id AND img.is_primary = 1
             LEFT JOIN categories cats
                    ON cats.id = p.category_id
             LEFT JOIN category_translations ct
                    ON ct.category_id = p.category_id AND ct.locale = ?
             LEFT JOIN category_translations ct_fr
                    ON ct_fr.category_id = p.category_id AND ct_fr.locale = 'fr'
             LEFT JOIN category_translations ct_cat
                    ON ct_cat.category_id = p.category_id AND ct_cat.locale = 'fr'
             $where
             ORDER BY $sort
             LIMIT ? OFFSET ?",
            [$locale, $locale, ...$bindings, $perPage, $offset]
        );

        return [
            'data'  => $data,
            'total' => $total,
            'pages' => (int) ceil($total / $perPage),
            'page'  => $page,
        ];
        }); // end Cache::remember
    }

    /**
     * Simple list (no pagination) for admin selects and sitemaps.
     *
     * @return list<array<string,mixed>>
     */
    public static function all(array $filters = []): array
    {
        $locale = Lang::getLocale();
        [$where, $bindings] = self::buildWhere($filters, $locale);

        return self::query(
            "SELECT p.id, p.slug, p.price, p.stock, p.status, p.featured, p.category_id,
                    COALESCE(pt.name, pt_fr.name, p.slug) AS name,
                    img.path AS primary_image,
                    cats.slug AS category_slug
             FROM products p
             LEFT JOIN product_translations pt
                    ON pt.product_id = p.id AND pt.locale = ?
             LEFT JOIN product_translations pt_fr
                    ON pt_fr.product_id = p.id AND pt_fr.locale = 'fr'
             LEFT JOIN product_images img
                    ON img.product_id = p.id AND img.is_primary = 1
             LEFT JOIN categories cats ON cats.id = p.category_id
             LEFT JOIN category_translations ct_cat
                    ON ct_cat.category_id = p.category_id AND ct_cat.locale = 'fr'
             $where
             ORDER BY p.sort_order, p.id",
            [$locale, ...$bindings]
        );
    }

    // ── Read — Single product ───────────────────────────────────

    /**
     * Full product by slug: translation, all images, variations.
     *
     * @return array<string,mixed>|null
     */
    public static function findBySlug(string $slug): ?array
    {
        $locale   = Lang::getLocale();
        $cacheKey = 'product_slug_' . $locale . '_' . $slug;

        return Cache::remember($cacheKey, 300, function () use ($locale, $slug) {

        $product = self::queryOne(
            "SELECT p.*,
                    COALESCE(pt.name, pt_fr.name, p.slug)               AS name,
                    COALESCE(pt.description, pt_fr.description)         AS description,
                    COALESCE(pt.story, pt_fr.story)                     AS story,
                    COALESCE(pt.origin_region, pt_fr.origin_region)     AS origin_region,
                    COALESCE(pt.farmer_name, pt_fr.farmer_name)         AS farmer_name,
                    COALESCE(pt.farmer_quote, pt_fr.farmer_quote)       AS farmer_quote,
                    COALESCE(pt.farmer_story, pt_fr.farmer_story)       AS farmer_story,
                    COALESCE(pt.harvest_process, pt_fr.harvest_process) AS harvest_process,
                    COALESCE(pt.harvest_season, pt_fr.harvest_season)   AS harvest_season,
                    COALESCE(pt.certifications, pt_fr.certifications)   AS certifications,
                    COALESCE(ct.name, ct_fr.name, '')                   AS category_name,
                    cats.slug                                           AS category_slug
             FROM products p
             LEFT JOIN product_translations pt
                    ON pt.product_id = p.id AND pt.locale = ?
             LEFT JOIN product_translations pt_fr
                    ON pt_fr.product_id = p.id AND pt_fr.locale = 'fr'
             LEFT JOIN categories cats ON cats.id = p.category_id
             LEFT JOIN category_translations ct
                    ON ct.category_id = p.category_id AND ct.locale = ?
             LEFT JOIN category_translations ct_fr
                    ON ct_fr.category_id = p.category_id AND ct_fr.locale = 'fr'
             WHERE p.slug = ? AND p.status = 'active'",
            [$locale, $locale, $slug]
        );

        if (!$product) return null;

        $product['images']       = self::imagesFor((int) $product['id']);
        $product['variations']   = self::variationsFor((int) $product['id']);
        $product['story_media']  = self::storyMediaFor((int) $product['id']);

        return $product;
        }); // end Cache::remember
    }

    /**
     * Full product by ID (for admin — ignores status).
     *
     * @return array<string,mixed>|null
     */
    public static function find(int $id): ?array
    {
        $product = self::queryOne('SELECT * FROM products WHERE id = ?', [$id]);
        if (!$product) return null;

        $product['translations'] = self::translationsFor($id);
        $product['images']       = self::imagesFor($id);
        $product['variations']   = self::variationsFor($id);

        return $product;
    }

    /**
     * Related products (same category, excluding current).
     *
     * @return list<array<string,mixed>>
     */
    public static function related(int $productId, int $limit = 4): array
    {
        $locale   = Lang::getLocale();
        $cacheKey = 'product_related_' . $locale . '_' . $productId;

        return Cache::remember($cacheKey, 600, function () use ($locale, $productId, $limit) {

        $current = self::queryOne('SELECT category_id FROM products WHERE id = ?', [$productId]);
        if (!$current) return [];

        return self::query(
            "SELECT p.id, p.slug, p.price, p.compare_price, p.stock,
                    COALESCE(pt.name, pt_fr.name, p.slug) AS name,
                    img.path AS primary_image
             FROM products p
             LEFT JOIN product_translations pt
                    ON pt.product_id = p.id AND pt.locale = ?
             LEFT JOIN product_translations pt_fr
                    ON pt_fr.product_id = p.id AND pt_fr.locale = 'fr'
             LEFT JOIN product_images img
                    ON img.product_id = p.id AND img.is_primary = 1
             WHERE p.category_id = ? AND p.id <> ? AND p.status = 'active'
             ORDER BY RAND()
             LIMIT ?",
            [$locale, $current['category_id'], $productId, $limit]
        );
        }); // end Cache::remember
    }

    /**
     * "Customers who bought this also bought…"
     * Uses order_items self-join: products that appear in the same orders.
     *
     * @return list<array<string,mixed>>
     */
    public static function alsoBoaught(int $productId, int $limit = 6): array
    {
        $locale   = Lang::getLocale();
        $cacheKey = 'product_alsobought_' . $locale . '_' . $productId;

        return Cache::remember($cacheKey, 900, function () use ($locale, $productId, $limit) {
        return self::query(
            "SELECT p.id, p.slug, p.price, p.compare_price, p.stock,
                    COALESCE(pt.name, pt_fr.name, p.slug) AS name,
                    img.path AS primary_image,
                    COUNT(*) AS co_purchase_count
             FROM order_items oi_main
             JOIN order_items oi_co
                    ON oi_co.order_id = oi_main.order_id
                   AND oi_co.product_id <> oi_main.product_id
             JOIN products p ON p.id = oi_co.product_id AND p.status = 'active'
             LEFT JOIN product_translations pt
                    ON pt.product_id = p.id AND pt.locale = ?
             LEFT JOIN product_translations pt_fr
                    ON pt_fr.product_id = p.id AND pt_fr.locale = 'fr'
             LEFT JOIN product_images img
                    ON img.product_id = p.id AND img.is_primary = 1
             WHERE oi_main.product_id = ?
             GROUP BY p.id, p.slug, p.price, p.compare_price, p.stock, pt.name, pt_fr.name, img.path
             ORDER BY co_purchase_count DESC
             LIMIT ?",
            [$locale, $productId, $limit]
        );
        }); // end Cache::remember
    }

    /**
     * Upsell suggestions for cart (based on cart product IDs, avoids duplicates).
     *
     * @param list<int> $cartProductIds  IDs already in cart
     * @return list<array<string,mixed>>
     */
    public static function upsellsFor(array $cartProductIds, int $limit = 4): array
    {
        if (empty($cartProductIds)) {
            // Fallback: return featured products
            return self::query(
                "SELECT p.id, p.slug, p.price,
                        COALESCE(pt.name, p.slug) AS name,
                        img.path AS primary_image
                 FROM products p
                 LEFT JOIN product_translations pt ON pt.product_id = p.id AND pt.locale = 'fr'
                 LEFT JOIN product_images img ON img.product_id = p.id AND img.is_primary = 1
                 WHERE p.status = 'active' AND p.featured = 1
                 ORDER BY RAND() LIMIT ?",
                [$limit]
            );
        }

        $locale      = Lang::getLocale();
        $placeholders = implode(',', array_fill(0, count($cartProductIds), '?'));
        $excludeList  = implode(',', array_fill(0, count($cartProductIds), '?'));

        // Co-purchase weighted, excluding items already in cart
        $rows = self::query(
            "SELECT p.id, p.slug, p.price,
                    COALESCE(pt.name, pt_fr.name, p.slug) AS name,
                    img.path AS primary_image,
                    COUNT(*) AS score
             FROM order_items oi_main
             JOIN order_items oi_co
                    ON oi_co.order_id = oi_main.order_id
                   AND oi_co.product_id NOT IN ({$excludeList})
             JOIN products p ON p.id = oi_co.product_id AND p.status = 'active'
             LEFT JOIN product_translations pt ON pt.product_id = p.id AND pt.locale = ?
             LEFT JOIN product_translations pt_fr ON pt_fr.product_id = p.id AND pt_fr.locale = 'fr'
             LEFT JOIN product_images img ON img.product_id = p.id AND img.is_primary = 1
             WHERE oi_main.product_id IN ({$placeholders})
             GROUP BY p.id, p.slug, p.price, pt.name, pt_fr.name, img.path
             ORDER BY score DESC
             LIMIT ?",
            [...$cartProductIds, $locale, ...$cartProductIds, $limit]
        );

        // Top-up with featured if not enough co-purchase data
        if (count($rows) < $limit) {
            $found   = array_column($rows, 'id');
            $exclude = array_merge($cartProductIds, $found);
            $ep      = implode(',', array_fill(0, count($exclude), '?'));
            $extra   = self::query(
                "SELECT p.id, p.slug, p.price,
                        COALESCE(pt.name, p.slug) AS name,
                        img.path AS primary_image
                 FROM products p
                 LEFT JOIN product_translations pt ON pt.product_id = p.id AND pt.locale = 'fr'
                 LEFT JOIN product_images img ON img.product_id = p.id AND img.is_primary = 1
                 WHERE p.status = 'active' AND p.id NOT IN ({$ep})
                 ORDER BY p.featured DESC, RAND()
                 LIMIT ?",
                [...$exclude, $limit - count($rows)]
            );
            $rows = array_merge($rows, $extra);
        }

        return $rows;
    }

    // ── Write ───────────────────────────────────────────────────

    /**
     * @param array<string,mixed> $data  product data (slug, category_id, price, …)
     */
    public static function create(array $data): int
    {
        self::execute(
            'INSERT INTO products
                 (slug, category_id, sku, price, compare_price, stock, status, featured, sort_order)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $data['slug'],
                $data['category_id'] ?: null,
                $data['sku'] ?: null,
                $data['price'],
                $data['compare_price'] ?: null,
                (int) ($data['stock'] ?? 0),
                $data['status'] ?? 'draft',
                (int) ($data['featured'] ?? 0),
                (int) ($data['sort_order'] ?? 0),
            ]
        );
        Cache::flushProducts();
        return (int) self::lastInsertId();
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function update(int $id, array $data): void
    {
        self::execute(
            'UPDATE products SET
                slug=?, category_id=?, sku=?, price=?, compare_price=?,
                stock=?, status=?, featured=?, sort_order=?, updated_at=NOW()
             WHERE id=?',
            [
                $data['slug'],
                $data['category_id'] ?: null,
                $data['sku'] ?: null,
                $data['price'],
                $data['compare_price'] ?: null,
                (int) ($data['stock'] ?? 0),
                $data['status'] ?? 'draft',
                (int) ($data['featured'] ?? 0),
                (int) ($data['sort_order'] ?? 0),
                $id,
            ]
        );
        Cache::flushProducts();
    }

    public static function delete(int $id): void
    {
        // Delete images from filesystem first
        $images = self::imagesFor($id);
        foreach ($images as $img) {
            $fullPath = BASE_PATH . '/public/' . ltrim($img['path'], '/');
            if (is_file($fullPath)) {
                @unlink($fullPath);
            }
        }
        self::execute('DELETE FROM products WHERE id = ?', [$id]);
    }

    // ── Translations ────────────────────────────────────────────

    /**
     * Upsert a locale translation.
     */
    public static function saveTranslation(int $productId, string $locale, array $data): void
    {
        self::execute(
            'INSERT INTO product_translations
                 (product_id, locale, name, description, story,
                  origin_region, farmer_name, farmer_quote, farmer_story,
                  harvest_process, harvest_season, certifications)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
                 name=VALUES(name), description=VALUES(description), story=VALUES(story),
                 origin_region=VALUES(origin_region), farmer_name=VALUES(farmer_name),
                 farmer_quote=VALUES(farmer_quote), farmer_story=VALUES(farmer_story),
                 harvest_process=VALUES(harvest_process), harvest_season=VALUES(harvest_season),
                 certifications=VALUES(certifications)',
            [
                $productId, $locale,
                $data['name']            ?? '',
                $data['description']     ?? '',
                $data['story']           ?? '',
                $data['origin_region']   ?? '',
                $data['farmer_name']     ?? '',
                $data['farmer_quote']    ?? '',
                $data['farmer_story']    ?? '',
                $data['harvest_process'] ?? '',
                $data['harvest_season']  ?? '',
                $data['certifications']  ?? '',
            ]
        );
    }

    /**
     * @return array<string, array<string,string>>  locale → {name, description, story, …storytelling fields}
     */
    public static function translationsFor(int $productId): array
    {
        $rows = self::query(
            'SELECT locale, name, description, story,
                    origin_region, farmer_name, farmer_quote, farmer_story,
                    harvest_process, harvest_season, certifications
             FROM product_translations WHERE product_id = ?',
            [$productId]
        );
        $result = [];
        foreach ($rows as $row) {
            $result[$row['locale']] = [
                'name'            => $row['name']            ?? '',
                'description'     => $row['description']    ?? '',
                'story'           => $row['story']           ?? '',
                'origin_region'   => $row['origin_region']  ?? '',
                'farmer_name'     => $row['farmer_name']    ?? '',
                'farmer_quote'    => $row['farmer_quote']   ?? '',
                'farmer_story'    => $row['farmer_story']   ?? '',
                'harvest_process' => $row['harvest_process']?? '',
                'harvest_season'  => $row['harvest_season'] ?? '',
                'certifications'  => $row['certifications'] ?? '',
            ];
        }
        return $result;
    }

    /**
     * Media per story section for a product.
     * @return array<string, list<array<string,mixed>>>  section → images
     */
    public static function storyMediaFor(int $productId): array
    {
        $rows = self::query(
            'SELECT * FROM product_story_media WHERE product_id = ? ORDER BY section, sort_order',
            [$productId]
        );
        $result = [];
        foreach ($rows as $row) {
            $result[$row['section']][] = $row;
        }
        return $result;
    }

    // ── Images ──────────────────────────────────────────────────

    /**
     * @return list<array<string,mixed>>
     */
    public static function imagesFor(int $productId): array
    {
        return self::query(
            'SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order ASC',
            [$productId]
        );
    }

    /**
     * Save an uploaded image record.
     */
    public static function addImage(int $productId, string $path, string $alt = '', bool $isPrimary = false): int
    {
        // If this is marked primary, clear existing primaries
        if ($isPrimary) {
            self::execute(
                'UPDATE product_images SET is_primary = 0 WHERE product_id = ?',
                [$productId]
            );
        }

        // Compute next sort_order
        $maxOrder = (int) (self::queryOne(
            'SELECT COALESCE(MAX(sort_order), 0) AS m FROM product_images WHERE product_id = ?',
            [$productId]
        )['m'] ?? 0);

        self::execute(
            'INSERT INTO product_images (product_id, path, alt, sort_order, is_primary)
             VALUES (?, ?, ?, ?, ?)',
            [$productId, $path, $alt, $maxOrder + 1, (int) $isPrimary]
        );
        return (int) self::lastInsertId();
    }

    /**
     * Set a single image as primary for a product.
     */
    public static function setPrimaryImage(int $productId, int $imageId): void
    {
        self::execute('UPDATE product_images SET is_primary = 0 WHERE product_id = ?', [$productId]);
        self::execute('UPDATE product_images SET is_primary = 1 WHERE id = ? AND product_id = ?', [$imageId, $productId]);
    }

    public static function deleteImage(int $imageId): void
    {
        $img = self::queryOne('SELECT * FROM product_images WHERE id = ?', [$imageId]);
        if ($img) {
            $fullPath = BASE_PATH . '/public/' . ltrim($img['path'], '/');
            if (is_file($fullPath)) {
                @unlink($fullPath);
            }
            self::execute('DELETE FROM product_images WHERE id = ?', [$imageId]);
        }
    }

    // ── Variations ──────────────────────────────────────────────

    /**
     * @return list<array<string,mixed>>
     */
    public static function variationsFor(int $productId): array
    {
        $rows = self::query(
            'SELECT * FROM product_variations WHERE product_id = ? ORDER BY sort_order, id',
            [$productId]
        );
        foreach ($rows as &$row) {
            $row['attributes'] = json_decode($row['attributes'] ?? '{}', true) ?: [];
        }
        return $rows;
    }

    /**
     * Replace all variations for a product.
     *
     * @param list<array{sku?:string, price?:float, stock:int, attributes:array}> $variations
     */
    public static function syncVariations(int $productId, array $variations): void
    {
        self::execute('DELETE FROM product_variations WHERE product_id = ?', [$productId]);

        foreach ($variations as $i => $v) {
            if (empty($v['attributes'])) continue;
            self::execute(
                'INSERT INTO product_variations (product_id, sku, price, stock, attributes, sort_order)
                 VALUES (?, ?, ?, ?, ?, ?)',
                [
                    $productId,
                    $v['sku'] ?: null,
                    $v['price'] ?: null,
                    (int) ($v['stock'] ?? 0),
                    json_encode($v['attributes'], JSON_UNESCAPED_UNICODE),
                    $i,
                ]
            );
        }
    }

    // ── Private helpers ─────────────────────────────────────────

    /**
     * Build the WHERE clause and bindings from $filters.
     * Note: the first binding should already have $locale prepended by the caller.
     *
     * @return array{string, list<mixed>}
     */
    private static function buildWhere(array $filters, string $locale): array
    {
        $conditions = [];
        $bindings   = [];

        if (!empty($filters['status'])) {
            $conditions[] = 'p.status = ?';
            $bindings[]   = $filters['status'];
        } else {
            // Default: exclude archived in admin; frontend always shows active
            if (($filters['admin'] ?? false)) {
                // No status filter in admin unless specified
            } else {
                $conditions[] = "p.status = 'active'";
            }
        }

        if (!empty($filters['category'])) {
            $conditions[] = 'cats.slug = ?';
            $bindings[]   = $filters['category'];
        }

        if ($filters['featured'] ?? false) {
            $conditions[] = 'p.featured = 1';
        }

        if (!empty($filters['search'])) {
            $conditions[] = '(COALESCE(pt.name, pt_fr.name, p.slug) LIKE ? OR p.sku LIKE ?)';
            $term         = '%' . $filters['search'] . '%';
            $bindings[]   = $term;
            $bindings[]   = $term;
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
        return [$where, $bindings];
    }

    /**
     * Generate a slug from a product name.
     */
    public static function slugify(string $str): string
    {
        $str = mb_strtolower($str, 'UTF-8');
        $str = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $str) ?: $str;
        $str = preg_replace('/[^a-z0-9]+/', '-', $str) ?? $str;
        return trim($str, '-');
    }
}
