<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

/**
 * Order model — creates and retrieves orders + items.
 */
class Order extends Model
{
    // ── Read ────────────────────────────────────────────────────

    /**
     * @return array<string,mixed>|null
     */
    public static function find(int $id): ?array
    {
        $order = self::queryOne('SELECT * FROM orders WHERE id = ?', [$id]);
        if (!$order) return null;
        $order['items'] = self::itemsFor((int) $order['id']);
        return $order;
    }

    /**
     * @return array<string,mixed>|null
     */
    public static function findByRef(string $ref): ?array
    {
        $order = self::queryOne('SELECT * FROM orders WHERE reference = ?', [$ref]);
        if (!$order) return null;
        $order['items'] = self::itemsFor((int) $order['id']);
        return $order;
    }

    /**
     * @return list<array<string,mixed>>
     */
    public static function itemsFor(int $orderId): array
    {
        return self::query(
            'SELECT oi.*, p.slug AS product_slug
             FROM order_items oi
             LEFT JOIN products p ON p.id = oi.product_id
             WHERE oi.order_id = ?
             ORDER BY oi.id',
            [$orderId]
        );
    }

    // ── Write ───────────────────────────────────────────────────

    /**
     * Create a new order and return [id, reference].
     *
     * @param array<string,mixed> $data
     * @return array{id:int, reference:string}
     */
    public static function create(array $data): array
    {
        $ref = self::generateRef();

        self::execute(
            'INSERT INTO orders (
                reference, user_id,
                billing_name, billing_email, billing_phone,
                billing_address, billing_city, billing_zip, billing_country,
                shipping_same,
                shipping_name, shipping_address, shipping_city, shipping_zip, shipping_country,
                shipping_method, shipping_label, shipping_cost,
                coupon_code, discount, subtotal, total,
                notes, ip, status
             ) VALUES (
                ?, ?,
                ?, ?, ?,
                ?, ?, ?, ?,
                ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?, ?
             )',
            [
                $ref,
                $data['user_id'] ?? null,

                $data['billing_name'],
                $data['billing_email'],
                $data['billing_phone'] ?? null,
                $data['billing_address'],
                $data['billing_city'],
                $data['billing_zip'],
                $data['billing_country'] ?? 'France',

                (int) ($data['shipping_same'] ?? 1),

                $data['shipping_name']    ?? null,
                $data['shipping_address'] ?? null,
                $data['shipping_city']    ?? null,
                $data['shipping_zip']     ?? null,
                $data['shipping_country'] ?? null,

                $data['shipping_method'] ?? 'standard',
                $data['shipping_label']  ?? 'Livraison standard',
                (float) ($data['shipping_cost'] ?? 0),

                $data['coupon_code'] ?? null,
                (float) ($data['discount'] ?? 0),
                (float) ($data['subtotal'] ?? 0),
                (float) ($data['total'] ?? 0),

                $data['notes'] ?? null,
                $_SERVER['REMOTE_ADDR'] ?? null,
                'pending',
            ]
        );

        $id = (int) self::lastInsertId();
        return ['id' => $id, 'reference' => $ref];
    }

    /**
     * Bulk-insert order items from the cart.
     *
     * @param list<array{id:string, name:string, price:float, qty:int, sku?:string}> $cartItems
     */
    public static function addItems(int $orderId, array $cartItems): void
    {
        foreach ($cartItems as $item) {
            // Extract numeric product_id from composite id (e.g. "12_0" or "12")
            $parts     = explode('_', (string) ($item['id'] ?? ''), 2);
            $productId = (int) $parts[0];
            $varIdx    = isset($parts[1]) ? (int) $parts[1] : null;
            $qty       = max(1, (int) ($item['qty'] ?? 1));
            $price     = (float) ($item['price'] ?? 0);

            self::execute(
                'INSERT INTO order_items (order_id, product_id, name, sku, price, qty, subtotal)
                 VALUES (?, ?, ?, ?, ?, ?, ?)',
                [
                    $orderId,
                    $productId ?: null,
                    $item['name'] ?? 'Produit',
                    $item['sku'] ?? null,
                    $price,
                    $qty,
                    round($price * $qty, 2),
                ]
            );
        }
    }

    public static function updateStatus(int $id, string $status): void
    {
        self::execute('UPDATE orders SET status = ? WHERE id = ?', [$status, $id]);
    }

    // ── Helpers ─────────────────────────────────────────────────

    /**
     * Generate a unique order reference like VGM-A1B2C3.
     */
    private static function generateRef(): string
    {
        do {
            $ref = 'VGM-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
            $exists = self::queryOne('SELECT id FROM orders WHERE reference = ?', [$ref]);
        } while ($exists);

        return $ref;
    }
}
