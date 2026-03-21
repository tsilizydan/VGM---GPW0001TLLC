<?php
/**
 * RESCUE-DEBUG.PHP — Emergency 500 Diagnostic
 * =============================================
 * Upload to project root: /home/tsilscpx/vanillagroup-madagascar.tsilizy.com/rescue-debug.php
 * Access: https://vanillagroup-madagascar.tsilizy.com/rescue-debug.php
 *
 * ⚠️  This file is SELF-CONTAINED — does NOT require any project files (autoloader, .env, etc.)
 * ⚠️  DELETE THIS FILE AFTER USE.
 */

// === FORCE ALL ERRORS VISIBLE ===
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

ob_start();

// === Helpers ===
function ok(string $label, string $detail = ''): void { echo "<tr><td style='color:#4ade80'>✅</td><td>$label</td><td style='color:#64748b;font-size:.8rem'>$detail</td></tr>"; }
function fail(string $label, string $detail = ''): void { echo "<tr style='background:rgba(127,29,29,.2)'><td style='color:#f87171'>❌</td><td>$label</td><td style='color:#fca5a5;font-size:.8rem'>$detail</td></tr>"; }
function warn(string $label, string $detail = ''): void { echo "<tr style='background:rgba(120,53,15,.2)'><td style='color:#fcd34d'>⚠️</td><td>$label</td><td style='color:#fcd34d;font-size:.8rem'>$detail</td></tr>"; }

// === Page header ===
echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Rescue Debug</title></head>';
echo '<body style="font-family:-apple-system,sans-serif;background:#0f172a;color:#e2e8f0;padding:1.5rem;margin:0">';
echo '<h1 style="color:#f59e0b;font-size:1.3rem;margin-bottom:.3rem">🚨 Rescue Debug — Vanilla Groupe Madagascar</h1>';
echo '<p style="color:#94a3b8;font-size:.8rem;margin-bottom:1rem">PHP ' . PHP_VERSION . ' · ' . ($_SERVER['SERVER_SOFTWARE'] ?? 'unknown') . ' · ' . date('Y-m-d H:i:s') . '</p>';
echo '<table style="width:100%;border-collapse:collapse;font-size:.85rem"><thead><tr style="text-align:left"><th style="padding:.4rem .6rem;background:#1e293b;color:#94a3b8;font-size:.7rem;text-transform:uppercase">St</th><th style="padding:.4rem .6rem;background:#1e293b;color:#94a3b8;font-size:.7rem">Check</th><th style="padding:.4rem .6rem;background:#1e293b;color:#94a3b8;font-size:.7rem">Detail</th></tr></thead><tbody>';

// ========================================================
// 1. PHP version
// ========================================================
PHP_VERSION_ID >= 80100
    ? ok('PHP ≥ 8.1', PHP_VERSION)
    : fail('PHP < 8.1 — UPGRADE REQUIRED', PHP_VERSION . ' — Set PHP 8.1+ in cPanel → MultiPHP Manager');

// ========================================================
// 2. Document root + project structure
// ========================================================
$docRoot = $_SERVER['DOCUMENT_ROOT'] ?? realpath('.');
ok('Document root', $docRoot);
ok('Script location', __FILE__);

// Detect project root
$projectRoot = __DIR__;
if (basename($projectRoot) === 'public') $projectRoot = dirname($projectRoot);

// Check critical files
$criticalFiles = [
    'index.php'              => $projectRoot . '/index.php',
    'public/index.php'       => $projectRoot . '/public/index.php',
    '.env'                   => $projectRoot . '/.env',
    '.htaccess (root)'       => $projectRoot . '/.htaccess',
    'public/.htaccess'       => $projectRoot . '/public/.htaccess',
    'core/Application.php'   => $projectRoot . '/core/Application.php',
    'core/Autoloader.php'    => $projectRoot . '/core/Autoloader.php',
    'core/helpers.php'       => $projectRoot . '/core/helpers.php',
    'core/Session.php'       => $projectRoot . '/core/Session.php',
    'core/Router.php'        => $projectRoot . '/core/Router.php',
    'core/RouteGuard.php'    => $projectRoot . '/core/RouteGuard.php',
    'routes/web.php'         => $projectRoot . '/routes/web.php',
    'config/database.php'    => $projectRoot . '/config/database.php',
];

foreach ($criticalFiles as $name => $path) {
    is_file($path)
        ? ok($name, substr($path, -60))
        : fail("MISSING: $name", $path);
}

// ========================================================
// 3. File permissions
// ========================================================
$dirs = ['public' => $projectRoot.'/public', 'core' => $projectRoot.'/core', 'storage' => $projectRoot.'/storage', 'storage/logs' => $projectRoot.'/storage/logs', 'storage/cache' => $projectRoot.'/storage/cache'];
foreach ($dirs as $name => $path) {
    if (!is_dir($path)) { fail("DIR MISSING: $name", $path); continue; }
    $p = decoct(fileperms($path) & 0777);
    $ok = (int)$p >= 755;
    $ok ? ok("Dir $name", "perms: $p") : warn("Dir $name perms: $p (want ≥755)", $path);
}

// ========================================================
// 4. PHP extensions
// ========================================================
foreach (['pdo', 'pdo_mysql', 'mbstring', 'json', 'openssl', 'fileinfo', 'gd'] as $ext) {
    extension_loaded($ext) ? ok("ext: $ext") : fail("MISSING ext: $ext", 'Enable in cPanel → PHP Extensions');
}

// ========================================================
// 5. .env file parse test
// ========================================================
$envPath = $projectRoot . '/.env';
$envVars = [];
if (is_file($envPath)) {
    foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if (!$line || $line[0] === '#' || !str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $envVars[trim($k)] = trim($v, " \t\"'");
    }
    ok('.env parsed', count($envVars) . ' keys');
    
    // Check critical values
    $appUrl = $envVars['APP_URL'] ?? '';
    if (str_starts_with($appUrl, 'https://')) {
        ok('APP_URL is HTTPS', $appUrl);
    } elseif ($appUrl) {
        fail('APP_URL is NOT HTTPS', "$appUrl — must start with https://");
    } else {
        fail('APP_URL missing', 'Add APP_URL=https://vanillagroup-madagascar.tsilizy.com');
    }
    
    !empty($envVars['DB_HOST'])   ? ok('DB_HOST set', $envVars['DB_HOST']) : fail('DB_HOST missing');
    !empty($envVars['DB_NAME'])   ? ok('DB_NAME set', $envVars['DB_NAME']) : fail('DB_NAME missing');
    !empty($envVars['DB_USER'])   ? ok('DB_USER set', $envVars['DB_USER']) : fail('DB_USER missing');
    !empty($envVars['DB_PASS'])   ? ok('DB_PASS set', '***' . substr($envVars['DB_PASS'] ?? '', -3)) : warn('DB_PASS empty');
} else {
    fail('.env FILE MISSING', $envPath);
}

// ========================================================
// 6. Database connection test
// ========================================================
if (!empty($envVars['DB_HOST']) && !empty($envVars['DB_NAME'])) {
    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s',
        $envVars['DB_HOST'], $envVars['DB_PORT'] ?? '3306', $envVars['DB_NAME'], $envVars['DB_CHARSET'] ?? 'utf8mb4');
    try {
        $pdo = new PDO($dsn, $envVars['DB_USER'] ?? '', $envVars['DB_PASS'] ?? '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 5]);
        $ver = $pdo->query('SELECT VERSION()')->fetchColumn();
        ok('DB connection', "MySQL $ver");
        
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        ok('Tables', count($tables) . ': ' . implode(', ', array_slice($tables, 0, 8)));
        
        // Check translations table (used by Lang on every page)
        $has = in_array('translations', $tables);
        $has ? ok('translations table exists') : warn('translations table MISSING — Lang::loadLocale() will silently fail');
    } catch (Throwable $e) {
        fail('DB connection FAILED', htmlspecialchars($e->getMessage()));
    }
} else {
    warn('DB test skipped', '.env DB vars incomplete');
}

// ========================================================
// 7. Session test
// ========================================================
try {
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }
    $_SESSION['_rescue_test'] = time();
    ok('Session writable', 'save_path: ' . session_save_path());
} catch (Throwable $e) {
    fail('Session FAILED', htmlspecialchars($e->getMessage()));
}

// ========================================================
// 8. .htaccess syntax check (basic)
// ========================================================
$htPaths = [$projectRoot . '/.htaccess', $projectRoot . '/public/.htaccess'];
foreach ($htPaths as $htPath) {
    if (!is_file($htPath)) { warn('.htaccess not found', $htPath); continue; }
    $ht = file_get_contents($htPath);
    $bn = basename(dirname($htPath)) . '/.htaccess';
    
    if (preg_match('/^\s*Require all denied\s*$/m', $ht) && !preg_match('/<FilesMatch/i', $ht)) {
        fail("$bn: bare 'Require all denied'", 'This blocks ALL requests → 403');
    } else {
        ok("$bn: no bare Require all denied");
    }
    
    if (str_contains($ht, 'RewriteEngine On')) {
        ok("$bn: RewriteEngine On found");
    } else {
        warn("$bn: no RewriteEngine On");
    }
}

// ========================================================
// 9. ACTUAL BOOTSTRAP TEST — the real test
// ========================================================
echo '</tbody></table>';
echo '<h2 style="color:#f59e0b;font-size:1.1rem;margin:1.5rem 0 .5rem">🧪 Bootstrap Test (the real one)</h2>';
echo '<div style="background:#1e293b;border-radius:8px;padding:1rem;font-size:.82rem;color:#cbd5e1;overflow-x:auto;white-space:pre-wrap">';

define('BASE_PATH', $projectRoot);

$steps = [
    'Load helpers.php' => function() {
        $f = BASE_PATH . '/core/helpers.php';
        if (!is_file($f)) throw new RuntimeException("Missing: $f");
        require_once $f;
        return 'env() function available: ' . (function_exists('env') ? 'YES' : 'NO');
    },
    'Load Autoloader' => function() {
        $f = BASE_PATH . '/core/Autoloader.php';
        if (!is_file($f)) throw new RuntimeException("Missing: $f");
        require_once $f;
        $loader = new \Core\Autoloader();
        $loader->addNamespace('Core', BASE_PATH . '/core');
        $loader->addNamespace('App',  BASE_PATH . '/app');
        $loader->register();
        return 'Autoloader registered';
    },
    'Load Session class' => function() {
        return class_exists(\Core\Session::class) ? 'Core\\Session loaded' : 'FAILED to load';
    },
    'Load Cache class' => function() {
        return class_exists(\Core\Cache::class) ? 'Core\\Cache loaded' : 'FAILED to load';
    },
    'Load Assets class' => function() {
        return class_exists(\Core\Assets::class) ? 'Core\\Assets loaded' : 'FAILED to load';
    },
    'Load Lang class' => function() {
        return class_exists(\Core\Lang::class) ? 'Core\\Lang loaded' : 'FAILED to load';
    },
    'Load RouteGuard class' => function() {
        return class_exists(\Core\RouteGuard::class) ? 'Core\\RouteGuard loaded' : 'FAILED to load';
    },
    'Load Router class' => function() {
        return class_exists(\Core\Router::class) ? 'Core\\Router loaded' : 'FAILED to load';
    },
    'Cache::init()' => function() {
        \Core\Cache::init(BASE_PATH . '/storage/cache');
        return 'OK — cache dir: ' . BASE_PATH . '/storage/cache';
    },
    'Session::start()' => function() {
        \Core\Session::start();
        return 'OK — session active';
    },
    'Load HomeController' => function() {
        return class_exists(\App\Controllers\HomeController::class) ? 'OK' : 'FAILED to load';
    },
    'View file exists' => function() {
        $vp = BASE_PATH . '/app/views/home/index.php';
        return is_file($vp) ? "OK — $vp" : "MISSING — $vp";
    },
    'Layout file exists' => function() {
        $lp = BASE_PATH . '/app/views/layouts/main.php';
        return is_file($lp) ? "OK — $lp" : "MISSING — $lp";
    },
    'Routes file loadable' => function() {
        $router = new \Core\Router();
        require BASE_PATH . '/routes/web.php';
        return 'OK — routes loaded';
    },
];

foreach ($steps as $name => $fn) {
    try {
        $result = $fn();
        echo "<span style='color:#4ade80'>✅ $name</span> — $result\n";
    } catch (\Throwable $e) {
        echo "<span style='color:#f87171'>❌ $name</span> — <strong>" . get_class($e) . ": " . htmlspecialchars($e->getMessage()) . "</strong>\n";
        echo "   File: " . htmlspecialchars($e->getFile()) . ":" . $e->getLine() . "\n";
        echo "   Trace: " . htmlspecialchars(substr($e->getTraceAsString(), 0, 500)) . "\n";
        echo "\n<span style='color:#fcd34d'>⚠️ STOPPED — fix the above error first.</span>\n";
        break;
    }
}

echo '</div>';

// ========================================================
// 10. Recent error logs
// ========================================================
echo '<h2 style="color:#f59e0b;font-size:1.1rem;margin:1.5rem 0 .5rem">📋 Recent Error Logs</h2>';
$logFiles = [
    $projectRoot . '/storage/logs/error.log',
    $projectRoot . '/storage/logs/php_errors.log',
    '/home/tsilscpx/vanillagroup-madagascar.tsilizy.com/storage/logs/error.log'
];

foreach ($logFiles as $logFile) {
    $bn = basename($logFile);
    if (!is_file($logFile)) { echo "<p style='color:#64748b;font-size:.8rem'>$bn — not found</p>"; continue; }
    $lines = array_slice(file($logFile), -20); // last 20 lines
    if (empty($lines)) { echo "<p style='color:#64748b;font-size:.8rem'>$bn — empty</p>"; continue; }
    echo "<details><summary style='color:#94a3b8;font-size:.85rem;cursor:pointer;margin-bottom:.3rem'>📄 $bn (" . count($lines) . " last lines)</summary>";
    echo '<pre style="background:#1e293b;border-radius:6px;padding:.8rem;font-size:.75rem;color:#cbd5e1;overflow-x:auto;max-height:300px">';
    echo htmlspecialchars(implode('', $lines));
    echo '</pre></details>';
}

echo '<p style="color:#f87171;font-size:.82rem;margin-top:2rem">⚠️ DELETE THIS FILE after diagnosing: <code>rm rescue-debug.php</code></p>';
echo '</body></html>';
ob_end_flush();
