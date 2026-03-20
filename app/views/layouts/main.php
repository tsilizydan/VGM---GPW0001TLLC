<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e($title ?? '') ?> – Pure Madagascar vanilla">
    <title><?= e($title ?? 'Vanilla Groupe Madagascar') ?></title>
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body>

    <header class="site-header">
        <div class="container">
            <a href="<?= url() ?>" class="logo">
                🌿 <?= e(env('APP_NAME', 'Vanilla Groupe Madagascar')) ?>
            </a>
            <nav class="main-nav">
                <a href="<?= url() ?>">Accueil</a>

                <?php if (\Core\Auth::check()): ?>
                    <a href="<?= url('dashboard') ?>">Tableau de bord</a>
                    <?php if (\Core\Auth::hasRole('admin')): ?>
                        <span class="nav-badge">Admin</span>
                    <?php endif; ?>
                    <span class="nav-user">
                        👤 <?= e(\Core\Auth::user()['name'] ?? '') ?>
                    </span>
                    <a href="<?= url('logout') ?>" class="nav-logout">Déconnexion</a>
                <?php else: ?>
                    <a href="<?= url('login') ?>">Connexion</a>
                    <a href="<?= url('register') ?>" class="btn nav-cta">S'inscrire</a>
                <?php endif; ?>
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
            <?= $content ?>
        </div>
    </main>

    <footer class="site-footer">
        <div class="container">
            <p>&copy; <?= date('Y') ?> <?= e(env('APP_NAME', 'Vanilla Groupe Madagascar')) ?>. Tous droits réservés.</p>
        </div>
    </footer>

</body>
</html>
