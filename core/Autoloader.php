<?php

declare(strict_types=1);

namespace Core;

/**
 * PSR-4 compatible autoloader.
 * Maps namespace prefixes to base directories.
 * Works without Composer — ideal for shared hosting (Namecheap).
 *
 * On Linux (Namecheap), file paths are CASE SENSITIVE.
 * The loadClass method tries both the exact path and a lowercase-directory
 * variant to handle the common App\Controllers → app/controllers mismatch.
 */
class Autoloader
{
    /** @var array<string, string> namespace prefix → directory */
    private array $prefixes = [];

    /**
     * Register this autoloader with SPL.
     */
    public function register(): void
    {
        spl_autoload_register([$this, 'loadClass']);
    }

    /**
     * Add a PSR-4 namespace → directory mapping.
     */
    public function addNamespace(string $prefix, string $baseDir): void
    {
        $prefix  = rtrim($prefix, '\\') . '\\';
        $baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        $this->prefixes[$prefix] = $baseDir;
    }

    /**
     * Load the class file for a fully-qualified class name.
     *
     * Tries two resolution strategies:
     *  1. Exact PSR-4: App\Controllers\HomeController → app/Controllers/HomeController.php
     *  2. Lowercase dirs: App\Controllers\HomeController → app/controllers/HomeController.php
     *
     * This handles the common case where directory names are lowercase on Linux
     * but namespace segments are PascalCase.
     */
    public function loadClass(string $class): bool
    {
        foreach ($this->prefixes as $prefix => $baseDir) {
            if (!str_starts_with($class, $prefix)) {
                continue;
            }

            $relativeClass = substr($class, strlen($prefix));
            $file = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';

            // 1. Try exact PSR-4 path (standard)
            if (is_file($file)) {
                require $file;
                return true;
            }

            // 2. Try lowercase directory names (keep filename as-is)
            //    App\Controllers\Admin\ProductController → app/controllers/admin/ProductController.php
            $parts    = explode(DIRECTORY_SEPARATOR, str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass));
            $filename = array_pop($parts); // e.g. "ProductController"
            $dirParts = array_map('strtolower', $parts); // ["controllers", "admin"]
            $altFile  = $baseDir . implode(DIRECTORY_SEPARATOR, $dirParts) . DIRECTORY_SEPARATOR . $filename . '.php';

            if ($altFile !== $file && is_file($altFile)) {
                require $altFile;
                return true;
            }
        }

        return false;
    }
}
