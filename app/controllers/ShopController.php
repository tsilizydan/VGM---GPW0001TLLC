<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Request;
use Core\Seo;
use App\Models\Product;
use App\Models\Bundle;
use App\Models\Recipe;
use App\Models\Category;

/**
 * Shop — product listing, detail, and recipe pages.
 */
class ShopController extends Controller
{
    // ── Listing ────────────────────────────────────────────────

    public function index(Request $request): void
    {
        $categorySlug = $request->input('category', '');
        $search       = $request->input('q', '');
        $sort         = $request->input('sort', '');
        $page         = max(1, (int) $request->input('page', 1));

        $result = Product::paginate([
            'category' => $categorySlug,
            'search'   => $search,
            'sort'     => $sort,
            'page'     => $page,
            'per_page' => 12,
        ]);

        $categories = Category::all();

        Seo::title('Boutique Vanille de Madagascar — Vanilla Groupe Madagascar')
           ->description('Découvrez notre sélection de vanilles et épices pures de Madagascar. Livraison internationale.')
           ->canonical(locale_url('shop'))
           ->set('og:type', 'website');

        $this->render('shop/index', [
            'products'     => $result['data'],
            'pagination'   => $result,
            'categories'   => $categories,
            'activeFilter' => $categorySlug,
            'search'       => $search,
            'sort'         => $sort,
        ], 'app');
    }

    // ── Product detail ──────────────────────────────────────────

    public function show(Request $request): void
    {
        $slug    = $request->routeParam('slug');
        $product = Product::findBySlug($slug);

        if (!$product) {
            \Core\Response::abort(404);
        }

        $pid = (int) $product['id'];

        Seo::forProduct($product);

        $this->render('shop/product', [
            'product'     => $product,
            'related'     => Product::related($pid, 4),
            'alsoBoaught' => Product::alsoBoaught($pid, 6),
            'bundles'     => Bundle::forProduct($pid),
            'recipes'     => Recipe::forProduct($pid, 3),
        ], 'app');
    }

    // ── Recipes listing ─────────────────────────────────────────

    public function recipes(Request $request): void
    {
        $page   = max(1, (int) $request->input('page', 1));
        $result = Recipe::paginate(['page' => $page, 'per_page' => 12]);

        Seo::title('Recettes à la vanille de Madagascar — Vanilla Groupe Madagascar')
           ->description('Inspirez-vous de nos recettes gourmandes à la vanille pure de Madagascar. Crèmes, desserts, marinades…')
           ->canonical(locale_url('recipes'))
           ->ogType('website');

        $this->render('shop/recipes', [
            'recipes'    => $result['data'],
            'pagination' => $result,
        ], 'app');
    }

    // ── Single recipe ───────────────────────────────────────────

    public function recipe(Request $request): void
    {
        $slug   = $request->routeParam('slug');
        $recipe = Recipe::findBySlug($slug);

        if (!$recipe) {
            \Core\Response::abort(404);
        }

        Seo::forRecipe($recipe);

        $this->render('shop/recipe', [
            'recipe' => $recipe,
        ], 'app');
    }
}
