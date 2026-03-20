<!-- ══════════════════════════════════════════════
     FOOTER — 4-column premium footer
     ══════════════════════════════════════════════ -->
<footer class="bg-vanilla-900 text-cream-200 pt-16 pb-8 mt-auto">

    <!-- Organic divider wave -->
    <div class="-mt-1 mb-0 overflow-hidden leading-none">
        <svg viewBox="0 0 1440 40" preserveAspectRatio="none" class="-scale-y-100 fill-cream-100 w-full" style="display:block">
            <path d="M0 32L60 26.7C120 21 240 11 360 10.7C480 11 600 21 720 26.7C840 32 960 32 1080 26.7C1200 21 1320 11 1380 5.3L1440 0V40H0Z"/>
        </svg>
    </div>

    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Main grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10 lg:gap-8 pb-12 border-b border-vanilla-700/50">

            <!-- Col 1 — Brand -->
            <div class="lg:col-span-1">
                <div class="flex items-center gap-2.5 mb-4">
                    <div class="w-9 h-9 rounded-xl bg-vanilla-700 flex items-center justify-center">
                        <svg viewBox="0 0 32 32" fill="none" class="w-5 h-5 text-gold-300">
                            <path d="M16 4 C16 4 24 10 24 18 C24 24 20 28 16 28 C12 28 8 24 8 18 C8 10 16 4 16 4Z" fill="currentColor" fill-opacity="0.9"/>
                            <path d="M16 8 L16 26" stroke="#4E342E" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <div>
                        <span class="block font-serif font-bold text-cream-100 text-base">Vanilla</span>
                        <span class="block text-[10px] font-medium text-vanilla-400 tracking-widest uppercase">Groupe Madagascar</span>
                    </div>
                </div>
                <p class="text-sm text-vanilla-400 leading-relaxed">
                    Depuis les terres fertiles de Madagascar, nous cultivons et exportons la vanille la plus pure du monde,
                    en partenariat direct avec nos agriculteurs.
                </p>
                <!-- Social -->
                <div class="flex gap-3 mt-5">
                    <?php
                    $socials = [
                        'Instagram' => ['icon' => '📸', 'href' => '#'],
                        'Facebook'  => ['icon' => '💼', 'href' => '#'],
                        'LinkedIn'  => ['icon' => '🔗', 'href' => '#'],
                    ];
                    foreach ($socials as $name => $s):
                    ?>
                    <a href="<?= $s['href'] ?>" aria-label="<?= $name ?>"
                       class="w-9 h-9 rounded-xl bg-vanilla-800 border border-vanilla-700
                              flex items-center justify-center text-sm
                              hover:bg-vanilla-700 hover:border-vanilla-600 transition-all duration-250">
                        <?= $s['icon'] ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Col 2 — Navigation -->
            <div>
                <h4 class="font-serif font-semibold text-cream-100 text-sm mb-4 tracking-wide">Navigation</h4>
                <ul class="space-y-2.5">
                    <?php foreach ([
                        url()           => 'Accueil',
                        url('shop')     => 'Boutique',
                        url('about')    => 'Notre histoire',
                        url('contact')  => 'Contact',
                    ] as $href => $label): ?>
                    <li>
                        <a href="<?= e($href) ?>"
                           class="text-sm text-vanilla-400 hover:text-cream-100 transition-colors duration-200 flex items-center gap-2 group">
                            <span class="w-1 h-1 rounded-full bg-gold-500 opacity-0 group-hover:opacity-100 transition-opacity shrink-0"></span>
                            <?= e($label) ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Col 3 — Customer -->
            <div>
                <h4 class="font-serif font-semibold text-cream-100 text-sm mb-4 tracking-wide">Service client</h4>
                <ul class="space-y-2.5">
                    <?php foreach ([
                        ['#', 'FAQ'],
                        ['#', 'Livraison &amp; retours'],
                        ['#', 'Suivi commande'],
                        ['#', 'Politique de confidentialité'],
                        ['#', "Conditions d'utilisation"],
                    ] as [$href, $label]): ?>
                    <li>
                        <a href="<?= e($href) ?>"
                           class="text-sm text-vanilla-400 hover:text-cream-100 transition-colors duration-200 flex items-center gap-2 group">
                            <span class="w-1 h-1 rounded-full bg-gold-500 opacity-0 group-hover:opacity-100 transition-opacity shrink-0"></span>
                            <?= $label ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Col 4 — Newsletter -->
            <div>
                <h4 class="font-serif font-semibold text-cream-100 text-sm mb-2 tracking-wide">Newsletter</h4>
                <p class="text-xs text-vanilla-400 mb-4 leading-relaxed">
                    Recevez nos actualités, recettes et offres exclusives directement dans votre boîte mail.
                </p>
                <form
                    x-data="{ email: '', sent: false }"
                    @submit.prevent="sent = true; email = ''"
                    class="flex flex-col gap-2"
                >
                    <input
                        type="email"
                        x-model="email"
                        placeholder="votre@email.com"
                        required
                        class="w-full px-4 py-2.5 rounded-xl bg-vanilla-800 border border-vanilla-700
                               text-cream-100 text-sm placeholder:text-vanilla-500
                               focus:outline-none focus:ring-2 focus:ring-gold-400/50 focus:border-gold-400
                               transition-all duration-200"
                    >
                    <button
                        type="submit"
                        class="w-full px-4 py-2.5 rounded-xl bg-gold-400 text-vanilla-900
                               text-sm font-semibold hover:bg-gold-300 transition-all duration-250
                               hover:-translate-y-0.5 hover:shadow-gold"
                    >
                        S'abonner
                    </button>
                    <p x-show="sent" class="text-xs text-forest-400 text-center mt-1 animate__animated animate__fadeIn">
                        ✓ Merci ! Vous êtes abonné(e).
                    </p>
                </form>

                <!-- Contact info -->
                <div class="mt-5 space-y-1.5">
                    <a href="mailto:contact@vanillagroupe.mg"
                       class="flex items-center gap-2 text-xs text-vanilla-400 hover:text-gold-400 transition-colors">
                        <span>✉</span> contact@vanillagroupe.mg
                    </a>
                    <a href="tel:+261200000000"
                       class="flex items-center gap-2 text-xs text-vanilla-400 hover:text-gold-400 transition-colors">
                        <span>☎</span> +261 20 00 000 00
                    </a>
                </div>
            </div>
        </div>

        <!-- Bottom bar -->
        <div class="pt-6 flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-xs text-vanilla-500">
                &copy; <?= date('Y') ?> Vanilla Groupe Madagascar. Tous droits réservés.
            </p>
            <p class="text-xs text-vanilla-600 flex items-center gap-1">
                <span>🌿</span> Cultivé avec soin à Madagascar
            </p>
        </div>
    </div>
</footer>
