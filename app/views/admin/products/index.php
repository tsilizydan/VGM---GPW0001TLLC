<!-- ══════════════════════════════════════════════
     ADMIN — Product List
     ══════════════════════════════════════════════ -->
<section class="py-8">
<div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-vanilla-400 mb-1">Administration</p>
            <h1 class="font-serif font-bold text-vanilla-900 text-2xl"><?= e($title) ?></h1>
        </div>
        <a href="<?= locale_url('admin/products/create') ?>"
           class="btn-primary btn inline-flex">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Nouveau produit
        </a>
    </div>

    <!-- Filters -->
    <form method="GET" action="" class="flex flex-wrap gap-2 mb-5 items-end">
        <input type="text" name="q" value="<?= e($filters['search']) ?>" placeholder="Rechercher…"
               class="form-input text-sm py-2 w-52">
        <select name="category" class="form-input text-sm py-2 w-44">
            <option value="">Toutes catégories</option>
            <?php foreach ($categories as $cat): ?>
            <option value="<?= e($cat['slug']) ?>" <?= $filters['category'] === $cat['slug'] ? 'selected' : '' ?>>
                <?= e($cat['name']) ?>
            </option>
            <?php endforeach; ?>
        </select>
        <select name="status" class="form-input text-sm py-2 w-36">
            <option value="">Tous statuts</option>
            <option value="active"   <?= $filters['status'] === 'active'   ? 'selected' : '' ?>>Actif</option>
            <option value="draft"    <?= $filters['status'] === 'draft'    ? 'selected' : '' ?>>Brouillon</option>
            <option value="archived" <?= $filters['status'] === 'archived' ? 'selected' : '' ?>>Archivé</option>
        </select>
        <select name="sort" class="form-input text-sm py-2 w-40">
            <option value="newest"     <?= $filters['sort'] === 'newest'     ? 'selected' : '' ?>>Plus récents</option>
            <option value="price_asc"  <?= $filters['sort'] === 'price_asc'  ? 'selected' : '' ?>>Prix ↑</option>
            <option value="price_desc" <?= $filters['sort'] === 'price_desc' ? 'selected' : '' ?>>Prix ↓</option>
        </select>
        <button type="submit" class="btn-primary btn py-2 px-4 text-sm">Filtrer</button>
        <a href="<?= locale_url('admin/products') ?>" class="btn-ghost btn py-2 px-4 text-sm">Reset</a>
    </form>

    <!-- Table -->
    <div class="bg-white/80 backdrop-blur-md border border-white/60 rounded-2xl shadow-glass overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-vanilla-800 text-cream-100 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-left w-16"></th>
                        <th class="px-4 py-3 text-left">Produit</th>
                        <th class="px-4 py-3 text-left">Catégorie</th>
                        <th class="px-4 py-3 text-right">Prix</th>
                        <th class="px-4 py-3 text-right">Stock</th>
                        <th class="px-4 py-3 text-center">Statut</th>
                        <th class="px-4 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-vanilla-100">
                <?php foreach ($result['data'] as $product): ?>
                    <tr class="hover:bg-cream-50 transition-colors duration-150">
                        <!-- Thumbnail -->
                        <td class="px-4 py-3">
                            <?php if ($product['primary_image']): ?>
                            <img src="<?= e($product['primary_image']) ?>"
                                 alt="<?= e($product['name']) ?>"
                                 class="w-12 h-12 rounded-lg object-cover border border-vanilla-100">
                            <?php else: ?>
                            <div class="w-12 h-12 rounded-lg bg-cream-200 flex items-center justify-center text-vanilla-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M2.25 12V6.75A2.25 2.25 0 0 1 4.5 4.5h15A2.25 2.25 0 0 1 21.75 6.75V17.25A2.25 2.25 0 0 1 19.5 19.5H4.5A2.25 2.25 0 0 1 2.25 17.25V12Z"/>
                                </svg>
                            </div>
                            <?php endif; ?>
                        </td>
                        <!-- Name + slug -->
                        <td class="px-4 py-3">
                            <p class="font-semibold text-vanilla-800"><?= e($product['name']) ?></p>
                            <code class="text-[11px] text-vanilla-400"><?= e($product['slug']) ?></code>
                            <?php if ($product['featured']): ?>
                            <span class="ml-1 inline-block text-[10px] bg-gold-100 text-gold-700 border border-gold-300 px-1.5 rounded-pill font-semibold uppercase">⭐ Vedette</span>
                            <?php endif; ?>
                        </td>
                        <!-- Category -->
                        <td class="px-4 py-3 text-vanilla-500">
                            <?= e($product['category_name'] ?? '—') ?>
                        </td>
                        <!-- Price -->
                        <td class="px-4 py-3 text-right font-semibold text-vanilla-800">
                            <?= number_format((float)$product['price'], 2, ',', ' ') ?> €
                        </td>
                        <!-- Stock -->
                        <td class="px-4 py-3 text-right">
                            <?php
                            $stock = (int)$product['stock'];
                            $cls = $stock === 0 ? 'text-red-600 bg-red-50' : ($stock <= 5 ? 'text-amber-700 bg-amber-50' : 'text-forest-700 bg-forest-50');
                            ?>
                            <span class="inline-flex items-center justify-center min-w-[2rem] h-6 px-2 rounded-full text-xs font-bold <?= $cls ?>">
                                <?= $stock ?>
                            </span>
                        </td>
                        <!-- Status -->
                        <td class="px-4 py-3 text-center">
                            <?php
                            $statusMap = [
                                'active'   => ['bg-forest-100 text-forest-700', 'Actif'],
                                'draft'    => ['bg-cream-200 text-vanilla-500', 'Brouillon'],
                                'archived' => ['bg-red-100 text-red-600', 'Archivé'],
                            ];
                            [$cls, $lbl] = $statusMap[$product['status']] ?? ['bg-gray-100 text-gray-500', $product['status']];
                            ?>
                            <span class="inline-block text-xs font-semibold px-2.5 py-0.5 rounded-pill <?= $cls ?>">
                                <?= $lbl ?>
                            </span>
                        </td>
                        <!-- Actions -->
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <a href="<?= locale_url("admin/products/{$product['id']}/edit") ?>"
                                   class="p-1.5 rounded-lg hover:bg-vanilla-100 text-vanilla-500 hover:text-vanilla-800 transition-colors"
                                   title="Modifier">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/>
                                    </svg>
                                </a>
                                <a href="<?= locale_url("shop/{$product['slug']}") ?>" target="_blank"
                                   class="p-1.5 rounded-lg hover:bg-forest-50 text-vanilla-400 hover:text-forest-700 transition-colors"
                                   title="Voir">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178Z"/>
                                        <path stroke-linecap="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                    </svg>
                                </a>
                                <form method="POST" action="<?= locale_url("admin/products/{$product['id']}/delete") ?>"
                                      onsubmit="return confirm('Supprimer ce produit ?')">
                                    <?= csrf_field() ?>
                                    <button type="submit"
                                            class="p-1.5 rounded-lg hover:bg-red-50 text-vanilla-400 hover:text-red-600 transition-colors"
                                            title="Supprimer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($result['data'])): ?>
                    <tr><td colspan="7" class="py-16 text-center text-vanilla-400 text-sm">Aucun produit trouvé.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($result['pages'] > 1): ?>
        <div class="px-4 py-3 border-t border-vanilla-100 flex items-center justify-between">
            <p class="text-xs text-vanilla-400"><?= $result['total'] ?> produits — page <?= $result['page'] ?> / <?= $result['pages'] ?></p>
            <div class="flex gap-1">
                <?php for ($p = 1; $p <= $result['pages']; $p++): ?>
                <a href="?<?= http_build_query(array_merge($filters, ['page' => $p])) ?>"
                   class="px-3 py-1 rounded text-xs font-semibold <?= $p === (int)$result['page'] ? 'bg-vanilla-700 text-cream-100' : 'bg-cream-100 text-vanilla-600 hover:bg-vanilla-100' ?>">
                    <?= $p ?>
                </a>
                <?php endfor; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Quick links -->
    <div class="mt-4 flex gap-3">
        <a href="<?= locale_url('admin/categories') ?>" class="text-sm text-vanilla-500 hover:text-vanilla-800 transition-colors">
            → Gérer les catégories
        </a>
    </div>
</div>
</section>
