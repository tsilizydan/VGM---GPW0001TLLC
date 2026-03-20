<!-- Checkout progress bar (shared partial) -->
<?php
$steps = [
    ['url' => 'checkout',         'label' => 'Adresse'],
    ['url' => 'checkout/shipping','label' => 'Livraison'],
    ['url' => 'checkout/confirm', 'label' => 'Paiement'],
];

// Detect current step from URL
$uri = ltrim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '', '/');
// strip locale
$uri = preg_replace('#^(fr|en|es)/#', '', $uri);

$currentStep = 0;
if (str_contains($uri, 'confirm')) $currentStep = 2;
elseif (str_contains($uri, 'shipping')) $currentStep = 1;
?>
<div class="bg-cream-100/80 border-b border-vanilla-200/40">
<div class="max-w-[1100px] mx-auto px-4 sm:px-6 lg:px-8 py-4">
    <nav class="flex items-center gap-2 justify-center">
        <?php foreach ($steps as $i => $step): ?>
            <?php if ($i > 0): ?>
            <div class="h-px w-8 md:w-16 bg-vanilla-200 <?= $i <= $currentStep ? 'bg-vanilla-700' : '' ?>"></div>
            <?php endif; ?>
            <div class="flex items-center gap-2">
                <span class="w-7 h-7 rounded-full text-xs font-bold flex items-center justify-center transition-colors
                    <?= $i < $currentStep ? 'bg-forest-500 text-white' : ($i === $currentStep ? 'bg-vanilla-700 text-cream-100' : 'bg-cream-200 text-vanilla-400') ?>">
                    <?= $i < $currentStep ? '✓' : ($i + 1) ?>
                </span>
                <span class="text-sm font-semibold <?= $i === $currentStep ? 'text-vanilla-800' : ($i < $currentStep ? 'text-forest-600' : 'text-vanilla-400') ?>
                             hidden sm:inline">
                    <?= $step['label'] ?>
                </span>
            </div>
        <?php endforeach; ?>
    </nav>
</div>
</div>
