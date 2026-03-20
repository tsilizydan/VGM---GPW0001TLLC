<?php

declare(strict_types=1);

namespace Core;

/**
 * View renderer.
 * Supports an optional layout that wraps view content.
 */
class View
{
    /**
     * Render a view file, optionally inside a layout.
     *
     * @param string               $view   Dot-notation path relative to /app/views/
     *                                     e.g. 'home/index' → /app/views/home/index.php
     * @param array<string, mixed> $data   Variables to extract into view scope
     * @param string|null          $layout Layout file relative to /app/views/layouts/
     *                                     e.g. 'main' → /app/views/layouts/main.php
     *                                     Pass null to render without layout.
     */
    public static function render(
        string $view,
        array $data = [],
        ?string $layout = 'main'
    ): void {
        // Resolve view file path
        $viewPath = view_path(str_replace('.', DIRECTORY_SEPARATOR, $view) . '.php');

        if (!is_file($viewPath)) {
            throw new \RuntimeException("View not found: {$viewPath}");
        }

        // Extract data into local scope
        extract($data, EXTR_SKIP);

        // Capture view output
        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        if ($layout === null) {
            echo $content;
            return;
        }

        // Resolve layout file path
        $layoutPath = view_path('layouts' . DIRECTORY_SEPARATOR . $layout . '.php');

        if (!is_file($layoutPath)) {
            throw new \RuntimeException("Layout not found: {$layoutPath}");
        }

        // Render layout (which uses $content)
        require $layoutPath;
    }
}
