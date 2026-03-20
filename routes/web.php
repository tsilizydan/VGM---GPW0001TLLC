<?php

declare(strict_types=1);

/**
 * Web routes.
 * $router is injected by Application::loadRoutes().
 *
 * NOTE: The locale prefix (/fr/, /en/, /es/) is stripped by Application::resolveLocale()
 * before reaching the Router. All routes here are locale-agnostic.
 */

// Public
$router->get('/', 'HomeController@index');

// Authentication
$router->get('/register',      'AuthController@showRegister');
$router->post('/register',     'AuthController@register');
$router->get('/login',         'AuthController@showLogin');
$router->post('/login',        'AuthController@login');
$router->get('/logout',        'AuthController@logout');
$router->get('/verify-notice', 'AuthController@verifyNotice');
$router->get('/verify-email',  'AuthController@verifyEmail');

// Protected
$router->get('/dashboard', 'DashboardController@index');

// Shop
$router->get('/shop',        'ShopController@index');
$router->get('/shop/{slug}', 'ShopController@show');

// Cart — page + AJAX endpoints
$router->get('/cart',          'CartController@index');
$router->post('/cart/add',     'CartController@add');
$router->post('/cart/update',  'CartController@update');
$router->post('/cart/remove',  'CartController@remove');
$router->post('/cart/clear',   'CartController@clear');

// Checkout
$router->get('/checkout',                 'CheckoutController@index');
$router->post('/checkout/shipping',       'CheckoutController@shipping');
$router->post('/checkout/set-shipping',   'CheckoutController@setShipping');
$router->get('/checkout/confirm',         'CheckoutController@confirm');
$router->post('/checkout/place',          'CheckoutController@place');
$router->get('/checkout/thanks',          'CheckoutController@thanks');
$router->post('/checkout/coupon',         'CheckoutController@applyCoupon');

// Admin — products
$router->get('/admin/products',                    'Admin\\ProductController@index');
$router->get('/admin/products/create',             'Admin\\ProductController@create');
$router->post('/admin/products',                   'Admin\\ProductController@store');
$router->get('/admin/products/{id}/edit',          'Admin\\ProductController@edit');
$router->post('/admin/products/{id}',              'Admin\\ProductController@update');
$router->post('/admin/products/{id}/delete',       'Admin\\ProductController@destroy');
$router->post('/admin/products/{id}/img-del',      'Admin\\ProductController@deleteImage');
$router->post('/admin/products/{id}/img-primary',  'Admin\\ProductController@setPrimary');

// Admin — categories
$router->get('/admin/categories',              'Admin\\CategoryController@index');
$router->get('/admin/categories/create',       'Admin\\CategoryController@create');
$router->post('/admin/categories',             'Admin\\CategoryController@store');
$router->get('/admin/categories/{id}/edit',    'Admin\\CategoryController@edit');
$router->post('/admin/categories/{id}',        'Admin\\CategoryController@update');
$router->post('/admin/categories/{id}/delete', 'Admin\\CategoryController@destroy');

// Admin — translations
$router->get('/admin/translations',        'Admin\\TranslationController@index');
$router->post('/admin/translations/update', 'Admin\\TranslationController@update');

// Admin — dashboard (home)
$router->get('/admin',         'Admin\\DashboardController@index');
$router->get('/admin/charts',  'Admin\\DashboardController@charts');  // AJAX — Chart.js data

// Admin — orders
$router->get('/admin/orders',                   'Admin\\OrderController@index');
$router->get('/admin/orders/{id}',              'Admin\\OrderController@show');
$router->post('/admin/orders/{id}/status',      'Admin\\OrderController@updateStatus');

// Admin — customers
$router->get('/admin/customers',                'Admin\\CustomerController@index');
$router->get('/admin/customers/{id}',           'Admin\\CustomerController@show');

// Admin — content editor (TinyMCE)
$router->get('/admin/content',                  'Admin\\ContentController@index');
$router->post('/admin/content/update',          'Admin\\ContentController@update');

// Admin — settings
$router->get('/admin/settings',                 'Admin\\SettingsController@index');
$router->post('/admin/settings',                'Admin\\SettingsController@update');

// Design styleguide (dev only)
$router->get('/design', 'DesignController@index');

// Public — recipes
$router->get('/recipes',        'ShopController@recipes');
$router->get('/recipes/{slug}', 'ShopController@recipe');

// Admin — bundles
$router->get('/admin/bundles',                 'Admin\\BundleController@index');
$router->get('/admin/bundles/create',          'Admin\\BundleController@create');
$router->post('/admin/bundles',                'Admin\\BundleController@store');
$router->get('/admin/bundles/{id}/edit',       'Admin\\BundleController@edit');
$router->post('/admin/bundles/{id}',           'Admin\\BundleController@update');
$router->post('/admin/bundles/{id}/delete',    'Admin\\BundleController@destroy');

// Admin — recipes
$router->get('/admin/recipes',                 'Admin\\RecipeController@index');
$router->get('/admin/recipes/create',          'Admin\\RecipeController@create');
$router->post('/admin/recipes',                'Admin\\RecipeController@store');
$router->get('/admin/recipes/{id}/edit',       'Admin\\RecipeController@edit');
$router->post('/admin/recipes/{id}',           'Admin\\RecipeController@update');
$router->get('/admin/recipes/{id}/delete',    'Admin\\RecipeController@destroy');

// ── Non-locale-prefixed system routes ───────────────────────────────
// In Application.php these are registered before locale resolution so
// they are accessible as /sitemap.xml and /robots.txt directly.
$router->get('/sitemap.xml', 'SitemapController@sitemap');
$router->get('/robots.txt',  'SitemapController@robots');
