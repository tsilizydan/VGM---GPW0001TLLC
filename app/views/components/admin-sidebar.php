<?php
// Admin sidebar component
// $currentPath = current request URI (for active state detection)
$uri = ltrim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '', '/');
$uri = preg_replace('#^(fr|en|es)/#', '', $uri); // strip locale

function adminNavIs(string $uri, string $prefix): bool {
    return str_starts_with($uri, ltrim($prefix, '/'));
}

$navGroups = [
    'Tableau de bord' => [
        ['label' => 'Dashboard',       'href' => 'admin',              'icon' => 'chart-bar'],
    ],
    'Catalogue' => [
        ['label' => 'Produits',        'href' => 'admin/products',     'icon' => 'tag'],
        ['label' => 'Catégories',      'href' => 'admin/categories',   'icon' => 'folder'],
    ],
    'Ventes' => [
        ['label' => 'Commandes',       'href' => 'admin/orders',       'icon' => 'shopping-bag'],
        ['label' => 'Clients',         'href' => 'admin/customers',    'icon' => 'users'],
    ],
    'Contenu' => [
        ['label' => 'Éditeur',         'href' => 'admin/content',      'icon' => 'pencil-square'],
        ['label' => 'Traductions',     'href' => 'admin/translations', 'icon' => 'language'],
    ],
    'Configuration' => [
        ['label' => 'Paramètres',      'href' => 'admin/settings',     'icon' => 'cog'],
    ],
];

/** Return outline SVG icon */
function adminIcon(string $name): string {
    $icons = [
        'chart-bar'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/>',
        'tag'           => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z"/>',
        'folder'        => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z"/>',
        'shopping-bag'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>',
        'users'         => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/>',
        'pencil-square' => '<path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/>',
        'language'      => '<path stroke-linecap="round" stroke-linejoin="round" d="m10.5 21 5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 0 1 6-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502m9.334-12.138c.896.061 1.785.147 2.666.257m-4.589 8.495a18.023 18.023 0 0 1-3.827-5.802"/>',
        'cog'           => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>',
    ];
    $d = $icons[$name] ?? '';
    return '<svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">' . $d . '</svg>';
}
?>

<!-- Sidebar -->
<aside
    class="fixed inset-y-0 left-0 z-40 w-64 flex flex-col bg-vanilla-800 transition-transform duration-300 ease-smooth"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
>
    <!-- Logo -->
    <div class="flex items-center gap-3 h-14 px-5 border-b border-vanilla-700/60">
        <span class="text-gold-400 text-xl">🍂</span>
        <div>
            <p class="font-serif font-bold text-cream-100 text-sm leading-tight">Vanilla Groupe</p>
            <p class="text-vanilla-400 text-[10px] font-semibold uppercase tracking-widest">Admin</p>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-5">
        <?php foreach ($navGroups as $group => $items): ?>
        <div>
            <p class="text-[10px] font-bold uppercase tracking-widest text-vanilla-500 px-2 mb-1.5"><?= $group ?></p>
            <ul class="space-y-0.5">
            <?php foreach ($items as $item): ?>
            <?php $isActive = adminNavIs($uri, $item['href']); ?>
            <li>
                <a href="<?= locale_url($item['href']) ?>"
                   class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200
                          <?= $isActive
                              ? 'bg-vanilla-700 text-cream-100 shadow-inner'
                              : 'text-vanilla-300 hover:bg-vanilla-700/50 hover:text-cream-100' ?>">
                    <?= adminIcon($item['icon']) ?>
                    <?= $item['label'] ?>
                    <?php if ($isActive): ?>
                    <span class="ml-auto w-1.5 h-1.5 rounded-full bg-gold-400"></span>
                    <?php endif; ?>
                </a>
            </li>
            <?php endforeach; ?>
            </ul>
        </div>
        <?php endforeach; ?>
    </nav>

    <!-- User info -->
    <div class="border-t border-vanilla-700/60 px-4 py-3 flex items-center gap-3">
        <div class="w-8 h-8 rounded-full bg-vanilla-600 flex items-center justify-center text-cream-100 text-sm font-bold shrink-0">
            <?= mb_strtoupper(mb_substr(\Core\Auth::user()['name'] ?? 'A', 0, 1)) ?>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-cream-100 text-sm font-semibold truncate"><?= e(\Core\Auth::user()['name'] ?? 'Admin') ?></p>
            <p class="text-vanilla-400 text-xs truncate"><?= e(\Core\Auth::user()['email'] ?? '') ?></p>
        </div>
        <a href="<?= locale_url('logout') ?>" class="text-vanilla-400 hover:text-red-400 transition-colors" title="Déconnexion">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75"/>
            </svg>
        </a>
    </div>
</aside>
