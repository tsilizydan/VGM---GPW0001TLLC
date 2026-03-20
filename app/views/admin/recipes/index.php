<!-- ══════════════════════════════════════════════
     ADMIN — Recipe List
     ══════════════════════════════════════════════ -->
<?php /** @var list<array<string,mixed>> $recipes */ ?>
<section class="py-8">
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

    <div class="flex items-center justify-between mb-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-vanilla-400 mb-1">Administration › Recettes</p>
            <h1 class="font-serif font-bold text-vanilla-900 text-2xl">Gestion des recettes</h1>
        </div>
        <a href="<?= locale_url('admin/recipes/create') ?>" class="btn-primary btn btn-sm">+ Nouvelle recette</a>
    </div>

    <?php if ($flash = \Core\Session::getFlash('success')): ?>
    <div class="alert-success alert mb-4"><?= e($flash) ?></div>
    <?php endif; ?>

    <?php if (empty($recipes)): ?>
    <div class="glass-card rounded-2xl p-12 text-center text-vanilla-400">
        <p class="text-4xl mb-3">🍳</p>
        <p class="font-semibold text-vanilla-700">Aucune recette pour l'instant.</p>
        <a href="<?= locale_url('admin/recipes/create') ?>" class="btn-primary btn btn-sm mt-4 inline-flex">Créer la première</a>
    </div>
    <?php else: ?>
    <div class="glass-card rounded-2xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-vanilla-50 border-b border-vanilla-200">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-vanilla-600">Recette</th>
                    <th class="px-4 py-3 text-left font-semibold text-vanilla-600">Difficulté</th>
                    <th class="px-4 py-3 text-left font-semibold text-vanilla-600">Statut</th>
                    <th class="px-4 py-3 text-left font-semibold text-vanilla-600">Créée le</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-vanilla-100">
            <?php foreach ($recipes as $r): ?>
            <tr class="hover:bg-cream-50 transition-colors">
                <td class="px-4 py-3 font-semibold text-vanilla-800">
                    <?= e($r['title']) ?>
                    <span class="text-xs text-vanilla-400 font-normal block"><?= e($r['slug']) ?></span>
                </td>
                <td class="px-4 py-3 text-vanilla-600"><?= match($r['difficulty']) { 'hard' => '🔴 Difficile', 'medium' => '🟡 Moyen', default => '🟢 Facile' } ?></td>
                <td class="px-4 py-3">
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold
                        <?= $r['status'] === 'published' ? 'bg-forest-100 text-forest-700' : 'bg-cream-200 text-vanilla-500' ?>">
                        <?= $r['status'] === 'published' ? 'Publié' : 'Brouillon' ?>
                    </span>
                </td>
                <td class="px-4 py-3 text-vanilla-400 text-xs"><?= date('d/m/Y', strtotime($r['created_at'] ?? 'now')) ?></td>
                <td class="px-4 py-3 text-right whitespace-nowrap">
                    <a href="<?= locale_url('recipes/' . $r['slug']) ?>" target="_blank"
                       class="text-xs text-vanilla-400 hover:text-vanilla-600 mr-2">Voir</a>
                    <a href="<?= locale_url("admin/recipes/{$r['id']}/edit") ?>"
                       class="text-xs font-semibold text-vanilla-500 hover:text-vanilla-800 transition-colors">Modifier</a>
                    <form method="POST" action="<?= locale_url("admin/recipes/{$r['id']}/delete") ?>"
                          class="inline" onsubmit="return confirm('Supprimer cette recette ?')">
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
