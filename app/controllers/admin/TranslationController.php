<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Controller;
use Core\Request;
use Core\Lang;
use App\Models\Translation;

/**
 * TranslationController — admin CRUD for the translations table.
 *
 * Routes (admin-protected):
 *   GET  /admin/translations          → index()
 *   POST /admin/translations/update   → update()
 */
class TranslationController extends Controller
{
    public function index(Request $request): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        $pivot  = Translation::pivot(); // key → [fr=>, en=>, es=>]
        $search = trim($request->input('q', ''));

        if ($search !== '') {
            $pivot = array_filter(
                $pivot,
                fn ($key) => str_contains(strtolower($key), strtolower($search)),
                ARRAY_FILTER_USE_KEY
            );
        }

        $this->render('admin/translations/index', [
            'title'  => t('admin.translations'),
            'pivot'  => $pivot,
            'search' => $search,
        ], 'app');
    }

    /**
     * AJAX / form POST: update a single key/locale pair.
     *
     * Expects: locale, key, value
     *
     * Returns JSON: { success: true } or { success: false, message: '...' }
     */
    public function update(Request $request): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        // CSRF check
        if (!$this->validateCsrf($request)) {
            $this->jsonError('Invalid CSRF token.', 403);
        }

        $locale = $request->input('locale', '');
        $key    = trim($request->input('key', ''));
        $value  = $request->input('value', '');

        if (!Lang::isSupported($locale)) {
            $this->jsonError('Unsupported locale: ' . $locale);
        }

        if ($key === '') {
            $this->jsonError('Key cannot be empty.');
        }

        Translation::upsert($locale, $key, $value);
        Lang::clearCache($locale); // Invalidate in-memory cache

        $this->json(['success' => true, 'message' => t('admin.saved')]);
    }

    // -----------------------------------------------------------------------
    // Private helpers
    // -----------------------------------------------------------------------

    private function json(array $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    private function jsonError(string $message, int $status = 422): never
    {
        $this->json(['success' => false, 'message' => $message], $status);
    }

    private function validateCsrf(Request $request): bool
    {
        $token = $request->input('_token', '');
        return \Core\Csrf::validate($token);
    }
}
