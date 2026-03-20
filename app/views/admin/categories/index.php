<!-- ADMIN — Categories -->
<section class="py-8">
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="font-serif font-bold text-vanilla-900 text-2xl">Catégories</h1>
        <a href="<?= locale_url('admin/categories/create') ?>" class="btn-primary btn btn-sm">+ Nouvelle catégorie</a>
    </div>
    <div class="bg-white/80 backdrop-blur-md border border-white/60 rounded-2xl shadow-glass overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-vanilla-800 text-cream-100 text-xs uppercase tracking-wider">
                <tr>
                    <th class="px-4 py-3 text-left">Slug</th>
                    <th class="px-4 py-3 text-left">Nom (FR)</th>
                    <th class="px-4 py-3 text-center">Ordre</th>
                    <th class="px-4 py-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-vanilla-100">
            <?php foreach ($categories as $cat): ?>
                <tr class="hover:bg-cream-50 transition-colors">
                    <td class="px-4 py-3"><code class="text-xs text-vanilla-500 bg-cream-100 px-2 py-0.5 rounded"><?= e($cat['slug']) ?></code></td>
                    <td class="px-4 py-3 font-semibold text-vanilla-800"><?= e($cat['name']) ?></td>
                    <td class="px-4 py-3 text-center text-vanilla-500"><?= (int)$cat['sort_order'] ?></td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-center gap-2">
                            <a href="<?= locale_url("admin/categories/{$cat['id']}/edit") ?>"
                               class="p-1.5 rounded-lg hover:bg-vanilla-100 text-vanilla-500 hover:text-vanilla-800 transition-colors" title="Modifier">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/>
                                </svg>
                            </a>
                            <form method="POST" action="<?= locale_url("admin/categories/{$cat['id']}/delete") ?>"
                                  onsubmit="return confirm('Supprimer cette catégorie et ses traductions ?')">
                                <?= csrf_field() ?>
                                <button type="submit" class="p-1.5 rounded-lg hover:bg-red-50 text-vanilla-400 hover:text-red-600 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($categories)): ?>
                <tr><td colspan="4" class="py-12 text-center text-vanilla-400">Aucune catégorie.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="mt-4"><a href="<?= locale_url('admin/products') ?>" class="text-sm text-vanilla-500 hover:text-vanilla-800 transition-colors">← Retour aux produits</a></div>
</div>
</section>
