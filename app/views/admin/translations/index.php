<!-- ══════════════════════════════════════════════
     ADMIN — Translation Editor
     Inline Alpine.js saves, search filter, pivot table (FR | EN | ES)
     ══════════════════════════════════════════════ -->

<section class="py-10">
<div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-vanilla-400 mb-1">Administration</p>
            <h1 class="font-serif font-bold text-vanilla-900 text-h1"><?= t('admin.translations') ?></h1>
        </div>
        <a href="<?= locale_url('admin') ?>"
           class="inline-flex items-center gap-2 text-sm font-semibold text-vanilla-500 hover:text-vanilla-700 transition-colors">
            ← Tableau de bord
        </a>
    </div>

    <!-- Search & stats -->
    <div
        x-data
        class="flex flex-col sm:flex-row items-start sm:items-center gap-3 mb-6"
    >
        <form method="GET" action="" class="flex-1 max-w-sm">
            <div class="relative">
                <input
                    type="search"
                    name="q"
                    value="<?= e($search ?? '') ?>"
                    placeholder="<?= t('admin.filter') ?>"
                    class="w-full pl-9 pr-4 py-2.5 rounded-xl bg-white/70 border border-vanilla-200/60
                           text-sm text-vanilla-800 placeholder:text-vanilla-400
                           focus:outline-none focus:ring-2 focus:ring-forest-300/40 focus:border-forest-400
                           backdrop-blur-sm transition-all duration-250"
                >
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-vanilla-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                </svg>
            </div>
        </form>
        <span class="text-xs font-medium text-vanilla-400">
            <?= count($pivot) ?> clé(s)
            <?php if ($search): ?>&nbsp;· Filtrées sur "<strong><?= e($search) ?></strong>"<?php endif; ?>
        </span>
    </div>

    <!-- Table -->
    <div class="bg-white/80 backdrop-blur-md border border-white/60 rounded-2xl shadow-glass overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-vanilla-800 text-cream-100 text-xs uppercase tracking-wider">
                        <th class="px-4 py-3 text-left font-semibold w-64"><?= t('admin.key') ?></th>
                        <th class="px-4 py-3 text-left font-semibold">
                            🇫🇷 <?= t('admin.fr') ?>
                        </th>
                        <th class="px-4 py-3 text-left font-semibold border-l border-vanilla-700">
                            🇬🇧 <?= t('admin.en') ?>
                        </th>
                        <th class="px-4 py-3 text-left font-semibold border-l border-vanilla-700">
                            🇪🇸 <?= t('admin.es') ?>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-vanilla-100">
                    <?php foreach ($pivot as $key => $locales): ?>
                    <tr
                        x-data="{
                            key:    '<?= e(addslashes($key)) ?>',
                            fr_val: '<?= e(addslashes($locales['fr'] ?? '')) ?>',
                            en_val: '<?= e(addslashes($locales['en'] ?? '')) ?>',
                            es_val: '<?= e(addslashes($locales['es'] ?? '')) ?>',
                            saving: '',
                            saved:  '',
                            async save(locale) {
                                this.saving = locale;
                                const val = this[locale + '_val'];
                                const resp = await fetch('<?= locale_url('admin/translations/update') ?>', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': '<?= csrf_token() ?>' },
                                    body: JSON.stringify({ locale, key: this.key, value: val, _token: '<?= csrf_token() ?>' })
                                });
                                const data = await resp.json();
                                this.saving = '';
                                this.saved  = locale;
                                setTimeout(() => this.saved = '', 2000);
                            }
                        }"
                        class="hover:bg-cream-50 transition-colors duration-150 group"
                    >
                        <!-- Key -->
                        <td class="px-4 py-3 align-top">
                            <code class="text-xs font-mono text-vanilla-600 bg-cream-100 px-2 py-0.5 rounded-md break-all">
                                <?= e($key) ?>
                            </code>
                        </td>

                        <!-- French (read-only reference column) -->
                        <td class="px-4 py-3 align-top text-vanilla-600 text-sm leading-snug max-w-xs">
                            <span class="line-clamp-2" :title="fr_val" x-text="fr_val"></span>
                        </td>

                        <!-- English -->
                        <td class="px-4 py-3 align-top border-l border-vanilla-100">
                            <div class="flex items-start gap-2">
                                <textarea
                                    x-model="en_val"
                                    rows="2"
                                    class="flex-1 w-full text-xs text-vanilla-800 px-2.5 py-1.5 rounded-lg
                                           bg-white border border-vanilla-200/60 resize-none
                                           focus:outline-none focus:ring-2 focus:ring-forest-300/40 focus:border-forest-400
                                           transition-all duration-200"
                                    @keydown.ctrl.enter.prevent="save('en')"
                                ></textarea>
                                <button
                                    @click="save('en')"
                                    :disabled="saving === 'en'"
                                    class="shrink-0 mt-0.5 w-7 h-7 rounded-lg flex items-center justify-center transition-all duration-200"
                                    :class="saved === 'en'
                                        ? 'bg-forest-100 text-forest-600'
                                        : 'bg-cream-200 text-vanilla-500 hover:bg-vanilla-200 hover:text-vanilla-800'"
                                    :title="'<?= t('admin.save') ?>'"
                                >
                                    <svg x-show="saving !== 'en' && saved !== 'en'" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" d="M16.5 3.75V16.5L12 14.25 7.5 16.5V3.75m9 0H18A2.25 2.25 0 0 1 20.25 6v12A2.25 2.25 0 0 1 18 20.25H6A2.25 2.25 0 0 1 3.75 18V6A2.25 2.25 0 0 1 6 3.75h1.5m9 0h-9"/>
                                    </svg>
                                    <svg x-show="saved === 'en'" class="w-3.5 h-3.5 text-forest-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" d="m4.5 12.75 6 6 9-13.5"/>
                                    </svg>
                                    <svg x-show="saving === 'en'" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                    </svg>
                                </button>
                            </div>
                        </td>

                        <!-- Spanish -->
                        <td class="px-4 py-3 align-top border-l border-vanilla-100">
                            <div class="flex items-start gap-2">
                                <textarea
                                    x-model="es_val"
                                    rows="2"
                                    class="flex-1 w-full text-xs text-vanilla-800 px-2.5 py-1.5 rounded-lg
                                           bg-white border border-vanilla-200/60 resize-none
                                           focus:outline-none focus:ring-2 focus:ring-forest-300/40 focus:border-forest-400
                                           transition-all duration-200"
                                    @keydown.ctrl.enter.prevent="save('es')"
                                ></textarea>
                                <button
                                    @click="save('es')"
                                    :disabled="saving === 'es'"
                                    class="shrink-0 mt-0.5 w-7 h-7 rounded-lg flex items-center justify-center transition-all duration-200"
                                    :class="saved === 'es'
                                        ? 'bg-forest-100 text-forest-600'
                                        : 'bg-cream-200 text-vanilla-500 hover:bg-vanilla-200 hover:text-vanilla-800'"
                                >
                                    <svg x-show="saving !== 'es' && saved !== 'es'" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" d="M16.5 3.75V16.5L12 14.25 7.5 16.5V3.75m9 0H18A2.25 2.25 0 0 1 20.25 6v12A2.25 2.25 0 0 1 18 20.25H6A2.25 2.25 0 0 1 3.75 18V6A2.25 2.25 0 0 1 6 3.75h1.5m9 0h-9"/>
                                    </svg>
                                    <svg x-show="saved === 'es'" class="w-3.5 h-3.5 text-forest-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" d="m4.5 12.75 6 6 9-13.5"/>
                                    </svg>
                                    <svg x-show="saving === 'es'" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>

                    <?php if (empty($pivot)): ?>
                    <tr>
                        <td colspan="4" class="py-16 text-center text-vanilla-400 text-sm">
                            Aucune clé trouvée<?= $search ? ' pour "' . e($search) . '"' : '' ?>.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Table footer -->
        <div class="px-4 py-3 bg-cream-50 border-t border-vanilla-100 flex items-center justify-between">
            <p class="text-xs text-vanilla-400">
                💡 Appuyez sur <kbd class="bg-cream-200 border border-vanilla-200 px-1.5 py-0.5 rounded text-[10px] font-mono">Ctrl + Enter</kbd> dans une cellule pour sauvegarder rapidement.
            </p>
            <p class="text-xs text-vanilla-400">
                Langue de référence : 🇫🇷 Français (non éditable ici)
            </p>
        </div>
    </div>

</div>
</section>
