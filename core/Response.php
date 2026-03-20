<?php

declare(strict_types=1);

namespace Core;

/**
 * HTTP Response helpers.
 */
class Response
{
    private int $statusCode = 200;

    public function setStatus(int $code): static
    {
        $this->statusCode = $code;
        http_response_code($code);
        return $this;
    }

    public function getStatus(): int
    {
        return $this->statusCode;
    }

    /**
     * Redirect to another URL and exit.
     */
    public static function redirect(string $url, int $status = 302): never
    {
        http_response_code($status);
        header('Location: ' . $url);
        exit;
    }

    /**
     * Send a JSON response and exit.
     *
     * @param array<string, mixed> $data
     */
    public static function json(array $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Render a styled error page and exit.
     */
    public static function abort(int $code = 404, string $message = ''): never
    {
        http_response_code($code);

        $viewFile = defined('BASE_PATH')
            ? BASE_PATH . '/app/views/errors/' . $code . '.php'
            : null;

        if ($viewFile && is_file($viewFile)) {
            // Override the message if supplied
            if ($message !== '') {
                $GLOBALS['__error_message_override__'] = $message;
            }
            require $viewFile;
            exit;
        }

        // Fallback if views aren't available yet (early bootstrap errors)
        header('Content-Type: text/html; charset=UTF-8');
        echo "<h1>{$code}</h1><p>" . htmlspecialchars($message ?: 'Error', ENT_QUOTES, 'UTF-8') . "</p>";
        exit;
    }
}
