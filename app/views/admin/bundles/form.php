<!-- ══════════════════════════════════════════════
     ADMIN — Bundle Create / Edit
     ══════════════════════════════════════════════ -->
<?php
/** @var array<string,mixed>|null $bundle */
/** @var list<array<string,mixed>> $products  */
/** @var array<string,string> $locales */
$isEdit  = $bundle !== null;
$action  = $isEdit ? locale_url("admin/bundles/{$bundle['id']}") : locale_url('admin/bundles');
$tr      = $bundle['translations'] ?? [];
$items   = $bundle['items'] ?? [];
// Build a map: product_id => qty for existing items
$itemMap = [];
foreach ($items as $it) { $itemMap[(int)$it['product_id']] = (int)$it['qty']; }
?>
<section x-data="{ items: <?= json_encode(
    array_values(array_map(fn($it) => ['product_id' => (int)$it['product_id'], 'qty' => (int)$it['qty']], $items)),
    JSON_HEX_TAG) ?> }" class="py-8">
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

    <div class="flex items-center justify-between mb-6">
        <h1 class="font-serif font-bold text-vanilla-900 text-2xl"><?= e($title) ?></h1>
        <a href="<?= locale_url('admin/bundles') ?>" class="text-sm font-semibold text-vanilla-500 hover:text-vanilla-800">← Retour</a>
    </div>

    <form method="POST" action="<?= e($action) ?>" class="space-y-6">
        <?= csrf_field() ?>

        <!-- Traductions -->
        <?php foreach ($locales as $lc => $lcLabel): ?>
        <div class="glass-card p-5 rounded-2xl space-y-3">
            <h3 class="font-semibold text-vanilla-800"><?= $lcLabel ?></h3>
            <div>
                <label class="form-label">Nom</label>
                <input type="text" name="<?= $lc ?>_name" value="<?= e($tr[$lc]['name'] ?? '') ?>"
                       class="form-input" placeholder="Nom du bundle en <?= $lcLabel ?>">
            </div>
            <div>
                <label class="form-label">Description (courte)</label>
                <textarea name="<?= $lc ?>_description" rows="2" class="form-input resize-none"><?= e($tr[$lc]['description'] ?? '') ?></textarea>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- Settings -->
        <div class="glass-card p-5 rounded-2xl space-y-4">
            <h3 class="font-semibold text-vanilla-800">Paramètres</h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div>
                    <label class="form-label">Slug</label>
                    <input type="text" name="slug" value="<?= e($bundle['slug'] ?? '') ?>" class="form-input text-sm font-mono">
                </div>
                <div>
                    <label class="form-label">Remise flat (€)</label>
                    <input type="number" name="discount" step="0.01" min="0" value="<?= (float)($bundle['discount'] ?? 0) ?>" class="form-input">
                </div>
                <div>
                    <label class="form-label">Remise (%)</label>
                    <input type="number" name="discount_pct" min="0" max="100" value="<?= (int)($bundle['discount_pct'] ?? 0) ?>" class="form-input">
                </div>
                <div>
                    <label class="form-label">Statut</label>
                    <select name="status" class="form-input">
                        <option value="active" <?= ($bundle['status'] ?? '') === 'active' ? 'selected' : '' ?>>Actif</option>
                        <option value="draft"  <?= ($bundle['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Brouillon</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Products in bundle -->
        <div class="glass-card p-5 rounded-2xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-vanilla-800">Produits inclus</h3>
                <button type="button" @click="items.push({ product_id: '', qty: 1 })" class="btn-ghost btn btn-sm text-xs">+ Ajouter</button>
            </div>

            <div class="space-y-3">
            <template x-for="(item, i) in items" :key="i">
                <div class="flex items-center gap-3 p-3 rounded-xl bg-cream-50 border border-vanilla-100">
                    <select :name="`items[${i}][product_id]`" x-model="item.product_id" class="form-input flex-1 text-sm py-2">
                        <option value="">— Choisir un produit —</option>
                        <?php foreach ($products as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= e($p['name']) ?> (<?= number_format((float)$p['price'], 2, ',', '  ') ?> €)</option>
                        <?php endforeach; ?>
                    </select>
                    <div class="flex items-center gap-1 shrink-0">
                        <label class="text-xs text-vanilla-500 whitespace-nowrap">Qté</label>
                        <input type="number" :name="`items[${i}][qty]`" x-model="item.qty" min="1" class="form-input w-16 text-sm py-2">
                    </div>
                    <button type="button" @click="items.splice(i, 1)" class="text-red-400 hover:text-red-600 text-sm shrink-0">✕</button>
                </div>
            </template>
            <p x-show="items.length === 0" class="text-sm text-vanilla-400 text-center py-4">
                Aucun produit. Cliquez sur « + Ajouter » pour commencer.
            </p>
            </div>
        </div>

        <div class="flex items-center justify-between pt-4 border-t border-vanilla-100">
            <a href="<?= locale_url('admin/bundles') ?>" class="btn-ghost btn">Annuler</a>
            <button type="submit" class="btn-primary btn"><?= $isEdit ? 'Enregistrer' : 'Créer le bundle' ?></button>
        </div>
    </form>
</div>
</section>
