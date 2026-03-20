<?php
/** Usage:
 * include with $field = [
 *   'name'        => 'email',
 *   'label'       => 'Adresse e-mail',
 *   'type'        => 'email',       // text|email|number|tel|url
 *   'placeholder' => 'vous@ex.com',
 *   'value'       => old('email'),
 *   'required'    => true,
 *   'error'       => $errors['email'] ?? null,
 *   'hint'        => 'Optional helper text',
 * ]
 */
$name        = $field['name']        ?? '';
$label       = $field['label']       ?? '';
$type        = $field['type']        ?? 'text';
$placeholder = $field['placeholder'] ?? '';
$value       = $field['value']       ?? '';
$required    = $field['required']    ?? false;
$error       = $field['error']       ?? null;
$hint        = $field['hint']        ?? null;
$id          = $field['id']          ?? $name;

$inputClass = $error
    ? 'w-full font-sans text-sm text-vanilla-800 px-4 py-3 rounded-xl bg-white/70 border border-red-400 backdrop-blur-sm placeholder:text-vanilla-400 transition-all duration-250 outline-none focus:bg-white/90 focus:border-red-500 focus:ring-2 focus:ring-red-300/40'
    : 'w-full font-sans text-sm text-vanilla-800 px-4 py-3 rounded-xl bg-white/70 border border-vanilla-200/60 backdrop-blur-sm placeholder:text-vanilla-400 transition-all duration-250 outline-none focus:bg-white/90 focus:border-forest-400 focus:ring-2 focus:ring-forest-300/40';
?>
<div class="space-y-1.5">
    <?php if ($label): ?>
    <label for="<?= e($id) ?>" class="block text-sm font-semibold text-vanilla-700">
        <?= e($label) ?>
        <?php if ($required): ?><span class="text-red-500 ml-0.5">*</span><?php endif; ?>
    </label>
    <?php endif; ?>

    <input
        type="<?= e($type) ?>"
        id="<?= e($id) ?>"
        name="<?= e($name) ?>"
        value="<?= e((string) $value) ?>"
        placeholder="<?= e($placeholder) ?>"
        <?= $required ? 'required' : '' ?>
        <?= $error ? 'aria-invalid="true"' : '' ?>
        aria-describedby="<?= $error ? "{$id}-error" : ($hint ? "{$id}-hint" : '') ?>"
        class="<?= $inputClass ?>"
    >

    <?php if ($error): ?>
    <p id="<?= e($id) ?>-error" class="flex items-center gap-1.5 text-xs text-red-600">
        <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        <?= e($error) ?>
    </p>
    <?php elseif ($hint): ?>
    <p id="<?= e($id) ?>-hint" class="text-xs text-vanilla-400"><?= e($hint) ?></p>
    <?php endif; ?>
</div>
