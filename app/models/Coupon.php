<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

/**
 * Coupon model — validation and redemption.
 */
class Coupon extends Model
{
    /**
     * Find a coupon by code (case-insensitive).
     *
     * @return array<string,mixed>|null
     */
    public static function findByCode(string $code): ?array
    {
        return self::queryOne(
            'SELECT * FROM coupons WHERE UPPER(code) = UPPER(?) AND active = 1',
            [$code]
        );
    }

    /**
     * Validate a coupon against the current cart subtotal.
     *
     * @return array{valid:bool, discount:float, message:string}
     */
    public static function validate(array $coupon, float $subtotal): array
    {
        // Usage limit
        if ($coupon['max_uses'] !== null && (int) $coupon['used_count'] >= (int) $coupon['max_uses']) {
            return ['valid' => false, 'discount' => 0.0, 'message' => 'Ce coupon a atteint sa limite d\'utilisation.'];
        }

        // Expiry
        if ($coupon['expires_at'] !== null && strtotime($coupon['expires_at']) < time()) {
            return ['valid' => false, 'discount' => 0.0, 'message' => 'Ce coupon a expiré.'];
        }

        // Minimum order
        if ($subtotal < (float) $coupon['min_order']) {
            return [
                'valid'    => false,
                'discount' => 0.0,
                'message'  => sprintf(
                    'Commande minimum de %.2f € requise pour ce coupon.',
                    $coupon['min_order']
                ),
            ];
        }

        // Calculate discount
        $discount = 0.0;
        if ($coupon['type'] === 'percent') {
            $discount = round($subtotal * (float) $coupon['value'] / 100, 2);
        } else {
            $discount = min((float) $coupon['value'], $subtotal);
        }

        $label = $coupon['type'] === 'percent'
            ? "-{$coupon['value']}%"
            : "-{$coupon['value']} €";

        return [
            'valid'    => true,
            'discount' => $discount,
            'message'  => "Code appliqué : {$label}",
        ];
    }

    /**
     * Increment the usage counter after a successful order.
     */
    public static function redeem(string $code): void
    {
        self::execute(
            'UPDATE coupons SET used_count = used_count + 1 WHERE UPPER(code) = UPPER(?)',
            [$code]
        );
    }
}
