<!DOCTYPE html>
<html lang="fr" class="antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Administration') ?> — Vanilla Groupe Madagascar</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

    <!-- Tailwind CDN (dev) -->
    <?php if (env('APP_ENV', 'local') !== 'production'): ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            vanilla: { 50:'#FDF9F7',100:'#F8F1EC',200:'#EFD9CD',300:'#DEB99E',400:'#C48B68',500:'#A06642',600:'#7A4E33',700:'#4E342E',800:'#3B2420',900:'#271814' },
            cream:   { 50:'#FEFEFE',100:'#F8F5F0',200:'#EDE7DC',300:'#DDD3C5',400:'#C9BAA7',500:'#B09B87' },
            forest:  { 50:'#F2F7EE',100:'#E0EDD7',200:'#BDDA9E',300:'#94BF68',400:'#6A8F4E',500:'#4F6E39',600:'#395029',700:'#28391C' },
            gold:    { 50:'#FDF9EC',100:'#F9EFCC',200:'#EEDB8E',300:'#DFC154',400:'#C8A96A',500:'#A8893C',600:'#7D6428',700:'#57431A' },
          },
          fontFamily: {
            serif: ['"Playfair Display"','Georgia','serif'],
            sans:  ['"Inter"','system-ui','sans-serif'],
          },
          boxShadow: {
            'glass':    '0 4px 32px rgba(78,52,46,0.08), 0 1px 0 rgba(255,255,255,0.3) inset',
            'soft':     '0 1px 4px rgba(0,0,0,0.04), 0 4px 16px rgba(0,0,0,0.06)',
          },
          transitionTimingFunction: { 'smooth': 'cubic-bezier(0.16,1,0.3,1)' },
        },
      },
    };
    </script>
    <?php else: ?>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <?php endif; ?>

    <!-- TinyMCE (loaded per-page via $headExtra) -->
    <?= $headExtra ?? '' ?>
</head>

<body
    class="font-sans bg-cream-100 text-vanilla-800 min-h-screen"
    x-data="{ sidebarOpen: window.innerWidth >= 1024 }"
    @resize.window="sidebarOpen = window.innerWidth >= 1024"
>

<!-- Alpine stores -->
<script src="<?= asset('js/store.js') ?>"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<!-- Global alerts -->
<div class="fixed top-4 right-4 z-[200] flex flex-col gap-2 w-80" x-data aria-live="polite">
    <template x-for="alert in $store.alerts.list" :key="alert.id">
        <div
            x-show="alert.visible"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-4"
            x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0 translate-x-4"
            class="flex items-start gap-3 p-4 rounded-xl border shadow-glass text-sm font-medium backdrop-blur-md"
            :class="{
                'bg-forest-50 border-forest-200 text-forest-800': alert.type === 'success',
                'bg-red-50 border-red-200 text-red-800': alert.type === 'error',
                'bg-gold-50 border-gold-200 text-gold-800': alert.type === 'info',
            }"
        >
            <span class="shrink-0 mt-0.5">
                <template x-if="alert.type === 'success'">✅</template>
                <template x-if="alert.type === 'error'">❌</template>
                <template x-if="alert.type === 'info'">ℹ️</template>
            </span>
            <span x-text="alert.message" class="flex-1 leading-relaxed"></span>
            <button @click="$store.alerts.dismiss(alert.id)" class="opacity-40 hover:opacity-70 transition-opacity">✕</button>
        </div>
    </template>
</div>

<!-- Layout wrapper -->
<div class="flex min-h-screen">

    <!-- ── Sidebar ────────────────────────────────────────────── -->
    <?php require __DIR__ . '/../components/admin-sidebar.php'; ?>

    <!-- ── Main area ─────────────────────────────────────────── -->
    <div class="flex-1 flex flex-col min-w-0 transition-all duration-300"
         :class="sidebarOpen ? 'lg:ml-64' : 'ml-0'">

        <!-- Top bar -->
        <header class="sticky top-0 z-30 flex items-center gap-3 h-14 px-4 bg-white/80 backdrop-blur-md border-b border-vanilla-200/60 shadow-soft">
            <!-- Hamburger -->
            <button
                @click="sidebarOpen = !sidebarOpen"
                class="p-2 text-vanilla-600 hover:text-vanilla-900 hover:bg-vanilla-100 rounded-lg transition-colors"
                aria-label="Toggle sidebar"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                </svg>
            </button>

            <!-- Breadcrumb -->
            <div class="flex-1">
                <p class="text-xs text-vanilla-400 font-medium">Administration › <?= e($title ?? '') ?></p>
            </div>

            <!-- Quick links -->
            <div class="flex items-center gap-2">
                <a href="<?= locale_url('shop') ?>" target="_blank"
                   class="p-2 text-vanilla-400 hover:text-vanilla-700 rounded-lg hover:bg-vanilla-100 transition-colors" title="Voir le site">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                    </svg>
                </a>
                <a href="<?= locale_url('logout') ?>"
                   class="p-2 text-vanilla-400 hover:text-red-500 rounded-lg hover:bg-red-50 transition-colors" title="Déconnexion">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/>
                    </svg>
                </a>
            </div>
        </header>

        <!-- Flash messages -->
        <?php
        $successFlash = \Core\Session::getFlash('success');
        $errorFlash   = \Core\Session::getFlash('error');
        ?>
        <?php if ($successFlash): ?>
        <div class="mx-4 mt-3 p-3 bg-forest-50 border border-forest-200 text-forest-800 rounded-xl text-sm flex items-center gap-2">
            ✅ <?= e($successFlash) ?>
        </div>
        <?php endif; ?>
        <?php if ($errorFlash): ?>
        <div class="mx-4 mt-3 p-3 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm flex items-center gap-2">
            ❌ <?= e($errorFlash) ?>
        </div>
        <?php endif; ?>

        <!-- Page content -->
        <main class="flex-1 p-4 md:p-6 lg:p-8">
            <?= $content ?>
        </main>

        <!-- Footer -->
        <footer class="p-4 border-t border-vanilla-200/40 text-xs text-vanilla-400 text-center">
            Vanilla Groupe Madagascar — Administration © <?= date('Y') ?>
        </footer>
    </div>

</div><!-- /layout -->

<!-- Sidebar overlay on mobile -->
<div
    x-show="sidebarOpen && window.innerWidth < 1024"
    @click="sidebarOpen = false"
    class="fixed inset-0 z-20 bg-black/40 lg:hidden"
    style="display:none"
    x-transition:enter="transition-opacity duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
></div>

</body>
</html>
