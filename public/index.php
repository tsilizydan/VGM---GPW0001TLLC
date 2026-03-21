<?php

declare(strict_types=1);

/**
 * Public Front Controller — Vanilla Groupe Madagascar
 *
 * All requests enter here via .htaccess → index.php.
 * BASE_PATH = project root (one level above /public/).
 *
 * Includes FORCE_DEBUG + try/catch so the exact PHP error is revealed
 * on-screen instead of redirecting to the styled 500 view.
 *
 * ⚠️  SET FORCE_DEBUG to false AFTER THE SITE IS WORKING.
 */

// ── FORCE_DEBUG: set to true to see the raw stack trace on 500 ──
define('FORCE_DEBUG', false);

ob_start();

define('BASE_PATH', dirname(__DIR__));

// Force full PHP error display while debugging
ini_set('display_errors',         '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Error logging
$logDir = BASE_PATH . '/storage/logs';
if (!is_dir($logDir)) @mkdir($logDir, 0775, true);
ini_set('log_errors', '1');
ini_set('error_log',  $logDir . '/php_errors.log');

// Catch-all exception handler — shows raw trace
set_exception_handler(function (\Throwable $e): void {
    error_log(sprintf('[Uncaught %s] %s in %s:%d', get_class($e), $e->getMessage(), $e->getFile(), $e->getLine()));
    if (!headers_sent()) http_response_code(500);
    if (ob_get_level()) ob_end_clean();
    echo '<pre style="background:#1e1e1e;color:#f87171;padding:2rem;font-size:.85rem;margin:0;overflow-x:auto">';
    echo '<strong>' . get_class($e) . ': ' . htmlspecialchars($e->getMessage()) . '</strong>' . "\n\n";
    echo 'File: ' . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . "\n\n";
    echo htmlspecialchars($e->getTraceAsString());
    echo '</pre>';
    exit(1);
});

// Bootstrap the application
try {
    require_once BASE_PATH . '/core/Application.php';
    \Core\Application::run();
} catch (\Throwable $e) {
    error_log(sprintf('[Bootstrap] %s: %s in %s:%d', get_class($e), $e->getMessage(), $e->getFile(), $e->getLine()));
    if (!headers_sent()) http_response_code(500);
    if (ob_get_level()) ob_end_clean();
    echo '<pre style="background:#1e1e1e;color:#f87171;padding:2rem;font-size:.85rem;margin:0;overflow-x:auto">';
    echo '<strong>BOOTSTRAP ERROR</strong>' . "\n\n";
    echo '<strong>' . get_class($e) . ': ' . htmlspecialchars($e->getMessage()) . '</strong>' . "\n\n";
    echo 'File: ' . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . "\n\n";
    echo htmlspecialchars($e->getTraceAsString());
    echo '</pre>';
    exit(1);
}

if (ob_get_level() > 0) ob_end_flush();
