<!-- ADMIN — Customers list -->
<div class="space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <h1 class="font-serif font-bold text-vanilla-900 text-2xl">Clients</h1>
        <span class="text-sm text-vanilla-500"><?= $pagination['total'] ?> client<?= $pagination['total'] > 1 ? 's' : '' ?></span>
    </div>

    <!-- Search -->
    <form method="GET" class="flex gap-2">
        <input type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="Nom ou email…" class="form-input text-sm py-2 w-72">
        <button class="btn-primary btn py-2 px-4 text-sm">Rechercher</button>
        <?php if ($filters['q']): ?>
        <a href="<?= locale_url('admin/customers') ?>" class="btn-ghost btn py-2 px-4 text-sm">Reset</a>
        <?php endif; ?>
    </form>

    <!-- Table -->
    <div class="bg-white/80 border border-vanilla-200/60 rounded-2xl shadow-soft overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-vanilla-800 text-cream-100 text-xs uppercase tracking-wider">
                <tr>
                    <th class="px-4 py-3 text-left">Client</th>
                    <th class="px-4 py-3 text-left">Email</th>
                    <th class="px-4 py-3 text-center">Rôle</th>
                    <th class="px-4 py-3 text-center">Commandes</th>
                    <th class="px-4 py-3 text-right">Dépenses totales</th>
                    <th class="px-4 py-3 text-right">Inscrit le</th>
                    <th class="px-4 py-3 w-10"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-vanilla-50">
            <?php foreach ($customers as $c): ?>
            <tr class="hover:bg-cream-50 transition-colors">
                <td class="px-4 py-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-full bg-vanilla-200 flex items-center justify-center text-vanilla-700 font-bold text-xs shrink-0">
                            <?= mb_strtoupper(mb_substr($c['name'] ?? '?', 0, 1)) ?>
                        </div>
                        <span class="font-semibold text-vanilla-800"><?= e($c['name']) ?></span>
                    </div>
                </td>
                <td class="px-4 py-3 text-vanilla-500"><?= e($c['email']) ?></td>
                <td class="px-4 py-3 text-center">
                    <span class="inline-block text-[10px] px-2 py-0.5 rounded-full font-bold
                        <?= $c['role'] === 'admin' ? 'bg-gold-100 text-gold-700' : 'bg-cream-200 text-vanilla-600' ?>">
                        <?= e($c['role']) ?>
                    </span>
                </td>
                <td class="px-4 py-3 text-center font-semibold text-vanilla-700"><?= (int)$c['order_count'] ?></td>
                <td class="px-4 py-3 text-right font-bold text-vanilla-800"><?= number_format((float)$c['lifetime_value'], 2, ',', ' ') ?> €</td>
                <td class="px-4 py-3 text-right text-xs text-vanilla-400"><?= date('d/m/Y', strtotime($c['created_at'])) ?></td>
                <td class="px-4 py-3">
                    <a href="<?= locale_url("admin/customers/{$c['id']}") ?>"
                       class="p-1.5 rounded hover:bg-vanilla-100 text-vanilla-400 hover:text-vanilla-700 transition-colors block">→</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($customers)): ?>
            <tr><td colspan="7" class="py-12 text-center text-vanilla-400">Aucun client trouvé.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        </div>
        <?php if ($pagination['pages'] > 1): ?>
        <div class="px-4 py-3 border-t border-vanilla-100 flex justify-between items-center">
            <p class="text-xs text-vanilla-400">Page <?= $pagination['page'] ?> / <?= $pagination['pages'] ?></p>
            <div class="flex gap-1">
            <?php for ($p = 1; $p <= min(8, $pagination['pages']); $p++): ?>
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
