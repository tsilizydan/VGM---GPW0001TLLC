<!-- ══════════════════════════════════════════════
     CHECKOUT — Step 1: Address
     ══════════════════════════════════════════════ -->

<?php
$old    = \Core\Session::getFlash('old', []);
$errors = \Core\Session::getFlash('errors', []);
$old    = array_merge($address, is_array($old) ? $old : []);

function fval(array $old, string $key, string $def = ''): string {
    return htmlspecialchars($old[$key] ?? $def, ENT_QUOTES);
}
?>

<!-- Progress indicator -->
<?php require __DIR__ . '/_progress.php'; ?>

<div class="max-w-[1100px] mx-auto px-4 sm:px-6 lg:px-8 py-8">
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

    <!-- ── Address Form ── -->
    <div
        x-data="{ shippingSame: <?= ($old['shipping_same'] ?? true) ? 'true' : 'false' ?> }"
        class="lg:col-span-2 space-y-5"
    >
        <?php if (!empty($errors)): ?>
        <div class="alert-error alert">
            <ul class="list-disc list-inside text-sm space-y-1">
                <?php foreach ((array)$errors as $e): ?><li><?= e($e) ?></li><?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <form method="POST" action="<?= locale_url('checkout/shipping') ?>" class="space-y-6">
            <?= csrf_field() ?>

            <!-- Billing address -->
            <div class="glass-card p-6 rounded-2xl space-y-4">
                <h2 class="font-serif font-semibold text-vanilla-800 text-lg flex items-center gap-2">
                    <span class="w-7 h-7 rounded-full bg-vanilla-700 text-cream-100 text-xs font-bold flex items-center justify-center">1</span>
                    Adresse de facturation
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="form-label">Nom complet <span class="text-red-500">*</span></label>
                        <input type="text" name="billing_name" value="<?= fval($old, 'billing_name') ?>"
                               required class="form-input" placeholder="Jean Dupont">
                    </div>
                    <div>
                        <label class="form-label">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="billing_email" value="<?= fval($old, 'billing_email') ?>"
                               required class="form-input" placeholder="email@exemple.fr">
                    </div>
                    <div>
                        <label class="form-label">Téléphone</label>
                        <input type="tel" name="billing_phone" value="<?= fval($old, 'billing_phone') ?>"
                               class="form-input" placeholder="+33 6 00 00 00 00">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="form-label">Adresse <span class="text-red-500">*</span></label>
                        <input type="text" name="billing_address" value="<?= fval($old, 'billing_address') ?>"
                               required class="form-input" placeholder="12 rue de la Vanille">
                    </div>
                    <div>
                        <label class="form-label">Ville <span class="text-red-500">*</span></label>
                        <input type="text" name="billing_city" value="<?= fval($old, 'billing_city') ?>"
                               required class="form-input" placeholder="Paris">
                    </div>
                    <div>
                        <label class="form-label">Code postal <span class="text-red-500">*</span></label>
                        <input type="text" name="billing_zip" value="<?= fval($old, 'billing_zip') ?>"
                               required class="form-input" placeholder="75001">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="form-label">Pays</label>
                        <select name="billing_country" class="form-input">
                            <?php foreach (['France','Madagascar','Belgique','Suisse','Canada','Autre'] as $c): ?>
                            <option <?= ($old['billing_country'] ?? 'France') === $c ? 'selected' : '' ?>><?= $c ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Shipping address toggle -->
            <div class="glass-card p-6 rounded-2xl space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="font-serif font-semibold text-vanilla-800 text-lg flex items-center gap-2">
                        <span class="w-7 h-7 rounded-full bg-vanilla-700 text-cream-100 text-xs font-bold flex items-center justify-center">2</span>
                        Adresse de livraison
                    </h2>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden"   name="shipping_same" value="0">
                        <input type="checkbox" name="shipping_same" value="1"
                               x-model="shippingSame"
                               <?= ($old['shipping_same'] ?? true) ? 'checked' : '' ?>
                               class="sr-only peer">
                        <div class="w-10 h-6 bg-vanilla-200 rounded-full peer-checked:bg-vanilla-700 transition-colors relative">
                            <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full shadow peer-checked:translate-x-4 transition-transform"></div>
                        </div>
                        <span class="text-sm text-vanilla-600 font-medium">Identique à la facturation</span>
                    </label>
                </div>

                <div x-show="!shippingSame" class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-2">
                    <div class="sm:col-span-2">
                        <label class="form-label">Nom du destinataire</label>
                        <input type="text" name="shipping_name" value="<?= fval($old, 'shipping_name') ?>" class="form-input">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="form-label">Adresse</label>
                        <input type="text" name="shipping_address" value="<?= fval($old, 'shipping_address') ?>" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Ville</label>
                        <input type="text" name="shipping_city" value="<?= fval($old, 'shipping_city') ?>" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Code postal</label>
                        <input type="text" name="shipping_zip" value="<?= fval($old, 'shipping_zip') ?>" class="form-input">
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div class="glass-card p-5 rounded-2xl">
                <label class="form-label">Note pour la commande <span class="text-vanilla-400 text-xs font-normal">(optionnel)</span></label>
                <textarea name="notes" rows="2" class="form-input resize-none" placeholder="Instructions de livraison…"><?= fval($old, 'notes') ?></textarea>
            </div>

            <!-- Coupon (pre-checkout) -->
            <?php require __DIR__ . '/_coupon.php'; ?>

            <button type="submit" class="w-full btn-primary btn py-3 text-base">
                Continuer vers la livraison →
            </button>
        </form>
    </div>

    <!-- ── Order Summary Sidebar ── -->
    <?php require __DIR__ . '/_summary.php'; ?>

</div>
</div>
