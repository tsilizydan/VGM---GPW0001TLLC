<!-- ══════════════════════════════════════════════
     DESIGN SYSTEM STYLEGUIDE — /design
     Full component showcase (dev only)
     ══════════════════════════════════════════════ -->

<!-- Hero -->
<section class="py-16 bg-gradient-to-b from-vanilla-800 to-vanilla-700 text-center relative overflow-hidden">
    <div class="absolute inset-0 opacity-[0.07]"
         style="background-image:url(\"data:image/svg+xml,%3Csvg width='48' height='48' viewBox='0 0 48 48' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23F8F5F0'%3E%3Cpath d='M0 0h24v24H0zM24 24h24v24H24z'/%3E%3C/g%3E%3C/svg%3E\")">
    </div>
    <div class="relative">
        <p class="text-gold-300 text-xs tracking-[0.2em] uppercase font-semibold mb-3">Système de Design</p>
        <h1 class="font-serif font-bold text-cream-100 text-display mb-3">Design System</h1>
        <p class="text-vanilla-300 max-w-lg mx-auto">Vanilla Groupe Madagascar · Composants &amp; Tokens</p>
    </div>
</section>

<div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-16 space-y-24">

<!-- ── 1. COLOR PALETTE ── -->
<section>
    <h2 class="font-serif font-bold text-vanilla-800 text-h2 mb-2">1. Palette de couleurs</h2>
    <p class="text-vanilla-400 text-sm mb-8">Tokens centralisés dans <code class="bg-cream-200 px-1.5 py-0.5 rounded text-xs">tailwind.config.js</code></p>

    <?php
    $palettes = [
        'Vanilla (Primary)' => ['bg-vanilla-50','bg-vanilla-100','bg-vanilla-200','bg-vanilla-300','bg-vanilla-400','bg-vanilla-500','bg-vanilla-600','bg-vanilla-700','bg-vanilla-800','bg-vanilla-900'],
        'Cream (Secondary)' => ['bg-cream-50','bg-cream-100','bg-cream-200','bg-cream-300','bg-cream-400','bg-cream-500'],
        'Forest (Accent)'   => ['bg-forest-50','bg-forest-100','bg-forest-200','bg-forest-300','bg-forest-400','bg-forest-500','bg-forest-600','bg-forest-700'],
        'Gold (Luxury)'     => ['bg-gold-50','bg-gold-100','bg-gold-200','bg-gold-300','bg-gold-400','bg-gold-500','bg-gold-600','bg-gold-700'],
    ];
    foreach ($palettes as $name => $swatches):
    ?>
    <div class="mb-6">
        <p class="text-xs font-semibold text-vanilla-500 mb-2 uppercase tracking-wide"><?= e($name) ?></p>
        <div class="flex flex-wrap gap-2">
            <?php foreach ($swatches as $swatch): ?>
            <div class="flex flex-col items-center gap-1">
                <div class="w-12 h-12 rounded-xl border border-white/50 shadow-soft <?= $swatch ?>"></div>
                <span class="text-[10px] text-vanilla-400 font-mono"><?= str_replace('bg-', '', $swatch) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>
</section>

<div class="border-t border-vanilla-100"></div>

<!-- ── 2. TYPOGRAPHY ── -->
<section>
    <h2 class="font-serif font-bold text-vanilla-800 text-h2 mb-8">2. Typographie</h2>
    <div class="space-y-6">
        <div><p class="text-xs text-vanilla-400 mb-1 font-mono">font-serif · display · 4.5rem</p>
             <p class="font-serif font-bold text-vanilla-900 leading-none" style="font-size:4rem">Vanilla Madagascar</p></div>
        <div><p class="text-xs text-vanilla-400 mb-1 font-mono">font-serif · h1 · 3.25rem</p>
             <h1 class="font-serif font-bold text-vanilla-800 text-h1">Extrait de Vanille Premium</h1></div>
        <div><p class="text-xs text-vanilla-400 mb-1 font-mono">font-serif · h2 · 2.25rem</p>
             <h2 class="font-serif font-bold text-vanilla-800 text-h2">Notre Collection</h2></div>
        <div><p class="text-xs text-vanilla-400 mb-1 font-mono">font-serif · h3 · 1.5rem</p>
             <h3 class="font-serif font-bold text-vanilla-800 text-h3">Origine · Sava, Madagascar</h3></div>
        <div><p class="text-xs text-vanilla-400 mb-1 font-mono">font-sans · body-lg · 1.125rem</p>
             <p class="font-sans text-body-lg text-vanilla-700">Des produits d'exception, cultivés avec soin dans les terres riches de Madagascar.</p></div>
        <div><p class="text-xs text-vanilla-400 mb-1 font-mono">font-sans · body · 1rem</p>
             <p class="font-sans text-body text-vanilla-600">Un extrait pur et intense élaboré à partir de gousses de vanille sélectionnées à Madagascar.</p></div>
        <div><p class="text-xs text-vanilla-400 mb-1 font-mono">font-sans · caption · 0.75rem · tracking-wide</p>
             <p class="font-sans text-caption text-vanilla-400 uppercase tracking-widest">COMMERCE ÉQUITABLE · NATUREL · PREMIUM</p></div>
        <div><p class="text-xs text-vanilla-400 mb-1 font-mono">gradient-text-warm</p>
             <p class="font-serif font-bold text-h2 bg-gradient-to-r from-vanilla-700 via-gold-500 to-vanilla-600 bg-clip-text text-transparent">
                 Pure Malagasy Vanilla
             </p></div>
    </div>
</section>

<div class="border-t border-vanilla-100"></div>

<!-- ── 3. BUTTONS ── -->
<section>
    <h2 class="font-serif font-bold text-vanilla-800 text-h2 mb-8">3. Boutons</h2>
    <div class="flex flex-wrap items-center gap-3 mb-6">
        <button class="inline-flex items-center justify-center gap-2 font-sans font-semibold text-sm px-6 py-3 rounded-lg border border-transparent transition-all duration-250 bg-vanilla-700 text-cream-100 hover:bg-vanilla-600 hover:shadow-[0_0_24px_rgba(200,169,106,0.35)] hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-vanilla-500 focus:ring-offset-2">
            Primaire
        </button>
        <button class="inline-flex items-center justify-center gap-2 font-sans font-semibold text-sm px-6 py-3 rounded-lg border border-vanilla-300/60 transition-all duration-250 bg-white/15 text-vanilla-700 backdrop-blur-sm hover:bg-white/30 hover:border-vanilla-400 hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-vanilla-400 focus:ring-offset-2">
            Secondaire
        </button>
        <button class="inline-flex items-center justify-center gap-2 font-sans font-semibold text-sm px-6 py-3 rounded-lg border border-transparent transition-all duration-250 bg-transparent text-vanilla-600 hover:bg-vanilla-50 hover:text-vanilla-800 focus:outline-none focus:ring-2 focus:ring-vanilla-300 focus:ring-offset-2">
            Ghost
        </button>
        <button class="inline-flex items-center justify-center gap-2 font-sans font-semibold text-sm px-6 py-3 rounded-lg border border-transparent transition-all duration-250 bg-forest-400 text-white hover:bg-forest-500 hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-forest-300 focus:ring-offset-2">
            Forest
        </button>
        <button class="inline-flex items-center justify-center gap-2 font-sans font-semibold text-sm px-6 py-3 rounded-lg border border-transparent transition-all duration-250 bg-gold-400 text-vanilla-900 hover:bg-gold-300 hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-gold-300 focus:ring-offset-2">
            Gold
        </button>
    </div>
    <!-- Sizes -->
    <div class="flex flex-wrap items-center gap-3 mb-6">
        <button class="inline-flex font-semibold text-[10px] px-3 py-1.5 rounded-md bg-vanilla-700 text-cream-100">xs</button>
        <button class="inline-flex font-semibold text-xs px-4 py-2 rounded-lg bg-vanilla-700 text-cream-100">sm</button>
        <button class="inline-flex font-semibold text-sm px-6 py-3 rounded-lg bg-vanilla-700 text-cream-100">md (default)</button>
        <button class="inline-flex font-semibold text-base px-8 py-4 rounded-xl bg-vanilla-700 text-cream-100">lg</button>
    </div>
    <!-- States -->
    <div class="flex flex-wrap items-center gap-3">
        <button class="inline-flex font-semibold text-sm px-6 py-3 rounded-lg bg-vanilla-700 text-cream-100 opacity-50 cursor-not-allowed" disabled>Désactivé</button>
        <button class="inline-flex font-semibold text-sm px-6 py-3 rounded-lg bg-vanilla-700 text-cream-100 ring-2 ring-vanilla-500 ring-offset-2">Focus</button>
    </div>
</section>

<div class="border-t border-vanilla-100"></div>

<!-- ── 4. BADGES ── -->
<section>
    <h2 class="font-serif font-bold text-vanilla-800 text-h2 mb-6">4. Badges</h2>
    <div class="flex flex-wrap gap-3">
        <span class="inline-flex items-center text-[11px] font-bold px-2.5 py-1 rounded-full border uppercase tracking-wide bg-gold-100 text-gold-700 border-gold-300">Premium</span>
        <span class="inline-flex items-center text-[11px] font-bold px-2.5 py-1 rounded-full border uppercase tracking-wide bg-forest-100 text-forest-700 border-forest-300">Bio</span>
        <span class="inline-flex items-center text-[11px] font-bold px-2.5 py-1 rounded-full border uppercase tracking-wide bg-vanilla-100 text-vanilla-700 border-vanilla-300">Nouveau</span>
        <span class="inline-flex items-center text-[11px] font-bold px-2.5 py-1 rounded-full border uppercase tracking-wide bg-red-100 text-red-700 border-red-300">Promo</span>
    </div>
</section>

<div class="border-t border-vanilla-100"></div>

<!-- ── 5. GLASS EFFECTS ── -->
<section>
    <h2 class="font-serif font-bold text-vanilla-800 text-h2 mb-6">5. Glassmorphism</h2>
    <div class="relative rounded-2xl overflow-hidden p-8"
         style="background:linear-gradient(135deg,#4E342E,#6A8F4E)">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="p-5 rounded-xl backdrop-blur-sm bg-white/[0.08] border border-white/15 text-center">
                <p class="text-xs text-white/60 mb-1">glass-sm</p>
                <p class="text-white font-semibold">Subtle</p>
            </div>
            <div class="p-5 rounded-xl backdrop-blur-md bg-white/[0.15] border border-white/20 shadow-[0_4px_32px_rgba(78,52,46,0.08)] text-center">
                <p class="text-xs text-white/60 mb-1">glass (default)</p>
                <p class="text-white font-semibold">Standard</p>
            </div>
            <div class="p-5 rounded-xl backdrop-blur-xl bg-white/[0.25] border border-white/30 shadow-[0_12px_48px_rgba(78,52,46,0.15)] text-center">
                <p class="text-xs text-white/60 mb-1">glass-strong</p>
                <p class="text-white font-semibold">Strong</p>
            </div>
        </div>
    </div>
</section>

<div class="border-t border-vanilla-100"></div>

<!-- ── 6. FORM INPUTS ── -->
<section>
    <h2 class="font-serif font-bold text-vanilla-800 text-h2 mb-8">6. Formulaires</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 max-w-2xl">
        <?php
        $field = ['name'=>'demo_text','label'=>'Champ texte','type'=>'text','placeholder'=>'Jean Rakoto','value'=> '','required'=>true,'hint'=>'Votre nom complet.'];
        require __DIR__ . '/../components/forms/input.php';
        $field = ['name'=>'demo_email','label'=>'Adresse e-mail','type'=>'email','placeholder'=>'vous@exemple.com','value'=>'','required'=>true];
        require __DIR__ . '/../components/forms/input.php';
        $field = ['name'=>'demo_error','label'=>'État d\'erreur','type'=>'text','placeholder'=>'...','value'=>'','required'=>true,'error'=>'Ce champ est obligatoire.'];
        require __DIR__ . '/../components/forms/input.php';
        $field = ['name'=>'demo_select','label'=>'Sélection','options'=>['' => 'Choisir...','premium'=>'Premium','bio'=>'Bio'],'value'=>''];
        require __DIR__ . '/../components/forms/select.php';
        ?>
        <div class="sm:col-span-2">
        <?php
        $field = ['name'=>'demo_textarea','label'=>'Message','rows'=>4,'placeholder'=>'Votre message...','value'=>''];
        require __DIR__ . '/../components/forms/textarea.php';
        ?>
        </div>
    </div>
</section>

<div class="border-t border-vanilla-100"></div>

<!-- ── 7. ALERTS ── -->
<section>
    <h2 class="font-serif font-bold text-vanilla-800 text-h2 mb-6">7. Alertes</h2>
    <div
        x-data="{ show: true }"
        class="space-y-3 max-w-xl"
    >
        <div class="flex items-start gap-3 p-4 rounded-xl border backdrop-blur-sm bg-forest-50 border-forest-200 text-forest-800 text-sm font-medium">
            <span>✅</span><span>Produit ajouté au panier avec succès.</span>
        </div>
        <div class="flex items-start gap-3 p-4 rounded-xl border backdrop-blur-sm bg-red-50 border-red-200 text-red-800 text-sm font-medium">
            <span>❌</span><span>Une erreur s'est produite. Veuillez réessayer.</span>
        </div>
        <div class="flex items-start gap-3 p-4 rounded-xl border backdrop-blur-sm bg-gold-50 border-gold-200 text-gold-800 text-sm font-medium">
            <span>ℹ️</span><span>Votre commande est en cours de traitement.</span>
        </div>
        <!-- Auto-dismiss demo -->
        <div
            x-data
            class="flex items-center justify-between p-4 rounded-xl border backdrop-blur-sm bg-forest-50 border-forest-200 text-forest-800 text-sm font-medium"
        >
            <span class="flex items-center gap-2">✅ Alerte avec auto-dismiss (Alpine demo)</span>
            <button
                @click="$store.alerts.add('Test d\'alerte auto-dismiss !', 'success')"
                class="text-xs font-bold bg-forest-200 hover:bg-forest-300 px-3 py-1 rounded-full transition-colors ml-3">
                Tester
            </button>
        </div>
    </div>
</section>

<div class="border-t border-vanilla-100"></div>

<!-- ── 8. PRODUCT CARD ── -->
<section>
    <h2 class="font-serif font-bold text-vanilla-800 text-h2 mb-8">8. Carte Produit</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 max-w-3xl">
        <?php
        foreach ([
            ['id'=>99,'slug'=>'demo-premium','name'=>'Extrait Premium','price'=>34.90,'image'=>'','badge'=>'Premium','description'=>'Un extrait pur et intense.'],
            ['id'=>98,'slug'=>'demo-bio','name'=>'Gousses Bio','price'=>24.90,'original_price'=>29.90,'image'=>'','badge'=>'Bio','description'=>'Cultivées sans pesticides.'],
            ['id'=>97,'slug'=>'demo-new','name'=>'Poudre Vanille','price'=>18.50,'image'=>'','badge'=>'Nouveau','description'=>'Finement broyée.'],
        ] as $product):
        ?>
        <?php require __DIR__ . '/../components/product-card.php'; ?>
        <?php endforeach; ?>
    </div>
</section>

<div class="border-t border-vanilla-100"></div>

<!-- ── 9. MODAL ── -->
<section>
    <h2 class="font-serif font-bold text-vanilla-800 text-h2 mb-6">9. Modal</h2>
    <div x-data class="flex gap-3 flex-wrap">
        <button @click="$store.modal.open('demo-modal-sm')"
                class="inline-flex font-semibold text-sm px-6 py-3 rounded-xl bg-vanilla-700 text-cream-100 hover:bg-vanilla-600 transition-all">
            Ouvrir modal (sm)
        </button>
        <button @click="$store.modal.open('demo-modal-lg')"
                class="inline-flex font-semibold text-sm px-6 py-3 rounded-xl border border-vanilla-300 text-vanilla-700 hover:bg-vanilla-50 transition-all">
            Ouvrir modal (lg)
        </button>
    </div>

    <!-- Modals -->
    <?php
    $modalId = 'demo-modal-sm'; $title = 'Modal — Taille SM'; $size = 'sm';
    $content = '<p class="text-sm text-vanilla-600 leading-relaxed">Ceci est un exemple de modal réutilisable. Fermez-le avec ✕, en cliquant en dehors, ou avec <kbd class=\'bg-cream-200 px-1.5 py-0.5 rounded text-xs\'>ESC</kbd>.</p><div class="mt-5 flex gap-2"><button @click="$store.modal.close()" class=\'px-4 py-2 rounded-xl bg-vanilla-700 text-cream-100 text-sm font-semibold hover:bg-vanilla-600 transition-all\'>Confirmer</button><button @click="$store.modal.close()" class=\'px-4 py-2 rounded-xl border border-vanilla-300 text-vanilla-600 text-sm font-semibold hover:bg-vanilla-50 transition-all\'>Annuler</button></div>';
    require __DIR__ . '/../components/modal.php';

    $modalId = 'demo-modal-lg'; $title = 'Modal — Taille LG'; $size = 'lg';
    $content = '<p class="text-sm text-vanilla-600">Exemple de modal à grande taille pour afficher davantage de contenu (détails produit, formulaires complexes, etc.).</p>';
    require __DIR__ . '/../components/modal.php';
    ?>
</section>

<div class="border-t border-vanilla-100"></div>

<!-- ── 10. MALAGASY PATTERNS ── -->
<section>
    <h2 class="font-serif font-bold text-vanilla-800 text-h2 mb-6">10. Motifs Malgaches</h2>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="h-32 rounded-2xl border border-vanilla-200"
             style="background:url(\"data:image/svg+xml,%3Csvg width='48' height='48' viewBox='0 0 48 48' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%234E342E' fill-opacity='0.04'%3E%3Cpath d='M0 0h24v24H0zM24 24h24v24H24z'/%3E%3C/g%3E%3C/svg%3E\"),#F8F5F0">
            <div class="h-full flex items-end p-3"><span class="text-xs font-semibold text-vanilla-500">Tissage Malgache</span></div>
        </div>
        <div class="h-32 rounded-2xl border border-vanilla-200"
             style="background:url(\"data:image/svg+xml,%3Csvg width='24' height='24' viewBox='0 0 24 24' xmlns='http://www.w3.org/2000/svg'%3E%3Ccircle cx='12' cy='12' r='2' fill='%236A8F4E' fill-opacity='0.08'/%3E%3C/svg%3E\"),#F2F7EE">
            <div class="h-full flex items-end p-3"><span class="text-xs font-semibold text-forest-500">Points Bio</span></div>
        </div>
        <div class="h-32 rounded-2xl overflow-hidden"
             style="background:linear-gradient(135deg,#4E342E 0%,#7A4E33 35%,#6A8F4E 100%)">
            <div class="h-full flex items-end p-3"><span class="text-xs font-semibold text-gold-300">Dégradé Héros</span></div>
        </div>
    </div>
</section>

</div>
<!-- End styleguide content -->
