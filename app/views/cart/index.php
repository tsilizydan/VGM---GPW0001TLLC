<!-- ══════════════════════════════════════════════
     CART — Server-rendered session cart
     ══════════════════════════════════════════════ -->

<?php
$noImg  = '/assets/img/placeholder-product.svg';
$coupon = \Core\Session::get('checkout_coupon', []);
$couponDiscount = (float)($coupon['discount'] ?? 0);
$grandTotal = max(0, $totals['subtotal'] - $couponDiscount);
?>

<!-- Hero strip -->
<div class="bg-vanilla-800 py-10 relative overflow-hidden">
    <div class="absolute inset-0 malagasy-bg opacity-15"></div>
    <div class="relative max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="font-serif font-bold text-cream-100 text-h1"><?= t('cart.title') ?></h1>
        <p class="text-vanilla-300 text-sm mt-2"><?= count($cart) ?> article<?= count($cart) !== 1 ? 's' : '' ?></p>
    </div>
</div>

<section class="section">
<div class="max-w-[1100px] mx-auto px-4 sm:px-6 lg:px-8">
<?php if (empty($cart)): ?>
<!-- Empty state -->
<div class="py-24 text-center space-y-4">
    <p class="text-5xl">🛒</p>
    <h2 class="font-serif text-vanilla-700 text-h2">Votre panier est vide</h2>
    <p class="text-vanilla-400 text-sm"><?= t('cart.empty') ?></p>
    <a href="<?= locale_url('shop') ?>" class="btn-primary btn inline-flex mt-2">Découvrir nos produits</a>
</div>

<?php else: ?>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

    <!-- ── Cart items ── -->
    <div class="lg:col-span-2 space-y-3"
         x-data="{ updating: false }">

        <?php foreach ($cart as $item): ?>
        <?php
        $qty      = max(1, (int)($item['qty'] ?? 1));
        $lineTotal = round((float)($item['price'] ?? 0) * $qty, 2);
        ?>
        <div
            id="cart-item-<?= e($item['id']) ?>"
            class="glass-card flex items-start gap-4 p-4 rounded-2xl transition-opacity duration-300"
            x-data="{ qty: <?= $qty ?>, removing: false }"
        >
            <!-- Image -->
            <img src="<?= e($item['image'] ?? $noImg) ?>"
                 alt="<?= e($item['name'] ?? '') ?>"
                 class="w-20 h-20 rounded-xl object-cover border border-vanilla-100 shrink-0">

            <!-- Details -->
            <div class="flex-1 min-w-0">
                <p class="font-serif font-semibold text-vanilla-800 line-clamp-1"><?= e($item['name'] ?? '') ?></p>
                <p class="text-sm text-vanilla-500 mt-0.5"><?= number_format((float)($item['price'] ?? 0), 2, ',', ' ') ?> € / unité</p>

                <!-- Qty controls -->
                <div class="flex items-center gap-2 mt-2">
                    <div class="flex items-center border border-vanilla-200 rounded-xl overflow-hidden">
                        <button type="button"
                            @click="qty = Math.max(1, qty - 1); $nextTick(() => updateCart('<?= e($item['id']) ?>', qty))"
                            class="qty-btn w-8 h-8 text-vanilla-600 hover:bg-vanilla-100">−</button>
                        <span class="w-10 text-center text-sm font-bold text-vanilla-800" x-text="qty"><?= $qty ?></span>
                        <button type="button"
                            @click="qty = qty + 1; $nextTick(() => updateCart('<?= e($item['id']) ?>', qty))"
                            class="qty-btn w-8 h-8 text-vanilla-600 hover:bg-vanilla-100">+</button>
                    </div>

                    <!-- Remove -->
                    <button type="button"
                        @click="removing = true; removeFromCart('<?= e($item['id']) ?>')"
                        :class="removing ? 'opacity-50 cursor-not-allowed' : ''"
                        class="text-xs text-vanilla-400 hover:text-red-500 transition-colors ml-2">
                        ✕ Supprimer
                    </button>
                </div>
            </div>

            <!-- Line total -->
            <div class="text-right shrink-0">
                <p class="font-bold text-vanilla-800" id="line-total-<?= e($item['id']) ?>">
                    <?= number_format($lineTotal, 2, ',', ' ') ?> €
                </p>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- Continue shopping -->
        <div class="flex items-center justify-between pt-2">
            <a href="<?= locale_url('shop') ?>" class="text-sm text-vanilla-500 hover:text-vanilla-700 transition-colors">← Continuer mes achats</a>
            <button type="button"
                    onclick="if(confirm('Vider le panier ?')) clearCart()"
                    class="text-xs text-red-400 hover:text-red-600 transition-colors">Vider le panier</button>
        </div>
    </div>

    <!-- ── Summary & checkout ── -->
    <div class="space-y-4">
        <!-- Coupon -->
        <?php
        $totals_for_coupon = $totals;  // already set by controller
        require __DIR__ . '/../checkout/_coupon.php';
        ?>

        <!-- Summary -->
        <div class="glass-card p-5 rounded-2xl space-y-3">
            <h2 class="font-semibold text-vanilla-800 border-b border-vanilla-100 pb-3">Résumé</h2>

            <div class="space-y-1.5 text-sm" id="cart-totals">
                <div class="flex justify-between text-vanilla-600">
                    <span>Sous-total</span>
                    <span id="cart-subtotal"><?= number_format($totals['subtotal'], 2, ',', ' ') ?> €</span>
                </div>
                <?php if ($couponDiscount > 0): ?>
                <div class="flex justify-between text-forest-600 font-semibold">
                    <span>Coupon (<?= e($coupon['code']) ?>)</span>
                    <span>−<?= number_format($couponDiscount, 2, ',', ' ') ?> €</span>
                </div>
                <?php endif; ?>
                <div class="flex justify-between text-vanilla-500 text-xs">
                    <span>Livraison</span>
                    <span>Calculée au checkout</span>
                </div>
                <div class="flex justify-between font-bold text-vanilla-900 text-base border-t border-vanilla-200 pt-2">
                    <span>Total</span>
                    <span id="cart-total"><?= number_format($grandTotal, 2, ',', ' ') ?> €</span>
                </div>
            </div>

            <a href="<?= locale_url('checkout') ?>" class="w-full btn-primary btn py-3 text-center block mt-2">
                Passer la commande →
            </a>

            <!-- Trust -->
            <div class="text-xs text-vanilla-400 space-y-1 pt-1">
                <p>🔒 Paiement 100% sécurisé</p>
                <p>📦 Livraison soignée sous 5-7j</p>
                <p>↩️ Retours sous 14 jours</p>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ══ UPSELL SUGGESTIONS ══ -->
<?php if (!empty($cart) && !empty($upsells)): ?>
<div class="mt-12 pt-10 border-t border-vanilla-100">
    <div class="flex items-center gap-3 mb-6">
        <span class="text-2xl">✨</span>
        <div>
            <p class="text-xs font-bold uppercase tracking-widest text-vanilla-400">Vous pourriez aimer</p>
            <h2 class="font-serif font-bold text-vanilla-900 text-xl">Complétez votre panier</h2>
        </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
    <?php foreach ($upsells as $up):
        $upImg     = $up['primary_image'] ?? '/assets/img/placeholder-product.svg';
        $upInStock = ($up['stock'] ?? 1) > 0;
    ?>
    <div class="glass-card rounded-2xl p-3 border border-vanilla-100 hover:border-vanilla-300 transition-all duration-200 group" x-data>
        <a href="<?= locale_url('shop/' . $up['slug']) ?>" class="block relative aspect-square rounded-xl overflow-hidden mb-3 bg-cream-50">
            <img src="<?= e($upImg) ?>" alt="<?= e($up['name']) ?>"
                 class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" loading="lazy">
        </a>
        <p class="text-xs font-semibold text-vanilla-800 line-clamp-2 mb-2 leading-snug">
            <?= e($up['name']) ?>
        </p>
        <div class="flex items-center justify-between">
            <span class="text-sm font-bold text-vanilla-700"><?= number_format((float)$up['price'], 2, ',', ' ') ?> €</span>
            <?php if ($upInStock): ?>
            <button
                @click="$store.cart.add({ id:'<?= $up['id'] ?>', name:'<?= addslashes(e($up['name'])) ?>', price:<?= (float)$up['price'] ?>, image:'<?= addslashes(e($upImg)) ?>' }); $store.alerts.add('Ajouté !','success')"
                class="p-1.5 rounded-lg bg-vanilla-100 hover:bg-vanilla-700 hover:text-cream-100 text-vanilla-700 transition-all duration-200 text-xs font-bold"
                title="Ajouter au panier">+ Panier
            </button>
            <?php else: ?>
            <span class="text-[10px] text-red-400 font-semibold">Rupture</span>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
</div>
</section>

<script>
async function updateCart(id, qty) {
    const res = await fetch('<?= locale_url('cart/update') ?>', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({ id, qty })
    });
    const data = await res.json();
    if (data.success) {
        // Update Alpine cart store count
        if (Alpine.store('cart')) Alpine.store('cart').count = data.count;
        location.reload(); // simple refresh to update all totals
    }
}

async function removeFromCart(id) {
    const res = await fetch('<?= locale_url('cart/remove') ?>', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({ id })
    });
    const data = await res.json();
    if (data.success) {
        if (Alpine.store('cart')) Alpine.store('cart').count = data.count;
        document.getElementById('cart-item-' + id)?.remove();
        if (data.count === 0) location.reload();
    }
}

async function clearCart() {
    await fetch('<?= locale_url('cart/clear') ?>', { method: 'POST' });
    location.reload();
}
</script>
