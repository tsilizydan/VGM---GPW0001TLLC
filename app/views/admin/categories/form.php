<!-- ADMIN — Category Create/Edit Form -->
<section class="py-8">
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="font-serif font-bold text-vanilla-900 text-2xl"><?= e($title) ?></h1>
        <a href="<?= locale_url('admin/categories') ?>" class="text-sm font-semibold text-vanilla-500 hover:text-vanilla-800 transition-colors">← Retour</a>
    </div>

    <?php
    $isEdit = $category !== null;
    $action = $isEdit
        ? locale_url("admin/categories/{$category['id']}")
        : locale_url('admin/categories');
    $tr = $category['translations'] ?? [];
    ?>

    <form method="POST" action="<?= e($action) ?>" class="space-y-5">
        <?= csrf_field() ?>

        <div class="glass-card p-6 rounded-2xl space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Slug (URL)</label>
                    <input type="text" name="slug" value="<?= e($category['slug'] ?? '') ?>"
                           class="form-input font-mono text-sm" placeholder="extraits-bio">
                </div>
                <div>
                    <label class="form-label">Ordre d'affichage</label>
                    <input type="number" name="sort_order" value="<?= (int)($category['sort_order'] ?? 0) ?>"
                           min="0" class="form-input">
                </div>
            </div>
        </div>

        <?php foreach (['fr' => '🇫🇷 Français', 'en' => '🇬🇧 English', 'es' => '🇪🇸 Español'] as $lc => $lLabel): ?>
        <div class="glass-card p-5 rounded-2xl space-y-3">
            <h3 class="font-semibold text-vanilla-800"><?= $lLabel ?></h3>
            <div>
                <label class="form-label">Nom</label>
                <input type="text" name="<?= $lc ?>_name" value="<?= e($tr[$lc]['name'] ?? '') ?>"
                       class="form-input" placeholder="Nom de la catégorie">
            </div>
            <div>
                <label class="form-label">Description <span class="text-vanilla-400 text-xs font-normal">(optionnel)</span></label>
                <textarea name="<?= $lc ?>_description" rows="2" class="form-input resize-y"><?= e($tr[$lc]['description'] ?? '') ?></textarea>
            </div>
        </div>
        <?php endforeach; ?>

        <div class="flex items-center justify-between pt-2">
            <a href="<?= locale_url('admin/categories') ?>" class="btn-ghost btn">Annuler</a>
            <button type="submit" class="btn-primary btn">
                <?= $isEdit ? 'Enregistrer' : 'Créer la catégorie' ?>
            </button>
        </div>
    </form>
</div>
</section>
