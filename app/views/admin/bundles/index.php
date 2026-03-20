<!-- ══════════════════════════════════════════════
     ADMIN — Bundle List
     ══════════════════════════════════════════════ -->
<?php /** @var list<array<string,mixed>> $bundles */ ?>
<section class="py-8">
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

    <div class="flex items-center justify-between mb-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-vanilla-400 mb-1">Administration › Offres</p>
            <h1 class="font-serif font-bold text-vanilla-900 text-2xl">Offres groupées</h1>
        </div>
        <a href="<?= locale_url('admin/bundles/create') ?>" class="btn-primary btn btn-sm">+ Nouveau bundle</a>
    </div>

    <?php if ($flash = \Core\Session::getFlash('success')): ?>
    <div class="alert-success alert mb-4"><?= e($flash) ?></div>
    <?php endif; ?>

    <?php if (empty($bundles)): ?>
    <div class="glass-card rounded-2xl p-12 text-center text-vanilla-400">
        <p class="text-4xl mb-3">🎁</p>
        <p class="font-semibold text-vanilla-700">Aucun bundle créé pour l'instant.</p>
        <a href="<?= locale_url('admin/bundles/create') ?>" class="btn-primary btn btn-sm mt-4 inline-flex">Créer le premier</a>
    </div>
    <?php else: ?>
    <div class="glass-card rounded-2xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-vanilla-50 border-b border-vanilla-200">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-vanilla-600">Nom</th>
                    <th class="px-4 py-3 text-left font-semibold text-vanilla-600">Remise</th>
                    <th class="px-4 py-3 text-left font-semibold text-vanilla-600">Statut</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-vanilla-100">
            <?php foreach ($bundles as $b): ?>
            <tr class="hover:bg-cream-50 transition-colors">
                <td class="px-4 py-3 font-semibold text-vanilla-800"><?= e($b['name']) ?></td>
                <td class="px-4 py-3 text-vanilla-600">
                    <?php if ((float)$b['discount'] > 0): ?>
                    −<?= number_format((float)$b['discount'], 2, ',', ' ') ?> €
                    <?php elseif ((int)$b['discount_pct'] > 0): ?>
                    −<?= (int)$b['discount_pct'] ?>%
                    <?php else: ?>
                    <span class="text-vanilla-400">—</span>
                    <?php endif; ?>
                </td>
                <td class="px-4 py-3">
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold
                        <?= $b['status'] === 'active' ? 'bg-forest-100 text-forest-700' : 'bg-cream-200 text-vanilla-500' ?>">
                        <?= $b['status'] === 'active' ? 'Actif' : 'Brouillon' ?>
                    </span>
                </td>
                <td class="px-4 py-3 text-right">
                    <a href="<?= locale_url("admin/bundles/{$b['id']}/edit") ?>"
                       class="text-xs font-semibold text-vanilla-500 hover:text-vanilla-800 transition-colors">Modifier</a>
                    <form method="POST" action="<?= locale_url("admin/bundles/{$b['id']}/delete") ?>"
                          class="inline" onsubmit="return confirm('Supprimer ce bundle ?')">
                        <?= csrf_field() ?>
                        <button type="submit" class="ml-3 text-xs text-red-400 hover:text-red-600 font-semibold">Supprimer</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
</section>
