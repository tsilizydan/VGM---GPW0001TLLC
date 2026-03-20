<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Controller;
use Core\Request;
use Core\Csrf;
use App\Models\Category;

/**
 * Admin — Category CRUD.
 */
class CategoryController extends Controller
{
    public function index(Request $request): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        $this->render('admin/categories/index', [
            'title'      => 'Catégories',
            'categories' => Category::all(),
        ], 'app');
    }

    public function create(Request $request): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        $this->render('admin/categories/form', [
            'title'    => 'Nouvelle catégorie',
            'category' => null,
        ], 'app');
    }

    public function store(Request $request): void
    {
        $this->requireAuth();
        $this->requireRole('admin');
        $this->checkCsrf($request);

        $slug = trim($request->input('slug', ''));
        if ($slug === '') {
            $slug = Category::slugify($request->input('fr_name', ''));
        }

        $categoryId = Category::create([
            'slug'       => $slug,
            'sort_order' => (int) $request->input('sort_order', 0),
        ]);

        foreach (['fr', 'en', 'es'] as $locale) {
            $name = trim($request->input("{$locale}_name", ''));
            if ($name !== '') {
                Category::saveTranslation(
                    $categoryId,
                    $locale,
                    $name,
                    trim($request->input("{$locale}_description", ''))
                );
            }
        }

        $this->redirect(locale_url('admin/categories'), 'Catégorie créée.');
    }

    public function edit(Request $request): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        $id       = (int) $request->routeParam('id');
        $category = Category::find($id);
        if (!$category) \Core\Response::abort(404);

        $this->render('admin/categories/form', [
            'title'    => 'Modifier : ' . ($category['translations']['fr']['name'] ?? $category['slug']),
            'category' => $category,
        ], 'app');
    }

    public function update(Request $request): void
    {
        $this->requireAuth();
        $this->requireRole('admin');
        $this->checkCsrf($request);

        $id = (int) $request->routeParam('id');
        Category::update($id, [
            'slug'       => trim($request->input('slug', '')),
            'sort_order' => (int) $request->input('sort_order', 0),
        ]);

        foreach (['fr', 'en', 'es'] as $locale) {
            $name = trim($request->input("{$locale}_name", ''));
            if ($name !== '') {
                Category::saveTranslation(
                    $id,
                    $locale,
                    $name,
                    trim($request->input("{$locale}_description", ''))
                );
            }
        }

        $this->redirect(locale_url('admin/categories'), 'Catégorie mise à jour.');
    }

    public function destroy(Request $request): void
    {
        $this->requireAuth();
        $this->requireRole('admin');
        $this->checkCsrf($request);

        $id = (int) $request->routeParam('id');
        Category::delete($id);
        $this->redirect(locale_url('admin/categories'), 'Catégorie supprimée.');
    }

    private function checkCsrf(Request $request): void
    {
        if (!Csrf::validate($request->input('_token', ''))) {
            \Core\Response::abort(403, 'Invalid CSRF token.');
        }
    }

    private function redirect(string $url, string $flash = ''): never
    {
        if ($flash) \Core\Session::flash('success', $flash);
        header('Location: ' . $url, true, 302);
        exit;
    }
}
