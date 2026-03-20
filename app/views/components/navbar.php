<!-- ══════════════════════════════════════════════
     NAVBAR — Glassmorphic sticky navigation
     Stores: $store.nav, $store.cart
     ══════════════════════════════════════════════ -->
<header
    x-data
    class="fixed top-0 left-0 right-0 z-50 transition-all duration-400"
    :class="$store.nav.scrolled
        ? 'bg-cream-100/80 backdrop-blur-lg border-b border-vanilla-200/40 shadow-soft'
        : 'bg-transparent'"
>
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 md:h-20">

            <!-- ── Logo ── -->
            <a href="<?= url() ?>" class="flex items-center gap-2.5 group shrink-0">
                <div class="w-9 h-9 rounded-xl bg-vanilla-700 flex items-center justify-center shadow-gold
                            group-hover:shadow-gold group-hover:scale-105 transition-all duration-250">
                    <svg viewBox="0 0 32 32" fill="none" class="w-5 h-5 text-gold-300" aria-hidden="true">
                        <!-- Stylised vanilla pod icon -->
                        <path d="M16 4 C16 4 24 10 24 18 C24 24 20 28 16 28 C12 28 8 24 8 18 C8 10 16 4 16 4Z"
                              fill="currentColor" fill-opacity="0.9"/>
                        <path d="M16 8 L16 26" stroke="#4E342E" stroke-width="1.5" stroke-linecap="round"/>
                        <path d="M13 14 Q16 16 19 14" stroke="#4E342E" stroke-width="1.2" stroke-linecap="round" fill="none"/>
                    </svg>
                </div>
                <div class="leading-none">
                    <span class="block font-serif font-bold text-vanilla-800 text-base tracking-tight">Vanilla</span>
                    <span class="block font-sans text-[10px] font-medium text-vanilla-500 tracking-widest uppercase">Groupe Madagascar</span>
                </div>
            </a>

            <!-- ── Desktop Nav ── -->
            <nav class="hidden md:flex items-center gap-7" aria-label="Navigation principale">
                <?php
                $locale = current_locale();
                $navLinks = [
                    locale_url()          => t('nav.home'),
                    locale_url('shop')    => t('nav.shop'),
                    locale_url('about')   => t('nav.about'),
                    locale_url('contact') => t('nav.contact'),
                ];
                $currentPath = $_SERVER['REQUEST_URI'] ?? '/';
                foreach ($navLinks as $href => $label):
                    $hrefPath = parse_url($href, PHP_URL_PATH) ?? '/';
                    $isActive = rtrim($hrefPath, '/') === rtrim($currentPath, '/');
                ?>
                <a href="<?= e($href) ?>"
                   class="font-sans font-medium text-sm transition-all duration-250 relative group
                          <?= $isActive ? 'text-vanilla-800' : 'text-vanilla-600 hover:text-vanilla-800' ?>">
                    <?= e($label) ?>
                    <span class="absolute -bottom-0.5 left-0 right-0 h-0.5 bg-vanilla-700 rounded origin-left
                                 transition-transform duration-250
                                 <?= $isActive ? 'scale-x-100' : 'scale-x-0 group-hover:scale-x-100' ?>"></span>
                </a>
                <?php endforeach; ?>
            </nav>

            <!-- ── Right Actions ── -->
            <div class="flex items-center gap-2">

                <!-- Language Switcher (real locale navigation) -->
                <div class="relative hidden md:block" x-data="{ open: false }">
                    <button
                        @click="open = !open"
                        class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold text-vanilla-600
                               hover:bg-vanilla-100 hover:text-vanilla-800 transition-all duration-200"
                        :aria-expanded="open"
                    >
                        <span><?= strtoupper(current_locale()) ?></span>
                        <svg class="w-3 h-3 transition-transform duration-200" :class="{'rotate-180': open}"
                             viewBox="0 0 10 6" fill="currentColor">
                            <path d="M1 1l4 4 4-4"/>
                        </svg>
                    </button>
                    <!-- Dropdown -->
                    <div
                        x-show="open"
                        @click.outside="open = false"
                        x-transition:enter="transition ease-smooth duration-200"
                        x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="absolute right-0 top-full mt-2 w-32 bg-white/90 backdrop-blur-md
                               border border-vanilla-200/50 rounded-xl shadow-glass-lg overflow-hidden"
                        style="display:none"
                    >
                        <?php
                        $localeOptions = ['fr' => '🇫🇷 Français', 'en' => '🇬🇧 English', 'es' => '🇪🇸 Español'];
                        foreach ($localeOptions as $lc => $lLabel):
                        ?>
                        <a
                            href="<?= e(switch_locale_url($lc)) ?>"
                            class="flex items-center gap-2 px-4 py-2.5 text-xs font-semibold transition-colors
                                   hover:bg-vanilla-50
                                   <?= current_locale() === $lc ? 'text-vanilla-800 bg-vanilla-50' : 'text-vanilla-500' ?>"
                        ><?= $lLabel ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Cart Button -->
                <div class="relative" x-data>
                    <button
                        @click="$store.nav.toggleCart()"
                        class="relative p-2.5 rounded-xl text-vanilla-700
                               hover:bg-vanilla-100 hover:text-vanilla-800
                               transition-all duration-200 group"
                        aria-label="Panier"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/>
                        </svg>
                        <!-- Badge -->
                        <span
                            x-show="$store.cart.count > 0"
                            x-text="$store.cart.count"
                            class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1
                                   bg-vanilla-700 text-cream-100 text-[10px] font-bold
                                   rounded-full flex items-center justify-center
                                   group-hover:scale-110 transition-transform"
                            style="display:none"
                        ></span>
                    </button>

                    <!-- Mini Cart Dropdown -->
                    <div
                        x-show="$store.nav.cartOpen"
                        @click.outside="$store.nav.cartOpen = false"
                        x-transition:enter="transition ease-smooth duration-300"
                        x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0 translate-y-2"
                        class="absolute right-0 top-full mt-3 w-80 bg-white/95 backdrop-blur-xl
                               border border-vanilla-200/50 rounded-2xl shadow-glass-lg overflow-hidden"
                        style="display:none"
                    >
                        <?php require __DIR__ . '/cart-mini.php'; ?>
                    </div>
                </div>

                <!-- Auth Links -->
                <?php if (\Core\Auth::check()): ?>
                <a href="<?= url('dashboard') ?>"
                   class="hidden md:flex items-center gap-2 px-4 py-2 rounded-xl
                          bg-vanilla-700 text-cream-100 text-sm font-semibold
                          hover:bg-vanilla-600 hover:-translate-y-0.5 shadow-sm
                          transition-all duration-250">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                    </svg>
                    <?= e(\Core\Auth::user()['name'] ?? 'Mon compte') ?>
                </a>
                <?php else: ?>
                <a href="<?= url('login') ?>"
                   class="hidden md:inline-flex items-center px-4 py-2 rounded-xl
                          text-sm font-semibold text-vanilla-700
                          hover:bg-vanilla-100 transition-all duration-250">
                    Connexion
                </a>
                <a href="<?= url('register') ?>"
                   class="hidden md:inline-flex items-center px-4 py-2 rounded-xl
                          bg-vanilla-700 text-cream-100 text-sm font-semibold
                          hover:bg-vanilla-600 hover:-translate-y-0.5 shadow-sm
                          transition-all duration-250">
                    S'inscrire
                </a>
                <?php endif; ?>

                <!-- Mobile Hamburger -->
                <button
                    @click="$store.nav.toggle()"
                    class="md:hidden p-2.5 rounded-xl text-vanilla-700 hover:bg-vanilla-100 transition-all duration-200"
                    :aria-expanded="$store.nav.mobileOpen"
                    aria-label="Menu"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path x-show="!$store.nav.mobileOpen" stroke-linecap="round" stroke-linejoin="round"
                              d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                        <path x-show="$store.nav.mobileOpen" stroke-linecap="round" stroke-linejoin="round"
                              d="M6 18L18 6M6 6l12 12" style="display:none"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- ── Mobile Menu ── -->
    <div
        x-show="$store.nav.mobileOpen"
        x-transition:enter="transition ease-smooth duration-300"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0 -translate-y-2"
        class="md:hidden bg-cream-100/95 backdrop-blur-xl border-t border-vanilla-200/40 shadow-glass"
        style="display:none"
    >
        <div class="max-w-[1280px] mx-auto px-4 py-6 flex flex-col gap-1">
            <a href="<?= locale_url() ?>"       @click="$store.nav.mobileOpen=false" class="px-4 py-3 rounded-xl text-sm font-semibold text-vanilla-700 hover:bg-vanilla-100 transition-colors"><?= t('nav.home') ?></a>
            <a href="<?= locale_url('shop') ?>" @click="$store.nav.mobileOpen=false" class="px-4 py-3 rounded-xl text-sm font-semibold text-vanilla-700 hover:bg-vanilla-100 transition-colors"><?= t('nav.shop') ?></a>
            <a href="<?= locale_url('about') ?>" @click="$store.nav.mobileOpen=false" class="px-4 py-3 rounded-xl text-sm font-semibold text-vanilla-700 hover:bg-vanilla-100 transition-colors"><?= t('nav.about') ?></a>
            <a href="<?= locale_url('contact') ?>" @click="$store.nav.mobileOpen=false" class="px-4 py-3 rounded-xl text-sm font-semibold text-vanilla-700 hover:bg-vanilla-100 transition-colors"><?= t('nav.contact') ?></a>
            <div class="divider-organic mt-2"></div>
            <div class="flex gap-2 pt-1">
                <?php if (\Core\Auth::check()): ?>
                <a href="<?= locale_url('dashboard') ?>" class="flex-1 text-center px-4 py-2.5 rounded-xl bg-vanilla-700 text-cream-100 text-sm font-semibold"><?= t('nav.dashboard') ?></a>
                <a href="<?= locale_url('logout') ?>"    class="flex-1 text-center px-4 py-2.5 rounded-xl border border-vanilla-300 text-vanilla-700 text-sm font-semibold"><?= t('nav.logout') ?></a>
                <?php else: ?>
                <a href="<?= locale_url('login') ?>"    class="flex-1 text-center px-4 py-2.5 rounded-xl border border-vanilla-300 text-vanilla-700 text-sm font-semibold"><?= t('nav.login') ?></a>
                <a href="<?= locale_url('register') ?>" class="flex-1 text-center px-4 py-2.5 rounded-xl bg-vanilla-700 text-cream-100 text-sm font-semibold"><?= t('nav.register') ?></a>
                <?php endif; ?>
            </div>
            <!-- Language switcher mobile -->
            <div class="flex gap-2 pt-2">
                <?php
                $localeOptions = ['fr' => '🇫🇷 FR', 'en' => '🇬🇧 EN', 'es' => '🇪🇸 ES'];
                foreach ($localeOptions as $lc => $lLabel):
                ?>
                <a
                    href="<?= e(switch_locale_url($lc)) ?>"
                    class="flex-1 text-center py-2 rounded-lg text-xs font-bold transition-all
                           <?= current_locale() === $lc
                               ? 'bg-vanilla-700 text-cream-100'
                               : 'bg-vanilla-100 text-vanilla-600 hover:bg-vanilla-200' ?>"
                ><?= $lLabel ?></a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</header>
<!-- Spacer (for fixed navbar) -->
<div class="h-16 md:h-20"></div>
