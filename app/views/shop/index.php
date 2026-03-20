<!-- ══════════════════════════════════════════════
     SHOP — Product Grid (real DB data, category filter)
     ══════════════════════════════════════════════ -->

<?php
$noImage = '/assets/img/placeholder-product.svg';
?>

<!-- Hero -->
<div class="relative overflow-hidden bg-vanilla-800 py-16 md:py-24">
    <div class="absolute inset-0 malagasy-bg opacity-20"></div>
    <div class="relative max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <p class="text-gold-300 text-sm font-semibold uppercase tracking-widest mb-3 animate__animated animate__fadeInDown">Vanilla Groupe Madagascar</p>
        <h1 class="font-serif font-bold text-cream-100 text-h1 text-balance mb-4 animate__animated animate__fadeIn">
            <?= t('shop.title') ?>
        </h1>
        <p class="text-vanilla-300 text-body max-w-xl mx-auto animate__animated animate__fadeInUp">
            <?= t('shop.subtitle') ?>
        </p>
    </div>
</div>

<!-- Filter bar -->
<div class="sticky top-16 md:top-20 z-40 bg-cream-100/90 backdrop-blur-lg border-b border-vanilla-200/40 shadow-sm">
<div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center gap-2 py-3 overflow-x-auto scrollbar-none">
        <a href="<?= e(locale_url('shop')) ?>"
           class="shrink-0 px-4 py-2 rounded-pill text-sm font-semibold transition-all duration-200
                  <?= $activeFilter === '' ? 'bg-vanilla-700 text-cream-100' : 'bg-cream-200 text-vanilla-600 hover:bg-vanilla-100' ?>">
            <?= t('shop.filter.all') ?>
        </a>
        <?php foreach ($categories as $cat): ?>
        <a href="<?= e(locale_url('shop') . '?category=' . urlencode($cat['slug'])) ?>"
           class="shrink-0 px-4 py-2 rounded-pill text-sm font-semibold transition-all duration-200
                  <?= $activeFilter === $cat['slug'] ? 'bg-vanilla-700 text-cream-100' : 'bg-cream-200 text-vanilla-600 hover:bg-vanilla-100' ?>">
            <?= e($cat['name']) ?>
        </a>
        <?php endforeach; ?>

        <!-- Spacer -->
        <div class="flex-1"></div>

        <!-- Sort + Search -->
        <form method="GET" action="<?= locale_url('shop') ?>" class="flex items-center gap-2 shrink-0">
            <?php if ($activeFilter): ?>
            <input type="hidden" name="category" value="<?= e($activeFilter) ?>">
            <?php endif; ?>

            <input type="search" name="q" value="<?= e($search) ?>" placeholder="Rechercher…"
                   class="text-xs form-input py-1.5 w-32 md:w-44">

            <select name="sort" class="text-xs form-input py-1.5 w-32" onchange="this.form.submit()">
                <option value="">Trier par</option>
                <option value="price_asc"  <?= $sort === 'price_asc'  ? 'selected' : '' ?>>Prix ↑</option>
                <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Prix ↓</option>
                <option value="newest"     <?= $sort === 'newest'     ? 'selected' : '' ?>>Nouveautés</option>
            </select>
        </form>

        <span class="text-xs text-vanilla-400 shrink-0">
            <?= t('shop.products_count', ['count' => $pagination['total']]) ?>
        </span>
    </div>
</div>
</div>

<!-- Product Grid -->
<section class="section">
<div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">

    <?php if (empty($products)): ?>
    <div class="py-24 text-center">
        <p class="text-vanilla-300 text-4xl mb-4">🌿</p>
        <h2 class="font-serif text-vanilla-700 text-h2 mb-2"><?= t('shop.empty') ?></h2>
        <p class="text-vanilla-400"><?= t('shop.empty_sub') ?></p>
    </div>
    <?php else: ?>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <?php foreach ($products as $i => $product): ?>
        <?php
        $img   = $product['primary_image'] ?: $noImage;
        $inStock = (int)$product['stock'] > 0;
        $hasCompare = $product['compare_price'] && $product['compare_price'] > $product['price'];
        ?>
        <article
            class="product-card group animate-on-scroll"
            style="--delay: <?= $i % 8 * 60 ?>ms; animation-delay: var(--delay)"
        >
            <!-- Image -->
            <a href="<?= locale_url('shop/' . $product['slug']) ?>" class="product-img-wrap block">
                <img src="<?= e($img) ?>" alt="<?= e($product['name']) ?>"
                     class="w-full h-full object-cover transition-transform duration-700 ease-smooth group-hover:scale-110"
                     loading="lazy">
                <!-- Badges -->
                <div class="absolute top-3 left-3 flex flex-col gap-1.5">
                    <?php if ($product['featured'] ?? false): ?>
                    <span class="badge-premium badge text-[10px]">Premium</span>
                    <?php endif; ?>
                    <?php if ($hasCompare): ?>
                    <span class="badge-sale badge text-[10px]">Promo</span>
                    <?php endif; ?>
                    <?php if (!$inStock): ?>
                    <span class="badge text-[10px] bg-red-100 text-red-700 border border-red-300">Rupture</span>
                    <?php endif; ?>
                </div>
            </a>

            <!-- Body -->
            <div class="p-4 flex flex-col gap-2">
                <p class="text-xs font-semibold text-vanilla-400 uppercase tracking-widest">
                    <?= e($product['category_name'] ?? '') ?>
                </p>
                <h2 class="font-serif font-semibold text-vanilla-800 text-base line-clamp-2 leading-snug">
                    <a href="<?= locale_url('shop/' . $product['slug']) ?>" class="hover:text-vanilla-600 transition-colors">
                        <?= e($product['name']) ?>
                    </a>
                </h2>
                <?php if ($product['description']): ?>
                <p class="text-sm text-vanilla-500 line-clamp-2 leading-relaxed"><?= e($product['description']) ?></p>
                <?php endif; ?>

                <!-- Price + CTA -->
                <div class="flex items-center justify-between mt-auto pt-2">
                    <div>
                        <span class="font-bold text-vanilla-800 text-body">
                            <?= number_format((float)$product['price'], 2, ',', ' ') ?> €
                        </span>
                        <?php if ($hasCompare): ?>
                        <span class="text-xs text-vanilla-400 line-through ml-1.5">
                            <?= number_format((float)$product['compare_price'], 2, ',', ' ') ?> €
                        </span>
                        <?php endif; ?>
                    </div>
                    <?php if ($inStock): ?>
                    <button
                        @click="$store.cart.add({
                            id:    '<?= $product['id'] ?>',
                            name:  '<?= addslashes(e($product['name'])) ?>',
                            price: <?= (float)$product['price'] ?>,
                            image: '<?= addslashes(e($img)) ?>'
                        }); $store.alerts.add('<?= addslashes(t('alert.added_to_cart', ['name' => $product['name']])) ?>', 'success')"
                        class="btn-primary btn btn-sm text-xs"
                        aria-label="<?= e(t('shop.add_to_cart')) ?>"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" d="M12 4.5v15m7.5-7.5h-15"/>
                        </svg>
                        <?= t('shop.add_to_cart') ?>
                    </button>
                    <?php else: ?>
                    <span class="text-xs font-semibold text-red-500">Rupture de stock</span>
                    <?php endif; ?>
                </div>
            </div>
        </article>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($pagination['pages'] > 1): ?>
    <div class="flex items-center justify-center gap-2 mt-12">
        <?php if ($pagination['page'] > 1): ?>
        <a href="?<?= http_build_query(array_filter(['category' => $activeFilter, 'q' => $search, 'sort' => $sort, 'page' => $pagination['page'] - 1])) ?>"
           class="px-4 py-2 rounded-xl bg-cream-200 text-vanilla-700 text-sm font-semibold hover:bg-vanilla-100 transition-colors">← Précédent</a>
        <?php endif; ?>
        <?php for ($p = 1; $p <= $pagination['pages']; $p++): ?>
        <a href="?<?= http_build_query(array_filter(['category' => $activeFilter, 'q' => $search, 'sort' => $sort, 'page' => $p])) ?>"
           class="w-10 h-10 flex items-center justify-center rounded-xl text-sm font-semibold
                  <?= $p === (int)$pagination['page'] ? 'bg-vanilla-700 text-cream-100' : 'bg-cream-200 text-vanilla-600 hover:bg-vanilla-100' ?>">
            <?= $p ?>
        </a>
        <?php endfor; ?>
        <?php if ($pagination['page'] < $pagination['pages']): ?>
        <a href="?<?= http_build_query(array_filter(['category' => $activeFilter, 'q' => $search, 'sort' => $sort, 'page' => $pagination['page'] + 1])) ?>"
           class="px-4 py-2 rounded-xl bg-cream-200 text-vanilla-700 text-sm font-semibold hover:bg-vanilla-100 transition-colors">Suivant →</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php endif; ?>
</div>
</section>
