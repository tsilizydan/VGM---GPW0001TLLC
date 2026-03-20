<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e($title ?? '') ?> – <?= e(env('APP_NAME', 'Vanilla Groupe Madagascar')) ?>">
    <title><?= e($title ?? 'Auth') ?> — <?= e(env('APP_NAME', 'Vanilla Groupe Madagascar')) ?></title>
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/auth.css') ?>">
</head>
<body class="auth-body">

    <div class="auth-wrapper">

        <a href="<?= url() ?>" class="auth-brand">
            🌿 <?= e(env('APP_NAME', 'Vanilla Groupe Madagascar')) ?>
        </a>

        <div class="auth-card">
            <?php if (\Core\Session::hasFlash('errors')): ?>
                <div class="alert alert-error">
                    <?php foreach ((array) \Core\Session::getFlash('errors') as $err): ?>
                        <p><?= e($err) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (\Core\Session::hasFlash('success')): ?>
                <div class="alert alert-success">
                    <p><?= e(\Core\Session::getFlash('success')) ?></p>
                </div>
            <?php endif; ?>

            <?= $content ?>
        </div>

        <p class="auth-footer-note">
            &copy; <?= date('Y') ?> <?= e(env('APP_NAME', '')) ?>
        </p>

    </div>

</body>
</html>
