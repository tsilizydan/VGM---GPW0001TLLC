<!-- ADMIN — Orders list -->
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
?>
<div class="space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <h1 class="font-serif font-bold text-vanilla-900 text-2xl">Commandes</h1>
        <span class="text-sm text-vanilla-500"><?= $pagination['total'] ?> commande<?= $pagination['total'] > 1 ? 's' : '' ?></span>
    </div>

    <!-- Filters -->
    <form method="GET" class="flex flex-wrap gap-2">
        <input type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="Référence, nom, email…" class="form-input text-sm py-2 w-56">
        <select name="status" class="form-input text-sm py-2" onchange="this.form.submit()">
            <option value="">Tous les statuts</option>
            <?php foreach ($statuses as $key => $s): ?>
            <option value="<?= $key ?>" <?= $filters['status'] === $key ? 'selected' : '' ?>><?= $s['label'] ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn-primary btn py-2 px-4 text-sm">Filtrer</button>
        <a href="<?= locale_url('admin/orders') ?>" class="btn-ghost btn py-2 px-4 text-sm">Reset</a>
    </form>

    <!-- Table -->
    <div class="bg-white/80 border border-vanilla-200/60 rounded-2xl shadow-soft overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-vanilla-800 text-cream-100 text-xs uppercase tracking-wider">
                <tr>
                    <th class="px-4 py-3 text-left">Référence</th>
                    <th class="px-4 py-3 text-left">Client</th>
                    <th class="px-4 py-3 text-center">Articles</th>
                    <th class="px-4 py-3 text-right">Total</th>
                    <th class="px-4 py-3 text-center">Statut</th>
                    <th class="px-4 py-3 text-right">Date</th>
                    <th class="px-4 py-3 w-10"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-vanilla-50">
            <?php foreach ($orders as $order): ?>
            <tr class="hover:bg-cream-50 transition-colors">
                <td class="px-4 py-3 font-mono text-xs font-bold text-vanilla-700"><?= e($order['reference']) ?></td>
                <td class="px-4 py-3">
                    <p class="font-medium text-vanilla-800"><?= e($order['billing_name']) ?></p>
                    <p class="text-xs text-vanilla-400"><?= e($order['billing_email']) ?></p>
                </td>
                <td class="px-4 py-3 text-center text-vanilla-500"><?= (int)$order['items_count'] ?></td>
                <td class="px-4 py-3 text-right font-bold text-vanilla-800"><?= number_format((float)$order['total'], 2, ',', ' ') ?> €</td>
                <td class="px-4 py-3 text-center">
                    <span class="inline-block text-[11px] px-2.5 py-0.5 rounded-full font-semibold <?= $statusClass($order['status']) ?>">
                        <?= $statuses[$order['status']]['label'] ?? $order['status'] ?>
                    </span>
                </td>
                <td class="px-4 py-3 text-right text-xs text-vanilla-400"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                <td class="px-4 py-3">
                    <a href="<?= locale_url("admin/orders/{$order['id']}") ?>"
                       class="p-1.5 rounded hover:bg-vanilla-100 text-vanilla-400 hover:text-vanilla-700 transition-colors block">→</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($orders)): ?>
            <tr><td colspan="7" class="py-12 text-center text-vanilla-400">Aucune commande.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        </div>
        <!-- Pagination -->
        <?php if ($pagination['pages'] > 1): ?>
        <div class="px-4 py-3 border-t border-vanilla-100 flex justify-between items-center">
            <p class="text-xs text-vanilla-400">Page <?= $pagination['page'] ?> / <?= $pagination['pages'] ?></p>
            <div class="flex gap-1">
            <?php for ($p = 1; $p <= $pagination['pages']; $p++): ?>
            <a href="?<?= http_build_query(array_merge($filters, ['page' => $p])) ?>"
               class="w-8 h-8 flex items-center justify-center rounded text-xs font-semibold
                      <?= $p === $pagination['page'] ? 'bg-vanilla-700 text-cream-100' : 'bg-cream-100 text-vanilla-500 hover:bg-vanilla-100' ?>">
                <?= $p ?>
            </a>
            <?php endfor; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
