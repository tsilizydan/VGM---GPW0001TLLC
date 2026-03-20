<?php

declare(strict_types=1);

namespace Core;

/**
 * Base Controller.
 * All application controllers should extend this class.
 */
abstract class Controller
{
    // -----------------------------------------------------------------------
    // Rendering
    // -----------------------------------------------------------------------

    /**
     * Render a view with optional data, inside the default layout.
     *
     * @param string               $view   e.g. 'home/index'
     * @param array<string, mixed> $data   Data available inside the view
     * @param string|null          $layout Layout name, null for no layout
     */
    protected function render(string $view, array $data = [], ?string $layout = 'main'): void
    {
        View::render($view, $data, $layout);
    }

    // -----------------------------------------------------------------------
    // Responses
    // -----------------------------------------------------------------------

    protected function redirect(string $url, int $status = 302): never
    {
        Response::redirect($url, $status);
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function json(array $data, int $status = 200): never
    {
        Response::json($data, $status);
    }

    // -----------------------------------------------------------------------
    // Database
    // -----------------------------------------------------------------------

    protected function db(): \PDO
    {
        return Model::getConnection();
    }

    // -----------------------------------------------------------------------
    // Auth guards — call at the top of any protected controller method
    // -----------------------------------------------------------------------

    /**
     * Require the user to be authenticated.
     * Redirects to /login with a flash message if not.
     */
    protected function requireAuth(): void
    {
        if (Auth::guest()) {
            Session::flash('error', 'You must be logged in to access this page.');
            Response::redirect(url('login'));
        }
    }

    /**
     * Require the visitor to be a guest (not authenticated).
     * Redirects to /dashboard if already logged in.
     */
    protected function requireGuest(): void
    {
        if (Auth::check()) {
            Response::redirect(url('dashboard'));
        }
    }

    /**
     * Require the authenticated user to have a specific role.
     * Aborts with 403 if the role doesn't match.
     */
    protected function requireRole(string $role): void
    {
        $this->requireAuth();

        if (!Auth::hasRole($role)) {
            Response::abort(403, 'Forbidden – insufficient permissions.');
        }
    }
}
