<!-- ADMIN — Website Settings -->
<?php /** @var array<string,string> $settings */ ?>
<div class="max-w-3xl space-y-6">
    <h1 class="font-serif font-bold text-vanilla-900 text-2xl">Paramètres</h1>

    <form method="POST" action="<?= locale_url('admin/settings') ?>" class="space-y-5">
        <?= csrf_field() ?>

        <!-- Site Identity -->
        <section class="bg-white/80 border border-vanilla-200/60 rounded-2xl shadow-soft p-6 space-y-4">
            <h2 class="font-semibold text-vanilla-800 text-sm uppercase tracking-widest border-b border-vanilla-100 pb-3">🏷 Identité du site</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Nom du site</label>
                    <input type="text" name="site_name" value="<?= e($settings['site_name'] ?? 'Vanilla Groupe Madagascar') ?>" class="form-input">
                </div>
                <div>
                    <label class="form-label">Slogan</label>
                    <input type="text" name="site_tagline" value="<?= e($settings['site_tagline'] ?? '') ?>" class="form-input">
                </div>
                <div>
                    <label class="form-label">Email de contact</label>
                    <input type="email" name="site_email" value="<?= e($settings['site_email'] ?? '') ?>" class="form-input">
                </div>
                <div>
                    <label class="form-label">Téléphone</label>
                    <input type="text" name="site_phone" value="<?= e($settings['site_phone'] ?? '') ?>" class="form-input">
                </div>
                <div class="sm:col-span-2">
                    <label class="form-label">Adresse</label>
                    <input type="text" name="site_address" value="<?= e($settings['site_address'] ?? '') ?>" class="form-input">
                </div>
            </div>
        </section>

        <!-- Social -->
        <section class="bg-white/80 border border-vanilla-200/60 rounded-2xl shadow-soft p-6 space-y-4">
            <h2 class="font-semibold text-vanilla-800 text-sm uppercase tracking-widest border-b border-vanilla-100 pb-3">📱 Réseaux sociaux</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="form-label">Facebook</label>
                    <input type="url" name="site_facebook" value="<?= e($settings['site_facebook'] ?? '') ?>" placeholder="https://facebook.com/…" class="form-input text-sm">
                </div>
                <div>
                    <label class="form-label">Instagram</label>
                    <input type="url" name="site_instagram" value="<?= e($settings['site_instagram'] ?? '') ?>" placeholder="https://instagram.com/…" class="form-input text-sm">
                </div>
                <div>
                    <label class="form-label">X / Twitter</label>
                    <input type="url" name="site_twitter" value="<?= e($settings['site_twitter'] ?? '') ?>" placeholder="https://x.com/…" class="form-input text-sm">
                </div>
            </div>
        </section>

        <!-- Commerce -->
        <section class="bg-white/80 border border-vanilla-200/60 rounded-2xl shadow-soft p-6 space-y-4">
            <h2 class="font-semibold text-vanilla-800 text-sm uppercase tracking-widest border-b border-vanilla-100 pb-3">🛍 Commerce</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Livraison gratuite à partir de (€)</label>
                    <input type="number" name="shipping_free_over" value="<?= e($settings['shipping_free_over'] ?? '75') ?>" min="0" step="0.01" class="form-input">
                </div>
                <div>
                    <label class="form-label">Taux de TVA (%)</label>
                    <input type="number" name="tax_rate" value="<?= e($settings['tax_rate'] ?? '0') ?>" min="0" max="100" step="0.1" class="form-input">
                </div>
            </div>
        </section>

        <!-- SMTP -->
        <section class="bg-white/80 border border-vanilla-200/60 rounded-2xl shadow-soft p-6 space-y-4">
            <h2 class="font-semibold text-vanilla-800 text-sm uppercase tracking-widest border-b border-vanilla-100 pb-3">📧 Envoi d'emails (SMTP)</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Hôte SMTP</label>
                    <input type="text" name="smtp_host" value="<?= e($settings['smtp_host'] ?? '') ?>" placeholder="smtp.gmail.com" class="form-input text-sm">
                </div>
                <div>
                    <label class="form-label">Port</label>
                    <input type="number" name="smtp_port" value="<?= e($settings['smtp_port'] ?? '587') ?>" class="form-input text-sm">
                </div>
                <div>
                    <label class="form-label">Utilisateur SMTP</label>
                    <input type="text" name="smtp_user" value="<?= e($settings['smtp_user'] ?? '') ?>" class="form-input text-sm">
                </div>
                <div>
                    <label class="form-label">Mot de passe SMTP <span class="text-vanilla-400 font-normal text-xs">(laissez vide pour garder l'actuel)</span></label>
                    <input type="password" name="smtp_password" value="" autocomplete="new-password" class="form-input text-sm">
                </div>
            </div>
        </section>

        <!-- Maintenance -->
        <section class="bg-white/80 border border-vanilla-200/60 rounded-2xl shadow-soft p-5">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-semibold text-vanilla-800 text-sm">🔧 Mode maintenance</h2>
                    <p class="text-xs text-vanilla-400 mt-0.5">Affiche une page de maintenance aux visiteurs. L'admin reste accessible.</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer" x-data>
                    <input type="hidden" name="maintenance_mode" value="0">
                    <input type="checkbox" name="maintenance_mode" value="1"
                           <?= ($settings['maintenance_mode'] ?? '0') === '1' ? 'checked' : '' ?>
                           class="sr-only peer">
                    <div class="w-11 h-6 bg-vanilla-200 rounded-full peer peer-checked:bg-vanilla-700 transition-colors relative">
                        <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full shadow peer-checked:translate-x-5 transition-transform"></div>
                    </div>
                </label>
            </div>
        </section>

        <div class="flex justify-end gap-3 pt-2">
            <button type="reset" class="btn-ghost btn py-2 px-5 text-sm">Annuler</button>
            <button type="submit" class="btn-primary btn py-2 px-6 text-sm">💾 Enregistrer les paramètres</button>
        </div>
    </form>
</div>
