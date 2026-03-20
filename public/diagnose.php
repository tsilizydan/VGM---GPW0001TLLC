<?php
/**
 * 403 Diagnostic Script — Vanilla Groupe Madagascar
 *
 * ⚠️  SECURITY: DELETE THIS FILE OR PASSWORD-PROTECT IT AFTER DIAGNOSING.
 * Access: https://yourdomain.com/diagnose.php
 *
 * Checks all common causes of 403 Forbidden on Namecheap shared hosting.
 * Logs results to /storage/logs/403_debug.log
 */

// ── Basic protection (change this token) ─────────────────────────
define('DIAG_TOKEN', 'vanilla403debug');
if (($_GET['token'] ?? '') !== DIAG_TOKEN) {
    http_response_code(403);
    die('Access denied. Append ?token=vanilla403debug to the URL.');
}

define('BASE_PATH', dirname(__FILE__) . '/..'); // project root relative to public/
$logFile = BASE_PATH . '/storage/logs/403_debug.log';
$results = [];
$pass    = 0;
$fail    = 0;

function check(string $label, bool $ok, string $detail = ''): void
{
    global $results, $pass, $fail;
    $status      = $ok ? '✅ PASS' : '❌ FAIL';
    $results[]   = compact('label', 'ok', 'status', 'detail');
    $ok ? $pass++ : $fail++;
}

// ── 1. PHP & Server Info ─────────────────────────────────────────
check('PHP Version ≥ 8.1', PHP_MAJOR_VERSION >= 8 && PHP_MINOR_VERSION >= 1, 'PHP ' . PHP_VERSION);
check('Document Root readable', is_readable($_SERVER['DOCUMENT_ROOT']), $_SERVER['DOCUMENT_ROOT']);
check('SERVER_SOFTWARE detected', !empty($_SERVER['SERVER_SOFTWARE']), $_SERVER['SERVER_SOFTWARE'] ?? 'unknown');
check('index.php in document root', file_exists($_SERVER['DOCUMENT_ROOT'] . '/index.php'), 'Checks if front controller is reachable');

// ── 2. Critical file permissions ─────────────────────────────────
$criticalFiles = [
    BASE_PATH . '/public/index.php'    => '644',
    BASE_PATH . '/public/.htaccess'    => '644',
    BASE_PATH . '/.htaccess'           => '644',
    BASE_PATH . '/.env'                => '600',
    BASE_PATH . '/config/database.php' => '644',
    BASE_PATH . '/core/Application.php'=> '644',
];

foreach ($criticalFiles as $path => $expected) {
    if (!file_exists($path)) {
        check("File exists: " . basename($path), false, "Missing: $path");
        continue;
    }
    $perms = substr(sprintf('%o', fileperms($path)), -3);
    $readable = is_readable($path);
    check(
        "Permission ok: " . basename($path),
        $readable,
        "Perms: $perms (expected $expected), readable: " . ($readable ? 'yes' : 'NO')
    );
}

// ── 3. Critical directory permissions ─────────────────────────────
$criticalDirs = [
    BASE_PATH . '/public'          => '755',
    BASE_PATH . '/public/assets'   => '755',
    BASE_PATH . '/storage'         => '755',
    BASE_PATH . '/storage/cache'   => '775',
    BASE_PATH . '/storage/logs'    => '775',
    BASE_PATH . '/app'             => '755',
    BASE_PATH . '/core'            => '755',
    BASE_PATH . '/config'          => '755',
];

foreach ($criticalDirs as $path => $expected) {
    if (!is_dir($path)) {
        check("Dir exists: " . basename($path), false, "Missing directory: $path");
        continue;
    }
    $perms    = substr(sprintf('%o', fileperms($path)), -3);
    $readable = is_readable($path) && is_executable($path);
    check(
        "Dir permission: " . basename($path),
        $readable,
        "Perms: $perms (expected ≥ $expected), accessible: " . ($readable ? 'yes' : 'NO')
    );
}

// ── 4. Storage write permissions ─────────────────────────────────
$writeDirs = [
    BASE_PATH . '/storage/cache',
    BASE_PATH . '/storage/logs',
    BASE_PATH . '/public/assets/img',
    BASE_PATH . '/public/assets/cache',
];
foreach ($writeDirs as $dir) {
    if (!is_dir($dir)) {
        check("Writable dir: " . basename($dir), false, "Directory does not exist: $dir");
    } else {
        check("Writable: " . basename($dir), is_writable($dir), $dir);
    }
}

// ── 5. PHP extensions ─────────────────────────────────────────────
$requiredExts = ['pdo_mysql', 'gd', 'mbstring', 'json', 'openssl', 'fileinfo'];
foreach ($requiredExts as $ext) {
    check("PHP ext: $ext", extension_loaded($ext));
}

// ── 6. .htaccess content checks ───────────────────────────────────
$htaccessPath = BASE_PATH . '/public/.htaccess';
if (file_exists($htaccessPath)) {
    $htContent = file_get_contents($htaccessPath);
    check('.htaccess: RewriteEngine On', str_contains($htContent, 'RewriteEngine On'));
    check('.htaccess: index.php route exists', str_contains($htContent, 'index.php'));
    check('.htaccess: No bare Deny from all (outside FilesMatch)', !preg_match('/^Deny from all/m', $htContent), 'Bare Deny from all causes 403 for all requests');
    check('.htaccess: CSP single-line (no backslash cont.)', !preg_match('/Content-Security-Policy.*\\\\\n/s', $htContent), 'Multi-line CSP with backslash causes Apache parse errors → 403/500');
} else {
    check('.htaccess exists in /public', false, "Missing: $htaccessPath");
}

// ── 7. Root .htaccess safety test ────────────────────────────────
$rootHtaccess = BASE_PATH . '/.htaccess';
if (file_exists($rootHtaccess)) {
    $rootContent = file_get_contents($rootHtaccess);
    $hasBareBlock = preg_match('/^Require all denied\s*$/m', $rootContent) 
                 && !str_contains($rootContent, '<FilesMatch');
    check('Root .htaccess: not blocking everything', !$hasBareBlock, $hasBareBlock ? '⚠️  Root .htaccess has Require all denied without FilesMatch scope — causes 403 for all requests!' : 'OK');
}

// ── 8. Session writable ───────────────────────────────────────────
$sessionPath = session_save_path() ?: sys_get_temp_dir();
check('Session dir writable', is_writable($sessionPath), $sessionPath);

// ── 9. ENV file present ───────────────────────────────────────────
check('.env file exists', file_exists(BASE_PATH . '/.env'), BASE_PATH . '/.env');

// ── 10. HTTPS / SSL ───────────────────────────────────────────────
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ($_SERVER['SERVER_PORT'] ?? 80) == 443;
check('HTTPS active', $isHttps, $isHttps ? 'HTTPS is on ✓' : 'Running on plain HTTP — HSTS header is conditional, no loop');

// ── Log results ───────────────────────────────────────────────────
$logDir = dirname($logFile);
if (!is_dir($logDir)) {
    @mkdir($logDir, 0775, true);
}
$logEntry = "\n" . str_repeat('=', 60) . "\n";
$logEntry .= 'Diagnostic run: ' . date('Y-m-d H:i:s') . ' | IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "\n";
$logEntry .= "PHP: " . PHP_VERSION . " | Server: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'unknown') . "\n";
$logEntry .= str_repeat('-', 60) . "\n";
foreach ($results as $r) {
    $logEntry .= sprintf("[%s] %s — %s\n", $r['ok'] ? 'PASS' : 'FAIL', $r['label'], $r['detail']);
}
$logEntry .= "\nTotal: {$pass} passed, {$fail} failed\n";
@file_put_contents($logFile, $logEntry, FILE_APPEND);

// ── HTML Output ───────────────────────────────────────────────────
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 Diagnostic — Vanilla Groupe Madagascar</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #0f172a; color: #e2e8f0; padding: 2rem; }
        h1 { color: #f59e0b; font-size: 1.5rem; margin-bottom: 0.5rem; }
        .meta { color: #94a3b8; font-size: 0.85rem; margin-bottom: 2rem; }
        .summary { display: flex; gap: 1rem; margin-bottom: 2rem; }
        .badge { padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 700; font-size: 1.1rem; }
        .badge.pass { background: #14532d; color: #4ade80; }
        .badge.fail { background: #7f1d1d; color: #fca5a5; }
        table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
        th { background: #1e293b; color: #94a3b8; text-align: left; padding: 0.6rem 1rem; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; }
        td { padding: 0.6rem 1rem; border-bottom: 1px solid #1e293b; }
        tr.ok td:first-child { color: #4ade80; }
        tr.fail td:first-child { color: #f87171; }
        tr.fail { background: rgba(127,29,29,0.2); }
        .detail { color: #64748b; font-size: 0.8rem; }
        .warning { background: #78350f; color: #fcd34d; padding: 1rem; border-radius: 8px; margin-bottom: 2rem; font-size: 0.9rem; }
    </style>
</head>
<body>
<h1>🔍 403 Diagnostic Report</h1>
<p class="meta">Run at <?= date('Y-m-d H:i:s') ?> · PHP <?= PHP_VERSION ?> · <?= htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? 'unknown') ?></p>

<div class="warning">⚠️ Delete or password-protect this file before going to production.</div>

<div class="summary">
    <div class="badge pass">✅ <?= $pass ?> passed</div>
    <div class="badge fail">❌ <?= $fail ?> failed</div>
</div>

<table>
    <thead><tr><th>Status</th><th>Check</th><th>Detail</th></tr></thead>
    <tbody>
    <?php foreach ($results as $r): ?>
        <tr class="<?= $r['ok'] ? 'ok' : 'fail' ?>">
            <td><?= $r['status'] ?></td>
            <td><?= htmlspecialchars($r['label']) ?></td>
            <td class="detail"><?= htmlspecialchars($r['detail']) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<p style="color:#475569;font-size:0.8rem;margin-top:1.5rem;">
    Full log saved to: <?= htmlspecialchars($logFile) ?>
</p>
</body>
</html>
