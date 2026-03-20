<!-- Order Summary Sidebar (shared partial) -->
<?php
// $cart, $totals, $coupon must be set by the parent view
$couponDiscount = (float)($coupon['discount'] ?? 0);
$noImg = '/assets/img/placeholder-product.svg';
?>
<aside class="lg:col-span-1">
    <div class="glass-card p-5 rounded-2xl sticky top-28 space-y-4">
        <h2 class="font-serif font-semibold text-vanilla-800 text-base border-b border-vanilla-100 pb-3">
            Votre commande <span class="text-vanilla-400 font-normal text-sm">(<?= count($cart) ?> article<?= count($cart) > 1 ? 's' : '' ?>)</span>
        </h2>

        <!-- Items -->
        <ul class="space-y-3 max-h-64 overflow-y-auto pr-1">
        <?php foreach ($cart as $item): ?>
        <?php $qty = max(1, (int)($item['qty'] ?? 1)); ?>
        <li class="flex items-start gap-3">
            <div class="relative shrink-0">
                <img src="<?= e($item['image'] ?? $noImg) ?>"
                     alt="<?= e($item['name'] ?? '') ?>"
                     class="w-12 h-12 rounded-xl object-cover border border-vanilla-100">
                <span class="absolute -top-2 -right-2 w-5 h-5 rounded-full bg-vanilla-700 text-cream-100 text-[10px] font-bold flex items-center justify-center">
                    <?= $qty ?>
                </span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-vanilla-800 line-clamp-1"><?= e($item['name'] ?? '') ?></p>
                <p class="text-xs text-vanilla-400"><?= number_format((float)($item['price'] ?? 0), 2, ',', ' ') ?> € / unité</p>
            </div>
            <span class="text-sm font-bold text-vanilla-700 shrink-0">
                <?= number_format((float)($item['price'] ?? 0) * $qty, 2, ',', ' ') ?> €
            </span>
        </li>
        <?php endforeach; ?>
        </ul>

        <!-- Totals -->
        <div class="border-t border-vanilla-100 pt-3 space-y-2 text-sm">
            <div class="flex justify-between text-vanilla-600">
                <span>Sous-total</span>
                <span><?= number_format($totals['subtotal'], 2, ',', ' ') ?> €</span>
            </div>
            <?php if ($couponDiscount > 0): ?>
            <div class="flex justify-between text-forest-600 font-semibold">
                <span>Code promo (<?= e($coupon['code']) ?>)</span>
                <span>−<?= number_format($couponDiscount, 2, ',', ' ') ?> €</span>
            </div>
            <?php endif; ?>
            <?php if (isset($shippingCost)): ?>
            <div class="flex justify-between text-vanilla-600">
                <span>Livraison</span>
                <span><?= $shippingCost > 0 ? number_format($shippingCost, 2, ',', ' ') . ' €' : '<span class="text-forest-600 font-semibold">Gratuite ✓</span>' ?></span>
            </div>
            <?php endif; ?>
            <div class="flex justify-between font-bold text-vanilla-900 text-base border-t border-vanilla-200 pt-2">
                <span>Total</span>
                <span><?= number_format($total ?? $totals['subtotal'] - $couponDiscount, 2, ',', ' ') ?> €</span>
            </div>
        </div>

        <!-- Trust badges -->
        <div class="border-t border-vanilla-100 pt-3 space-y-1.5 text-xs text-vanilla-500">
            <p class="flex items-center gap-2">🔒 Paiement 100% sécurisé</p>
            <p class="flex items-center gap-2">📦 Emballage soigné &amp; éco-responsable</p>
            <p class="flex items-center gap-2">↩️ Retours acceptés sous 14 jours</p>
        </div>
    </div>
</aside>
