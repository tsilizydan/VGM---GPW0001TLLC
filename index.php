<?php
/**
 * Root Front Controller — Vanilla Groupe Madagascar
 * LiteSpeed / Namecheap shared hosting (doc root = project root)
 *
 * Implements all safety measures:
 * - Output buffering (prevents "headers already sent")
 * - Error visibility controlled by APP_DEBUG env
 * - Full error logging to /storage/logs/php_errors.log
 * - try/catch with friendly fallback page
 * - PHP version guard
 * - Extension pre-flight check
 */

// ── 1. OUTPUT BUFFERING — must be absolute first line ──────────
ob_start();

// ── 2. DEFINE BASE PATH ────────────────────────────────────────
define('BASE_PATH', __DIR__);

// ── 3. LOGGING — set up before anything else can fail ──────────
$logDir  = BASE_PATH . '/storage/logs';
$errLog  = $logDir . '/php_errors.log';

// Ensure log directory exists and is writable
if (!is_dir($logDir)) {
    @mkdir($logDir, 0775, true);
}

ini_set('log_errors',  '1');
ini_set('error_log',   $errLog);

// ── 4. PHP VERSION GUARD ───────────────────────────────────────
if (PHP_VERSION_ID < 80100) {
    http_response_code(500);
    ob_end_clean();
    echo '<!DOCTYPE html><html><body style="font-family:sans-serif;padding:2rem">';
    echo '<h2>⚠️ PHP ' . PHP_VERSION . ' detected</h2>';
    echo '<p>This application requires <strong>PHP 8.1 or higher</strong>.</p>';
    echo '<p>Please upgrade PHP in your hosting control panel (cPanel → MultiPHP Manager).</p>';
    echo '</body></html>';
    exit(1);
}

// ── 5. REQUIRED EXTENSIONS CHECK ──────────────────────────────
$required = ['pdo', 'pdo_mysql', 'mbstring', 'json', 'openssl', 'fileinfo', 'gd'];
$missing  = array_filter($required, fn($e) => !extension_loaded($e));

if ($missing) {
    http_response_code(500);
    error_log('[Bootstrap] Missing PHP extensions: ' . implode(', ', $missing));
    ob_end_clean();
    echo '<!DOCTYPE html><html><body style="font-family:sans-serif;padding:2rem">';
    echo '<h2>⚠️ Missing PHP Extensions</h2>';
    echo '<p>Required: <code>' . implode(', ', $missing) . '</code></p>';
    echo '<p>Enable these in cPanel → PHP Extensions, then reload.</p>';
    echo '</body></html>';
    exit(1);
}

// ── 6. LOAD .ENV EARLY (needed to read APP_DEBUG) ──────────────
// Minimal inline parser so we can read APP_DEBUG before autoloader
$envFile = BASE_PATH . '/.env';
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $k = trim($k);
        $v = trim($v, " \t\n\r\0\x0B\"'");
        if (!array_key_exists($k, $_ENV)) {
            $_ENV[$k] = $v;
            putenv("$k=$v");
        }
    }
}

// ── 7. ERROR DISPLAY — controlled by APP_DEBUG ────────────────
$debug = filter_var($_ENV['APP_DEBUG'] ?? 'false', FILTER_VALIDATE_BOOLEAN);

if ($debug) {
    ini_set('display_errors',         '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors',         '0');
    ini_set('display_startup_errors', '0');
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
}

// ── 8. GLOBAL ERROR/EXCEPTION HANDLERS ────────────────────────
set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) return false;
    error_log(sprintf('[PHP Error %d] %s in %s:%d', $severity, $message, $file, $line));
    return false; // let PHP default handler also run (so display_errors works)
});

set_exception_handler(function (\Throwable $e): void {
    error_log(sprintf(
        '[Uncaught %s] %s in %s:%d | Trace: %s',
        get_class($e), $e->getMessage(), $e->getFile(), $e->getLine(),
        str_replace("\n", ' | ', $e->getTraceAsString())
    ));
    if (!headers_sent()) http_response_code(500);
    ob_end_clean();
    _render_fallback('Uncaught: ' . get_class($e) . ': ' . $e->getMessage(), $e);
    exit(1);
});

// ── 9. FALLBACK PAGE RENDERER ─────────────────────────────────
function _render_fallback(string $brief, ?\Throwable $e = null): void
{
    global $debug;
    $title   = 'Site temporarily unavailable';
    $appName = $_ENV['APP_NAME'] ?? 'Vanilla Groupe Madagascar';
    ?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= htmlspecialchars($appName) ?> — Erreur</title>
    <style>
        body{margin:0;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#0f172a;color:#e2e8f0;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:2rem}
        .box{max-width:520px;text-align:center}
        .icon{font-size:3rem;margin-bottom:1rem}
        h1{font-size:1.4rem;color:#f59e0b;margin-bottom:.5rem}
        p{color:#94a3b8;font-size:.95rem;line-height:1.6}
        .debug{margin-top:1.5rem;background:#1e293b;border-radius:8px;padding:1rem;text-align:left;font-size:.8rem;color:#cbd5e1;overflow-x:auto;white-space:pre-wrap;word-break:break-all}
        a{color:#f59e0b}
    </style>
</head>
<body>
<div class="box">
    <div class="icon">⚙️</div>
    <h1>Site temporairement indisponible</h1>
    <p>Nous travaillons à rétablir le service. Veuillez réessayer dans quelques minutes.<br>
    <a href="/">Réessayer</a></p>
    <?php if ($debug && $e): ?>
    <div class="debug"><strong><?= htmlspecialchars(get_class($e)) ?>:</strong>
<?= htmlspecialchars($e->getMessage()) ?>

in <?= htmlspecialchars($e->getFile()) ?>:<?= $e->getLine() ?>

<?= htmlspecialchars($e->getTraceAsString()) ?></div>
    <?php elseif ($debug): ?>
    <div class="debug"><?= htmlspecialchars($brief) ?></div>
    <?php endif; ?>
</div>
</body></html><?php
}

// ── 10. BOOTSTRAP APPLICATION ──────────────────────────────────
try {
    require_once BASE_PATH . '/core/Application.php';
    \Core\Application::run();
} catch (\Throwable $e) {
    $code   = (int) $e->getCode();
    $isHttp = in_array($code, [400, 403, 404, 405, 419, 422, 429, 500], true);
    $status = ($isHttp && $code >= 400) ? $code : 500;

    error_log(sprintf(
        '[Bootstrap Exception] %s: %s in %s:%d',
        get_class($e), $e->getMessage(), $e->getFile(), $e->getLine()
    ));

    if (!headers_sent()) {
        http_response_code($status);
    }

    ob_end_clean();
    _render_fallback($e->getMessage(), $e);
    exit(1);
}

// ── 11. FLUSH OUTPUT BUFFER ────────────────────────────────────
if (ob_get_level() > 0) {
    ob_end_flush();
}
