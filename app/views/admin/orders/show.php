<!-- ADMIN — Order detail + status update -->
<?php
$statusClass = fn($s) => match($s) {
    'pending'    => 'bg-amber-100 text-amber-700',
    'paid'       => 'bg-blue-100 text-blue-700',
    'processing' => 'bg-indigo-100 text-indigo-700',
    'shipped'    => 'bg-purple-100 text-purple-700',
    'completed'  => 'bg-forest-100 text-forest-700',
    'cancelled'  => 'bg-red-100 text-red-600',
    default      => 'bg-gray-100 text-gray-500',
};
$discount  = (float)$order['discount'];
$shipping  = (float)$order['shipping_cost'];
$subtotal  = (float)$order['subtotal'];
$total     = (float)$order['total'];
?>
<div class="max-w-4xl space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <a href="<?= locale_url('admin/orders') ?>" class="text-xs text-vanilla-400 hover:text-vanilla-700">← Retour aux commandes</a>
            <h1 class="font-serif font-bold text-vanilla-900 text-2xl mt-1">Commande <?= e($order['reference']) ?></h1>
            <p class="text-xs text-vanilla-400"><?= date('d/m/Y à H:i', strtotime($order['created_at'])) ?></p>
        </div>
        <span class="inline-block text-sm px-4 py-1.5 rounded-full font-semibold <?= $statusClass($order['status']) ?>">
            <?= $statuses[$order['status']]['label'] ?? $order['status'] ?>
        </span>
    </div>

    <!-- Status update -->
    <form method="POST" action="<?= locale_url("admin/orders/{$order['id']}/status") ?>"
          class="flex items-center gap-3 p-4 bg-white/80 border border-vanilla-200/60 rounded-xl shadow-soft">
        <?= csrf_field() ?>
        <label class="text-sm font-semibold text-vanilla-700">Changer le statut :</label>
        <select name="status" class="form-input text-sm py-1.5 flex-1 max-w-xs">
            <?php foreach ($statuses as $key => $s): ?>
            <option value="<?= $key ?>" <?= $order['status'] === $key ? 'selected' : '' ?>><?= $s['label'] ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn-primary btn py-1.5 px-4 text-sm">Enregistrer</button>
    </form>

    <!-- Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

        <!-- Billing -->
        <div class="bg-white/80 border border-vanilla-200/60 rounded-2xl p-5 shadow-soft">
            <h2 class="text-xs font-bold uppercase tracking-widest text-vanilla-400 mb-3">Facturation</h2>
            <p class="font-semibold text-vanilla-800"><?= e($order['billing_name']) ?></p>
            <p class="text-sm text-vanilla-600"><?= e($order['billing_email']) ?></p>
            <?php if ($order['billing_phone']): ?><p class="text-sm text-vanilla-600"><?= e($order['billing_phone']) ?></p><?php endif; ?>
            <p class="text-sm text-vanilla-600 mt-2"><?= e($order['billing_address']) ?></p>
            <p class="text-sm text-vanilla-600"><?= e($order['billing_zip']) ?> <?= e($order['billing_city']) ?>, <?= e($order['billing_country']) ?></p>
        </div>

        <!-- Shipping -->
        <div class="bg-white/80 border border-vanilla-200/60 rounded-2xl p-5 shadow-soft">
            <h2 class="text-xs font-bold uppercase tracking-widest text-vanilla-400 mb-3">Livraison</h2>
            <?php if ($order['shipping_same']): ?>
            <p class="text-sm text-vanilla-500 italic">Identique à la facturation</p>
            <?php else: ?>
            <p class="font-semibold text-vanilla-800"><?= e($order['shipping_name'] ?? $order['billing_name']) ?></p>
            <p class="text-sm text-vanilla-600"><?= e($order['shipping_address'] ?? $order['billing_address']) ?></p>
            <p class="text-sm text-vanilla-600"><?= e($order['shipping_zip'] ?? $order['billing_zip']) ?> <?= e($order['shipping_city'] ?? $order['billing_city']) ?></p>
            <?php endif; ?>
            <p class="text-sm font-semibold text-vanilla-700 mt-3">📦 <?= e($order['shipping_label'] ?? '') ?></p>
        </div>
    </div>

    <!-- Items -->
    <div class="bg-white/80 border border-vanilla-200/60 rounded-2xl shadow-soft overflow-hidden">
        <h2 class="px-5 py-3 border-b border-vanilla-100 text-xs font-bold uppercase tracking-widest text-vanilla-400">Articles</h2>
        <table class="w-full text-sm">
            <thead class="bg-cream-50 text-xs text-vanilla-400">
                <tr>
                    <th class="px-4 py-2 text-left">Produit</th>
                    <th class="px-4 py-2 text-center">Qté</th>
                    <th class="px-4 py-2 text-right">P.U.</th>
                    <th class="px-4 py-2 text-right">Sous-total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-vanilla-50">
            <?php foreach ($order['items'] as $item): ?>
            <tr>
                <td class="px-4 py-2.5 font-medium text-vanilla-800"><?= e($item['name']) ?></td>
                <td class="px-4 py-2.5 text-center text-vanilla-500">×<?= $item['qty'] ?></td>
                <td class="px-4 py-2.5 text-right text-vanilla-600"><?= number_format((float)$item['price'], 2, ',', ' ') ?> €</td>
                <td class="px-4 py-2.5 text-right font-bold text-vanilla-800"><?= number_format((float)$item['subtotal'], 2, ',', ' ') ?> €</td>
            </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot class="border-t border-vanilla-100 text-sm">
                <tr class="text-vanilla-500"><td colspan="3" class="px-4 py-2 text-right">Sous-total</td><td class="px-4 py-2 text-right"><?= number_format($subtotal, 2, ',', ' ') ?> €</td></tr>
                <?php if ($discount > 0): ?>
                <tr class="text-forest-600"><td colspan="3" class="px-4 py-1 text-right">Coupon (<?= e($order['coupon_code']) ?>)</td><td class="px-4 py-1 text-right">−<?= number_format($discount, 2, ',', ' ') ?> €</td></tr>
                <?php endif; ?>
                <tr class="text-vanilla-500"><td colspan="3" class="px-4 py-1 text-right">Livraison</td><td class="px-4 py-1 text-right"><?= $shipping > 0 ? number_format($shipping, 2, ',', ' ') . ' €' : 'Gratuite' ?></td></tr>
                <tr class="font-bold text-vanilla-900 text-base"><td colspan="3" class="px-4 py-2 text-right border-t border-vanilla-200">Total</td><td class="px-4 py-2 text-right border-t border-vanilla-200"><?= number_format($total, 2, ',', ' ') ?> €</td></tr>
            </tfoot>
        </table>
    </div>

    <?php if ($order['notes']): ?>
    <div class="bg-gold-50 border border-gold-200 rounded-xl p-4 text-sm">
        <p class="font-semibold text-gold-700 mb-1">📝 Note du client</p>
        <p class="text-vanilla-700"><?= nl2br(e($order['notes'])) ?></p>
    </div>
    <?php endif; ?>

</div>
