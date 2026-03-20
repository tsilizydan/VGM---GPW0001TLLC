<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;
use Core\Lang;

/**
 * Bundle model.
 *
 * A bundle groups 2+ products at a discounted price.
 * Supports flat-€ discount (discount) or percent discount (discount_pct).
 */
class Bundle extends Model
{
    // ── Read ─────────────────────────────────────────────────────

    /**
     * Active bundles that include a given product (for product detail page).
     *
     * @return list<array<string,mixed>>
     */
    public static function forProduct(int $productId): array
    {
        $locale = Lang::getLocale();
        $rows   = self::query(
            "SELECT b.id, b.slug, b.discount, b.discount_pct,
                    COALESCE(bt.name, bt_fr.name, b.slug) AS name,
                    COALESCE(bt.description, bt_fr.description) AS description
             FROM bundles b
             JOIN bundle_items bi ON bi.bundle_id = b.id
             LEFT JOIN bundle_translations bt
                    ON bt.bundle_id = b.id AND bt.locale = ?
             LEFT JOIN bundle_translations bt_fr
                    ON bt_fr.bundle_id = b.id AND bt_fr.locale = 'fr'
             WHERE bi.product_id = ? AND b.status = 'active'
             ORDER BY b.sort_order",
            [$locale, $productId]
        );

        foreach ($rows as &$row) {
            $row['items'] = self::itemsFor((int)$row['id']);
            $row['total'] = array_sum(array_map(fn($i) => (float)$i['price'] * (int)$i['qty'], $row['items']));
            $row['price'] = $row['discount'] > 0
                ? max(0, $row['total'] - (float)$row['discount'])
                : max(0, $row['total'] * (1 - (float)$row['discount_pct'] / 100));
            $row['savings'] = $row['total'] - $row['price'];
        }
        unset($row);

        return $rows;
    }

    /**
     * All bundles (admin list).
     *
     * @return list<array<string,mixed>>
     */
    public static function all(): array
    {
        $locale = Lang::getLocale();
        return self::query(
            "SELECT b.id, b.slug, b.discount, b.discount_pct, b.status, b.sort_order,
                    COALESCE(bt.name, bt_fr.name, b.slug) AS name
             FROM bundles b
             LEFT JOIN bundle_translations bt
                    ON bt.bundle_id = b.id AND bt.locale = ?
             LEFT JOIN bundle_translations bt_fr
                    ON bt_fr.bundle_id = b.id AND bt_fr.locale = 'fr'
             ORDER BY b.sort_order, b.id",
            [$locale]
        );
    }

    /**
     * Single bundle by ID (admin edit).
     *
     * @return array<string,mixed>|null
     */
    public static function find(int $id): ?array
    {
        $bundle = self::queryOne('SELECT * FROM bundles WHERE id = ?', [$id]);
        if (!$bundle) return null;

        $bundle['translations'] = self::translationsFor($id);
        $bundle['items']        = self::itemsFor($id);
        return $bundle;
    }

    // ── Write ─────────────────────────────────────────────────────

    public static function create(array $data): int
    {
        self::execute(
            'INSERT INTO bundles (slug, discount, discount_pct, status, sort_order) VALUES (?,?,?,?,?)',
            [$data['slug'], $data['discount'] ?? 0, $data['discount_pct'] ?? 0, $data['status'] ?? 'draft', $data['sort_order'] ?? 0]
        );
        return (int)self::lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        self::execute(
            'UPDATE bundles SET slug=?, discount=?, discount_pct=?, status=?, sort_order=? WHERE id=?',
            [$data['slug'], $data['discount'] ?? 0, $data['discount_pct'] ?? 0, $data['status'] ?? 'draft', $data['sort_order'] ?? 0, $id]
        );
    }

    public static function delete(int $id): void
    {
        self::execute('DELETE FROM bundles WHERE id=?', [$id]);
    }

    public static function saveTranslation(int $bundleId, string $locale, array $data): void
    {
        self::execute(
            'INSERT INTO bundle_translations (bundle_id, locale, name, description) VALUES (?,?,?,?)
             ON DUPLICATE KEY UPDATE name=VALUES(name), description=VALUES(description)',
            [$bundleId, $locale, $data['name'] ?? '', $data['description'] ?? '']
        );
    }

    /**
     * Replace all items for a bundle.
     *
     * @param list<array{product_id:int, qty:int}> $items
     */
    public static function syncItems(int $bundleId, array $items): void
    {
        self::execute('DELETE FROM bundle_items WHERE bundle_id=?', [$bundleId]);
        foreach ($items as $item) {
            if (empty($item['product_id'])) continue;
            self::execute(
                'INSERT INTO bundle_items (bundle_id, product_id, qty) VALUES (?,?,?)',
                [$bundleId, (int)$item['product_id'], max(1, (int)($item['qty'] ?? 1))]
            );
        }
    }

    // ── Helpers ───────────────────────────────────────────────────

    /** @return list<array<string,mixed>> */
    public static function itemsFor(int $bundleId): array
    {
        $locale = Lang::getLocale();
        return self::query(
            "SELECT bi.qty, p.id AS product_id, p.slug, p.price,
                    COALESCE(pt.name, pt_fr.name, p.slug) AS name,
                    img.path AS image
             FROM bundle_items bi
             JOIN products p ON p.id = bi.product_id
             LEFT JOIN product_translations pt
                    ON pt.product_id = p.id AND pt.locale = ?
             LEFT JOIN product_translations pt_fr
                    ON pt_fr.product_id = p.id AND pt_fr.locale = 'fr'
             LEFT JOIN product_images img
                    ON img.product_id = p.id AND img.is_primary = 1
             WHERE bi.bundle_id = ?
             ORDER BY bi.id",
            [$locale, $bundleId]
        );
    }

    /** @return array<string, array<string,string>> */
    private static function translationsFor(int $bundleId): array
    {
        $rows   = self::query('SELECT * FROM bundle_translations WHERE bundle_id=?', [$bundleId]);
        $result = [];
        foreach ($rows as $row) {
            $result[$row['locale']] = ['name' => $row['name'], 'description' => $row['description'] ?? ''];
        }
        return $result;
    }
}
