<!DOCTYPE html>
<html lang="<?= e(current_locale()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e($title ?? '') ?> – Vanilla Groupe Madagascar">
    <title><?= e($title ?? 'Vanilla Groupe Madagascar') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <?php if (class_exists(\Core\Seo::class)): ?>
        <?= \Core\Seo::head() ?>
        <?= \Core\Seo::hreflang() ?>
    <?php endif; ?>
    <style>
        /* Mobile hamburger — scoped inline to avoid needing JS framework */
        .nav-toggle { display:none; background:none; border:none; cursor:pointer; padding:.5rem; color:var(--color-text,#1a1a1a); }
        .nav-toggle svg { width:24px; height:24px; }
        @media(max-width:768px) {
            .nav-toggle { display:block; }
            .main-nav { display:none; position:absolute; top:100%; left:0; right:0; background:var(--color-surface,#fff); flex-direction:column; padding:1rem 1.5rem; gap:.5rem; border-bottom:1px solid var(--color-border,#e0e0d8); box-shadow:0 4px 12px rgba(0,0,0,.1); }
            .main-nav.open { display:flex; }
            .main-nav a { margin-left:0; padding:.5rem 0; }
            .lang-switcher { justify-content:flex-start !important; }
        }
    </style>
</head>
<body>

    <header class="site-header" style="position:relative">
        <div class="container">
            <a href="<?= locale_url() ?>" class="logo">
                🌿 <?= e(env('APP_NAME', 'Vanilla Groupe Madagascar')) ?>
            </a>

            <button class="nav-toggle" onclick="document.querySelector('.main-nav').classList.toggle('open')" aria-label="Menu">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            <nav class="main-nav">
                <a href="<?= locale_url() ?>"><?= t('nav.home', []) ?: 'Accueil' ?></a>
                <a href="<?= locale_url('shop') ?>"><?= t('nav.shop', []) ?: 'Boutique' ?></a>
                <a href="<?= locale_url('recipes') ?>"><?= t('nav.recipes', []) ?: 'Recettes' ?></a>

                <?php if (\Core\Auth::check()): ?>
                    <a href="<?= locale_url('dashboard') ?>"><?= t('nav.dashboard', []) ?: 'Tableau de bord' ?></a>
                    <?php if (\Core\Auth::hasRole('admin')): ?>
                        <a href="<?= locale_url('admin') ?>" style="color:var(--color-accent,#c8a84b);font-weight:600">Admin</a>
                    <?php endif; ?>
                    <span style="font-size:.85rem;color:var(--color-muted,#6b6b6b)">
                        👤 <?= e(\Core\Auth::user()['name'] ?? '') ?>
                    </span>
                    <a href="<?= locale_url('logout') ?>"><?= t('nav.logout', []) ?: 'Déconnexion' ?></a>
                <?php else: ?>
                    <a href="<?= locale_url('login') ?>"><?= t('nav.login', []) ?: 'Connexion' ?></a>
                    <a href="<?= locale_url('register') ?>" class="btn nav-cta"><?= t('nav.register', []) ?: "S'inscrire" ?></a>
                <?php endif; ?>

                <!-- Language switcher -->
                <div class="lang-switcher" style="display:flex;gap:.3rem;margin-left:auto;align-items:center">
                    <?php foreach (['fr','en','es'] as $lc): ?>
                        <?php if ($lc === current_locale()): ?>
                            <span style="background:var(--color-primary,#2d6a2d);color:#fff;padding:.2rem .5rem;border-radius:4px;font-size:.75rem;font-weight:600;text-transform:uppercase"><?= $lc ?></span>
                        <?php else: ?>
                            <a href="<?= switch_locale_url($lc) ?>" style="padding:.2rem .5rem;border-radius:4px;font-size:.75rem;font-weight:500;text-transform:uppercase;border:1px solid var(--color-border,#e0e0d8);color:var(--color-muted,#6b6b6b)"><?= $lc ?></a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </nav>
        </div>
    </header>

    <main class="site-main">
        <div class="container">
            <?php if (\Core\Session::hasFlash('success')): ?>
                <div class="page-alert alert-success" style="margin:1.5rem 0;padding:.85rem 1rem;border-radius:8px;background:#f0fdf4;border:1px solid #86efac;color:#166534;font-size:.9rem;">
                    <?= e(\Core\Session::getFlash('success')) ?>
                </div>
            <?php endif; ?>
            <?php if (\Core\Session::hasFlash('error')): ?>
                <div class="page-alert alert-error" style="margin:1.5rem 0;padding:.85rem 1rem;border-radius:8px;background:#fff1f2;border:1px solid #fca5a5;color:#991b1b;font-size:.9rem;">
                    <?= e(\Core\Session::getFlash('error')) ?>
                </div>
            <?php endif; ?>
            <?= $content ?>
        </div>
    </main>

    <footer class="site-footer">
        <div class="container" style="text-align:center;padding:2rem 1.25rem">
            <p style="margin-bottom:.5rem">&copy; <?= date('Y') ?> <?= e(env('APP_NAME', 'Vanilla Groupe Madagascar')) ?>. Tous droits réservés.</p>
            <div style="display:flex;justify-content:center;gap:1.5rem;flex-wrap:wrap;font-size:.85rem;color:var(--color-muted,#6b6b6b)">
                <a href="<?= locale_url('shop') ?>">Boutique</a>
                <a href="<?= locale_url('recipes') ?>">Recettes</a>
                <span>📍 Madagascar</span>
                <span>✉️ contact@vanillagroupe.mg</span>
            </div>
        </div>
    </footer>

</body>
</html>
