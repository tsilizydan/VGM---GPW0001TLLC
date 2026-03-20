<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Controller;
use Core\Request;
use Core\Model;
use Core\Session;
use Core\Csrf;

/**
 * Admin — Content editor with TinyMCE.
 * Stores pages as key-value pairs in `content_pages` table.
 */
class ContentController extends Controller
{
    private const PAGES = [
        'home_hero'    => 'Accueil — Hero',
        'home_story'   => 'Accueil — Notre histoire',
        'about'        => 'À propos',
        'contact_info' => 'Contact — Informations',
        'footer_text'  => 'Pied de page',
    ];

    public function index(Request $request): void
    {
        $this->requireAuth(); $this->requireRole('admin');

        $page    = $request->input('page', 'home_hero');
        $locale  = $request->input('locale', 'fr');
        if (!array_key_exists($page, self::PAGES)) $page = 'home_hero';

        $existing = $this->loadContent($page, $locale);

        $this->render('admin/content/index', [
            'title'    => 'Éditeur de contenu',
            'pages'    => self::PAGES,
            'page'     => $page,
            'locale'   => $locale,
            'content'  => $existing,
            'locales'  => ['fr' => '🇫🇷 Français', 'en' => '🇬🇧 English', 'es' => '🇪🇸 Español'],
            'headExtra' => '<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>',
        ], 'admin');
    }

    public function update(Request $request): void
    {
        $this->requireAuth(); $this->requireRole('admin');
        if (!Csrf::validate($request->input('_token', ''))) \Core\Response::abort(403);

        $page    = $request->input('page', '');
        $locale  = $request->input('locale', 'fr');
        $html    = $request->input('content_body', '');

        if (!array_key_exists($page, self::PAGES)) \Core\Response::abort(422);

        Model::rawQuery(
            'INSERT INTO content_pages (page_key, locale, body, updated_at)
             VALUES (?, ?, ?, NOW())
             ON DUPLICATE KEY UPDATE body = VALUES(body), updated_at = NOW()',
            [$page, $locale, $html]
        );

        Session::flash('success', 'Contenu enregistré.');
        header('Location: ' . locale_url("admin/content?page={$page}&locale={$locale}")); exit;
    }

    /** Public static helper — used in views to render content. */
    public static function render(string $pageKey, string $locale = 'fr'): string
    {
        try {
            $row = Model::rawQuery(
                'SELECT body FROM content_pages WHERE page_key = ? AND locale = ? LIMIT 1',
                [$pageKey, $locale]
            )[0] ?? null;
            return $row['body'] ?? '';
        } catch (\Throwable) {
            return '';
        }
    }

    private function loadContent(string $page, string $locale): string
    {
        try {
            $row = Model::rawQuery(
                'SELECT body FROM content_pages WHERE page_key = ? AND locale = ? LIMIT 1',
                [$page, $locale]
            )[0] ?? null;
            return $row['body'] ?? '';
        } catch (\Throwable) {
            return '';
        }
    }
}
