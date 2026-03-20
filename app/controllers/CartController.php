<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Request;
use Core\Session;

/**
 * Cart controller — session-backed, JSON API for Alpine.js.
 *
 * Session cart format:
 *   $_SESSION['cart'] = [
 *     ['id' => '12_0', 'name' => '...', 'price' => 34.90, 'qty' => 2, 'image' => '...'],
 *     ...
 *   ]
 */
class CartController extends Controller
{
    // ── Pages ───────────────────────────────────────────────────

    /** GET /cart */
    public function index(Request $request): void
    {
        $cart   = self::getCart();
        $totals = self::computeTotals($cart);

        // Extract numeric product IDs from cart item IDs (format: "productId_variationIdx")
        $cartProductIds = array_unique(array_map(
            static fn($item) => (int) explode('_', (string)($item['id'] ?? '0'))[0],
            $cart
        ));

        $this->render('cart/index', [
            'title'   => 'Mon panier',
            'cart'    => $cart,
            'totals'  => $totals,
            'upsells' => \App\Models\Product::upsellsFor(array_values($cartProductIds), 4),
        ], 'app');
    }

    // ── AJAX endpoints ──────────────────────────────────────────

    /** POST /cart/add */
    public function add(Request $request): void
    {
        $body = $this->jsonBody();

        $id    = (string) ($body['id'] ?? '');
        $name  = (string) ($body['name'] ?? 'Produit');
        $price = (float)  ($body['price'] ?? 0);
        $image = (string) ($body['image'] ?? '');
        $qty   = max(1, (int) ($body['qty'] ?? 1));

        if ($id === '' || $price <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'Produit invalide.'], 422);
        }

        $cart = self::getCart();
        $found = false;

        foreach ($cart as &$item) {
            if ($item['id'] === $id) {
                $item['qty'] += $qty;
                $found = true;
                break;
            }
        }
        unset($item);

        if (!$found) {
            $cart[] = compact('id', 'name', 'price', 'image', 'qty');
        }

        self::saveCart($cart);
        $totals = self::computeTotals($cart);

        $this->jsonResponse([
            'success' => true,
            'count'   => $totals['count'],
            'total'   => $totals['total'],
        ]);
    }

    /** POST /cart/update  — body: {id, qty} */
    public function update(Request $request): void
    {
        $body  = $this->jsonBody();
        $id    = (string) ($body['id'] ?? '');
        $qty   = max(0, (int) ($body['qty'] ?? 0));

        $cart = self::getCart();

        if ($qty === 0) {
            $cart = array_values(array_filter($cart, fn($i) => $i['id'] !== $id));
        } else {
            foreach ($cart as &$item) {
                if ($item['id'] === $id) { $item['qty'] = $qty; break; }
            }
            unset($item);
        }

        self::saveCart($cart);
        $totals = self::computeTotals($cart);

        $this->jsonResponse([
            'success'  => true,
            'count'    => $totals['count'],
            'subtotal' => $totals['subtotal'],
            'total'    => $totals['total'],
        ]);
    }

    /** POST /cart/remove  — body: {id} */
    public function remove(Request $request): void
    {
        $body = $this->jsonBody();
        $id   = (string) ($body['id'] ?? '');

        $cart = array_values(array_filter(self::getCart(), fn($i) => $i['id'] !== $id));
        self::saveCart($cart);
        $totals = self::computeTotals($cart);

        $this->jsonResponse([
            'success' => true,
            'count'   => $totals['count'],
            'total'   => $totals['total'],
        ]);
    }

    /** POST /cart/clear */
    public function clear(Request $request): void
    {
        self::saveCart([]);
        $this->jsonResponse(['success' => true, 'count' => 0]);
    }

    // ── Static helpers (used by CheckoutController too) ─────────

    /** @return list<array<string,mixed>> */
    public static function getCart(): array
    {
        Session::start();
        $raw = $_SESSION['cart'] ?? [];
        return is_array($raw) ? $raw : [];
    }

    public static function saveCart(array $cart): void
    {
        Session::start();
        $_SESSION['cart'] = array_values($cart);
    }

    /**
     * @return array{count:int, subtotal:float, total:float}
     */
    public static function computeTotals(array $cart): array
    {
        $count    = 0;
        $subtotal = 0.0;

        foreach ($cart as $item) {
            $qty     = max(0, (int) ($item['qty'] ?? 1));
            $price   = (float) ($item['price'] ?? 0);
            $count   += $qty;
            $subtotal += round($price * $qty, 2);
        }

        return [
            'count'    => $count,
            'subtotal' => round($subtotal, 2),
            'total'    => round($subtotal, 2), // shipping/discount applied at checkout
        ];
    }

    // ── Private helpers ─────────────────────────────────────────

    /** Decode JSON body, fall back to POST for form requests. */
    private function jsonBody(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) return $decoded;
        }
        return $_POST;
    }

    private function jsonResponse(array $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
