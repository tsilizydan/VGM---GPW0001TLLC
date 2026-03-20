<!-- ══════════════════════════════════════════════
     SHOP — Product Detail Page (with Storytelling System)
     Gallery · Variation selector · Storytelling · Related
     ══════════════════════════════════════════════ -->

<?php
/** @var array<string,mixed> $product */
/** @var list<array<string,mixed>> $related */

$noImg      = '/assets/img/placeholder-product.svg';
$images     = $product['images'] ?? [];
$variations = $product['variations'] ?? [];
$inStock    = (int)$product['stock'] > 0;
$primaryImg = '';
foreach ($images as $img) { if ($img['is_primary']) { $primaryImg = $img['path']; break; } }
if (!$primaryImg && $images) $primaryImg = $images[0]['path'];

// Storytelling fields
$hasStory       = ($product['farmer_story'] ?? '') || ($product['origin_region'] ?? '') || ($product['harvest_process'] ?? '');
$storyMedia     = $product['story_media'] ?? [];
$originRegion   = $product['origin_region'] ?? 'SAVA — Sambava, Madagascar';
$farmerName     = $product['farmer_name'] ?? '';
$farmerQuote    = $product['farmer_quote'] ?? '';
$farmerStory    = $product['farmer_story'] ?? '';
$harvestProcess = $product['harvest_process'] ?? '';
$harvestSeason  = $product['harvest_season'] ?? '';
$certifications = $product['certifications'] ?? '';

// Build Alpine variation options
$varOptions = [];
foreach ($variations as $v) {
    $varOptions[] = [
        'sku'   => $v['sku'] ?? '',
        'price' => (float)($v['price'] ?? $product['price']),
        'stock' => (int)$v['stock'],
        'attrs' => $v['attributes'] ?? [],
        'label' => implode(' · ', array_map(fn($k,$va) => "$k : $va", array_keys($v['attributes'] ?? []), $v['attributes'] ?? [])),
    ];
}
?>

<!-- Malagasy pattern CSS (inline: SVG woven lines / lambahoany-inspired geometry) -->
<style>
.malagasy-weave {
    background-image:
        repeating-linear-gradient(45deg, rgba(78,52,46,0.04) 0, rgba(78,52,46,0.04) 1px, transparent 0, transparent 50%),
        repeating-linear-gradient(-45deg, rgba(106,143,78,0.04) 0, rgba(106,143,78,0.04) 1px, transparent 0, transparent 50%);
    background-size: 18px 18px;
}
.malagasy-border {
    background:
        repeating-linear-gradient(90deg, #4E342E 0, #4E342E 6px, transparent 6px, transparent 12px),
        repeating-linear-gradient(90deg, #6A8F4E 0, #6A8F4E 4px, transparent 4px, transparent 12px);
    background-size: 12px 3px, 12px 3px;
    background-position: 0 0, 0 4px;
    height: 6px;
    border-radius: 4px;
}
.lambahoany-accent::before {
    content: '';
    display: block;
    width: 56px;
    height: 4px;
    background: linear-gradient(90deg, #C8A96A 0%, #6A8F4E 50%, #4E342E 100%);
    border-radius: 2px;
    margin-bottom: 12px;
}
.story-img-wrap {
    position: relative;
    overflow: hidden;
    border-radius: 1.25rem;
}
.story-img-wrap::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(78,52,46,0.35) 0%, transparent 55%);
    pointer-events: none;
}
</style>

<div
    x-data="{
        activeImg: '<?= e(addslashes($primaryImg ?: $noImg)) ?>',
        zoom: false,
        qty: 1,
        tab: 'description',
        selectedVar: <?= $varOptions ? '0' : 'null' ?>,
        variations: <?= json_encode($varOptions, JSON_HEX_TAG | JSON_UNESCAPED_UNICODE) ?>,
        get currentPrice() {
            if (this.selectedVar !== null && this.variations[this.selectedVar]) {
                return this.variations[this.selectedVar].price;
            }
            return <?= (float)$product['price'] ?>;
        },
        get currentStock() {
            if (this.selectedVar !== null && this.variations[this.selectedVar]) {
                return this.variations[this.selectedVar].stock;
            }
            return <?= (int)$product['stock'] ?>;
        },
        addToCart() {
            $store.cart.add({
                id:       '<?= $product['id'] ?>_' + (this.selectedVar !== null ? this.selectedVar : 0),
                name:     '<?= addslashes(e($product['name'])) ?>'
                          + (this.selectedVar !== null && this.variations[this.selectedVar]
                              ? ' (' + this.variations[this.selectedVar].label + ')' : ''),
                price:    this.currentPrice,
                image:    '<?= e(addslashes($primaryImg ?: $noImg)) ?>',
                quantity: this.qty,
            });
            $store.alerts.add('<?= addslashes(t('alert.added_to_cart', ['name' => $product['name']])) ?>', 'success');
        }
    }"
>

<!-- Breadcrumb -->
<div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 pt-6">
    <nav class="flex items-center gap-2 text-xs text-vanilla-400">
        <a href="<?= locale_url() ?>" class="hover:text-vanilla-700 transition-colors">Accueil</a>
        <span>/</span>
        <a href="<?= locale_url('shop') ?>" class="hover:text-vanilla-700 transition-colors"><?= t('shop.title') ?></a>
        <?php if ($product['category_name'] ?? ''): ?>
        <span>/</span>
        <a href="<?= locale_url('shop') ?>?category=<?= urlencode($product['category_slug'] ?? '') ?>"
           class="hover:text-vanilla-700 transition-colors"><?= e($product['category_name']) ?></a>
        <?php endif; ?>
        <span>/</span>
        <span class="text-vanilla-600 font-medium line-clamp-1"><?= e($product['name']) ?></span>
    </nav>
</div>

<!-- ── Main Product Block ── -->
<section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-10 grid grid-cols-1 lg:grid-cols-2 gap-12 items-start">

    <!-- Gallery -->
    <div class="space-y-3">
        <div class="relative rounded-2xl overflow-hidden aspect-square bg-cream-100 cursor-zoom-in"
             @click="zoom = !zoom">
            <img :src="activeImg" alt="<?= e($product['name']) ?>"
                 class="w-full h-full object-cover transition-transform duration-500"
                 :class="zoom ? 'scale-150 cursor-zoom-out' : 'scale-100'"
                 style="transform-origin: center">
            <?php if (!$inStock): ?>
            <div class="absolute inset-0 bg-black/30 flex items-center justify-center">
                <span class="bg-red-600 text-white text-sm font-bold px-4 py-2 rounded-xl">Rupture de stock</span>
            </div>
            <?php endif; ?>
        </div>
        <?php if (count($images) > 1): ?>
        <div class="flex gap-2 overflow-x-auto pb-1">
            <?php foreach ($images as $img): ?>
            <button type="button"
                @click="activeImg = '<?= e(addslashes($img['path'])) ?>'"
                class="shrink-0 w-18 h-18 rounded-xl overflow-hidden border-2 transition-all duration-200"
                :class="activeImg === '<?= e(addslashes($img['path'])) ?>'
                    ? 'border-vanilla-700 shadow-soft'
                    : 'border-vanilla-200 hover:border-vanilla-400'">
                <img src="<?= e($img['path']) ?>" alt="<?= e($img['alt'] ?? $product['name']) ?>"
                     class="w-full h-full object-cover">
            </button>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <p class="text-xs text-vanilla-400 text-center">
            <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607ZM10.5 7.5v6m3-3h-6"/></svg>
            Cliquez sur l'image pour zoomer
        </p>
    </div>

    <!-- Product Info -->
    <div class="space-y-5">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-vanilla-400 mb-2">
                <?= e($product['category_name'] ?? '') ?>
            </p>
            <h1 class="font-serif font-bold text-vanilla-900 text-h1 leading-tight mb-3">
                <?= e($product['name']) ?>
            </h1>
            <?php if ($originRegion): ?>
            <p class="flex items-center gap-2 text-sm text-vanilla-500 mb-3">
                <span class="text-base">🗺️</span>
                <span class="font-semibold text-vanilla-700"><?= e($originRegion) ?></span>
            </p>
            <?php endif; ?>

            <div class="flex items-baseline gap-3">
                <span class="text-3xl font-bold text-vanilla-800" x-text="currentPrice.toFixed(2).replace('.', ',') + ' €'"></span>
                <?php if ($product['compare_price'] && $product['compare_price'] > $product['price']): ?>
                <span class="text-xl text-vanilla-400 line-through">
                    <?= number_format((float)$product['compare_price'], 2, ',', ' ') ?> €
                </span>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($product['description']): ?>
        <p class="text-vanilla-600 leading-relaxed"><?= nl2br(e($product['description'])) ?></p>
        <?php endif; ?>

        <!-- Farmer quick-quote (if set) -->
        <?php if ($farmerQuote): ?>
        <blockquote class="border-l-4 border-gold-400 pl-4 italic text-vanilla-600 text-sm leading-relaxed bg-gold-50/50 py-3 pr-4 rounded-r-xl">
            "<?= e($farmerQuote) ?>"
            <?php if ($farmerName): ?>
            <footer class="not-italic mt-1 text-xs font-semibold text-vanilla-500">— <?= e($farmerName) ?></footer>
            <?php endif; ?>
        </blockquote>
        <?php endif; ?>

        <!-- Variation selector -->
        <?php if ($varOptions): ?>
        <div class="space-y-2">
            <label class="form-label">Choisir une option</label>
            <div class="flex flex-wrap gap-2">
                <template x-for="(v, i) in variations" :key="i">
                    <button type="button" @click="selectedVar = i" :disabled="v.stock === 0"
                        class="px-3 py-2 rounded-xl border text-sm font-semibold transition-all duration-200 disabled:opacity-40 disabled:cursor-not-allowed"
                        :class="selectedVar === i
                            ? 'border-vanilla-700 bg-vanilla-700 text-cream-100'
                            : 'border-vanilla-300 text-vanilla-600 hover:border-vanilla-500'"
                        x-text="v.label || ('Option ' + (i+1))"></button>
                </template>
            </div>
        </div>
        <?php endif; ?>

        <!-- Qty + Cart -->
        <div class="flex items-center gap-3">
            <div class="flex items-center border border-vanilla-200 rounded-xl overflow-hidden">
                <button type="button" @click="qty = Math.max(1, qty - 1)"
                        class="qty-btn rounded-none border-r border-vanilla-200">−</button>
                <span class="w-12 text-center font-bold text-vanilla-800 text-sm" x-text="qty"></span>
                <button type="button" @click="qty = Math.min(currentStock, qty + 1)"
                        class="qty-btn rounded-none border-l border-vanilla-200">+</button>
            </div>
            <button @click="addToCart()" :disabled="currentStock === 0"
                class="flex-1 btn-primary btn disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/>
                </svg>
                <span x-text="currentStock === 0 ? 'Rupture de stock' : '<?= t('product.add_to_cart') ?>'"></span>
            </button>
        </div>

        <p class="text-xs text-vanilla-400 flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full inline-block"
                  :class="currentStock > 5 ? 'bg-forest-500' : currentStock > 0 ? 'bg-amber-500' : 'bg-red-500'"></span>
            <span x-text="currentStock > 5 ? '<?= t('product.quantity') ?> disponible'
                         : currentStock > 0 ? 'Plus que ' + currentStock + ' en stock !'
                         : 'Rupture de stock'"></span>
        </p>

        <!-- Certifications pills -->
        <?php if ($certifications): ?>
        <div class="flex flex-wrap gap-2">
            <?php foreach (array_map('trim', explode(',', $certifications)) as $cert): ?>
            <?php if ($cert): ?>
            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-forest-100 text-forest-700 text-xs font-semibold border border-forest-200">
                🏅 <?= e($cert) ?>
            </span>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Trust badges -->
        <div class="flex flex-wrap gap-4 pt-2 border-t border-vanilla-100">
            <?php foreach ([
                ['🌿', 'Culture responsable'],
                ['🚚', 'Livraison soignée'],
                ['🔒', 'Paiement sécurisé'],
                ['🌍', $originRegion ?: 'Origine Madagascar'],
            ] as [$icon, $label]): ?>
            <span class="flex items-center gap-1.5 text-xs text-vanilla-500 font-medium">
                <span><?= $icon ?></span><?= e($label) ?>
            </span>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ════════════════════════════════════════════════════════════════
     STORYTELLING SECTIONS
     ════════════════════════════════════════════════════════════════ -->

<?php if ($hasStory): ?>

<!-- Malagasy divider -->
<div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
    <div class="malagasy-border"></div>
</div>

<!-- 1. ORIGIN REGION HERO -->
<section class="py-20 malagasy-weave relative overflow-hidden">
    <!-- Decorative background circle -->
    <div class="absolute -top-32 -right-32 w-80 h-80 rounded-full bg-forest-100/30 blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -left-24 w-72 h-72 rounded-full bg-gold-100/30 blur-3xl pointer-events-none"></div>

    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">

            <!-- Text -->
            <div class="order-2 lg:order-1">
                <p class="text-xs font-bold uppercase tracking-widest text-forest-600 mb-3 flex items-center gap-2">
                    <span class="inline-block w-8 h-0.5 bg-forest-500"></span>
                    Origine & Territoire
                </p>
                <h2 class="font-serif font-bold text-vanilla-900 text-4xl leading-tight mb-4 lambahoany-accent">
                    <?= e($originRegion) ?>
                </h2>
                <p class="text-vanilla-600 text-body leading-relaxed mb-6">
                    La région SAVA — <em>Sambava, Antalaha, Vohémar, Andapa</em> — est le berceau mondial de la vanille de Bourbon. Un micro-climat exceptionnel, des forêts primaires préservées et un savoir-faire ancestral transmis de génération en génération.
                </p>

                <!-- Malagasy map callout -->
                <div class="flex items-start gap-4 p-5 rounded-2xl bg-vanilla-800/5 border border-vanilla-200 backdrop-blur-sm">
                    <span class="text-3xl mt-0.5 shrink-0">🌍</span>
                    <div>
                        <p class="font-semibold text-vanilla-800 mb-0.5">Coordonnées</p>
                        <p class="text-xs text-vanilla-500 leading-relaxed">Madagascar · Côte nord-est · 14°N / 50°E<br>Altitude : 50–400 m · Pluviométrie : 2 000 mm/an</p>
                    </div>
                </div>
            </div>

            <!-- Region image -->
            <div class="order-1 lg:order-2">
                <?php
                $regionMedia = $storyMedia['region'][0]['path'] ?? ($images[0]['path'] ?? $noImg);
                ?>
                <div class="story-img-wrap aspect-[4/3] shadow-2xl">
                    <img src="<?= e($regionMedia) ?>" alt="Région <?= e($originRegion) ?>"
                         class="w-full h-full object-cover">
                    <div class="absolute bottom-4 left-4 z-10 text-cream-100 text-sm font-semibold drop-shadow-lg">
                        📍 <?= e($originRegion) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 2. FARMER STORY -->
<?php if ($farmerName || $farmerStory): ?>
<section class="py-20 bg-vanilla-800 relative overflow-hidden">
    <!-- Malagasy pattern overlay -->
    <div class="absolute inset-0 opacity-5"
         style="background-image: repeating-linear-gradient(60deg, #fff 0, #fff 1px, transparent 0, transparent 50%), repeating-linear-gradient(-60deg, #fff 0, #fff 1px, transparent 0, transparent 50%); background-size: 24px 24px;">
    </div>

    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-12 items-center">

            <!-- Farmer portrait -->
            <div class="lg:col-span-2">
                <?php $farmerMedia = $storyMedia['farmer'][0]['path'] ?? null; ?>
                <div class="relative">
                    <div class="story-img-wrap aspect-square max-w-xs mx-auto shadow-2xl ring-4 ring-gold-400/30">
                        <img src="<?= e($farmerMedia ?? ($images[0]['path'] ?? $noImg)) ?>"
                             alt="<?= e($farmerName) ?>"
                             class="w-full h-full object-cover">
                    </div>
                    <!-- Decorative circle -->
                    <div class="absolute -bottom-4 -right-4 w-24 h-24 rounded-full border-4 border-gold-400/40 -z-10"></div>
                    <!-- Name card -->
                    <?php if ($farmerName): ?>
                    <div class="absolute -bottom-6 left-1/2 -translate-x-1/2 bg-white px-5 py-2.5 rounded-full shadow-soft text-center whitespace-nowrap">
                        <p class="text-xs font-bold text-vanilla-800"><?= e($farmerName) ?></p>
                        <p class="text-[10px] text-vanilla-400">Agriculteur partenaire</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Story text -->
            <div class="lg:col-span-3 text-cream-100 pt-8 lg:pt-0">
                <p class="text-xs font-bold uppercase tracking-widest text-gold-400 mb-3 flex items-center gap-2">
                    <span class="inline-block w-8 h-0.5 bg-gold-400"></span>
                    L'histoire du producteur
                </p>
                <h2 class="font-serif font-bold text-cream-100 text-3xl leading-snug mb-6">
                    <?= $farmerName ? e($farmerName) : 'Un agriculteur passionné' ?>
                </h2>

                <?php if ($farmerQuote): ?>
                <blockquote class="text-xl font-serif italic text-gold-300 mb-6 leading-relaxed border-l-4 border-gold-400 pl-5">
                    "<?= e($farmerQuote) ?>"
                </blockquote>
                <?php endif; ?>

                <?php if ($farmerStory): ?>
                <div class="space-y-3 text-cream-100/80 leading-relaxed text-body">
                    <?php foreach (explode("\n\n", $farmerStory) as $para): ?>
                    <?php if (trim($para)): ?>
                    <p><?= nl2br(e(trim($para))) ?></p>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="text-cream-100/70 leading-relaxed">
                    Partenaire de confiance depuis plusieurs saisons, cet agriculteur perpétue les traditions de culture biologique héritées de ses ancêtres malgaches. Chaque plantation est entretenue avec le plus grand soin.
                </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 3. HARVEST PROCESS -->
<?php if ($harvestProcess): ?>
<section class="py-20 bg-cream-100 relative overflow-hidden">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center mb-14">
            <p class="text-xs font-bold uppercase tracking-widest text-gold-600 mb-3">Le voyage de la vanille</p>
            <h2 class="font-serif font-bold text-vanilla-900 text-4xl mb-4">Du terrain à votre table</h2>
            <div class="malagasy-border max-w-xs mx-auto"></div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">

            <!-- Process steps (auto-parsed from harvest_process paragraphs) -->
            <div class="space-y-6">
                <?php
                $steps = array_values(array_filter(array_map('trim', explode("\n\n", $harvestProcess))));
                $stepIcons = ['🌸', '🌿', '✂️', '☀️', '🏺', '📦'];
                foreach ($steps as $si => $step):
                    $icon = $stepIcons[$si % count($stepIcons)];
                    // Check for "Title: body" format
                    $parts = explode(':', $step, 2);
                    $stepTitle = count($parts) === 2 ? trim($parts[0]) : 'Étape ' . ($si + 1);
                    $stepBody  = count($parts) === 2 ? trim($parts[1]) : $step;
                ?>
                <div class="flex gap-4 animate__animated animate__fadeInLeft" style="animation-delay:<?= $si * 100 ?>ms">
                    <div class="shrink-0 w-11 h-11 rounded-full bg-vanilla-800 text-cream-100 flex items-center justify-center text-lg shadow-soft">
                        <?= $icon ?>
                    </div>
                    <div class="pt-1">
                        <p class="font-semibold text-vanilla-800 mb-1"><?= e($stepTitle) ?></p>
                        <p class="text-sm text-vanilla-500 leading-relaxed"><?= e($stepBody) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>

                <?php if ($harvestSeason): ?>
                <div class="flex items-center gap-3 mt-4 p-4 rounded-xl bg-forest-50 border border-forest-200">
                    <span class="text-2xl">📅</span>
                    <div>
                        <p class="text-xs font-bold text-forest-700 uppercase tracking-widest">Saison de récolte</p>
                        <p class="font-semibold text-vanilla-800"><?= e($harvestSeason) ?></p>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Harvest images -->
            <div class="grid grid-cols-2 gap-3">
                <?php
                $harvestImgs = $storyMedia['harvest'] ?? [];
                $processImgs = $storyMedia['process'] ?? [];
                $allStoryImgs = array_slice(array_merge($harvestImgs, $processImgs, $images), 0, 4);
                ?>
                <?php foreach ($allStoryImgs as $si => $simg): ?>
                <?php $path = is_array($simg) ? ($simg['path'] ?? '') : ($simg['path'] ?? ''); ?>
                <?php if (!$path) continue; ?>
                <div class="story-img-wrap <?= $si === 0 ? 'col-span-2 aspect-video' : 'aspect-square' ?> shadow-soft">
                    <img src="<?= e($path) ?>" alt="Récolte <?= $si + 1 ?>"
                         class="w-full h-full object-cover transition-transform duration-700 hover:scale-105">
                </div>
                <?php endforeach; ?>
                <?php if (count($allStoryImgs) < 3): ?>
                <!-- Placeholder visual if no story images uploaded -->
                <div class="aspect-square rounded-2xl bg-gradient-to-br from-forest-100 to-forest-200 flex items-center justify-center">
                    <span class="text-5xl">🌿</span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 4. CULTURAL IDENTITY FOOTER BAND (Malagasy) -->
<section class="py-14 bg-gradient-to-r from-vanilla-900 via-vanilla-800 to-forest-900 relative overflow-hidden">
    <!-- Lambahoany pattern strip (top) -->
    <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-gold-400 via-forest-500 to-vanilla-700"></div>

    <div class="absolute inset-0 opacity-10"
         style="background-image: repeating-linear-gradient(45deg, #fff 0, #fff 1px, transparent 0, transparent 20px); background-size: 28px 28px;">
    </div>

    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-8 text-cream-100">
            <div class="text-center">
                <div class="text-4xl mb-3">🌺</div>
                <p class="font-serif font-bold text-lg mb-1">Savoir-faire ancestral</p>
                <p class="text-xs text-cream-100/60 leading-relaxed">Techniques de culture transmises depuis des générations de familles malgaches</p>
            </div>
            <div class="text-center border-x border-cream-100/10">
                <div class="text-4xl mb-3">✋</div>
                <p class="font-serif font-bold text-lg mb-1">100 % Artisanal</p>
                <p class="text-xs text-cream-100/60 leading-relaxed">Pollinisation à la main, récolte sélective, séchage solaire traditionnel</p>
            </div>
            <div class="text-center">
                <div class="text-4xl mb-3">🤝</div>
                <p class="font-serif font-bold text-lg mb-1">Commerce équitable</p>
                <p class="text-xs text-cream-100/60 leading-relaxed">Rémunération juste, traçabilité totale, partenariat durable avec nos agriculteurs</p>
            </div>
        </div>

        <?php if ($certifications): ?>
        <div class="mt-10 flex flex-wrap justify-center gap-3">
            <?php foreach (array_map('trim', explode(',', $certifications)) as $cert): ?>
            <?php if ($cert): ?>
            <span class="px-4 py-2 rounded-full bg-white/10 border border-white/20 text-cream-100 text-xs font-semibold backdrop-blur-sm flex items-center gap-1.5">
                🏅 <?= e($cert) ?>
            </span>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <p class="text-center mt-8 text-cream-100/40 text-xs font-serif italic">
            « Ny vanila malagasy — ny tsofina mamy ho an'izao tontolo izao »<br>
            <span class="text-[10px]">La vanille malgache — la douceur ambrée pour le monde entier</span>
        </p>
    </div>
</section>

<?php endif; // hasStory ?>

<!-- Recommendations: bundles, also-bought, linked recipes -->
<?php require __DIR__ . '/_recommendations.php'; ?>

<!-- Description tab (when no storytelling) -->
<?php if (!$hasStory): ?>
<section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 pb-16">
    <div class="max-w-2xl prose prose-vanilla text-vanilla-700 leading-relaxed text-body space-y-4">
        <?php foreach (explode("\n\n", $product['description'] ?? 'Aucune description disponible.') as $para): ?>
        <p><?= e(trim($para)) ?></p>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- ── Related Products ── -->
<?php if ($related): ?>
<section class="section bg-cream-200/50">
<div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-baseline justify-between mb-8">
        <h2 class="font-serif font-bold text-vanilla-800 text-h2"><?= t('product.related') ?></h2>
        <a href="<?= locale_url('shop') ?>" class="text-sm font-semibold text-vanilla-500 hover:text-vanilla-700 transition-colors"><?= t('product.view_all') ?> →</a>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
        <?php foreach ($related as $rel): ?>
        <?php
        $relImg = $rel['primary_image'] ?: $noImg;
        $relInStock = (int)$rel['stock'] > 0;
        ?>
        <article class="product-card group">
            <a href="<?= locale_url('shop/' . $rel['slug']) ?>" class="product-img-wrap block">
                <img src="<?= e($relImg) ?>" alt="<?= e($rel['name']) ?>"
                     class="w-full h-full object-cover transition-transform duration-700 ease-smooth group-hover:scale-110"
                     loading="lazy">
            </a>
            <div class="p-3">
                <h3 class="font-serif font-semibold text-vanilla-800 text-sm line-clamp-2 mb-1">
                    <a href="<?= locale_url('shop/' . $rel['slug']) ?>" class="hover:text-vanilla-600"><?= e($rel['name']) ?></a>
                </h3>
                <div class="flex items-center justify-between">
                    <span class="font-bold text-vanilla-700 text-sm"><?= number_format((float)$rel['price'], 2, ',', ' ') ?> €</span>
                    <?php if ($relInStock): ?>
                    <button
                        @click="$store.cart.add({ id:'<?= $rel['id'] ?>', name:'<?= addslashes(e($rel['name'])) ?>', price:<?= (float)$rel['price'] ?>, image:'<?= addslashes(e($relImg)) ?>' })"
                        class="p-1.5 rounded-lg bg-vanilla-100 hover:bg-vanilla-700 hover:text-cream-100 text-vanilla-700 transition-all duration-200"
                        title="<?= t('shop.add_to_cart') ?>"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" d="M12 4.5v15m7.5-7.5h-15"/>
                        </svg>
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
</div>
</section>
<?php endif; ?>

</div><!-- /x-data -->
