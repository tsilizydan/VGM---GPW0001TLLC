<!-- ══════════════════════════════════════════════
     ADMIN — Product Create / Edit Form
     Alpine.js tabs: Informations | Traductions | Images | Stock & Variations
     ══════════════════════════════════════════════ -->

<?php
$isEdit    = $product !== null;
$action    = $isEdit
    ? locale_url("admin/products/{$product['id']}")
    : locale_url('admin/products');

$tr   = $product['translations'] ?? [];
$imgs = $product['images'] ?? [];
$vars = $product['variations'] ?? [];
?>

<section
    x-data="{
        tab: 'info',
        variations: <?= json_encode(array_map(fn($v) => [
            'sku'      => $v['sku'] ?? '',
            'price'    => $v['price'] ?? '',
            'stock'    => $v['stock'] ?? 0,
            'attr_keys' => array_keys($v['attributes'] ?? []),
            'attr_vals' => array_values($v['attributes'] ?? []),
        ], $vars), JSON_HEX_TAG) ?>,
        addVariation() {
            this.variations.push({ sku:'', price:'', stock:0, attr_keys:[''], attr_vals:[''] });
        },
        removeVariation(i) { this.variations.splice(i, 1); },
        addAttr(i) {
            this.variations[i].attr_keys.push('');
            this.variations[i].attr_vals.push('');
        },
        removeAttr(vi, ai) {
            this.variations[vi].attr_keys.splice(ai, 1);
            this.variations[vi].attr_vals.splice(ai, 1);
        }
    }"
    class="py-8"
>
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-vanilla-400 mb-1">Administration &rsaquo; Produits</p>
            <h1 class="font-serif font-bold text-vanilla-900 text-2xl"><?= e($title) ?></h1>
        </div>
        <a href="<?= locale_url('admin/products') ?>"
           class="text-sm font-semibold text-vanilla-500 hover:text-vanilla-800 transition-colors">
            ← Retour à la liste
        </a>
    </div>

    <!-- Flash errors -->
    <?php if ($errors = \Core\Session::getFlash('errors')): ?>
    <div class="alert-error alert mb-4">
        <ul class="list-disc list-inside text-sm">
            <?php foreach ((array)$errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <!-- Tab nav -->
    <div class="flex gap-1 mb-6 bg-cream-100 p-1 rounded-xl border border-vanilla-200/50 w-fit flex-wrap">
        <?php foreach ([
            'info'    => '📋 Informations',
            'story'   => '📖 Histoire',
            'trans'   => '🌍 Traductions',
            'images'  => '🖼 Images',
            'stock'   => '📦 Stock & Variations',
        ] as $key => $label): ?>
        <button
            @click="tab = '<?= $key ?>'" 
            :class="tab === '<?= $key ?>'
                ? 'bg-vanilla-700 text-cream-100 shadow-sm'
                : 'text-vanilla-500 hover:text-vanilla-700'"
            class="px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200"
        ><?= $label ?></button>
        <?php endforeach; ?>
    </div>

    <!-- FORM -->
    <form method="POST" action="<?= e($action) ?>" enctype="multipart/form-data" class="space-y-0">
        <?= csrf_field() ?>

        <!-- ── TAB: Informations ── -->
        <div x-show="tab === 'info'" class="space-y-5">
            <div class="glass-card p-6 rounded-2xl space-y-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- French name (drives slug) -->
                    <div>
                        <label class="form-label">Nom (français) <span class="text-red-500">*</span></label>
                        <input type="text" name="fr_name" id="fr_name"
                               value="<?= e($tr['fr']['name'] ?? '') ?>"
                               required
                               class="form-input"
                               @input="
                                    if (!$el.closest('form').querySelector('#slug').dataset.manual) {
                                        $el.closest('form').querySelector('#slug').value =
                                            $event.target.value.toLowerCase()
                                                .normalize('NFKD').replace(/[\u0300-\u036f]/g,'')
                                                .replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,'');
                                    }
                               ">
                    </div>
                    <!-- Slug -->
                    <div>
                        <label class="form-label">Slug (URL)</label>
                        <input type="text" name="slug" id="slug"
                               value="<?= e($product['slug'] ?? '') ?>"
                               class="form-input font-mono text-sm"
                               @input="$el.dataset.manual = '1'">
                        <p class="form-hint">Généré automatiquement depuis le nom FR.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <!-- Category -->
                    <div>
                        <label class="form-label">Catégorie</label>
                        <select name="category_id" class="form-input">
                            <option value="">— Aucune —</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"
                                    <?= ($product['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                <?= e($cat['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <!-- SKU -->
                    <div>
                        <label class="form-label">SKU</label>
                        <input type="text" name="sku" value="<?= e($product['sku'] ?? '') ?>" class="form-input font-mono text-sm" placeholder="EXT-001">
                    </div>
                    <!-- Sort order -->
                    <div>
                        <label class="form-label">Ordre d'affichage</label>
                        <input type="number" name="sort_order" value="<?= (int)($product['sort_order'] ?? 0) ?>" min="0" class="form-input">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <!-- Price -->
                    <div>
                        <label class="form-label">Prix (€) <span class="text-red-500">*</span></label>
                        <input type="number" name="price" step="0.01" min="0"
                               value="<?= e($product['price'] ?? '') ?>"
                               required class="form-input" placeholder="0.00">
                    </div>
                    <!-- Compare price -->
                    <div>
                        <label class="form-label">Prix barré (€)</label>
                        <input type="number" name="compare_price" step="0.01" min="0"
                               value="<?= e($product['compare_price'] ?? '') ?>"
                               class="form-input" placeholder="Optionnel">
                    </div>
                    <!-- Status -->
                    <div>
                        <label class="form-label">Statut</label>
                        <select name="status" class="form-input">
                            <?php foreach (['active' => 'Actif', 'draft' => 'Brouillon', 'archived' => 'Archivé'] as $v => $l): ?>
                            <option value="<?= $v ?>" <?= ($product['status'] ?? 'draft') === $v ? 'selected' : '' ?>><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Featured toggle -->
                <label class="flex items-center gap-3 cursor-pointer">
                    <div class="relative">
                        <input type="hidden" name="featured" value="0">
                        <input type="checkbox" name="featured" value="1"
                               <?= ($product['featured'] ?? 0) ? 'checked' : '' ?>
                               class="sr-only peer">
                        <div class="w-10 h-6 bg-vanilla-200 rounded-full peer-checked:bg-vanilla-700 transition-colors duration-200"></div>
                        <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full shadow peer-checked:translate-x-4 transition-transform duration-200"></div>
                    </div>
                    <span class="text-sm font-semibold text-vanilla-700">Produit vedette (mis en avant sur la page d'accueil)</span>
                </label>
            </div>
        </div>

        <!-- ── TAB: Histoire (Storytelling) ── -->
        <div x-show="tab === 'story'" class="space-y-6">

            <!-- Locale loop for narrative fields -->
            <?php foreach ($locales as $lc => $lcLabel): ?>
            <div class="glass-card p-6 rounded-2xl space-y-4">
                <h3 class="font-serif font-semibold text-vanilla-800 text-base mb-1">
                    📖 <?= $lcLabel ?>
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">🗺 Région d'origine</label>
                        <input type="text" name="<?= $lc ?>_origin_region"
                               value="<?= e($tr[$lc]['origin_region'] ?? '') ?>"
                               placeholder="ex: SAVA — Sambava, Madagascar"
                               class="form-input text-sm">
                    </div>
                    <div>
                        <label class="form-label">👤 Nom de l'agriculteur</label>
                        <input type="text" name="<?= $lc ?>_farmer_name"
                               value="<?= e($tr[$lc]['farmer_name'] ?? '') ?>"
                               placeholder="ex: Jean-Baptiste Rakotonirina"
                               class="form-input text-sm">
                    </div>
                </div>
                <div>
                    <label class="form-label">💬 Citation de l'agriculteur</label>
                    <input type="text" name="<?= $lc ?>_farmer_quote"
                           value="<?= e($tr[$lc]['farmer_quote'] ?? '') ?>"
                           placeholder="ex: Chaque gousse est cueillie à la main, avec patience…"
                           class="form-input text-sm">
                </div>
                <div>
                    <label class="form-label">📝 Histoire de l'agriculteur</label>
                    <textarea name="<?= $lc ?>_farmer_story" rows="4" class="form-input text-sm resize-y"
                              placeholder="Narratif — qui est cet agriculteur, son histoire avec la vanille…"><?= e($tr[$lc]['farmer_story'] ?? '') ?></textarea>
                </div>
                <div>
                    <label class="form-label">🌿 Processus de récolte</label>
                    <textarea name="<?= $lc ?>_harvest_process" rows="4" class="form-input text-sm resize-y"
                              placeholder="Comment la vanille est cultivée, pollinisée, récoltée et séchée…"><?= e($tr[$lc]['harvest_process'] ?? '') ?></textarea>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">📅 Saison de récolte</label>
                        <input type="text" name="<?= $lc ?>_harvest_season"
                               value="<?= e($tr[$lc]['harvest_season'] ?? '') ?>"
                               placeholder="ex: Mai – Juillet"
                               class="form-input text-sm">
                    </div>
                    <div>
                        <label class="form-label">🏅 Certifications</label>
                        <input type="text" name="<?= $lc ?>_certifications"
                               value="<?= e($tr[$lc]['certifications'] ?? '') ?>"
                               placeholder="ex: Bio, Fairtrade, Rainforest Alliance"
                               class="form-input text-sm">
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- Story media upload -->
            <?php if ($isEdit): ?>
            <div class="glass-card p-6 rounded-2xl space-y-4">
                <h3 class="font-semibold text-vanilla-800 mb-2">📷 Médias de l'histoire</h3>
                <p class="text-xs text-vanilla-400 mb-4">Images spécifiques à chaque section du récit (optionnel).</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <?php foreach (['farmer' => '👤 Agriculteur', 'region' => '🗺 Région', 'harvest' => '🌿 Récolte', 'process' => '⚗️ Processus'] as $sec => $secLabel): ?>
                <div>
                    <label class="form-label"><?= $secLabel ?></label>
                    <?php if (!empty($product['story_media'][$sec])): ?>
                    <div class="flex flex-wrap gap-2 mb-2">
                        <?php foreach ($product['story_media'][$sec] as $sm): ?>
                        <div class="relative group w-16 h-16 rounded-lg overflow-hidden border border-vanilla-200">
                            <img src="<?= e($sm['path']) ?>" class="w-full h-full object-cover">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    <input type="file" name="story_media_<?= $sec ?>[]" accept="image/*" multiple
                           class="text-xs text-vanilla-500 file:btn-ghost file:btn file:btn-sm file:text-xs file:py-1 file:mr-2">
                </div>
                <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- ── TAB: Translations ── -->
        <div x-show="tab === 'trans'" class="space-y-4">
            <?php foreach ($locales as $lc => $lcLabel): ?>
            <div class="glass-card p-6 rounded-2xl">
                <h3 class="font-serif font-semibold text-vanilla-800 text-base mb-4"><?= $lcLabel ?></h3>
                <div class="space-y-4">
                    <div>
                        <label class="form-label">Nom</label>
                        <input type="text" name="<?= $lc ?>_name"
                               value="<?= e($tr[$lc]['name'] ?? '') ?>"
                               class="form-input" placeholder="Nom du produit en <?= $lcLabel ?>">
                    </div>
                    <div>
                        <label class="form-label">Description</label>
                        <textarea name="<?= $lc ?>_description" rows="3" class="form-input resize-y"
                                  placeholder="Description courte…"><?= e($tr[$lc]['description'] ?? '') ?></textarea>
                    </div>
                    <div>
                        <label class="form-label">Histoire / Origine <span class="text-vanilla-400 text-xs font-normal">(section "Notre story")</span></label>
                        <textarea name="<?= $lc ?>_story" rows="4" class="form-input resize-y"
                                  placeholder="Paragraphe narratif sur la provenance et le savoir-faire…"><?= e($tr[$lc]['story'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- ── TAB: Images ── -->
        <div x-show="tab === 'images'" class="space-y-4">
            <!-- Existing images -->
            <?php if ($imgs): ?>
            <div class="glass-card p-6 rounded-2xl">
                <h3 class="font-semibold text-vanilla-800 mb-4">Images actuelles</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <?php foreach ($imgs as $img): ?>
                    <div class="relative group rounded-xl overflow-hidden border-2 <?= $img['is_primary'] ? 'border-vanilla-700' : 'border-vanilla-100' ?>">
                        <img src="<?= e($img['path']) ?>" alt="<?= e($img['alt'] ?? '') ?>"
                             class="w-full aspect-square object-cover">
                        <?php if ($img['is_primary']): ?>
                        <span class="absolute top-1 left-1 text-[10px] font-bold bg-vanilla-700 text-cream-100 px-1.5 py-0.5 rounded">PRINCIPALE</span>
                        <?php endif; ?>
                        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                            <!-- Set as primary -->
                            <?php if (!$img['is_primary']): ?>
                            <form method="POST" action="<?= locale_url("admin/products/{$product['id']}/img-primary") ?>">
                                <?= csrf_field() ?>
                                <input type="hidden" name="image_id" value="<?= $img['id'] ?>">
                                <button type="submit" title="Définir comme principale"
                                        class="p-1.5 bg-gold-500 text-white rounded-lg text-xs hover:bg-gold-600">⭐</button>
                            </form>
                            <?php endif; ?>
                            <!-- Delete -->
                            <form method="POST" action="<?= locale_url("admin/products/{$product['id']}/img-del") ?>"
                                  onsubmit="return confirm('Supprimer cette image ?')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="image_id" value="<?= $img['id'] ?>">
                                <button type="submit" title="Supprimer"
                                        class="p-1.5 bg-red-500 text-white rounded-lg text-xs hover:bg-red-600">✕</button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Upload new -->
            <div class="glass-card p-6 rounded-2xl">
                <h3 class="font-semibold text-vanilla-800 mb-4">Ajouter des images</h3>
                <label
                    class="flex flex-col items-center justify-center gap-3 p-8 rounded-xl border-2 border-dashed border-vanilla-300 hover:border-vanilla-500 cursor-pointer transition-colors bg-cream-50 hover:bg-cream-100"
                    x-data="{ files: [] }"
                    @dragover.prevent
                    @drop.prevent="
                        files = [...$event.dataTransfer.files];
                        $refs.imgInput.files = $event.dataTransfer.files;
                    "
                >
                    <svg class="w-10 h-10 text-vanilla-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/>
                    </svg>
                    <div class="text-center">
                        <p class="text-sm font-semibold text-vanilla-700">Cliquez ou glissez-déposez vos images ici</p>
                        <p class="text-xs text-vanilla-400 mt-1">JPG, PNG, WebP · Max 5 Mo chacune · Plusieurs fichiers autorisés</p>
                    </div>
                    <input type="file" name="images[]" x-ref="imgInput" multiple accept="image/*"
                           @change="files = [...$event.target.files]" class="sr-only">
                    <!-- File count feedback -->
                    <p x-show="files.length > 0"
                       x-text="files.length + ' fichier(s) sélectionné(s)'"
                       class="text-xs font-semibold text-forest-700 bg-forest-50 px-3 py-1 rounded-pill"></p>
                </label>
                <p class="text-xs text-vanilla-400 mt-2">La première image uploadée sera définie comme image principale si aucune n'existe encore.</p>
            </div>
        </div>

        <!-- ── TAB: Stock & Variations ── -->
        <div x-show="tab === 'stock'" class="space-y-4">
            <!-- Base stock -->
            <div class="glass-card p-6 rounded-2xl">
                <h3 class="font-semibold text-vanilla-800 mb-4">Stock de base</h3>
                <div class="flex items-center gap-4">
                    <div class="w-48">
                        <label class="form-label">Quantité en stock</label>
                        <input type="number" name="stock" min="0"
                               value="<?= (int)($product['stock'] ?? 0) ?>"
                               class="form-input" required>
                    </div>
                    <p class="text-sm text-vanilla-500 mt-5">
                        Stock = 0 affichera « Rupture de stock » sur la fiche produit et désactivera le bouton d'achat.
                    </p>
                </div>
            </div>

            <!-- Variations -->
            <div class="glass-card p-6 rounded-2xl">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-vanilla-800">Variations</h3>
                    <button type="button" @click="addVariation()"
                            class="btn-ghost btn btn-sm text-xs">
                        + Ajouter une variation
                    </button>
                </div>
                <p class="text-xs text-vanilla-400 mb-4">
                    Exemples d'attributs : <code>Contenance → 100 ml</code>, <code>Format → Extrait</code>, <code>Poids → 50 g</code>
                </p>

                <!-- Variation rows -->
                <div class="space-y-4">
                <template x-for="(v, vi) in variations" :key="vi">
                    <div class="border border-vanilla-200 rounded-xl p-4 space-y-3 bg-cream-50">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-vanilla-500 uppercase tracking-widest" x-text="'Variation ' + (vi+1)"></span>
                            <button type="button" @click="removeVariation(vi)"
                                    class="text-xs text-red-400 hover:text-red-600 font-semibold">✕ Supprimer</button>
                        </div>
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="form-label text-xs">SKU</label>
                                <input type="text" :name="`variations[${vi}][sku]`" x-model="v.sku"
                                       class="form-input text-sm py-2" placeholder="EXT-001-100ML">
                            </div>
                            <div>
                                <label class="form-label text-xs">Prix override (€)</label>
                                <input type="number" :name="`variations[${vi}][price]`" x-model="v.price"
                                       step="0.01" min="0"
                                       class="form-input text-sm py-2" placeholder="Laisser vide = prix produit">
                            </div>
                            <div>
                                <label class="form-label text-xs">Stock</label>
                                <input type="number" :name="`variations[${vi}][stock]`" x-model="v.stock"
                                       min="0" class="form-input text-sm py-2">
                            </div>
                        </div>
                        <!-- Attributes -->
                        <div>
                            <label class="form-label text-xs mb-2">Attributs (clé → valeur)</label>
                            <template x-for="(key, ai) in v.attr_keys" :key="ai">
                                <div class="flex items-center gap-2 mb-2">
                                    <input type="text" :name="`variations[${vi}][attr_keys][${ai}]`"
                                           x-model="v.attr_keys[ai]"
                                           class="form-input text-sm py-1.5 w-36" placeholder="ex: Contenance">
                                    <span class="text-vanilla-400">→</span>
                                    <input type="text" :name="`variations[${vi}][attr_vals][${ai}]`"
                                           x-model="v.attr_vals[ai]"
                                           class="form-input text-sm py-1.5 w-36" placeholder="ex: 100 ml">
                                    <button type="button" @click="removeAttr(vi, ai)"
                                            class="text-red-400 hover:text-red-600 text-sm">✕</button>
                                </div>
                            </template>
                            <button type="button" @click="addAttr(vi)"
                                    class="text-xs text-forest-600 hover:text-forest-800 font-semibold mt-1">
                                + Ajouter un attribut
                            </button>
                        </div>
                    </div>
                </template>
                <p x-show="variations.length === 0" class="text-sm text-vanilla-400 py-4 text-center">
                    Aucune variation. Cliquez sur « + Ajouter une variation » pour commencer.
                </p>
                </div>
            </div>
        </div>

        <!-- Submit -->
        <div class="flex items-center justify-between pt-6 border-t border-vanilla-100">
            <a href="<?= locale_url('admin/products') ?>" class="btn-ghost btn">Annuler</a>
            <button type="submit" class="btn-primary btn">
                <?= $isEdit ? 'Enregistrer les modifications' : 'Créer le produit' ?>
            </button>
        </div>
    </form>
</div>
</section>
