<!-- ══════════════════════════════════════════════
     MODAL — Reusable Alpine modal
     Usage:
       Open:  $store.modal.open('modal-id')
       Close: $store.modal.close()
     Props: $modalId (string), $title (string), optional $size
     ══════════════════════════════════════════════ -->
<?php
/** @var string $modalId */
/** @var string $title */
/** @var string $content  rendered slot HTML */
/** @var string $size  'sm' | 'md' | 'lg' | 'xl' (default 'md') */
$sizeClass = match($size ?? 'md') {
    'sm' => 'max-w-sm',
    'lg' => 'max-w-2xl',
    'xl' => 'max-w-4xl',
    default => 'max-w-lg',
};
?>
<div
    x-data
    x-show="$store.modal.isOpen('<?= e($modalId) ?>')"
    x-transition:enter="transition ease-[cubic-bezier(0.16,1,0.3,1)] duration-350"
    x-transition:enter-start="opacity-0 scale-95 translate-y-4"
    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95"
    class="fixed inset-0 z-[110] flex items-center justify-center p-4"
    style="display:none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="modal-title-<?= e($modalId) ?>"
    @keydown.escape.window="$store.modal.close()"
>
    <div class="w-full <?= $sizeClass ?> bg-white/95 backdrop-blur-xl rounded-2xl
                shadow-glass-lg border border-white/60 overflow-hidden">

        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-cream-200">
            <h2 id="modal-title-<?= e($modalId) ?>"
                class="font-serif font-bold text-vanilla-800 text-h4">
                <?= e($title ?? '') ?>
            </h2>
            <button
                @click="$store.modal.close()"
                class="p-2 rounded-xl text-vanilla-400 hover:text-vanilla-700 hover:bg-cream-100
                       transition-all duration-200"
                aria-label="Fermer"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Body -->
        <div class="px-6 py-5 max-h-[75vh] overflow-y-auto">
            <?= $content ?? '' ?>
        </div>
    </div>
</div>
