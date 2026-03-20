<!-- ══════════════════════════════════════════════
     SHOP — Recipe Detail Page
     ══════════════════════════════════════════════ -->
<?php
/** @var array<string,mixed> $recipe */
$noImg = '/assets/img/placeholder-product.svg';
$cover = $recipe['cover_image'] ?? $noImg;
$total = ($recipe['prep_time'] ?? 0) + ($recipe['cook_time'] ?? 0);
$diffLabel = match($recipe['difficulty'] ?? 'easy') { 'hard' => 'Difficile', 'medium' => 'Moyen', default => 'Facile' };
?>

<!-- Hero -->
<div class="relative h-72 md:h-96 overflow-hidden">
    <img src="<?= e($cover) ?>" alt="<?= e($recipe['title']) ?>"
         class="w-full h-full object-cover scale-105" style="filter:brightness(0.65)">
    <div class="absolute inset-0 bg-gradient-to-t from-vanilla-900/80 via-vanilla-900/30 to-transparent"></div>
    <div class="absolute bottom-0 left-0 right-0 max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 pb-10">
        <nav class="flex items-center gap-2 text-xs text-cream-100/60 mb-3">
            <a href="<?= locale_url() ?>" class="hover:text-cream-100 transition-colors">Accueil</a>
            <span>/</span>
            <a href="<?= locale_url('recipes') ?>" class="hover:text-cream-100 transition-colors">Recettes</a>
            <span>/</span>
            <span class="text-cream-100/80 line-clamp-1"><?= e($recipe['title']) ?></span>
        </nav>
        <h1 class="font-serif font-bold text-cream-100 text-4xl md:text-5xl leading-tight mb-4">
            <?= e($recipe['title']) ?>
        </h1>
        <!-- Meta pills -->
        <div class="flex flex-wrap gap-2">
            <?php if ($recipe['prep_time'] ?? null): ?>
            <span class="px-3 py-1 rounded-full bg-white/15 backdrop-blur-sm text-cream-100 text-xs font-semibold">🔪 Prépa <?= $recipe['prep_time'] ?> min</span>
            <?php endif; ?>
            <?php if ($recipe['cook_time'] ?? null): ?>
            <span class="px-3 py-1 rounded-full bg-white/15 backdrop-blur-sm text-cream-100 text-xs font-semibold">🔥 Cuisson <?= $recipe['cook_time'] ?> min</span>
            <?php endif; ?>
            <?php if ($recipe['servings'] ?? null): ?>
            <span class="px-3 py-1 rounded-full bg-white/15 backdrop-blur-sm text-cream-100 text-xs font-semibold">🍽 <?= $recipe['servings'] ?> personnes</span>
            <?php endif; ?>
            <span class="px-3 py-1 rounded-full bg-gold-500/80 backdrop-blur-sm text-white text-xs font-semibold">👨‍🍳 <?= $diffLabel ?></span>
        </div>
    </div>
</div>

<!-- Content -->
<div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-12">
<div class="grid grid-cols-1 lg:grid-cols-3 gap-12">

    <!-- ── Main content ── -->
    <div class="lg:col-span-2 space-y-10">

        <?php if ($recipe['intro'] ?? ''): ?>
        <div class="lead text-vanilla-600 text-lg leading-relaxed border-l-4 border-gold-400 pl-5 italic">
            <?= e($recipe['intro']) ?>
        </div>
        <?php endif; ?>

        <!-- Ingredients -->
        <?php if ($recipe['ingredients'] ?? ''): ?>
        <div>
            <h2 class="font-serif font-bold text-vanilla-900 text-2xl mb-5 flex items-center gap-2">
                <span class="text-2xl">🧾</span> Ingrédients
            </h2>
            <div class="glass-card rounded-2xl p-6">
                <ul class="space-y-2">
                <?php
                $lines = array_filter(array_map('trim', explode("\n", $recipe['ingredients'])));
                foreach ($lines as $line):
                    $line = ltrim($line, '-* ');
                    if (!$line) continue;
                ?>
                <li class="flex items-start gap-2 text-sm text-vanilla-700">
                    <span class="mt-1 w-1.5 h-1.5 rounded-full bg-gold-500 shrink-0"></span>
                    <?= e($line) ?>
                </li>
                <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>

        <!-- Steps (raw TinyMCE HTML) -->
        <?php if ($recipe['steps'] ?? ''): ?>
        <div>
            <h2 class="font-serif font-bold text-vanilla-900 text-2xl mb-5 flex items-center gap-2">
                <span class="text-2xl">📋</span> Préparation
            </h2>
            <div class="prose prose-vanilla max-w-none text-vanilla-700 leading-relaxed recipe-steps">
                <?= $recipe['steps'] /* TinyMCE HTML — already sanitised on save */ ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- ── Sidebar: linked products ── -->
    <?php if (!empty($recipe['products'])): ?>
    <aside class="space-y-4">
        <div class="glass-card rounded-2xl p-5 border border-gold-200/50 sticky top-24">
            <h3 class="font-serif font-semibold text-vanilla-900 mb-4 flex items-center gap-2">
                <span class="text-xl">🌿</span>
                Produits utilisés
            </h3>
            <div class="space-y-3">
            <?php foreach ($recipe['products'] as $rp): ?>
            <?php $rpImg = $rp['image'] ?? $noImg; ?>
            <div class="flex items-center gap-3 p-3 rounded-xl bg-cream-50 border border-vanilla-100 group hover:border-vanilla-300 transition-colors">
                <img src="<?= e($rpImg) ?>" alt="<?= e($rp['name']) ?>"
                     class="w-12 h-12 rounded-lg object-cover shrink-0">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-vanilla-800 line-clamp-1"><?= e($rp['name']) ?></p>
                    <p class="text-xs text-vanilla-500"><?= number_format((float)$rp['price'], 2, ',', ' ') ?> €</p>
                </div>
                <div class="flex flex-col gap-1" x-data>
                    <a href="<?= locale_url('shop/' . $rp['slug']) ?>"
                       class="text-xs text-vanilla-500 hover:text-vanilla-800 transition-colors">Voir</a>
                    <button
                        @click="$store.cart.add({ id:'<?= $rp['id'] ?>', name:'<?= addslashes(e($rp['name'])) ?>', price:<?= (float)$rp['price'] ?>, image:'<?= addslashes(e($rpImg)) ?>' }); $store.alerts.add('Ajouté !','success')"
                        class="text-xs font-semibold text-vanilla-600 hover:text-vanilla-900 transition-colors"
                        title="Ajouter au panier">+ Panier</button>
                </div>
            </div>
            <?php endforeach; ?>
            </div>
        </div>
    </aside>
    <?php endif; ?>

</div>
</div>

<style>
.recipe-steps ol { counter-reset: step; list-style: none; padding: 0; }
.recipe-steps ol li { counter-increment: step; display: flex; gap: 1rem; margin-bottom: 1.5rem; }
.recipe-steps ol li::before {
    content: counter(step);
    display: flex; align-items: center; justify-content: center;
    min-width: 2rem; height: 2rem; border-radius: 50%;
    background: #4E342E; color: #F8F5F0;
    font-weight: 700; font-size: 0.75rem; flex-shrink: 0; margin-top: 0.1rem;
}
</style>
