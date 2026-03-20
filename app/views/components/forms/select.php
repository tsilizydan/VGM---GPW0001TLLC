<?php
/** Usage:
 * $field = [
 *   'name'     => 'category',
 *   'label'    => 'Catégorie',
 *   'options'  => ['' => 'Choisir...', 'premium' => 'Premium', 'bio' => 'Bio'],
 *   'value'    => old('category'),
 *   'required' => false,
 *   'error'    => null,
 * ]
 */
$name     = $field['name']    ?? '';
$label    = $field['label']   ?? '';
$options  = $field['options'] ?? [];
$value    = $field['value']   ?? '';
$required = $field['required'] ?? false;
$error    = $field['error']   ?? null;
$id       = $field['id']      ?? $name;

$selectClass = $error
    ? 'w-full font-sans text-sm text-vanilla-800 px-4 py-3 rounded-xl bg-white/70 border border-red-400 backdrop-blur-sm transition-all duration-250 outline-none appearance-none cursor-pointer focus:bg-white/90 focus:border-red-500 focus:ring-2 focus:ring-red-300/40'
    : 'w-full font-sans text-sm text-vanilla-800 px-4 py-3 rounded-xl bg-white/70 border border-vanilla-200/60 backdrop-blur-sm transition-all duration-250 outline-none appearance-none cursor-pointer focus:bg-white/90 focus:border-forest-400 focus:ring-2 focus:ring-forest-300/40';
?>
<div class="space-y-1.5">
    <?php if ($label): ?>
    <label for="<?= e($id) ?>" class="block text-sm font-semibold text-vanilla-700">
        <?= e($label) ?>
        <?php if ($required): ?><span class="text-red-500 ml-0.5">*</span><?php endif; ?>
    </label>
    <?php endif; ?>
    <div class="relative">
        <select
            id="<?= e($id) ?>"
            name="<?= e($name) ?>"
            <?= $required ? 'required' : '' ?>
            class="<?= $selectClass ?>"
        >
            <?php foreach ($options as $optVal => $optLabel): ?>
            <option value="<?= e((string) $optVal) ?>"
                    <?= ((string)$value === (string)$optVal) ? 'selected' : '' ?>>
                <?= e($optLabel) ?>
            </option>
            <?php endforeach; ?>
        </select>
        <!-- Custom chevron -->
        <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-vanilla-400">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" d="m19 9-7 7-7-7"/>
            </svg>
        </div>
    </div>
    <?php if ($error): ?>
    <p class="flex items-center gap-1.5 text-xs text-red-600">
        <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        <?= e($error) ?>
    </p>
    <?php endif; ?>
</div>
