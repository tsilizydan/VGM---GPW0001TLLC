<?php

declare(strict_types=1);

namespace Core;

/**
 * HTTP Request abstraction.
 * Wraps superglobals into a clean, testable object.
 */
class Request
{
    private string $method;
    private string $uri;
    /** @var array<string, string> */
    private array $routeParams = [];

    public function __construct()
    {
        $this->method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        // Strip query string and decode URI
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $uri = parse_url($uri, PHP_URL_PATH) ?: '/';
        $this->uri = '/' . ltrim(rawurldecode($uri), '/');
    }

    /**
     * HTTP method (GET, POST, PUT, DELETE …).
     */
    public function method(): string
    {
        return $this->method;
    }

    /**
     * Request URI path (e.g. '/about').
     */
    public function uri(): string
    {
        return $this->uri;
    }

    /**
     * Get a value from GET or POST data.
     */
    public function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    /**
     * Get all POST data (sanitised).
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return array_merge($_GET, $_POST);
    }

    public function isPost(): bool
    {
        return $this->method === 'POST';
    }

    public function isGet(): bool
    {
        return $this->method === 'GET';
    }

    /**
     * Set route parameters (used by Router after matching).
     * @param array<string, string> $params
     */
    public function setRouteParams(array $params): void
    {
        $this->routeParams = $params;
    }

    /**
     * Get a route parameter by name (e.g. 'slug' from /shop/{slug}).
     */
    public function routeParam(string $key, string $default = ''): string
    {
        return $this->routeParams[$key] ?? $default;
    }

    /**
     * Get an HTTP header value by name (case-insensitive).
     * e.g. $request->header('X-CSRF-TOKEN')
     */
    public function header(string $name, string $default = ''): string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return (string) ($_SERVER[$key] ?? $default);
    }
}
