<?php
/**
 * test_routes.php — 403/route health tester
 * Access: https://yourdomain.com/test_routes.php?token=vanilla403test
 *
 * ⚠️  DELETE AFTER TESTING IN PRODUCTION.
 *
 * Tests all critical routes by making internal HTTP calls and checking
 * for 200 OK (not 403/404/500). Results logged to storage/logs/route_test.log
 */

define('TEST_TOKEN', 'vanilla403test');
if (($_GET['token'] ?? '') !== TEST_TOKEN) {
    http_response_code(403);
    die('Access denied. Append ?token=vanilla403test to the URL.');
}

// Detect base URL automatically
$scheme  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host    = $_SERVER['HTTP_HOST'] ?? 'localhost';
$baseUrl = $scheme . '://' . $host;

$results = [];
$pass    = 0;
$fail    = 0;

/**
 * Make an HTTP GET request and return status code.
 */
function probe(string $url, int $timeout = 5): int
{
    $ctx = stream_context_create(['http' => [
        'timeout'         => $timeout,
        'ignore_errors'   => true,
        'follow_location' => false, // don't follow, just report redirect
        'user_agent'      => 'VGM-RouteTest/1.0',
        'method'          => 'GET',
    ]]);
    @file_get_contents($url, false, $ctx);
    preg_match('#HTTP/\d+\.\d+ (\d+)#', $http_response_header[0] ?? '', $m);
    return (int)($m[1] ?? 0);
}

function testRoute(string $label, string $path, array $expected = [200, 302]): void
{
    global $results, $pass, $fail, $baseUrl;
    $url    = $baseUrl . $path;
    $code   = probe($url);
    $ok     = in_array($code, $expected, true);
    $danger = $code === 403;
    $results[] = [
        'label'   => $label,
        'url'     => $path,
        'code'    => $code,
        'ok'      => $ok,
        'danger'  => $danger,
    ];
    $ok ? $pass++ : $fail++;
}

// ── Test: Root & Locale ───────────────────────────────────────────
testRoute('Root /               → 302 locale redirect', '/',        [301, 302]);
testRoute('French homepage /fr/',                       '/fr/',      [200]);
testRoute('English homepage /en/',                      '/en/',      [200]);
testRoute('Spanish homepage /es/',                      '/es/',      [200]);

// ── Test: Public pages ────────────────────────────────────────────
testRoute('Shop page /fr/shop',             '/fr/shop',         [200]);
testRoute('Login page /fr/login',           '/fr/login',        [200]);
testRoute('Register page /fr/register',     '/fr/register',     [200]);
testRoute('Cart page /fr/cart',             '/fr/cart',         [200, 302]);
testRoute('Sitemap /sitemap.xml',           '/sitemap.xml',     [200]);
testRoute('Robots /robots.txt',             '/robots.txt',      [200]);

// ── Test: 404 (must NOT be 403) ───────────────────────────────────
testRoute('Invalid route → 404 not 403',    '/fr/completely-bogus-page-xyz', [302, 404]);

// ── Test: Assets accessible ───────────────────────────────────────
// (Only tests if the file physically exists; tests serve 200 or file path ok)
$assetPaths = [
    '/assets/css/app.css',
    '/assets/js/app.js',
];
foreach ($assetPaths as $ap) {
    $physical = dirname(__FILE__) . $ap;
    if (file_exists($physical)) {
        testRoute("Asset: $ap", $ap, [200]);
    }
}

// ── Test: Blocked sensitive files ─────────────────────────────────
testRoute('.env blocked (must be 403/404)',  '/.env',            [403, 404]);
testRoute('.htaccess blocked',               '/.htaccess',       [403, 404]);

// ── Log results ───────────────────────────────────────────────────
$logPath = dirname(__FILE__) . '/../storage/logs/route_test.log';
$logDir  = dirname($logPath);
if (!is_dir($logDir)) @mkdir($logDir, 0775, true);

$logEntry = "\n[Route Test " . date('Y-m-d H:i:s') . "] Base: $baseUrl\n";
foreach ($results as $r) {
    $status = $r['ok'] ? 'PASS' : ($r['danger'] ? 'DANGER-403' : 'FAIL');
    $logEntry .= "[$status] {$r['code']} {$r['url']} ({$r['label']})\n";
}
$logEntry .= "Total: $pass passed, $fail failed\n";
@file_put_contents($logPath, $logEntry, FILE_APPEND);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Route Test — Vanilla Groupe Madagascar</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #0f172a; color: #e2e8f0; padding: 2rem; }
        h1 { color: #f59e0b; font-size: 1.4rem; margin-bottom: 0.5rem; }
        .meta { color: #94a3b8; font-size: 0.85rem; margin-bottom: 1.5rem; }
        .summary { display: flex; gap: 1rem; margin-bottom: 1.5rem; }
        .badge { padding: 0.6rem 1.2rem; border-radius: 6px; font-weight: 700; font-size: 1rem; }
        .badge.pass { background: #14532d; color: #4ade80; }
        .badge.fail { background: #7f1d1d; color: #fca5a5; }
        table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
        th { background: #1e293b; color: #94a3b8; text-align: left; padding: 0.5rem 0.75rem; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.05em; }
        td { padding: 0.5rem 0.75rem; border-bottom: 1px solid #1e293b; }
        tr.pass td:first-child { color: #4ade80; }
        tr.fail td:first-child { color: #f87171; }
        tr.danger { background: rgba(127,29,29,0.3); }
        tr.danger td:first-child { color: #fb923c; font-weight: bold; }
        .code-200 { color: #4ade80; }
        .code-302 { color: #60a5fa; }
        .code-404 { color: #94a3b8; }
        .code-403 { color: #f87171; font-weight: bold; }
        .code-500 { color: #f59e0b; font-weight: bold; }
        .code-0   { color: #6b7280; }
        .warn { background: #78350f; color: #fcd34d; padding: 0.75rem 1rem; border-radius: 6px; margin-bottom: 1.5rem; font-size: 0.85rem; }
    </style>
</head>
<body>
<h1>🧪 Route Health Test</h1>
<p class="meta">Base URL: <?= htmlspecialchars($baseUrl) ?> · <?= date('Y-m-d H:i:s') ?></p>

<div class="warn">⚠️ Delete this file before going to production.</div>

<div class="summary">
    <div class="badge pass">✅ <?= $pass ?> passed</div>
    <div class="badge fail">❌ <?= $fail ?> failed</div>
</div>

<table>
    <thead><tr><th>Result</th><th>HTTP</th><th>Route</th><th>Description</th></tr></thead>
    <tbody>
    <?php foreach ($results as $r):
        $rowClass = $r['danger'] ? 'danger' : ($r['ok'] ? 'pass' : 'fail');
        $codeClass = 'code-' . ($r['code'] ?: '0');
        $icon = $r['danger'] ? '🚨' : ($r['ok'] ? '✅' : '❌');
    ?>
        <tr class="<?= $rowClass ?>">
            <td><?= $icon ?></td>
            <td class="<?= $codeClass ?>"><?= $r['code'] ?: '—' ?></td>
            <td><?= htmlspecialchars($r['url']) ?></td>
            <td><?= htmlspecialchars($r['label']) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<p style="color:#475569;font-size:0.8rem;margin-top:1.2rem;">Log: <?= htmlspecialchars($logPath) ?></p>
</body>
</html>
