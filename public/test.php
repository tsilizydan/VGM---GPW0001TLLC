<?php
/**
 * test.php — Server Health Check
 * Access: https://yourdomain.com/test.php?token=vgmtest2026
 *
 * ⚠️  DELETE THIS FILE AFTER DIAGNOSING.
 * Tests: PHP, extensions, autoloader, DB connection, session, writable dirs.
 */

define('TEST_TOKEN', 'vgmtest2026');
if (($_GET['token'] ?? '') !== TEST_TOKEN) {
    http_response_code(403);
    die('Access denied. Append ?token=vgmtest2026');
}

ob_start();
error_reporting(E_ALL);
ini_set('display_errors', '1');

$results = [];
$t = fn(string $l, bool $ok, string $d = '') => $results[] = ['label'=>$l,'ok'=>$ok,'detail'=>$d];

// ── PHP ─────────────────────────────────────────────────────────
$t('PHP working',               true,                   'PHP ' . PHP_VERSION);
$t('PHP ≥ 8.1',                 PHP_VERSION_ID >= 80100, PHP_VERSION);
$t('OS',                        true,                   PHP_OS . ' ' . php_uname('m'));
$t('Server',                    true,                   $_SERVER['SERVER_SOFTWARE'] ?? 'unknown');
$t('Document root',             true,                   $_SERVER['DOCUMENT_ROOT'] ?? '—');
$t('Script path',               true,                   __FILE__);

// ── Extensions ──────────────────────────────────────────────────
foreach (['pdo','pdo_mysql','mbstring','json','openssl','fileinfo','gd','intl'] as $ext) {
    $t("ext: $ext", extension_loaded($ext));
}

// ── Paths ────────────────────────────────────────────────────────
$root    = dirname(__FILE__); // public/
$base    = dirname($root);    // project root (or same if doc root = /public)
// Detect whether we're in /public or at project root
$isPublicDir = basename($root) === 'public';
$projectRoot = $isPublicDir ? $base : $root;

$t('Detected project root', true, $projectRoot);
$t('index.php at project root',  file_exists($projectRoot . '/index.php'),    $projectRoot . '/index.php');
$t('core/Application.php',       file_exists($projectRoot . '/core/Application.php'));
$t('config/database.php',        file_exists($projectRoot . '/config/database.php'));
$t('.env file',                  file_exists($projectRoot . '/.env'));
$t('public/.htaccess',           file_exists($projectRoot . '/public/.htaccess') || file_exists($root . '/.htaccess'));

// ── Write permissions ────────────────────────────────────────────
foreach (['/storage/cache', '/storage/logs', '/public/assets/img', '/public/assets/cache'] as $dir) {
    $full = $projectRoot . $dir;
    if (!is_dir($full)) {
        @mkdir($full, 0775, true);
    }
    $t("writable: $dir", is_writable($full), $full);
}

// ── .env parse ───────────────────────────────────────────────────
$env = [];
$envFile = $projectRoot . '/.env';
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if (!$line || $line[0] === '#' || !str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $env[trim($k)] = trim($v, " \t\"'");
    }
    $t('.env parsed, keys found', count($env) > 0, count($env) . ' keys');
    $t('APP_URL set', !empty($env['APP_URL']), $env['APP_URL'] ?? '—');
    $t('DB_NAME set', !empty($env['DB_NAME']), $env['DB_NAME'] ?? '—');
    $t('DB_USER set', !empty($env['DB_USER']), $env['DB_USER'] ?? '—');
} else {
    $t('.env file readable', false, 'Missing: ' . $envFile);
}

// ── Autoloader ───────────────────────────────────────────────────
$autoloaderPath = $projectRoot . '/core/Autoloader.php';
if (file_exists($autoloaderPath)) {
    try {
        if (!defined('BASE_PATH')) define('BASE_PATH', $projectRoot);
        require_once $autoloaderPath;
        $loader = new \Core\Autoloader();
        $loader->addNamespace('Core', $projectRoot . '/core');
        $loader->addNamespace('App',  $projectRoot . '/app');
        $loader->register();
        $t('Autoloader registered', true);

        // Try loading a core class
        $t('Core\\Lang loadable', class_exists('Core\\Lang', true));
        $t('Core\\Router loadable', class_exists('Core\\Router', true));
    } catch (\Throwable $e) {
        $t('Autoloader', false, $e->getMessage());
    }
} else {
    $t('Autoloader file exists', false, $autoloaderPath);
}

// ── Database ─────────────────────────────────────────────────────
if (!empty($env['DB_HOST'])) {
    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s',
        $env['DB_HOST'],
        $env['DB_PORT'] ?? 3306,
        $env['DB_NAME'] ?? '',
        $env['DB_CHARSET'] ?? 'utf8mb4'
    );
    try {
        $pdo = new PDO($dsn, $env['DB_USER'] ?? '', $env['DB_PASS'] ?? '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5,
        ]);
        $ver = $pdo->query('SELECT VERSION()')->fetchColumn();
        $t('DB connection', true, 'MySQL ' . $ver);

        // Check tables
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        $t('Tables in DB', count($tables) > 0, count($tables) . ' tables: ' . implode(', ', array_slice($tables, 0, 6)));
    } catch (\Throwable $e) {
        $t('DB connection', false, $e->getMessage());
    }
} else {
    $t('DB config', false, '.env missing DB_HOST');
}

// ── Session ──────────────────────────────────────────────────────
try {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
        $_SESSION['_test'] = time();
    }
    $t('Session writable', isset($_SESSION['_test']), session_save_path());
} catch (\Throwable $e) {
    $t('Session', false, $e->getMessage());
}

// ── Summary ──────────────────────────────────────────────────────
$pass = count(array_filter($results, fn($r) => $r['ok']));
$fail = count(array_filter($results, fn($r) => !$r['ok']));

// ── Log to file ──────────────────────────────────────────────────
$logPath = $projectRoot . '/storage/logs/test_health.log';
$logText = "\n[Health Test " . date('Y-m-d H:i:s') . "]\n";
foreach ($results as $r) {
    $logText .= sprintf("[%s] %s — %s\n", $r['ok']?'PASS':'FAIL', $r['label'], $r['detail']);
}
@file_put_contents($logPath, $logText, FILE_APPEND);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Health Test — Vanilla Groupe Madagascar</title>
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#0f172a;color:#e2e8f0;padding:2rem}
        h1{color:#f59e0b;font-size:1.3rem;margin-bottom:.4rem}
        .meta{color:#94a3b8;font-size:.82rem;margin-bottom:1.2rem}
        .summary{display:flex;gap:.75rem;margin-bottom:1.2rem}
        .badge{padding:.5rem 1rem;border-radius:6px;font-weight:700;font-size:.95rem}
        .pass{background:#14532d;color:#4ade80}.fail{background:#7f1d1d;color:#fca5a5}
        table{width:100%;border-collapse:collapse;font-size:.82rem}
        th{background:#1e293b;color:#94a3b8;text-align:left;padding:.45rem .75rem;font-size:.7rem;text-transform:uppercase;letter-spacing:.05em}
        td{padding:.45rem .75rem;border-bottom:1px solid #1e293b}
        tr.ok td:first-child{color:#4ade80}
        tr.bad{background:rgba(127,29,29,.2)}
        tr.bad td:first-child{color:#f87171}
        .detail{color:#64748b;font-size:.78rem}
        .warn{background:#78350f;color:#fcd34d;padding:.6rem 1rem;border-radius:6px;margin-bottom:1rem;font-size:.82rem}
    </style>
</head>
<body>
<h1>🧪 Server Health Test</h1>
<p class="meta">PHP <?= PHP_VERSION ?> · <?= date('Y-m-d H:i:s') ?> · <?= htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? '') ?></p>
<div class="warn">⚠️ Run <code>rm public/test.php</code> after diagnosing.</div>
<div class="summary">
    <div class="badge pass">✅ <?= $pass ?> passed</div>
    <div class="badge fail">❌ <?= $fail ?> failed</div>
</div>
<table>
    <thead><tr><th>Result</th><th>Check</th><th>Detail</th></tr></thead>
    <tbody>
    <?php foreach ($results as $r): ?>
    <tr class="<?= $r['ok']?'ok':'bad' ?>">
        <td><?= $r['ok']?'✅':'❌' ?></td>
        <td><?= htmlspecialchars($r['label']) ?></td>
        <td class="detail"><?= htmlspecialchars($r['detail']) ?></td>
    </tr>
    <?php endforeach ?>
    </tbody>
</table>
<p style="color:#475569;font-size:.78rem;margin-top:1rem">Log: <?= htmlspecialchars($logPath) ?></p>
</body>
</html>
<?php ob_end_flush(); ?>
