<?php

declare(strict_types=1);

namespace Core;

/**
 * PSR-4 compatible autoloader.
 * Maps namespace prefixes to base directories.
 * Works without Composer – ideal for shared hosting (Namecheap).
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
     *
     * @param string $prefix    Namespace prefix (e.g. 'App\\')
     * @param string $baseDir   Absolute directory path (e.g. BASE_PATH . '/app')
     */
    public function addNamespace(string $prefix, string $baseDir): void
    {
        // Normalise: trailing namespace separator and directory separator
        $prefix  = rtrim($prefix, '\\') . '\\';
        $baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        $this->prefixes[$prefix] = $baseDir;
    }

    /**
     * Load the class file for a fully-qualified class name.
     */
    public function loadClass(string $class): bool
    {
        foreach ($this->prefixes as $prefix => $baseDir) {
            if (!str_starts_with($class, $prefix)) {
                continue;
            }

            // Strip prefix, convert namespace separators to directory separators
            $relativeClass = substr($class, strlen($prefix));
            $file = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';

            if (is_file($file)) {
                require $file;
                return true;
            }
        }

        return false;
    }
}
