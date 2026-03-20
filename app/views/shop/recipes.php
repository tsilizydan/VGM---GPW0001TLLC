<!-- ══════════════════════════════════════════════
     SHOP — Recipe Listing Page
     ══════════════════════════════════════════════ -->
<?php
/** @var list<array<string,mixed>> $recipes */
/** @var array{data:list<array>, total:int, pages:int, page:int} $pagination */
$noImg = '/assets/img/placeholder-product.svg';
?>

<!-- Hero -->
<div class="bg-vanilla-800 py-16 relative overflow-hidden">
    <div class="absolute inset-0 malagasy-weave opacity-10"></div>
    <div class="absolute inset-0 bg-gradient-to-br from-vanilla-900/60 to-forest-900/40"></div>
    <div class="relative max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <p class="text-xs font-bold uppercase tracking-widest text-gold-400 mb-3">Inspirations</p>
        <h1 class="font-serif font-bold text-cream-100 text-5xl mb-3">Recettes à la Vanille</h1>
        <p class="text-cream-100/70 max-w-xl mx-auto">Découvrez comment sublimer vos créations culinaires avec notre vanille de Madagascar.</p>
    </div>
</div>

<!-- Grid -->
<section class="section">
<div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">

    <?php if (empty($recipes)): ?>
    <div class="py-24 text-center">
        <p class="text-5xl mb-4">🌿</p>
        <h2 class="font-serif text-vanilla-700 text-2xl">Aucune recette publiée pour l'instant</h2>
        <p class="text-vanilla-400 text-sm mt-2">Revenez bientôt pour découvrir nos inspirations culinaires.</p>
    </div>
    <?php else: ?>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
    <?php foreach ($recipes as $recipe): ?>
    <?php
    $cover = $recipe['cover_image'] ?? $noImg;
    $total = ($recipe['prep_time'] ?? 0) + ($recipe['cook_time'] ?? 0);
    $diff  = match($recipe['difficulty'] ?? 'easy') { 'hard' => ['Difficile','bg-red-100 text-red-700'], 'medium' => ['Moyen','bg-amber-100 text-amber-700'], default => ['Facile','bg-forest-100 text-forest-700'] };
    ?>
    <article class="group bg-white rounded-3xl overflow-hidden shadow-soft hover:shadow-xl transition-shadow duration-300 border border-vanilla-100">
        <a href="<?= locale_url('recipes/' . $recipe['slug']) ?>" class="block relative aspect-video overflow-hidden">
            <img src="<?= e($cover) ?>" alt="<?= e($recipe['title']) ?>"
                 class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" loading="lazy">
            <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
            <!-- Badges -->
            <div class="absolute bottom-3 left-3 flex gap-2 flex-wrap">
                <?php if ($total): ?>
                <span class="text-[10px] font-bold bg-black/50 text-white backdrop-blur-sm px-2 py-1 rounded-full">⏱ <?= $total ?> min</span>
                <?php endif; ?>
                <?php if ($recipe['servings'] ?? null): ?>
                <span class="text-[10px] font-bold bg-black/50 text-white backdrop-blur-sm px-2 py-1 rounded-full">🍽 <?= $recipe['servings'] ?> pers.</span>
                <?php endif; ?>
                <span class="text-[10px] font-bold px-2 py-1 rounded-full <?= $diff[1] ?>"><?= $diff[0] ?></span>
            </div>
        </a>
        <div class="p-5">
            <h2 class="font-serif font-bold text-vanilla-900 text-lg line-clamp-2 mb-2 group-hover:text-vanilla-600 transition-colors">
                <a href="<?= locale_url('recipes/' . $recipe['slug']) ?>"><?= e($recipe['title']) ?></a>
            </h2>
            <?php if ($recipe['intro'] ?? ''): ?>
            <p class="text-sm text-vanilla-500 line-clamp-3 leading-relaxed"><?= e($recipe['intro']) ?></p>
            <?php endif; ?>
            <a href="<?= locale_url('recipes/' . $recipe['slug']) ?>"
               class="inline-flex items-center gap-1 mt-4 text-xs font-semibold text-vanilla-600 hover:text-vanilla-800 transition-colors">
                Voir la recette →
            </a>
        </div>
    </article>
    <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($pagination['pages'] > 1): ?>
    <div class="flex justify-center gap-2 pb-6">
        <?php for ($p = 1; $p <= $pagination['pages']; $p++): ?>
        <a href="?page=<?= $p ?>"
           class="w-9 h-9 rounded-xl flex items-center justify-center text-sm font-semibold transition-all
                  <?= $p === $pagination['page'] ? 'bg-vanilla-700 text-cream-100' : 'bg-cream-100 text-vanilla-600 hover:bg-vanilla-100' ?>">
            <?= $p ?>
        </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>

    <?php endif; ?>
</div>
</section>
