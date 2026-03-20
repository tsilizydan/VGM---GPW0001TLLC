<!-- ══════════════════════════════════════════════
     PRODUCT CARD — Reusable glass card component
     Usage: include with $product array:
       { id, name, price, image, description, badge?, slug }
     ══════════════════════════════════════════════ -->
<?php
/** @var array<string,mixed> $product */
$badge       = $product['badge']       ?? null;
$badgeClass  = match($badge) {
    'Premium' => 'bg-gold-100   text-gold-700   border-gold-300',
    'Bio'     => 'bg-forest-100 text-forest-700 border-forest-300',
    'Nouveau' => 'bg-vanilla-100 text-vanilla-700 border-vanilla-300',
    'Promo'   => 'bg-red-100    text-red-700    border-red-300',
    default   => 'bg-cream-200  text-vanilla-600 border-cream-300',
};
$productUrl = url('shop/' . ($product['slug'] ?? $product['id']));
?>
<article
    class="group relative bg-white/72 backdrop-blur-md border border-white/50
           rounded-2xl shadow-card overflow-hidden
           transition-all duration-400 ease-[cubic-bezier(0.16,1,0.3,1)]
           hover:shadow-card-hover hover:-translate-y-1.5"
    data-reveal
>
    <!-- ── Image ── -->
    <a href="<?= e($productUrl) ?>" class="block relative overflow-hidden aspect-[4/5] bg-cream-100">
        <img
            src="<?= e($product['image'] ?? asset('img/placeholder-product.jpg')) ?>"
            alt="<?= e($product['name'] ?? '') ?>"
            class="w-full h-full object-cover transition-transform duration-700
                   ease-[cubic-bezier(0.16,1,0.3,1)] group-hover:scale-110"
            loading="lazy"
            width="400" height="500"
        >
        <!-- Overlay gradient on hover -->
        <div class="absolute inset-0 bg-gradient-to-t from-vanilla-900/20 to-transparent
                    opacity-0 group-hover:opacity-100 transition-opacity duration-400"></div>

        <!-- Badge -->
        <?php if ($badge): ?>
        <span class="absolute top-3 left-3 inline-flex items-center text-[11px] font-bold
                     px-2.5 py-1 rounded-full border uppercase tracking-wide
                     <?= $badgeClass ?>">
            <?= e($badge) ?>
        </span>
        <?php endif; ?>

        <!-- Quick add overlay -->
        <div class="absolute bottom-3 inset-x-3
                    opacity-0 group-hover:opacity-100 translate-y-2 group-hover:translate-y-0
                    transition-all duration-350 ease-[cubic-bezier(0.16,1,0.3,1)]">
            <button
                x-data
                @click.prevent="$store.cart.add({
                    id:    <?= (int) ($product['id'] ?? 0) ?>,
                    name:  '<?= e(addslashes($product['name'] ?? '')) ?>',
                    price: <?= (float) ($product['price'] ?? 0) ?>,
                    image: '<?= e($product['image'] ?? '') ?>',
                    slug:  '<?= e($product['slug'] ?? $product['id'] ?? '') ?>',
                })"
                class="w-full py-2.5 rounded-xl bg-vanilla-700/90 text-cream-100
                       text-xs font-semibold backdrop-blur-sm
                       hover:bg-vanilla-700 transition-colors duration-200
                       flex items-center justify-center gap-1.5"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/>
                </svg>
                Ajouter au panier
            </button>
        </div>
    </a>

    <!-- ── Content ── -->
    <div class="p-4">
        <a href="<?= e($productUrl) ?>" class="block group/link">
            <h3 class="font-serif font-bold text-vanilla-800 text-base leading-snug
                       group-hover/link:text-vanilla-600 transition-colors duration-200 mb-1">
                <?= e($product['name'] ?? '') ?>
            </h3>
        </a>
        <p class="text-xs text-vanilla-400 leading-relaxed line-clamp-2 mb-3">
            <?= e($product['description'] ?? '') ?>
        </p>
        <div class="flex items-center justify-between">
            <div>
                <?php if (!empty($product['original_price']) && $product['original_price'] > $product['price']): ?>
                <span class="text-xs text-vanilla-400 line-through mr-1">
                    €<?= number_format($product['original_price'], 2) ?>
                </span>
                <?php endif; ?>
                <span class="font-serif font-bold text-vanilla-800 text-lg">
                    €<?= number_format((float)($product['price'] ?? 0), 2) ?>
                </span>
            </div>
            <!-- Wishlist / info -->
            <a href="<?= e($productUrl) ?>"
               class="p-2 rounded-lg text-vanilla-400 hover:text-vanilla-700 hover:bg-vanilla-50 transition-all duration-200"
               aria-label="Voir le produit">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                </svg>
            </a>
        </div>
    </div>
</article>
