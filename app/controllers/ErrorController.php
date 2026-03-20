<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Request;

/**
 * ErrorController — handles user-facing error pages with clean URLs.
 *
 * Route:
 *   GET /{locale}/not-found   → notFound()  (404)
 *
 * RouteGuard::handleInvalidRoute() redirects all unmatched routes here,
 * giving users a friendly URL like /fr/not-found instead of an inline abort.
 */
class ErrorController extends Controller
{
    /**
     * Render the 404 "Page not found" view with a proper HTTP status.
     */
    public function notFound(Request $request): void
    {
        http_response_code(404);

        $this->render('errors/not_found', [
            'title'   => '404 — Page introuvable',
            'backUrl' => $_SERVER['HTTP_REFERER'] ?? locale_url('/'),
        ], 'errors');
    }
}
