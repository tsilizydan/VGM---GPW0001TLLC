<section class="hero" style="text-align:center;padding:4rem 1rem;background:linear-gradient(135deg,#f0f7f0,#fafaf7,#f7f3e8)">
    <h1 style="font-family:'Playfair Display',Georgia,serif;font-size:clamp(2rem,5vw,3.5rem);font-weight:700;color:var(--color-primary-dk,#1e4a1e);line-height:1.2;margin-bottom:1rem">
        <?= e($title ?? 'Vanilla Groupe Madagascar') ?>
    </h1>
    <p class="tagline" style="font-size:1.2rem;color:var(--color-muted,#6b6b6b);max-width:600px;margin:0 auto 2rem"><?= e($tagline ?? 'Excellence naturelle depuis Madagascar') ?></p>
    <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap">
        <a href="<?= locale_url('shop') ?>" class="btn" style="display:inline-flex;align-items:center;gap:.5rem;background:var(--color-primary,#2d6a2d);color:#fff;padding:.75rem 2rem;border-radius:100px;font-weight:600;font-size:1rem">
            🛒 <?= t('home.shop_now', []) ?: 'Découvrir nos produits' ?>
        </a>
        <a href="#contact" class="btn" style="display:inline-flex;align-items:center;gap:.5rem;background:transparent;color:var(--color-primary,#2d6a2d);padding:.75rem 2rem;border-radius:100px;font-weight:600;font-size:1rem;border:2px solid var(--color-primary,#2d6a2d)">
            ✉️ <?= t('home.contact_us', []) ?: 'Nous contacter' ?>
        </a>
    </div>
</section>

<section class="features" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:2rem;padding:3rem 0">
    <div class="feature-card" style="background:var(--color-surface,#fff);border-radius:var(--radius,8px);padding:2rem;box-shadow:var(--shadow);border:1px solid var(--color-border,#e0e0d8);transition:transform .2s,box-shadow .2s">
        <span style="font-size:2.5rem;display:block;margin-bottom:1rem">🌱</span>
        <h2 style="font-family:'Playfair Display',Georgia,serif;font-size:1.3rem;margin-bottom:.5rem;color:var(--color-primary-dk,#1e4a1e)">
            <?= t('home.feature1_title', []) ?: 'Vanille Pure' ?>
        </h2>
        <p style="color:var(--color-muted,#6b6b6b);font-size:.9rem;line-height:1.6">
            <?= t('home.feature1_desc', []) ?: "Cultivée dans les meilleures régions de Madagascar, notre vanille Bourbon est d'une qualité incomparable." ?>
        </p>
    </div>
    <div class="feature-card" style="background:var(--color-surface,#fff);border-radius:var(--radius,8px);padding:2rem;box-shadow:var(--shadow);border:1px solid var(--color-border,#e0e0d8);transition:transform .2s,box-shadow .2s">
        <span style="font-size:2.5rem;display:block;margin-bottom:1rem">🤝</span>
        <h2 style="font-family:'Playfair Display',Georgia,serif;font-size:1.3rem;margin-bottom:.5rem;color:var(--color-primary-dk,#1e4a1e)">
            <?= t('home.feature2_title', []) ?: 'Commerce Équitable' ?>
        </h2>
        <p style="color:var(--color-muted,#6b6b6b);font-size:.9rem;line-height:1.6">
            <?= t('home.feature2_desc', []) ?: 'Nous travaillons directement avec les agriculteurs locaux pour garantir une rémunération équitable et durable.' ?>
        </p>
    </div>
    <div class="feature-card" style="background:var(--color-surface,#fff);border-radius:var(--radius,8px);padding:2rem;box-shadow:var(--shadow);border:1px solid var(--color-border,#e0e0d8);transition:transform .2s,box-shadow .2s">
        <span style="font-size:2.5rem;display:block;margin-bottom:1rem">🌍</span>
        <h2 style="font-family:'Playfair Display',Georgia,serif;font-size:1.3rem;margin-bottom:.5rem;color:var(--color-primary-dk,#1e4a1e)">
            <?= t('home.feature3_title', []) ?: 'Export Mondial' ?>
        </h2>
        <p style="color:var(--color-muted,#6b6b6b);font-size:.9rem;line-height:1.6">
            <?= t('home.feature3_desc', []) ?: "Nous exportons vers les quatre coins du monde, partageant l'excellence malgache avec les plus grands chefs." ?>
        </p>
    </div>
</section>

<!-- CTA Section -->
<section id="contact" style="background:var(--color-primary-dk,#1e4a1e);color:#fff;padding:3rem 1rem;text-align:center;border-radius:var(--radius,8px);margin-bottom:2rem">
    <h2 style="font-family:'Playfair Display',Georgia,serif;font-size:1.8rem;margin-bottom:.5rem">
        <?= t('home.cta_title', []) ?: 'Intéressé par notre vanille ?' ?>
    </h2>
    <p style="color:rgba(255,255,255,.7);margin-bottom:1.5rem;max-width:500px;margin-inline:auto">
        <?= t('home.cta_desc', []) ?: 'Contactez-nous pour obtenir un devis personnalisé ou en savoir plus sur nos produits.' ?>
    </p>
    <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap">
        <a href="https://wa.me/261340000000" target="_blank" style="display:inline-flex;align-items:center;gap:.5rem;background:#25d366;color:#fff;padding:.75rem 2rem;border-radius:100px;font-weight:600;font-size:.95rem;text-decoration:none">
            📱 WhatsApp
        </a>
        <a href="mailto:contact@vanillagroupe.mg" style="display:inline-flex;align-items:center;gap:.5rem;background:rgba(255,255,255,.15);color:#fff;padding:.75rem 2rem;border-radius:100px;font-weight:600;font-size:.95rem;text-decoration:none;border:1px solid rgba(255,255,255,.3)">
            ✉️ Email
        </a>
    </div>
</section>
