<!-- ══════════════════════════════════════════════
     ADMIN — Recipe Create / Edit (TinyMCE steps)
     ══════════════════════════════════════════════ -->
<?php
/** @var array<string,mixed>|null $recipe */
/** @var list<array<string,mixed>> $products */
/** @var array<string,string> $locales */
$isEdit  = $recipe !== null;
$action  = $isEdit ? locale_url("admin/recipes/{$recipe['id']}") : locale_url('admin/recipes');
$tr      = $recipe['translations'] ?? [];
$linked  = array_column($recipe['products'] ?? [], 'id');
?>
<section x-data="{ tab: 'info' }" class="py-8">
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

    <div class="flex items-center justify-between mb-6">
        <h1 class="font-serif font-bold text-vanilla-900 text-2xl"><?= e($title) ?></h1>
        <a href="<?= locale_url('admin/recipes') ?>" class="text-sm font-semibold text-vanilla-500 hover:text-vanilla-800">← Retour</a>
    </div>

    <!-- Tab nav -->
    <div class="flex gap-1 mb-6 bg-cream-100 p-1 rounded-xl border border-vanilla-200/50 w-fit flex-wrap">
        <?php foreach (['info' => '📋 Infos', 'content' => '✍️ Contenu', 'products' => '🌿 Produits'] as $k => $l): ?>
        <button @click="tab = '<?= $k ?>'"
                :class="tab === '<?= $k ?>' ? 'bg-vanilla-700 text-cream-100 shadow-sm' : 'text-vanilla-500 hover:text-vanilla-700'"
                class="px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200"><?= $l ?></button>
        <?php endforeach; ?>
    </div>

    <form method="POST" action="<?= e($action) ?>" enctype="multipart/form-data" class="space-y-0">
        <?= csrf_field() ?>

        <!-- ── TAB: Infos ── -->
        <div x-show="tab === 'info'" class="space-y-5">
            <div class="glass-card p-6 rounded-2xl space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Slug</label>
                        <input type="text" name="slug" value="<?= e($recipe['slug'] ?? '') ?>" class="form-input font-mono text-sm" placeholder="creme-brulee-vanille">
                        <p class="form-hint">Généré depuis le titre FR si laissé vide.</p>
                    </div>
                    <div>
                        <label class="form-label">Statut</label>
                        <select name="status" class="form-input">
                            <option value="published" <?= ($recipe['status'] ?? '') === 'published' ? 'selected' : '' ?>>Publié</option>
                            <option value="draft" <?= ($recipe['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Brouillon</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div>
                        <label class="form-label">Préparation (min)</label>
                        <input type="number" name="prep_time" min="0" value="<?= (int)($recipe['prep_time'] ?? '') ?>" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Cuisson (min)</label>
                        <input type="number" name="cook_time" min="0" value="<?= (int)($recipe['cook_time'] ?? '') ?>" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Personnes</label>
                        <input type="number" name="servings" min="1" value="<?= (int)($recipe['servings'] ?? '') ?>" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Difficulté</label>
                        <select name="difficulty" class="form-input">
                            <?php foreach (['easy' => '🟢 Facile', 'medium' => '🟡 Moyen', 'hard' => '🔴 Difficile'] as $v => $l): ?>
                            <option value="<?= $v ?>" <?= ($recipe['difficulty'] ?? 'easy') === $v ? 'selected' : '' ?>><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Cover image -->
                <div>
                    <label class="form-label">Image de couverture</label>
                    <?php if ($isEdit && ($recipe['cover_image'] ?? '')): ?>
                    <div class="mb-2 w-32 rounded-xl overflow-hidden border border-vanilla-200">
                        <img src="<?= e($recipe['cover_image']) ?>" class="w-full aspect-video object-cover">
                    </div>
                    <?php endif; ?>
                    <input type="file" name="cover_image" accept="image/*"
                           class="text-xs text-vanilla-500 file:btn-ghost file:btn file:btn-sm file:text-xs file:py-1 file:mr-2">
                </div>
            </div>
        </div>

        <!-- ── TAB: Content (per locale) ── -->
        <div x-show="tab === 'content'" class="space-y-5">
            <?php foreach ($locales as $lc => $lcLabel): ?>
            <div class="glass-card p-6 rounded-2xl space-y-4">
                <h3 class="font-serif font-semibold text-vanilla-800"><?= $lcLabel ?></h3>
                <div>
                    <label class="form-label">Titre</label>
                    <input type="text" name="<?= $lc ?>_title" value="<?= e($tr[$lc]['title'] ?? '') ?>"
                           class="form-input" placeholder="ex: Crème brûlée à la vanille de Madagascar">
                </div>
                <div>
                    <label class="form-label">Introduction</label>
                    <textarea name="<?= $lc ?>_intro" rows="2" class="form-input resize-y"
                              placeholder="Courte introduction mise en avant sur la carte…"><?= e($tr[$lc]['intro'] ?? '') ?></textarea>
                </div>
                <div>
                    <label class="form-label">Ingrédients <span class="text-vanilla-400 text-xs font-normal">(une ligne par ingrédient, préfixe – optionnel)</span></label>
                    <textarea name="<?= $lc ?>_ingredients" rows="6" class="form-input resize-y font-mono text-sm"
                              placeholder="– 4 jaunes d'œufs&#10;– 300 ml de crème entière&#10;– 1 gousse de vanille"><?= e($tr[$lc]['ingredients'] ?? '') ?></textarea>
                </div>
                <div>
                    <label class="form-label">Étapes de préparation <span class="text-vanilla-400 text-xs font-normal">(éditeur riche)</span></label>
                    <textarea id="steps_<?= $lc ?>" name="<?= $lc ?>_steps"
                              class="form-input" style="min-height:200px"><?= $tr[$lc]['steps'] ?? '' ?></textarea>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- ── TAB: Linked products ── -->
        <div x-show="tab === 'products'" class="space-y-4">
            <div class="glass-card p-6 rounded-2xl">
                <h3 class="font-semibold text-vanilla-800 mb-4">Produits utilisés dans cette recette</h3>
                <p class="text-xs text-vanilla-400 mb-4">Cochez les produits inclus dans cette recette. Ils s'afficheront dans le sidebar de la recette et dans la section « Recettes » sur la fiche produit.</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <?php foreach ($products as $p): ?>
                <label class="flex items-center gap-3 p-3 rounded-xl border border-vanilla-200 cursor-pointer hover:border-vanilla-400 transition-colors has-[:checked]:border-vanilla-700 has-[:checked]:bg-vanilla-50">
                    <input type="checkbox" name="linked_products[]" value="<?= $p['id'] ?>"
                           <?= in_array((int)$p['id'], array_map('intval', $linked), true) ? 'checked' : '' ?>
                           class="rounded border-vanilla-300 text-vanilla-700">
                    <div>
                        <p class="text-sm font-semibold text-vanilla-800"><?= e($p['name']) ?></p>
                        <p class="text-xs text-vanilla-400"><?= number_format((float)$p['price'], 2, ',', ' ') ?> €</p>
                    </div>
                </label>
                <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between pt-6 border-t border-vanilla-100">
            <a href="<?= locale_url('admin/recipes') ?>" class="btn-ghost btn">Annuler</a>
            <button type="submit" class="btn-primary btn"><?= $isEdit ? 'Enregistrer' : 'Créer la recette' ?></button>
        </div>
    </form>
</div>
</section>

<!-- TinyMCE for steps fields -->
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
<script>
tinymce.init({
    selector: '[id^="steps_"]',
    plugins: 'lists link image',
    toolbar: 'undo redo | bold italic | numlist bullist | link image | removeformat',
    menubar: false,
    height: 250,
    skin: 'oxide',
    content_css: 'default',
});
</script>
