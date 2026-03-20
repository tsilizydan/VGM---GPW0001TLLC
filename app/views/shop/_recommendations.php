<!-- ══════════════════════════════════════════════
     PRODUCT DETAIL — Recommendations Sections
     Append to product.php inside the x-data div,
     BEFORE the closing </div><!-- /x-data -->
     ══════════════════════════════════════════════

     This partial is included by shop/product.php
     Variables required:
       $bundles      - list<array>  (from Bundle::forProduct())
       $alsoBoaught  - list<array>  (from Product::alsoBoaught())
       $recipes      - list<array>  (from Recipe::forProduct())
       $noImg        - string       (placeholder path)
     ══════════════════════════════════════════════ -->
<?php
$noImg ??= '/assets/img/placeholder-product.svg';
?>

<!-- ══ BUNDLE OFFERS ══ -->
<?php if (!empty($bundles)): ?>
<section class="py-14 bg-gradient-to-br from-gold-50 to-cream-100 border-y border-gold-200/50">
<div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-8 flex items-center gap-4">
        <span class="text-3xl">🎁</span>
        <div>
            <p class="text-xs font-bold uppercase tracking-widest text-gold-600">Économisez plus</p>
            <h2 class="font-serif font-bold text-vanilla-900 text-2xl">Offres groupées</h2>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <?php foreach ($bundles as $bundle): ?>
    <div class="glass-card rounded-2xl p-5 border border-gold-200/60 hover:shadow-gold transition-shadow duration-300"
         x-data="{ bundleAdded: false }">
        <!-- Header -->
        <div class="flex items-start justify-between mb-4">
            <div>
                <p class="font-serif font-bold text-vanilla-900 text-lg"><?= e($bundle['name']) ?></p>
                <?php if ($bundle['description'] ?? ''): ?>
                <p class="text-xs text-vanilla-500 mt-0.5"><?= e($bundle['description']) ?></p>
                <?php endif; ?>
            </div>
            <div class="text-right shrink-0 ml-4">
                <p class="text-2xl font-bold text-vanilla-800"><?= number_format((float)$bundle['price'], 2, ',', ' ') ?> €</p>
                <p class="text-xs text-vanilla-400 line-through"><?= number_format((float)$bundle['total'], 2, ',', ' ') ?> €</p>
                <span class="inline-block text-[10px] font-bold bg-gold-500 text-white px-2 py-0.5 rounded-full mt-0.5">
                    −<?= number_format((float)$bundle['savings'], 2, ',', ' ') ?> €
                </span>
            </div>
        </div>

        <!-- Items -->
        <div class="flex flex-wrap gap-3 mb-4">
        <?php foreach ($bundle['items'] as $item): ?>
        <div class="flex items-center gap-2">
            <img src="<?= e($item['image'] ?? $noImg) ?>" alt="<?= e($item['name']) ?>"
                 class="w-10 h-10 rounded-lg object-cover border border-vanilla-100">
            <div>
                <p class="text-xs font-semibold text-vanilla-700 line-clamp-1"><?= e($item['name']) ?></p>
                <p class="text-[10px] text-vanilla-400">×<?= (int)$item['qty'] ?></p>
            </div>
        </div>
        <?php if (!array_key_last($bundle['items']) === $item): ?>
        <span class="text-vanilla-300 self-center">+</span>
        <?php endif; ?>
        <?php endforeach; ?>
        </div>

        <!-- CTA -->
        <button
            @click="
                <?php foreach ($bundle['items'] as $item): ?>
                $store.cart.add({ id:'<?= $item['product_id'] ?>_bundle', name:'<?= addslashes(e($item['name'])) ?>', price:<?= round((float)$bundle['price'] / max(1, count($bundle['items'])), 2) ?>, image:'<?= addslashes(e($item['image'] ?? $noImg)) ?>', quantity:<?= (int)$item['qty'] ?> });
                <?php endforeach; ?>
                bundleAdded = true;
                $store.alerts.add('Bundle ajouté au panier !', 'success');
            "
            class="w-full btn-primary btn relative overflow-hidden"
            :class="bundleAdded ? 'opacity-80' : ''"
        >
            <span x-show="!bundleAdded">🛒 Ajouter le bundle</span>
            <span x-show="bundleAdded">✓ Ajouté !</span>
        </button>
    </div>
    <?php endforeach; ?>
    </div>
</div>
</section>
<?php endif; ?>

<!-- ══ CUSTOMERS ALSO BOUGHT ══ -->
<?php if (!empty($alsoBoaught)): ?>
<section class="py-14 bg-cream-50">
<div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-7">
        <div>
            <p class="text-xs font-bold uppercase tracking-widest text-vanilla-400 mb-1">Achetés ensemble</p>
            <h2 class="font-serif font-bold text-vanilla-900 text-2xl">Les clients ont aussi acheté</h2>
        </div>
    </div>

    <!-- Horizontal scroll strip -->
    <div class="flex gap-4 overflow-x-auto pb-3 snap-x snap-mandatory scrollbar-hide">
    <?php foreach ($alsoBoaught as $p): ?>
    <?php
    $img     = $p['primary_image'] ?? $noImg;
    $inStock = (int)$p['stock'] > 0;
    ?>
    <div class="snap-start shrink-0 w-48 group" x-data>
        <a href="<?= locale_url('shop/' . $p['slug']) ?>" class="block">
            <div class="relative rounded-2xl overflow-hidden aspect-square bg-cream-100 mb-2.5 border border-vanilla-100">
                <img src="<?= e($img) ?>" alt="<?= e($p['name']) ?>"
                     class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                     loading="lazy">
                <?php if (!$inStock): ?>
                <div class="absolute inset-0 bg-black/20 flex items-end justify-center pb-2">
                    <span class="text-[10px] font-bold bg-red-500 text-white px-2 rounded-full">Rupture</span>
                </div>
                <?php endif; ?>
            </div>
        </a>
        <p class="text-sm font-semibold text-vanilla-800 line-clamp-2 mb-1">
            <a href="<?= locale_url('shop/' . $p['slug']) ?>" class="hover:text-vanilla-600"><?= e($p['name']) ?></a>
        </p>
        <div class="flex items-center justify-between">
            <span class="font-bold text-vanilla-700 text-sm"><?= number_format((float)$p['price'], 2, ',', ' ') ?> €</span>
            <?php if ($inStock): ?>
            <button
                @click="$store.cart.add({ id:'<?= $p['id'] ?>', name:'<?= addslashes(e($p['name'])) ?>', price:<?= (float)$p['price'] ?>, image:'<?= addslashes(e($img)) ?>' }); $store.alerts.add('Ajouté !','success')"
                class="p-1.5 rounded-lg bg-vanilla-100 hover:bg-vanilla-700 hover:text-cream-100 text-vanilla-700 transition-all duration-200"
                title="Ajouter au panier"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
            </button>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
    </div>
</div>
</section>
<?php endif; ?>

<!-- ══ LINKED RECIPES ══ -->
<?php if (!empty($recipes)): ?>
<section class="py-14 bg-vanilla-800 relative overflow-hidden">
    <div class="absolute inset-0 opacity-5"
         style="background-image: repeating-linear-gradient(60deg, #fff 0, #fff 1px, transparent 0, transparent 50%); background-size: 20px 20px;"></div>

    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="mb-8">
            <p class="text-xs font-bold uppercase tracking-widest text-gold-400 mb-1">Inspirations culinaires</p>
            <h2 class="font-serif font-bold text-cream-100 text-2xl">Recettes utilisant ce produit</h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <?php foreach ($recipes as $recipe): ?>
        <?php
        $cover  = $recipe['cover_image'] ?? $noImg;
        $prep   = ($recipe['prep_time'] ?? 0) + ($recipe['cook_time'] ?? 0);
        $diff   = match($recipe['difficulty'] ?? 'easy') { 'hard' => 'Difficile', 'medium' => 'Moyen', default => 'Facile' };
        ?>
        <a href="<?= locale_url('recipes/' . $recipe['slug']) ?>"
           class="group block rounded-2xl overflow-hidden bg-vanilla-700/50 border border-vanilla-600/50 hover:border-gold-400/50 transition-all duration-300 hover:-translate-y-1">
            <div class="relative aspect-video overflow-hidden">
                <img src="<?= e($cover) ?>" alt="<?= e($recipe['title']) ?>"
                     class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" loading="lazy">
                <div class="absolute inset-0 bg-gradient-to-t from-vanilla-900/60 to-transparent"></div>
                <div class="absolute bottom-3 left-3 flex gap-2">
                    <?php if ($prep): ?>
                    <span class="text-[10px] font-bold bg-black/50 text-cream-100 backdrop-blur-sm px-2 py-1 rounded-full">⏱ <?= $prep ?> min</span>
                    <?php endif; ?>
                    <span class="text-[10px] font-bold bg-black/50 text-cream-100 backdrop-blur-sm px-2 py-1 rounded-full">👨‍🍳 <?= $diff ?></span>
                </div>
            </div>
            <div class="p-4">
                <h3 class="font-serif font-semibold text-cream-100 line-clamp-2 mb-1 group-hover:text-gold-300 transition-colors"><?= e($recipe['title']) ?></h3>
                <?php if ($recipe['intro'] ?? ''): ?>
                <p class="text-xs text-vanilla-400 line-clamp-2"><?= e($recipe['intro']) ?></p>
                <?php endif; ?>
            </div>
        </a>
        <?php endforeach; ?>
        </div>

        <div class="mt-8 text-center">
            <a href="<?= locale_url('recipes') ?>" class="inline-flex items-center gap-2 px-6 py-3 rounded-full border border-gold-400/50 text-gold-300 hover:bg-gold-400/10 transition-all text-sm font-semibold">
                Voir toutes les recettes →
            </a>
        </div>
    </div>
</section>
<?php endif; ?>
