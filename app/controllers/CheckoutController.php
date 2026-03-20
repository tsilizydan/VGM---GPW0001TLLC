<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Request;
use Core\Session;
use Core\Csrf;
use App\Models\Order;
use App\Models\Coupon;

/**
 * CheckoutController — 4-step checkout flow.
 *
 * Session keys used:
 *   $_SESSION['checkout_address']  — billing/shipping form data
 *   $_SESSION['checkout_shipping'] — {method, label, cost}
 *   $_SESSION['checkout_coupon']   — {code, discount, message}
 */
class CheckoutController extends Controller
{
    private const SHIPPING_METHODS = [
        'standard' => [
            'label'    => 'Colissimo Standard',
            'desc'     => 'Livraison en 5-7 jours ouvrés',
            'cost'     => 8.90,
            'free_over' => 75.00,
            'icon'     => '📦',
        ],
        'express' => [
            'label' => 'Chronopost Express',
            'desc'  => 'Livraison en 2-3 jours ouvrés',
            'cost'  => 14.90,
            'free_over' => null,
            'icon'  => '⚡',
        ],
        'relay' => [
            'label' => 'Point Relais',
            'desc'  => 'Livraison en 3-5 jours ouvrés',
            'cost'  => 5.90,
            'free_over' => null,
            'icon'  => '🏪',
        ],
    ];

    // ── Step 1: Address ─────────────────────────────────────────

    public function index(Request $request): void
    {
        $cart = CartController::getCart();
        if (empty($cart)) {
            header('Location: ' . locale_url('cart'));
            exit;
        }

        $totals  = CartController::computeTotals($cart);
        $coupon  = Session::get('checkout_coupon', []);
        $address = Session::get('checkout_address', []);

        $this->render('checkout/index', [
            'title'   => 'Livraison & Adresse',
            'cart'    => $cart,
            'totals'  => $totals,
            'coupon'  => $coupon,
            'address' => $address,
        ], 'app');
    }

    // ── Step 1 → 2: Save address, go to shipping ───────────────

    public function shipping(Request $request): void
    {
        $this->checkCsrf($request);

        $cart = CartController::getCart();
        if (empty($cart)) {
            header('Location: ' . locale_url('cart'));
            exit;
        }

        $address = [
            'billing_name'    => trim($request->input('billing_name', '')),
            'billing_email'   => trim($request->input('billing_email', '')),
            'billing_phone'   => trim($request->input('billing_phone', '')),
            'billing_address' => trim($request->input('billing_address', '')),
            'billing_city'    => trim($request->input('billing_city', '')),
            'billing_zip'     => trim($request->input('billing_zip', '')),
            'billing_country' => trim($request->input('billing_country', 'France')),
            'shipping_same'   => (bool) $request->input('shipping_same', true),
            'shipping_name'   => trim($request->input('shipping_name', '')),
            'shipping_address'=> trim($request->input('shipping_address', '')),
            'shipping_city'   => trim($request->input('shipping_city', '')),
            'shipping_zip'    => trim($request->input('shipping_zip', '')),
            'shipping_country'=> trim($request->input('shipping_country', 'France')),
            'notes'           => trim($request->input('notes', '')),
        ];

        $errors = $this->validateAddress($address);
        if ($errors) {
            Session::flash('errors', $errors);
            Session::flash('old', $address);
            header('Location: ' . locale_url('checkout'));
            exit;
        }

        Session::set('checkout_address', $address);

        $totals = CartController::computeTotals($cart);
        $coupon = Session::get('checkout_coupon', []);

        $this->render('checkout/shipping', [
            'title'           => 'Mode de livraison',
            'cart'            => $cart,
            'totals'          => $totals,
            'coupon'          => $coupon,
            'address'         => $address,
            'shipping_methods' => self::SHIPPING_METHODS,
            'currentMethod'   => Session::get('checkout_shipping', [])['method'] ?? 'standard',
        ], 'app');
    }

    // ── Step 2 → 3: Save shipping method, go to confirm ────────

    public function setShipping(Request $request): void
    {
        $this->checkCsrf($request);

        $method = $request->input('shipping_method', 'standard');
        $cart   = CartController::getCart();
        if (!isset(self::SHIPPING_METHODS[$method])) $method = 'standard';

        $m = self::SHIPPING_METHODS[$method];

        // Apply free shipping rule
        $totals = CartController::computeTotals($cart);
        $cost   = ($m['free_over'] !== null && $totals['subtotal'] >= $m['free_over']) ? 0.0 : $m['cost'];

        Session::set('checkout_shipping', [
            'method' => $method,
            'label'  => $m['label'],
            'cost'   => $cost,
        ]);

        header('Location: ' . locale_url('checkout/confirm'));
        exit;
    }

    // ── Step 3: Confirm ─────────────────────────────────────────

    public function confirm(Request $request): void
    {
        $address  = Session::get('checkout_address');
        $shipping = Session::get('checkout_shipping');
        $cart     = CartController::getCart();

        if (!$address || !$shipping || empty($cart)) {
            header('Location: ' . locale_url('checkout'));
            exit;
        }

        $coupon  = Session::get('checkout_coupon', []);
        $totals  = CartController::computeTotals($cart);
        $discount = (float) ($coupon['discount'] ?? 0);
        $shippingCost = (float) ($shipping['cost'] ?? 0);
        $total   = max(0, round($totals['subtotal'] - $discount + $shippingCost, 2));

        $this->render('checkout/confirm', [
            'title'        => 'Récapitulatif & Paiement',
            'cart'         => $cart,
            'address'      => $address,
            'shipping'     => $shipping,
            'coupon'       => $coupon,
            'subtotal'     => $totals['subtotal'],
            'discount'     => $discount,
            'shippingCost' => $shippingCost,
            'total'        => $total,
        ], 'app');
    }

    // ── Step 4: Place order ─────────────────────────────────────

    public function place(Request $request): void
    {
        $this->checkCsrf($request);

        $address  = Session::get('checkout_address');
        $shipping = Session::get('checkout_shipping');
        $cart     = CartController::getCart();

        if (!$address || !$shipping || empty($cart)) {
            header('Location: ' . locale_url('checkout'));
            exit;
        }

        $coupon   = Session::get('checkout_coupon', []);
        $totals   = CartController::computeTotals($cart);
        $discount = (float) ($coupon['discount'] ?? 0);
        $shippingCost = (float) ($shipping['cost'] ?? 0);
        $total    = max(0, round($totals['subtotal'] - $discount + $shippingCost, 2));

        // Create order
        $result = Order::create([
            ...$address,
            'user_id'        => \Core\Auth::check() ? \Core\Auth::user()['id'] : null,
            'shipping_method' => $shipping['method'],
            'shipping_label'  => $shipping['label'],
            'shipping_cost'   => $shippingCost,
            'coupon_code'     => $coupon['code'] ?? null,
            'discount'        => $discount,
            'subtotal'        => $totals['subtotal'],
            'total'           => $total,
        ]);

        Order::addItems($result['id'], $cart);

        // Redeem coupon if used
        if (!empty($coupon['code'])) {
            Coupon::redeem($coupon['code']);
        }

        // Clean up session
        CartController::saveCart([]);
        Session::remove('checkout_address');
        Session::remove('checkout_shipping');
        Session::remove('checkout_coupon');
        Session::set('last_order_ref', $result['reference']);

        header('Location: ' . locale_url('checkout/thanks'));
        exit;
    }

    // ── Thank you page ──────────────────────────────────────────

    public function thanks(Request $request): void
    {
        $ref = Session::get('last_order_ref');
        if (!$ref) {
            header('Location: ' . locale_url('shop'));
            exit;
        }

        $order = Order::findByRef($ref);

        $this->render('checkout/thanks', [
            'title' => 'Commande confirmée !',
            'order' => $order,
        ], 'app');
    }

    // ── AJAX: Apply coupon ──────────────────────────────────────

    public function applyCoupon(Request $request): void
    {
        $raw  = file_get_contents('php://input');
        $body = $raw ? (json_decode($raw, true) ?? $_POST) : $_POST;

        $code = strtoupper(trim($body['code'] ?? ''));

        if ($code === '') {
            // Remove coupon
            Session::remove('checkout_coupon');
            $this->jsonResponse(['success' => true, 'removed' => true]);
        }

        $cart   = CartController::getCart();
        $totals = CartController::computeTotals($cart);

        $coupon = Coupon::findByCode($code);
        if (!$coupon) {
            $this->jsonResponse(['success' => false, 'message' => 'Code promo invalide.']);
        }

        $result = Coupon::validate($coupon, $totals['subtotal']);
        if (!$result['valid']) {
            $this->jsonResponse(['success' => false, 'message' => $result['message']]);
        }

        Session::set('checkout_coupon', [
            'code'     => $coupon['code'],
            'discount' => $result['discount'],
            'message'  => $result['message'],
        ]);

        $this->jsonResponse([
            'success'  => true,
            'code'     => $coupon['code'],
            'discount' => $result['discount'],
            'message'  => $result['message'],
        ]);
    }

    // ── Private helpers ─────────────────────────────────────────

    /** @return list<string> */
    private function validateAddress(array $a): array
    {
        $errors = [];
        if (strlen($a['billing_name']) < 2)    $errors[] = 'Nom requis.';
        if (!filter_var($a['billing_email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Email invalide.';
        if (strlen($a['billing_address']) < 5)  $errors[] = 'Adresse requise.';
        if (strlen($a['billing_city']) < 2)      $errors[] = 'Ville requise.';
        if (strlen($a['billing_zip']) < 3)       $errors[] = 'Code postal requis.';
        return $errors;
    }

    private function checkCsrf(Request $request): void
    {
        if (!Csrf::validate($request->input('_token', ''))) {
            \Core\Response::abort(403, 'Invalid CSRF token.');
        }
    }

    private function jsonResponse(array $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
