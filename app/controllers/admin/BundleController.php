<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Request;
use App\Models\Bundle;
use App\Models\Product;

/**
 * Admin — Bundle CRUD.
 *
 * Routes:
 *   GET  /admin/bundles               → index()
 *   GET  /admin/bundles/create        → create()
 *   POST /admin/bundles               → store()
 *   GET  /admin/bundles/{id}/edit     → edit()
 *   POST /admin/bundles/{id}          → update()
 *   POST /admin/bundles/{id}/delete   → destroy()
 */
class BundleController extends AdminController
{
    private const LOCALES = ['fr' => '🇫🇷 Français', 'en' => '🇬🇧 English', 'es' => '🇪🇸 Español'];

    public function index(Request $request): void
    {
        $this->render('admin/bundles/index', [
            'title'   => 'Offres groupées',
            'bundles' => Bundle::all(),
        ], 'app');
    }

    public function create(Request $request): void
    {
        $this->render('admin/bundles/form', [
            'title'    => 'Nouveau bundle',
            'bundle'   => null,
            'products' => Product::all(),
            'locales'  => self::LOCALES,
        ], 'app');
    }

    public function store(Request $request): void
    {
        $data      = $this->extractData($request);
        $bundleId  = Bundle::create($data);
        $this->saveTranslations($bundleId, $request);
        $this->saveItems($bundleId, $request);
        $this->flashRedirect(locale_url('admin/bundles'), 'Bundle créé !');
    }

    public function edit(Request $request): void
    {
        $id = (int) $request->routeParam('id');
        $bundle = Bundle::find($id);
        if (!$bundle) \Core\Response::abort(404);

        $this->render('admin/bundles/form', [
            'title'    => 'Modifier bundle',
            'bundle'   => $bundle,
            'products' => Product::all(),
            'locales'  => self::LOCALES,
        ], 'app');
    }

    public function update(Request $request): void
    {
        $id = (int) $request->routeParam('id');
        if (!Bundle::find($id)) \Core\Response::abort(404);

        Bundle::update($id, $this->extractData($request));
        $this->saveTranslations($id, $request);
        $this->saveItems($id, $request);
        $this->flashRedirect(locale_url('admin/bundles'), 'Bundle mis à jour.');
    }

    public function destroy(Request $request): void
    {
        Bundle::delete((int) $request->routeParam('id'));
        $this->flashRedirect(locale_url('admin/bundles'), 'Bundle supprimé.');
    }

    // ── Private helpers ──────────────────────────────────────────

    private function extractData(Request $request): array
    {
        $slug = trim($request->input('slug', ''));
        if ($slug === '') {
            $name = trim($request->input('fr_name', 'bundle'));
            $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/', '-', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name) ?: $name) ?? $name, '-'));
        }
        return [
            'slug'         => $slug,
            'discount'     => (float) $request->input('discount', 0),
            'discount_pct' => (int)   $request->input('discount_pct', 0),
            'status'       => $request->input('status', 'draft'),
            'sort_order'   => (int)   $request->input('sort_order', 0),
        ];
    }

    private function saveTranslations(int $id, Request $request): void
    {
        foreach (array_keys(self::LOCALES) as $lc) {
            $name = trim($request->input("{$lc}_name", ''));
            if ($name === '') continue;
            Bundle::saveTranslation($id, $lc, [
                'name'        => $name,
                'description' => trim($request->input("{$lc}_description", '')),
            ]);
        }
    }

    private function saveItems(int $id, Request $request): void
    {
        $raw   = $request->input('items', []);
        $items = [];
        if (is_array($raw)) {
            foreach ($raw as $row) {
                $pid = (int)($row['product_id'] ?? 0);
                $qty = max(1, (int)($row['qty'] ?? 1));
                if ($pid > 0) $items[] = ['product_id' => $pid, 'qty' => $qty];
            }
        }
        Bundle::syncItems($id, $items);
    }

    private function checkCsrf(Request $request): void { /* handled by Router */ }

    private function redirect(string $url, string $flash = ''): never
    {
        if ($flash) \Core\Session::flash('success', $flash);
        header('Location: ' . $url, true, 302); exit;
    }
}
