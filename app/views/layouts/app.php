<!DOCTYPE html>
<html lang="<?= e(current_locale()) ?>" class="antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?= \Core\Seo::head() ?>
    <?= \Core\Seo::hreflang() ?>

    <!-- Preconnect for performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;0,800;1,400;1,600&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

    <?php if (env('APP_ENV', 'local') !== 'production'): ?>
    <!-- Tailwind Play CDN (development only) -->
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
            'glass':     '0 4px 32px rgba(78,52,46,0.08), 0 1px 0 rgba(255,255,255,0.3) inset',
            'glass-lg':  '0 12px 48px rgba(78,52,46,0.15), 0 1px 0 rgba(255,255,255,0.25) inset',
            'card':      '0 2px 16px rgba(78,52,46,0.06), 0 8px 32px rgba(78,52,46,0.04)',
            'card-hover':'0 8px 40px rgba(78,52,46,0.14), 0 24px 64px rgba(78,52,46,0.08)',
            'gold':      '0 0 24px rgba(200,169,106,0.35)',
            'soft':      '0 1px 4px rgba(0,0,0,0.04), 0 4px 16px rgba(0,0,0,0.06)',
          },
          animation: {
            'fade-in':       'fadeIn 0.5s ease-out both',
            'slide-up':      'slideUp 0.5s ease-out both',
            'fade-in-scale': 'fadeInScale 0.4s cubic-bezier(0.16,1,0.3,1) both',
            'shimmer':       'shimmer 2s linear infinite',
          },
          keyframes: {
            fadeIn:       { '0%':{'opacity':'0','transform':'translateY(12px)'},'100%':{'opacity':'1','transform':'translateY(0)'} },
            slideUp:      { '0%':{'opacity':'0','transform':'translateY(24px)'},'100%':{'opacity':'1','transform':'translateY(0)'} },
            fadeInScale:  { '0%':{'opacity':'0','transform':'scale(0.95)'},'100%':{'opacity':'1','transform':'scale(1)'} },
            shimmer:      { '0%':{'backgroundPosition':'-200% 0'},'100%':{'backgroundPosition':'200% 0'} },
          },
          transitionTimingFunction: {
            'smooth': 'cubic-bezier(0.16, 1, 0.3, 1)',
          },
          maxWidth: { 'container': '1280px' },
        },
      },
    };
    </script>
    <?php else: ?>
    <!-- Compiled CSS (production) -->
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <?php endif; ?>

    <!-- Focus-visible accessibility styles -->
    <style>
    :focus-visible {
        outline: 3px solid #C8A96A !important;
        outline-offset: 3px !important;
        border-radius: 4px !important;
    }
    /* Skip link — visible only on focus */
    .skip-link {
        position: absolute;
        top: -100%;left: 50%;transform: translateX(-50%);
        background: #4E342E;color: #F8F5F0;
        padding: .75rem 1.5rem;border-radius: 0 0 8px 8px;
        font-weight: 600;font-size: .9rem;z-index: 9999;white-space: nowrap;
        transition: top .15s;
    }
    .skip-link:focus { top: 0; }
    </style>

    <?= $headExtra ?? '' ?>
</head>

<body
    x-data
    x-init="$store.nav.initScroll()"
    class="font-sans text-vanilla-800 bg-cream-100 min-h-screen flex flex-col"
>

    <!-- ── Skip to main content (accessibility) ─────────── -->
    <a href="#main-content" class="skip-link">Passer au contenu principal</a>

    <!-- ── Alpine stores loaded before Alpine boots ──────── -->
    <script src="<?= asset('js/store.js') ?>"></script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- ── Global Alerts ─────────────────────────────────── -->
    <div
        class="fixed top-4 right-4 z-[200] flex flex-col gap-2 w-80"
        x-data
        aria-live="polite"
    >
        <template x-for="alert in $store.alerts.list" :key="alert.id">
            <div
                x-show="alert.visible"
                x-transition:enter="transition ease-smooth duration-300"
                x-transition:enter-start="opacity-0 translate-x-4"
                x-transition:enter-end="opacity-100 translate-x-0"
                x-transition:leave="transition ease-in duration-250"
                x-transition:leave-start="opacity-100 translate-x-0"
                x-transition:leave-end="opacity-0 translate-x-4"
                class="flex items-start gap-3 p-4 rounded-xl border backdrop-blur-md shadow-glass text-sm font-medium"
                :class="{
                    'bg-forest-50 border-forest-200 text-forest-800': alert.type === 'success',
                    'bg-red-50 border-red-200 text-red-800': alert.type === 'error',
                    'bg-gold-50 border-gold-200 text-gold-800': alert.type === 'info',
                }"
            >
                <!-- Icon -->
                <span class="shrink-0 text-base mt-0.5">
                    <template x-if="alert.type === 'success'">✅</template>
                    <template x-if="alert.type === 'error'">❌</template>
                    <template x-if="alert.type === 'info'">ℹ️</template>
                </span>
                <span x-text="alert.message" class="flex-1 leading-relaxed"></span>
                <button
                    @click="$store.alerts.dismiss(alert.id)"
                    class="shrink-0 text-current opacity-40 hover:opacity-70 transition-opacity ml-1"
                    aria-label="Fermer"
                >✕</button>
            </div>
        </template>
    </div>

    <!-- ── Navbar ─────────────────────────────────────────── -->
    <?php require __DIR__ . '/../components/navbar.php'; ?>

    <!-- ── Main Content ───────────────────────────────────── -->
    <main id="main-content" class="flex-1" role="main">
        <?= $content ?>
    </main>

    <!-- ── Footer ─────────────────────────────────────────── -->
    <?php require __DIR__ . '/../components/footer.php'; ?>

    <!-- ── Global Modal Backdrop ──────────────────────────── -->
    <div
        x-data
        x-show="$store.modal.active !== null"
        x-transition:enter="transition ease-smooth duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="$store.modal.close()"
        @keydown.escape.window="$store.modal.close()"
        class="fixed inset-0 z-[100] bg-vanilla-900/50 backdrop-blur-sm"
        style="display:none"
    ></div>

    <!-- ── Scroll-Reveal Script ────────────────────────────── -->
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const io = new IntersectionObserver(
            entries => entries.forEach(e => {
                if (e.isIntersecting) {
                    e.target.classList.add('animate__animated', 'animate__fadeInUp');
                    e.target.style.opacity = '1';
                    io.unobserve(e.target);
                }
            }),
            { threshold: 0.1, rootMargin: '0px 0px -40px 0px' }
        );
        document.querySelectorAll('[data-reveal]').forEach(el => {
            el.style.opacity = '0';
            io.observe(el);
        });
    });
    </script>

</body>
</html>
