<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Request;
use Core\Validator;
use App\Models\Category;
use App\Models\Product;

/**
 * Admin — Product CRUD + image upload.
 *
 * Routes:
 *   GET  /admin/products                → index()
 *   GET  /admin/products/create         → create()
 *   POST /admin/products                → store()
 *   GET  /admin/products/{id}/edit      → edit()
 *   POST /admin/products/{id}           → update()
 *   POST /admin/products/{id}/delete    → destroy()
 *   POST /admin/products/{id}/img-del   → deleteImage()
 *   POST /admin/products/{id}/img-primary → setPrimary()
 */
class ProductController extends AdminController
{
    private const UPLOAD_DIR     = '/assets/img/products/';
    private const MAX_FILE_BYTES = 5 * 1024 * 1024; // 5 MB
    private const ALLOWED_MIME   = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    // ── Index ──────────────────────────────────────────────────

    public function index(Request $request): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        $filters = [
            'admin'    => true,
            'status'   => $request->input('status', ''),
            'category' => $request->input('category', ''),
            'search'   => $request->input('q', ''),
            'sort'     => $request->input('sort', 'newest'),
            'page'     => (int) $request->input('page', 1),
            'per_page' => 20,
        ];

        $result     = Product::paginate($filters);
        $categories = Category::all();

        $this->render('admin/products/index', [
            'title'      => 'Gestion des produits',
            'result'     => $result,
            'categories' => $categories,
            'filters'    => $filters,
        ], 'app');
    }

    // ── Create ─────────────────────────────────────────────────

    public function create(Request $request): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        $this->render('admin/products/form', [
            'title'      => 'Nouveau produit',
            'product'    => null,
            'categories' => Category::all(),
            'locales'    => ['fr' => '🇫🇷 Français', 'en' => '🇬🇧 English', 'es' => '🇪🇸 Español'],
        ], 'app');
    }

    // ── Store ──────────────────────────────────────────────────

    public function store(Request $request): void
    {
        $this->requireAuth();
        $this->requireRole('admin');
        $this->checkCsrf($request);

        $data = $this->extractProductData($request);
        $errors = $this->validateProduct($data);

        if ($errors) {
            $this->redirectBack(['errors' => $errors, 'old' => $data]);
        }

        $productId = Product::create($data);

        // Save translations
        $this->saveTranslations($productId, $request);

        // Handle image uploads
        $this->handleImageUploads($productId, $request);

        // Handle variations
        $this->saveVariations($productId, $request);

        $this->redirect(locale_url('admin/products'), 'Produit créé avec succès !');
    }

    // ── Edit ───────────────────────────────────────────────────

    public function edit(Request $request): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        $id      = (int) $request->routeParam('id');
        $product = Product::find($id);

        if (!$product) {
            \Core\Response::abort(404, 'Produit introuvable.');
        }

        $this->render('admin/products/form', [
            'title'      => 'Modifier : ' . ($product['translations']['fr']['name'] ?? $product['slug']),
            'product'    => $product,
            'categories' => Category::all(),
            'locales'    => ['fr' => '🇫🇷 Français', 'en' => '🇬🇧 English', 'es' => '🇪🇸 Español'],
        ], 'app');
    }

    // ── Update ─────────────────────────────────────────────────

    public function update(Request $request): void
    {
        $this->requireAuth();
        $this->requireRole('admin');
        $this->checkCsrf($request);

        $id      = (int) $request->routeParam('id');
        $product = Product::find($id);
        if (!$product) \Core\Response::abort(404);

        $data = $this->extractProductData($request);
        $errors = $this->validateProduct($data, $id);

        if ($errors) {
            $this->redirect(locale_url("admin/products/{$id}/edit"), implode(' ', $errors));
        }

        Product::update($id, $data);
        $this->saveTranslations($id, $request);
        $this->handleImageUploads($id, $request);
        $this->saveVariations($id, $request);

        $this->redirect(locale_url('admin/products'), 'Produit mis à jour.');
    }

    // ── Destroy ────────────────────────────────────────────────

    public function destroy(Request $request): void
    {
        $this->requireAuth();
        $this->requireRole('admin');
        $this->checkCsrf($request);

        $id = (int) $request->routeParam('id');
        Product::delete($id);

        $this->redirect(locale_url('admin/products'), 'Produit supprimé.');
    }

    // ── Image sub-actions ──────────────────────────────────────

    public function deleteImage(Request $request): void
    {
        $this->requireAuth();
        $this->requireRole('admin');
        $this->checkCsrf($request);

        $productId = (int) $request->routeParam('id');
        $imageId   = (int) $request->input('image_id');
        Product::deleteImage($imageId);

        $this->jsonSuccess('Image supprimée.');
    }

    public function setPrimary(Request $request): void
    {
        $this->requireAuth();
        $this->requireRole('admin');
        $this->checkCsrf($request);

        $productId = (int) $request->routeParam('id');
        $imageId   = (int) $request->input('image_id');
        Product::setPrimaryImage($productId, $imageId);

        $this->jsonSuccess('Image principale définie.');
    }

    // ── Private helpers ─────────────────────────────────────────

    /** @return array<string,mixed> */
    private function extractProductData(Request $request): array
    {
        $name = trim($request->input('translations.fr.name', $request->input('fr_name', '')));
        $slug = trim($request->input('slug', ''));
        if ($slug === '') {
            $slug = Product::slugify($name);
        }

        return [
            'slug'          => $slug,
            'category_id'   => $request->input('category_id', ''),
            'sku'           => trim($request->input('sku', '')),
            'price'         => (float) $request->input('price', 0),
            'compare_price' => $request->input('compare_price', '') !== '' ? (float) $request->input('compare_price') : null,
            'stock'         => (int) $request->input('stock', 0),
            'status'        => $request->input('status', 'draft'),
            'featured'      => (int) (bool) $request->input('featured', false),
            'sort_order'    => (int) $request->input('sort_order', 0),
        ];
    }

    /** @return list<string> */
    private function validateProduct(array $data, ?int $excludeId = null): array
    {
        $errors = [];
        if ($data['slug'] === '') {
            $errors[] = 'Le slug est requis.';
        }
        if ($data['price'] <= 0) {
            $errors[] = 'Le prix doit être supérieur à 0.';
        }
        return $errors;
    }

    private function saveTranslations(int $productId, Request $request): void
    {
        foreach (['fr', 'en', 'es'] as $locale) {
            $name = trim($request->input("{$locale}_name", ''));
            if ($name === '') continue;

            Product::saveTranslation($productId, $locale, [
                'name'            => $name,
                'description'     => trim($request->input("{$locale}_description", '')),
                'story'           => trim($request->input("{$locale}_story", '')),
                // Storytelling fields
                'origin_region'   => trim($request->input("{$locale}_origin_region", '')),
                'farmer_name'     => trim($request->input("{$locale}_farmer_name", '')),
                'farmer_quote'    => trim($request->input("{$locale}_farmer_quote", '')),
                'farmer_story'    => trim($request->input("{$locale}_farmer_story", '')),
                'harvest_process' => trim($request->input("{$locale}_harvest_process", '')),
                'harvest_season'  => trim($request->input("{$locale}_harvest_season", '')),
                'certifications'  => trim($request->input("{$locale}_certifications", '')),
            ]);
        }
    }

    private function handleImageUploads(int $productId, Request $request): void
    {
        if (empty($_FILES['images']['name'][0])) return;

        $uploadDir  = BASE_PATH . '/public' . self::UPLOAD_DIR . $productId . '/';
        $publicBase = self::UPLOAD_DIR . $productId . '/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);

        $files       = $_FILES['images'];
        $count       = count($files['name']);
        $hasExisting = count(Product::imagesFor($productId)) > 0;

        for ($i = 0; $i < $count; $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;

            $singleFile = [
                'tmp_name' => $files['tmp_name'][$i],
                'name'     => $files['name'][$i],
                'size'     => $files['size'][$i],
                'error'    => $files['error'][$i],
                'type'     => $files['type'][$i],
            ];

            $relPath = \Core\ImageProcessor::handleUpload($singleFile, $uploadDir, $publicBase);
            if ($relPath === null) continue;

            $isPrimary = (!$hasExisting && $i === 0);
            $altText   = "Vanilla Groupe Madagascar — produit #{$productId}";
            Product::addImage($productId, $relPath, $altText, $isPrimary);
            $hasExisting = true;
        }
    }

    private function saveVariations(int $productId, Request $request): void
    {
        $rawVariations = $request->input('variations', []);
        if (!is_array($rawVariations) || empty($rawVariations)) return;

        $variations = [];
        foreach ($rawVariations as $v) {
            $attrs = [];
            if (!empty($v['attr_keys']) && is_array($v['attr_keys'])) {
                foreach ($v['attr_keys'] as $k => $key) {
                    $val = $v['attr_vals'][$k] ?? '';
                    if ($key !== '' && $val !== '') {
                        $attrs[trim($key)] = trim($val);
                    }
                }
            }
            if (empty($attrs)) continue;

            $variations[] = [
                'sku'        => $v['sku'] ?? '',
                'price'      => $v['price'] !== '' ? (float) $v['price'] : null,
                'stock'      => (int) ($v['stock'] ?? 0),
                'attributes' => $attrs,
            ];
        }

        Product::syncVariations($productId, $variations);
    }

    private function mimeToExt(string $mime): string
    {
        return match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/gif'  => 'gif',
            default      => 'jpg',
        };
    }

    private function checkCsrf(Request $request): void
    {
        // CSRF is validated globally by the Router on all POST requests.
        // This stub is kept for backwards compatibility with existing call sites.
    }

    private function redirect(string $url, string $flash = ''): never
    {
        if ($flash !== '') {
            \Core\Session::flash('success', $flash);
        }
        header('Location: ' . $url, true, 302);
        exit;
    }

    private function redirectBack(array $flash = []): never
    {
        foreach ($flash as $k => $v) {
            \Core\Session::flash($k, $v);
        }
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? locale_url('admin/products/create')), true, 302);
        exit;
    }

    private function jsonSuccess(string $message): never
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => $message]);
        exit;
    }
}
