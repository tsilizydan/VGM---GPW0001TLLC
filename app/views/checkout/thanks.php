<!-- ══════════════════════════════════════════════
     CHECKOUT — Step 4: Thank you / Confirmation
     ══════════════════════════════════════════════ -->

<?php
$items    = $order['items'] ?? [];
$total    = (float)($order['total'] ?? 0);
$shipping = (float)($order['shipping_cost'] ?? 0);
$discount = (float)($order['discount'] ?? 0);
$subtotal = (float)($order['subtotal'] ?? 0);
?>

<section class="py-16 md:py-24">
<div class="max-w-2xl mx-auto px-4 sm:px-6 text-center">

    <!-- Hero checkmark -->
    <div class="mx-auto mb-6 w-20 h-20 rounded-full bg-forest-100 flex items-center justify-center animate__animated animate__bounceIn">
        <svg class="w-10 h-10 text-forest-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
        </svg>
    </div>

    <h1 class="font-serif font-bold text-vanilla-900 text-h1 mb-3 animate__animated animate__fadeInUp">
        Commande confirmée !
    </h1>
    <p class="text-vanilla-500 mb-2 animate__animated animate__fadeIn">
        Merci pour votre confiance. Un email de confirmation a été envoyé à
        <strong class="text-vanilla-700"><?= e($order['billing_email'] ?? '') ?></strong>.
    </p>

    <!-- Reference badge -->
    <div class="inline-flex items-center gap-2 bg-vanilla-800 text-cream-100 px-4 py-2 rounded-xl text-sm font-mono font-bold mt-2 mb-8 animate__animated animate__fadeIn">
        <span class="text-vanilla-400 font-sans font-normal">Référence</span>
        <?= e($order['reference'] ?? '—') ?>
    </div>

    <!-- Order card -->
    <div class="glass-card p-6 rounded-2xl text-left space-y-5 animate__animated animate__fadeInUp">

        <!-- Items -->
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-vanilla-400 mb-3">Articles commandés</p>
            <ul class="divide-y divide-vanilla-100">
            <?php foreach ($items as $item): ?>
            <?php $qty = (int)($item['qty'] ?? 1); ?>
            <li class="flex items-center justify-between py-2.5 text-sm">
                <span class="text-vanilla-700 flex-1">
                    <?= e($item['name']) ?>
                    <span class="text-vanilla-400"> ×<?= $qty ?></span>
                </span>
                <span class="font-semibold text-vanilla-800"><?= number_format((float)$item['price'] * $qty, 2, ',', ' ') ?> €</span>
            </li>
            <?php endforeach; ?>
            </ul>
        </div>

        <!-- Totals -->
        <div class="border-t border-vanilla-100 pt-4 space-y-1.5 text-sm">
            <div class="flex justify-between text-vanilla-600">
                <span>Sous-total</span>
                <span><?= number_format($subtotal, 2, ',', ' ') ?> €</span>
            </div>
            <?php if ($discount > 0): ?>
            <div class="flex justify-between text-forest-600 font-semibold">
                <span>Réduction (<?= e($order['coupon_code'] ?? '') ?>)</span>
                <span>−<?= number_format($discount, 2, ',', ' ') ?> €</span>
            </div>
            <?php endif; ?>
            <div class="flex justify-between text-vanilla-600">
                <span><?= e($order['shipping_label'] ?? 'Livraison') ?></span>
                <span><?= $shipping > 0 ? number_format($shipping, 2, ',', ' ') . ' €' : 'Gratuite' ?></span>
            </div>
            <div class="flex justify-between font-bold text-vanilla-900 text-base border-t border-vanilla-200 pt-2">
                <span>Total payé</span>
                <span><?= number_format($total, 2, ',', ' ') ?> €</span>
            </div>
        </div>

        <!-- Delivery info -->
        <div class="bg-cream-100 rounded-xl p-4 text-sm">
            <p class="font-semibold text-vanilla-700 mb-1.5">📦 Livraison</p>
            <p class="text-vanilla-600"><?= e($order['shipping_label'] ?? '') ?></p>
            <p class="text-vanilla-500 mt-1"><?= e($order['billing_address']) ?>, <?= e($order['billing_zip']) ?> <?= e($order['billing_city']) ?>, <?= e($order['billing_country']) ?></p>
        </div>
    </div>

    <!-- CTAs -->
    <div class="flex flex-col sm:flex-row gap-3 justify-center mt-8">
        <a href="<?= locale_url('shop') ?>" class="btn-primary btn py-3 px-8">
            Continuer mes achats
        </a>
        <a href="<?= locale_url() ?>" class="btn-ghost btn py-3 px-8">
            Retour à l'accueil
        </a>
    </div>

    <!-- Malagasy cultural touch -->
    <div class="mt-12 p-6 rounded-2xl malagasy-bg bg-vanilla-800 text-cream-100/80 text-sm">
        <p class="font-serif text-cream-100 text-base mb-1">Misaotra betsaka 🌿</p>
        <p class="text-cream-100/70 text-xs">« Merci infiniment » en malgache — Votre confiance soutient directement nos agriculteurs de la région SAVA, Madagascar.</p>
    </div>

</div>
</section>
