<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Request;
use App\Models\Recipe;
use App\Models\Product;

/**
 * Admin — Recipe CRUD.
 *
 * Routes:
 *   GET  /admin/recipes               → index()
 *   GET  /admin/recipes/create        → create()
 *   POST /admin/recipes               → store()
 *   GET  /admin/recipes/{id}/edit     → edit()
 *   POST /admin/recipes/{id}          → update()
 *   POST /admin/recipes/{id}/delete   → destroy()
 */
class RecipeController extends AdminController
{
    private const LOCALES = ['fr' => '🇫🇷 Français', 'en' => '🇬🇧 English', 'es' => '🇪🇸 Español'];

    public function index(Request $request): void
    {
        $this->render('admin/recipes/index', [
            'title'   => 'Gestion des recettes',
            'recipes' => Recipe::all(),
        ], 'app');
    }

    public function create(Request $request): void
    {
        $this->render('admin/recipes/form', [
            'title'    => 'Nouvelle recette',
            'recipe'   => null,
            'products' => Product::all(),
            'locales'  => self::LOCALES,
        ], 'app');
    }

    public function store(Request $request): void
    {
        $data     = $this->extractData($request);
        $data     = array_merge($data, $this->handleCoverUpload($request));
        $recipeId = Recipe::create($data);
        $this->saveTranslations($recipeId, $request);
        Recipe::syncProducts($recipeId, array_map('intval', (array)$request->input('linked_products', [])));
        $this->flashRedirect(locale_url('admin/recipes'), 'Recette créée !');
    }

    public function edit(Request $request): void
    {
        $id = (int) $request->routeParam('id');
        $recipe = Recipe::find($id);
        if (!$recipe) \Core\Response::abort(404);

        $this->render('admin/recipes/form', [
            'title'    => 'Modifier recette',
            'recipe'   => $recipe,
            'products' => Product::all(),
            'locales'  => self::LOCALES,
        ], 'app');
    }

    public function update(Request $request): void
    {
        $id = (int) $request->routeParam('id');
        $recipe = Recipe::find($id);
        if (!$recipe) \Core\Response::abort(404);

        $data = $this->extractData($request);
        $up   = $this->handleCoverUpload($request);
        $data['cover_image'] = $up['cover_image'] ?? $recipe['cover_image'];

        Recipe::update($id, $data);
        $this->saveTranslations($id, $request);
        Recipe::syncProducts($id, array_map('intval', (array)$request->input('linked_products', [])));
        $this->flashRedirect(locale_url('admin/recipes'), 'Recette mise à jour.');
    }

    public function destroy(Request $request): void
    {
        Recipe::delete((int) $request->routeParam('id'));
        $this->flashRedirect(locale_url('admin/recipes'), 'Recette supprimée.');
    }

    // ── Private helpers ──────────────────────────────────────────

    private function extractData(Request $request): array
    {
        $slug = trim($request->input('slug', ''));
        if ($slug === '') {
            $title = trim($request->input('fr_title', 'recette'));
            $slug  = Recipe::slugify($title);
        }
        return [
            'slug'       => $slug,
            'prep_time'  => $request->input('prep_time', '') !== '' ? (int)$request->input('prep_time') : null,
            'cook_time'  => $request->input('cook_time', '') !== '' ? (int)$request->input('cook_time') : null,
            'servings'   => $request->input('servings', '') !== '' ? (int)$request->input('servings') : null,
            'difficulty' => $request->input('difficulty', 'easy'),
            'status'     => $request->input('status', 'draft'),
            'sort_order' => (int) $request->input('sort_order', 0),
        ];
    }

    private function handleCoverUpload(Request $request): array
    {
        if (empty($_FILES['cover_image']['tmp_name'])) return [];
        $file    = $_FILES['cover_image'];
        $dir     = BASE_PATH . '/public/assets/img/recipes/';
        $relPath = \Core\ImageProcessor::handleUpload($file, $dir, '/assets/img/recipes/');
        return $relPath !== null ? ['cover_image' => $relPath] : [];
    }

    private function saveTranslations(int $id, Request $request): void
    {
        foreach (array_keys(self::LOCALES) as $lc) {
            $title = trim($request->input("{$lc}_title", ''));
            if ($title === '') continue;
            Recipe::saveTranslation($id, $lc, [
                'title'       => $title,
                'intro'       => trim($request->input("{$lc}_intro", '')),
                'ingredients' => trim($request->input("{$lc}_ingredients", '')),
                'steps'       => strip_tags(
                $request->input("{$lc}_steps", ''),
                '<p><br><strong><em><b><i><ul><ol><li><h2><h3><h4><a><blockquote><img>'
            ),
            ]);
        }
    }

    private function checkCsrf(Request $request): void { /* handled by Router */ }

    private function redirect(string $url, string $flash = ''): never
    {
        if ($flash) \Core\Session::flash('success', $flash);
        header('Location: ' . $url, true, 302); exit;
    }
}
