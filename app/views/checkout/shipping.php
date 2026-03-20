<!-- ══════════════════════════════════════════════
     CHECKOUT — Step 2: Shipping Method
     ══════════════════════════════════════════════ -->

<?php require __DIR__ . '/_progress.php'; ?>

<div class="max-w-[1100px] mx-auto px-4 sm:px-6 lg:px-8 py-8">
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

    <!-- ── Shipping cards ── -->
    <div class="lg:col-span-2 space-y-5">
        <div class="glass-card p-6 rounded-2xl">
            <h2 class="font-serif font-semibold text-vanilla-800 text-lg mb-5 flex items-center gap-2">
                <span class="w-7 h-7 rounded-full bg-vanilla-700 text-cream-100 text-xs font-bold flex items-center justify-center">2</span>
                Mode de livraison
            </h2>

            <form method="POST" action="<?= locale_url('checkout/set-shipping') ?>" x-data="{ method: '<?= e($currentMethod) ?>' }">
                <?= csrf_field() ?>

                <div class="space-y-3 mb-6">
                <?php foreach ($shipping_methods as $key => $m): ?>
                <?php
                // Compute free shipping
                $effectiveCost = ($m['free_over'] !== null && ($totals['subtotal'] - ($coupon['discount'] ?? 0)) >= $m['free_over'])
                    ? 0.0 : $m['cost'];
                ?>
                <label
                    class="flex items-center gap-4 p-4 rounded-xl border-2 cursor-pointer transition-all duration-200"
                    :class="method === '<?= $key ?>'
                        ? 'border-vanilla-700 bg-vanilla-50 shadow-soft'
                        : 'border-vanilla-200 hover:border-vanilla-400'"
                >
                    <input type="radio" name="shipping_method" value="<?= $key ?>"
                           x-model="method"
                           class="sr-only" <?= $currentMethod === $key ? 'checked' : '' ?>>

                    <!-- Radio circle -->
                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0 transition-colors"
                         :class="method === '<?= $key ?>' ? 'border-vanilla-700' : 'border-vanilla-300'">
                        <div class="w-2.5 h-2.5 rounded-full bg-vanilla-700 transition-transform"
                             :class="method === '<?= $key ?>' ? 'scale-100' : 'scale-0'"></div>
                    </div>

                    <!-- Icon -->
                    <span class="text-2xl shrink-0"><?= $m['icon'] ?></span>

                    <!-- Details -->
                    <div class="flex-1">
                        <p class="font-semibold text-vanilla-800"><?= e($m['label']) ?></p>
                        <p class="text-xs text-vanilla-500"><?= e($m['desc']) ?></p>
                        <?php if ($m['free_over'] !== null): ?>
                        <p class="text-xs text-forest-600 mt-0.5">Gratuite dès <?= number_format($m['free_over'], 0, ',', ' ') ?> € d'achat</p>
                        <?php endif; ?>
                    </div>

                    <!-- Price -->
                    <div class="text-right shrink-0">
                        <?php if ($effectiveCost === 0.0): ?>
                        <span class="font-bold text-forest-600">GRATUITE</span>
                        <?php else: ?>
                        <span class="font-bold text-vanilla-800"><?= number_format($effectiveCost, 2, ',', ' ') ?> €</span>
                        <?php endif; ?>
                    </div>
                </label>
                <?php endforeach; ?>
                </div>

                <!-- Address recap -->
                <div class="bg-cream-100 rounded-xl p-4 text-sm text-vanilla-600 mb-6">
                    <p class="font-semibold text-vanilla-700 mb-1">📍 Livraison à :</p>
                    <p><?= e($address['billing_name']) ?></p>
                    <p><?= e($address['billing_address']) ?></p>
                    <p><?= e($address['billing_zip']) ?> <?= e($address['billing_city']) ?>, <?= e($address['billing_country']) ?></p>
                    <a href="<?= locale_url('checkout') ?>" class="text-xs text-vanilla-500 hover:text-vanilla-700 underline mt-2 inline-block">Modifier l'adresse</a>
                </div>

                <button type="submit" class="w-full btn-primary btn py-3 text-base">
                    Continuer vers le paiement →
                </button>
            </form>
        </div>
    </div>

    <!-- Sidebar -->
    <?php require __DIR__ . '/_summary.php'; ?>

</div>
</div>
