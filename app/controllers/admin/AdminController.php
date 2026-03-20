<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Controller;
use Core\Middleware;
use Core\Session;

/**
 * Base controller for all admin area controllers.
 *
 * Every admin controller MUST extend this class.
 * The constructor enforces authentication + admin role on every request.
 */
abstract class AdminController extends Controller
{
    public function __construct()
    {
        // Enforce admin authentication on every admin action
        Middleware::requireAdmin();
    }

    // ── Admin helpers ─────────────────────────────────────────────

    /**
     * Redirect back to referrer with a flash success/error message.
     */
    protected function flashRedirect(string $url, string $message, string $type = 'success'): never
    {
        Session::flash($type, $message);
        \Core\Response::redirect($url);
    }

    /**
     * Validate input and redirect back with errors on failure.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, string> $rules
     * @return array<string, mixed>  The validated (safe) data
     */
    protected function validateOrRedirect(array $data, array $rules, string $backUrl = ''): array
    {
        $v = \Core\Validator::make($data, $rules);

        if ($v->fails()) {
            Session::flash('_errors', $v->errors());
            Session::flash('_old_input', $data);
            $back = $backUrl ?: ($_SERVER['HTTP_REFERER'] ?? locale_url('admin'));
            \Core\Response::redirect($back);
        }

        return $v->validated();
    }

    /**
     * Get validation errors from the session (set by validateOrRedirect).
     *
     * @return array<string, string>
     */
    protected function getErrors(): array
    {
        return Session::getFlash('_errors') ?? [];
    }
}
