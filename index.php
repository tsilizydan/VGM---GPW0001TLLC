<?php
/**
 * Root Front Controller — Vanilla Groupe Madagascar
 * LiteSpeed / Namecheap shared hosting (doc root = project root)
 *
 * ╔══════════════════════════════════════════════╗
 * ║  FORCE_DEBUG = true                          ║
 * ║  REMOVE after diagnosing the 500 error       ║
 * ╚══════════════════════════════════════════════╝
 */

// ── 1. OUTPUT BUFFERING — must be first ─────────────────────────
ob_start();

// ── 2. BASE PATH ─────────────────────────────────────────────────
define('BASE_PATH', __DIR__);

// ── 3. FORCE FULL ERROR DISPLAY (temporary) ──────────────────────
ini_set('display_errors',         '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// ── 4. LOGGING ───────────────────────────────────────────────────
$logDir = BASE_PATH . '/storage/logs';
if (!is_dir($logDir)) @mkdir($logDir, 0775, true);
ini_set('log_errors', '1');
ini_set('error_log',  $logDir . '/php_errors.log');

// ── 5. PHP VERSION GUARD ──────────────────────────────────────────
if (PHP_VERSION_ID < 80100) {
    ob_end_clean();
    die('<h2>PHP ' . PHP_VERSION . ' too old. Requires ≥ 8.1. Set PHP to 8.1+ in cPanel → MultiPHP Manager.</h2>');
}

// ── 6. EXTENSION CHECK ────────────────────────────────────────────
$missing = array_filter(['pdo','pdo_mysql','mbstring','json','openssl','fileinfo'], fn($e) => !extension_loaded($e));
if ($missing) {
    ob_end_clean();
    die('<h2>Missing PHP extensions: ' . implode(', ', $missing) . '</h2>');
}

// ── 7. INLINE .env PARSER ────────────────────────────────────────
$envFile = BASE_PATH . '/.env';
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if (!$line || $line[0] === '#' || !str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $k = trim($k); $v = trim($v, " \t\"'");
        if (!array_key_exists($k, $_ENV)) { $_ENV[$k] = $v; putenv("$k=$v"); }
    }
}

// ── 8. ERROR/EXCEPTION HANDLERS ──────────────────────────────────
set_error_handler(function (int $sev, string $msg, string $file, int $line): bool {
    error_log(sprintf('[PHP Error %d] %s in %s:%d', $sev, $msg, $file, $line));
    return false;
});

set_exception_handler(function (\Throwable $e): void {
    error_log(sprintf('[Uncaught %s] %s in %s:%d', get_class($e), $e->getMessage(), $e->getFile(), $e->getLine()));
    if (!headers_sent()) http_response_code(500);
    ob_end_clean();
    echo '<pre style="background:#1e1e1e;color:#f87171;padding:2rem;font-size:.85rem;margin:0">';
    echo '<strong>' . get_class($e) . ': ' . htmlspecialchars($e->getMessage()) . '</strong>' . "\n\n";
    echo 'File: ' . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . "\n\n";
    echo htmlspecialchars($e->getTraceAsString());
    echo '</pre>';
    exit(1);
});

// ── 9. BOOTSTRAP ─────────────────────────────────────────────────
try {
    require_once BASE_PATH . '/core/Application.php';
    \Core\Application::run();
} catch (\Throwable $e) {
    error_log(sprintf('[Bootstrap] %s: %s in %s:%d', get_class($e), $e->getMessage(), $e->getFile(), $e->getLine()));
    if (!headers_sent()) http_response_code(500);
    ob_end_clean();
    echo '<pre style="background:#1e1e1e;color:#f87171;padding:2rem;font-size:.85rem;margin:0">';
    echo '<strong>BOOTSTRAP ERROR</strong>' . "\n\n";
    echo '<strong>' . get_class($e) . ': ' . htmlspecialchars($e->getMessage()) . '</strong>' . "\n\n";
    echo 'File: ' . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . "\n\n";
    echo htmlspecialchars($e->getTraceAsString());
    echo '</pre>';
    exit(1);
}

// ── 10. FLUSH ────────────────────────────────────────────────────
if (ob_get_level() > 0) ob_end_flush();
