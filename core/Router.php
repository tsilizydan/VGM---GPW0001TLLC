<?php

declare(strict_types=1);

namespace Core;

/**
 * Router — registers GET/POST routes and dispatches them.
 * Supports static routes and simple {param} placeholders.
 * Example: 'ShopController@show' for GET /shop/{slug}
 */
class Router
{
    /** @var array<string, list<array{pattern: string, regex: string, params: list<string>, action: string}>> */
    private array $routes = [];

    // -----------------------------------------------------------------------
    // Route registration
    // -----------------------------------------------------------------------

    public function get(string $uri, string $action): void
    {
        $this->addRoute('GET', $uri, $action);
    }

    public function post(string $uri, string $action): void
    {
        $this->addRoute('POST', $uri, $action);
    }

    private function addRoute(string $method, string $uri, string $action): void
    {
        $uri    = '/' . ltrim($uri, '/');
        $params = [];

        // Convert {param} placeholders → named regex groups
        $regex = preg_replace_callback('/\{([a-z_]+)\}/', function ($m) use (&$params) {
            $params[] = $m[1];
            return '([^/]+)';
        }, $uri);

        $this->routes[$method][] = [
            'pattern' => $uri,
            'regex'   => '#^' . $regex . '$#',
            'params'  => $params,
            'action'  => $action,
        ];
    }

    // -----------------------------------------------------------------------
    // Dispatch
    // -----------------------------------------------------------------------

    public function dispatch(Request $request): void
    {
        $method = $request->method();
        $uri    = $request->uri();

        // ── Global middleware ──────────────────────────────────────────────

        // 1. CSRF: validate token on all state-changing requests
        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            Middleware::requireCsrf($request);
        }

        // 2. Admin guard: any /admin/* URI requires authentication + admin role
        if (str_starts_with($uri, '/admin/') || $uri === '/admin') {
            Middleware::requireAdmin();
        }

        // ── Route matching ─────────────────────────────────────────────────

        foreach (($this->routes[$method] ?? []) as $route) {
            if (!preg_match($route['regex'], $uri, $matches)) {
                continue;
            }

            // Attach route parameters to the request
            array_shift($matches); // remove full match
            $routeParams = array_combine($route['params'], $matches) ?: [];
            $request->setRouteParams($routeParams);

            [$controllerName, $methodName] = explode('@', $route['action'], 2);
            $class = 'App\\Controllers\\' . $controllerName;

            if (!class_exists($class)) {
                Response::abort(500, "Controller {$class} not found.");
            }

            $controller = new $class();

            if (!method_exists($controller, $methodName)) {
                Response::abort(500, "Method {$methodName} not found in {$class}.");
            }

            $controller->{$methodName}($request);
            return;
        }

        RouteGuard::handleInvalidRoute();
    }
}
