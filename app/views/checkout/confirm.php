<!-- ══════════════════════════════════════════════
     CHECKOUT — Step 3: Confirm & Payment
     ══════════════════════════════════════════════ -->

<?php require __DIR__ . '/_progress.php'; ?>

<div class="max-w-[1100px] mx-auto px-4 sm:px-6 lg:px-8 py-8">
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

    <!-- ── Payment & summary ── -->
    <div class="lg:col-span-2 space-y-5">

        <!-- Order recap -->
        <div class="glass-card p-6 rounded-2xl">
            <h2 class="font-serif font-semibold text-vanilla-800 text-lg mb-4 flex items-center gap-2">
                <span class="w-7 h-7 rounded-full bg-vanilla-700 text-cream-100 text-xs font-bold flex items-center justify-center">3</span>
                Récapitulatif de commande
            </h2>

            <!-- Items table -->
            <table class="w-full text-sm mb-4">
                <thead class="text-xs uppercase tracking-wider text-vanilla-400 border-b border-vanilla-100">
                    <tr>
                        <th class="pb-2 text-left">Produit</th>
                        <th class="pb-2 text-center w-12">Qté</th>
                        <th class="pb-2 text-right">Prix</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-vanilla-50">
                <?php foreach ($cart as $item): ?>
                <?php $qty = max(1, (int)($item['qty'] ?? 1)); ?>
                <tr>
                    <td class="py-2.5 text-vanilla-700 font-medium"><?= e($item['name'] ?? '') ?></td>
                    <td class="py-2.5 text-center text-vanilla-500">×<?= $qty ?></td>
                    <td class="py-2.5 text-right font-semibold text-vanilla-800">
                        <?= number_format((float)($item['price'] ?? 0) * $qty, 2, ',', ' ') ?> €
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Totals -->
            <div class="space-y-1.5 border-t border-vanilla-100 pt-3 text-sm">
                <div class="flex justify-between text-vanilla-600">
                    <span>Sous-total</span>
                    <span><?= number_format($subtotal, 2, ',', ' ') ?> €</span>
                </div>
                <?php if ($discount > 0 && is_array($coupon)): ?>
                <div class="flex justify-between text-forest-600 font-semibold">
                    <span>Coupon <?= e($coupon['code'] ?? '') ?></span>
                    <span>−<?= number_format($discount, 2, ',', ' ') ?> €</span>
                </div>
                <?php endif; ?>
                <div class="flex justify-between text-vanilla-600">
                    <span><?= e(is_array($shipping) ? ($shipping['label'] ?? 'Livraison') : 'Livraison') ?></span>
                    <span><?= $shippingCost > 0 ? number_format($shippingCost, 2, ',', ' ') . ' €' : '<span class="text-forest-600 font-semibold">Gratuite</span>' ?></span>
                </div>
                <div class="flex justify-between font-bold text-vanilla-900 text-base border-t border-vanilla-200 pt-2 mt-1">
                    <span>Total à payer</span>
                    <span><?= number_format($total, 2, ',', ' ') ?> €</span>
                </div>
            </div>
        </div>

        <!-- Delivery address recap -->
        <div class="glass-card p-5 rounded-2xl grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm text-vanilla-600">
            <div>
                <p class="font-semibold text-vanilla-800 mb-1 text-xs uppercase tracking-widest">Facturation</p>
                <p><?= e($address['billing_name']) ?></p>
                <p><?= e($address['billing_email']) ?></p>
                <?php if ($address['billing_phone']): ?>
                <p><?= e($address['billing_phone']) ?></p>
                <?php endif; ?>
                <p><?= e($address['billing_address']) ?></p>
                <p><?= e($address['billing_zip']) ?> <?= e($address['billing_city']) ?></p>
                <p><?= e($address['billing_country']) ?></p>
            </div>
            <div>
                <p class="font-semibold text-vanilla-800 mb-1 text-xs uppercase tracking-widest">Livraison</p>
                <?php if ($address['shipping_same'] ?? true): ?>
                    <p class="text-vanilla-500 italic">Identique à l'adresse de facturation</p>
                <?php else: ?>
                    <p><?= e($address['shipping_name'] ?? $address['billing_name']) ?></p>
                    <p><?= e($address['shipping_address'] ?? $address['billing_address']) ?></p>
                    <p><?= e($address['shipping_zip'] ?? $address['billing_zip']) ?> <?= e($address['shipping_city'] ?? $address['billing_city']) ?></p>
                <?php endif; ?>
                <p class="mt-2 font-medium text-vanilla-700">📦 <?= e(is_array($shipping) ? ($shipping['label'] ?? '') : '') ?></p>
            </div>
        </div>
        <div class="text-right text-xs">
            <a href="<?= locale_url('checkout') ?>" class="text-vanilla-400 hover:text-vanilla-700 transition-colors underline">← Modifier les informations</a>
        </div>

        <!-- Payment (simulated) -->
        <div
            x-data="{ cardNum: '', expiry: '', cvv: '', name: '' }"
            class="glass-card p-6 rounded-2xl"
        >
            <h3 class="font-serif font-semibold text-vanilla-800 text-base mb-4 flex items-center gap-2">
                🔒 Paiement sécurisé
                <span class="flex gap-1 ml-auto">
                    <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/visa/visa-original.svg"
                         alt="Visa" class="h-6 opacity-70">
                    <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/mastercard/mastercard-original.svg"
                         alt="MC" class="h-6 opacity-70">
                </span>
            </h3>

            <div class="space-y-4">
                <div>
                    <label class="form-label">Titulaire de la carte</label>
                    <input type="text" x-model="name" placeholder="Jean Dupont"
                           class="form-input" autocomplete="cc-name">
                </div>
                <div>
                    <label class="form-label">Numéro de carte</label>
                    <input type="text" x-model="cardNum"
                           @input="cardNum = $event.target.value.replace(/\D/g,'').replace(/(.{4})/g,'$1 ').trim().slice(0,19)"
                           placeholder="4242 4242 4242 4242"
                           maxlength="19" class="form-input font-mono tracking-widest"
                           autocomplete="cc-number">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Expiration</label>
                        <input type="text" x-model="expiry"
                               @input="expiry = $event.target.value.replace(/\D/g,'').replace(/^(\d{2})(\d)/,'$1/$2').slice(0,5)"
                               placeholder="MM/AA" maxlength="5" class="form-input font-mono"
                               autocomplete="cc-exp">
                    </div>
                    <div>
                        <label class="form-label">CVV</label>
                        <input type="password" x-model="cvv"
                               placeholder="•••" maxlength="4" class="form-input font-mono"
                               autocomplete="cc-csc">
                    </div>
                </div>
            </div>

            <p class="text-xs text-vanilla-400 mt-3 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-forest-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 0 0-4.5 4.5V9H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2h-.5V5.5A4.5 4.5 0 0 0 10 1Zm3 8V5.5a3 3 0 1 0-6 0V9h6Z" clip-rule="evenodd"/>
                </svg>
                Environnement de démonstration — aucune carte réelle débitée.
            </p>
        </div>

        <!-- Place order -->
        <form method="POST" action="<?= locale_url('checkout/place') ?>">
            <?= csrf_field() ?>
            <button type="submit"
                    class="w-full btn-primary btn py-4 text-base font-bold shadow-glass hover:shadow-soft transition-shadow">
                ✓ Confirmer et passer commande — <?= number_format($total, 2, ',', ' ') ?> €
            </button>
        </form>
    </div>

    <!-- Sidebar -->
    <?php require __DIR__ . '/_summary.php'; ?>

</div>
</div>
