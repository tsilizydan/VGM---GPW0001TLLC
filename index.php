<?php

declare(strict_types=1);

/**
 * Root Front Controller — for Namecheap / LiteSpeed shared hosting
 * where the document root is the PROJECT ROOT (not /public/).
 *
 * When Namecheap's domain points to /home/user/project/ instead of
 * /home/user/project/public/, this file takes over as the entry point.
 *
 * It sets BASE_PATH to the project root (__DIR__) and then delegates
 * entirely to the core application bootstrap.
 *
 * If your domain IS correctly pointed to /public/, this file is never
 * reached and public/index.php handles everything — that's fine too.
 */

define('BASE_PATH', __DIR__);

// Bootstrap the application
require_once BASE_PATH . '/core/Application.php';

use Core\Application;

Application::run();
