<!DOCTYPE html>
<html lang="fr" class="antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($errorCode ?? '404') . ' — ' . e($errorTitle ?? 'Page introuvable') ?> | Vanilla Groupe Madagascar</title>
    <meta name="robots" content="noindex,nofollow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    :root{
        --vanilla-800:#3B2420;--vanilla-600:#7A4E33;--vanilla-500:#A06642;
        --vanilla-400:#C48B68;--vanilla-200:#EFD9CD;--cream-100:#F8F5F0;
        --gold-400:#C8A96A;--forest-600:#395029;
    }
    html,body{height:100%;background:var(--cream-100);color:var(--vanilla-800);font-family:'Inter',system-ui,sans-serif}
    body{display:flex;flex-direction:column;min-height:100vh}
    a{color:var(--vanilla-500);text-decoration:none;transition:color .2s}
    a:hover{color:var(--vanilla-800)}
    a:focus-visible{outline:3px solid var(--gold-400);outline-offset:3px;border-radius:4px}

    /* Malagasy pattern top strip */
    .accent-bar{
        height:6px;
        background:linear-gradient(90deg,var(--vanilla-800) 0%,var(--gold-400) 40%,var(--forest-600) 100%);
    }

    .error-wrap{
        flex:1;display:flex;align-items:center;justify-content:center;
        padding:4rem 1.5rem;text-align:center;
    }
    .error-inner{max-width:540px}

    .error-code{
        font-family:'Playfair Display',Georgia,serif;
        font-size:clamp(6rem,15vw,10rem);font-weight:700;
        line-height:1;letter-spacing:-0.04em;
        background:linear-gradient(135deg,var(--vanilla-600),var(--gold-400));
        -webkit-background-clip:text;-webkit-text-fill-color:transparent;
        background-clip:text;
    }
    .error-title{
        font-family:'Playfair Display',Georgia,serif;
        font-size:1.75rem;font-weight:700;color:var(--vanilla-800);
        margin:.75rem 0 1rem;
    }
    .error-desc{
        font-size:1rem;color:var(--vanilla-500);line-height:1.6;margin-bottom:2rem;
    }
    .btn-primary{
        display:inline-flex;align-items:center;gap:.5rem;
        background:var(--vanilla-800);color:var(--cream-100);
        padding:.75rem 2rem;border-radius:100px;font-weight:600;
        font-size:.95rem;letter-spacing:.01em;transition:all .25s;
        border:none;cursor:pointer;
    }
    .btn-primary:hover{background:var(--vanilla-600);color:#fff;transform:translateY(-1px)}
    .btn-ghost{
        display:inline-flex;align-items:center;gap:.5rem;
        background:transparent;color:var(--vanilla-500);
        padding:.75rem 2rem;border-radius:100px;font-weight:600;
        font-size:.95rem;border:1.5px solid var(--vanilla-200);
        transition:all .25s;cursor:pointer;margin-left:.75rem;
    }
    .btn-ghost:hover{border-color:var(--vanilla-400);color:var(--vanilla-800)}

    .error-icon{font-size:4rem;margin-bottom:1rem;display:block}
    .nav-links{margin-top:2.5rem;padding-top:2rem;border-top:1px solid var(--vanilla-200)}
    .nav-links p{font-size:.8rem;text-transform:uppercase;letter-spacing:.1em;color:var(--vanilla-400);margin-bottom:.75rem}
    .nav-links a{
        display:inline-block;margin:.25rem .5rem;font-size:.85rem;
        color:var(--vanilla-600);font-weight:500;
    }
    .nav-links a:hover{color:var(--vanilla-800)}

    footer{
        text-align:center;padding:1.5rem;font-size:.8rem;color:var(--vanilla-400);
        border-top:1px solid var(--vanilla-200);background:#fff;
    }

    /* Floating vanilla pod decoration */
    .decoration{
        position:fixed;bottom:-3rem;right:-3rem;width:14rem;opacity:.06;
        pointer-events:none;transform:rotate(-15deg);
    }
    </style>
</head>
<body>
<div class="accent-bar" role="presentation"></div>

<main class="error-wrap" role="main">
    <div class="error-inner">
        <span class="error-icon" role="img" aria-label="Erreur"><?= e($errorEmoji ?? '🌿') ?></span>
        <div class="error-code" aria-label="Erreur <?= e($errorCode ?? '404') ?>"><?= e($errorCode ?? '404') ?></div>
        <h1 class="error-title"><?= e($errorTitle ?? 'Page introuvable') ?></h1>
        <p class="error-desc"><?= e($errorMessage ?? "Désolé, la page que vous recherchez n'existe pas ou a été déplacée.") ?></p>

        <div>
            <a href="/" class="btn-primary" aria-label="Retour à l'accueil">
                ← Retour à l'accueil
            </a>
            <a href="javascript:history.back()" class="btn-ghost">
                Page précédente
            </a>
        </div>

        <nav class="nav-links" aria-label="Liens utiles">
            <p>Liens utiles</p>
            <a href="/fr/shop">Boutique</a>
            <a href="/fr/recipes">Recettes</a>
            <a href="/fr/contact">Contact</a>
        </nav>
    </div>
</main>

<!-- SVG vanilla pod watermark -->
<svg class="decoration" viewBox="0 0 100 400" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <ellipse cx="50" cy="200" rx="12" ry="180" fill="#4E342E"/>
    <line x1="50" y1="30" x2="50" y2="370" stroke="#C8A96A" stroke-width="2"/>
</svg>

<footer>
    <span>© <?= date('Y') ?> Vanilla Groupe Madagascar</span>
</footer>
</body>
</html>
