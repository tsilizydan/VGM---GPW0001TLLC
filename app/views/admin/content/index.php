<!-- ADMIN — TinyMCE Content Editor -->
<?php
/** @var array<string,string> $pages */
/** @var string $page */
/** @var string $locale */
/** @var string $content */
/** @var array<string,string> $locales */
?>
<div class="space-y-5 max-w-5xl">
    <h1 class="font-serif font-bold text-vanilla-900 text-2xl">Éditeur de contenu</h1>

    <div class="flex flex-wrap gap-3 items-center">
        <!-- Page picker -->
        <form method="GET" id="page-picker-form" class="flex flex-wrap gap-2">
            <select name="page" class="form-input text-sm py-2" onchange="this.form.submit()">
            <?php foreach ($pages as $key => $label): ?>
            <option value="<?= $key ?>" <?= $page === $key ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
            </select>
            <!-- Keep locale param -->
            <input type="hidden" name="locale" value="<?= e($locale) ?>">
        </form>

        <!-- Locale tabs -->
        <div class="flex gap-1 bg-cream-200 rounded-xl p-1">
            <?php foreach ($locales as $code => $flag): ?>
            <a href="?page=<?= urlencode($page) ?>&locale=<?= $code ?>"
               class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors
                      <?= $locale === $code ? 'bg-white shadow text-vanilla-800' : 'text-vanilla-500 hover:text-vanilla-700' ?>">
                <?= $flag ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Editor form -->
    <form
        method="POST"
        action="<?= locale_url('admin/content/update') ?>"
        class="bg-white/80 border border-vanilla-200/60 rounded-2xl shadow-soft p-6 space-y-4"
    >
        <?= csrf_field() ?>
        <input type="hidden" name="page" value="<?= e($page) ?>">
        <input type="hidden" name="locale" value="<?= e($locale) ?>">

        <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold text-vanilla-700">
                <?= e($pages[$page]) ?> — <?= e($locales[$locale] ?? $locale) ?>
            </h2>
            <div class="flex gap-2">
                <button type="button" id="btn-preview"
                        class="btn-ghost btn btn-sm text-xs" onclick="togglePreview()">
                    👁 Aperçu
                </button>
                <button type="submit" class="btn-primary btn btn-sm text-xs">
                    💾 Enregistrer
                </button>
            </div>
        </div>

        <!-- TinyMCE target -->
        <textarea
            id="content_body"
            name="content_body"
            class="w-full min-h-[500px] border border-vanilla-200 rounded-xl"
        ><?= htmlspecialchars($content, ENT_QUOTES) ?></textarea>

        <!-- Preview pane -->
        <div id="preview-pane" class="hidden border border-vanilla-200 rounded-xl p-6 prose prose-vanilla max-w-none bg-cream-50">
            Aperçu chargé après sauvegarde.
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    if (typeof tinymce === 'undefined') return;
    tinymce.init({
        selector: '#content_body',
        plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
        promotion: false,
        branding: false,
        language: 'fr_FR',
        height: 520,
        content_style: `
            body { font-family: Inter, sans-serif; color: #3B2420; background: #F8F5F0; padding: 16px; }
            h1,h2,h3 { font-family: "Playfair Display", Georgia, serif; }
        `,
        skin: 'oxide',
        images_upload_url: '<?= locale_url('admin/content/upload') ?>',
        automatic_uploads: true,
        file_picker_types: 'image',
        setup: function(editor) {
            editor.on('change', function() { editor.save(); });
        }
    });
});

function togglePreview() {
    const pane = document.getElementById('preview-pane');
    if (pane.classList.contains('hidden')) {
        if (typeof tinymce !== 'undefined') {
            pane.innerHTML = tinymce.get('content_body').getContent();
        }
        pane.classList.remove('hidden');
    } else {
        pane.classList.add('hidden');
    }
}
</script>
