<?php

declare(strict_types=1);

/**
 * Front Controller — the ONLY publicly accessible PHP file.
 *
 * All requests are routed through here via /public/.htaccess.
 * BASE_PATH points to the project root (one level above /public/).
 */

define('BASE_PATH', dirname(__DIR__));

// Bootstrap the application
require_once BASE_PATH . '/core/Application.php';

use Core\Application;

Application::run();
