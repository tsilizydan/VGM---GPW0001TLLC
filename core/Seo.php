<?php

declare(strict_types=1);

namespace Core;

/**
 * SEO Meta Builder.
 *
 * Call Seo::set*() in any controller or view, then render
 * Seo::head() inside the <head> of the layout.
 *
 * Usage:
 *   Seo::title('Produit — Vanilla Groupe Madagascar')
 *      ->description('Vanille pure de Madagascar')
 *      ->canonical('/fr/shop/vanille-bourbon')
 *      ->og('image', 'https://...')
 *      ->schema(['@type' => 'Product', ...]);
 */
class Seo
{
    private static string  $title       = 'Vanilla Groupe Madagascar';
    private static string  $description = 'Vanille pure de Madagascar — producteur et exportateur premium.';
    private static string  $canonical   = '';
    private static string  $robots      = 'index,follow';
    private static string  $ogType      = 'website';
    private static ?string $ogImage     = null;
    private static array   $extra       = [];    // arbitrary og/meta key→value
    private static array   $schemas     = [];    // JSON-LD objects
    private static string  $siteName    = 'Vanilla Groupe Madagascar';

    // ── Fluent setters ────────────────────────────────────────────

    public static function title(string $title): static
    {
        self::$title = $title;
        return new static();
    }

    public static function description(string $desc): static
    {
        self::$description = mb_substr($desc, 0, 160);
        return new static();
    }

    public static function canonical(string $url): static
    {
        self::$canonical = $url;
        return new static();
    }

    public static function robots(string $value): static
    {
        self::$robots = $value;
        return new static();
    }

    public static function ogType(string $type): static
    {
        self::$ogType = $type;
        return new static();
    }

    public static function ogImage(string $url): static
    {
        self::$ogImage = $url;
        return new static();
    }

    /**
     * Set any additional meta / og property.
     * @param string $key   e.g. 'og:price:amount', 'twitter:card'
     */
    public static function set(string $key, string $value): static
    {
        self::$extra[$key] = $value;
        return new static();
    }

    /**
     * Add a JSON-LD schema.org object.
     * @param array<string,mixed> $data
     */
    public static function schema(array $data): static
    {
        self::$schemas[] = $data;
        return new static();
    }

    // ── Convenience presets ───────────────────────────────────────

    /**
     * Product page: OG type=product + schema.org Product.
     *
     * @param array<string,mixed> $product  Product model row
     */
    public static function forProduct(array $product): void
    {
        $name  = e($product['name'] ?? 'Produit');
        $desc  = strip_tags($product['description'] ?? '');
        $img   = $product['primary_image'] ?? null;
        $price = number_format((float)($product['price'] ?? 0), 2, '.', '');
        $slug  = $product['slug'] ?? '';
        $locale = \Core\Lang::getLocale();

        self::title("{$name} — Vanilla Groupe Madagascar")
            ->description($desc ?: "Découvrez {$name}, vanille pure de Madagascar.")
            ->canonical(locale_url("shop/{$slug}"))
            ->ogType('product')
            ->set('og:site_name',       self::$siteName)
            ->set('og:locale',          self::localeCode($locale))
            ->set('product:price:amount',   $price)
            ->set('product:price:currency', 'EUR');

        if ($img) self::ogImage(url(ltrim($img, '/')));

        self::schema([
            '@context'    => 'https://schema.org',
            '@type'       => 'Product',
            'name'        => $name,
            'description' => $desc,
            'image'       => $img ? url(ltrim($img, '/')) : null,
            'offers'      => [
                '@type'         => 'Offer',
                'priceCurrency' => 'EUR',
                'price'         => $price,
                'availability'  => ($product['stock'] ?? 0) > 0
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
                'url'           => locale_url("shop/{$slug}"),
            ],
            'brand' => [
                '@type' => 'Brand',
                'name'  => 'Vanilla Groupe Madagascar',
            ],
        ]);
    }

    /**
     * Recipe page: OG type=article + schema.org Recipe.
     *
     * @param array<string,mixed> $recipe  Recipe model row
     */
    public static function forRecipe(array $recipe): void
    {
        $title = e($recipe['title'] ?? 'Recette');
        $desc  = strip_tags($recipe['intro'] ?? '');
        $img   = $recipe['cover_image'] ?? null;
        $slug  = $recipe['slug'] ?? '';
        $locale = \Core\Lang::getLocale();

        self::title("{$title} — Recette Vanilla Groupe Madagascar")
            ->description($desc ?: "Recette à la vanille de Madagascar : {$title}")
            ->canonical(locale_url("recipes/{$slug}"))
            ->ogType('article')
            ->set('og:site_name', self::$siteName)
            ->set('og:locale', self::localeCode($locale));

        if ($img) self::ogImage(url(ltrim($img, '/')));

        $schemaPrepTime = $recipe['prep_time'] ? 'PT' . $recipe['prep_time'] . 'M' : null;
        $schemaCookTime = $recipe['cook_time'] ? 'PT' . $recipe['cook_time'] . 'M' : null;

        self::schema([
            '@context'     => 'https://schema.org',
            '@type'        => 'Recipe',
            'name'         => $title,
            'description'  => $desc,
            'image'        => $img ? url(ltrim($img, '/')) : null,
            'prepTime'     => $schemaPrepTime,
            'cookTime'     => $schemaCookTime,
            'recipeYield'  => $recipe['servings'] ? (string)$recipe['servings'] . ' personnes' : null,
            'author'       => ['@type' => 'Organization', 'name' => 'Vanilla Groupe Madagascar'],
        ]);
    }

    // ── Output ────────────────────────────────────────────────────

    /**
     * Render all SEO tags for placement inside <head>.
     */
    public static function head(): string
    {
        $locale   = \Core\Lang::getLocale();
        $appUrl   = rtrim(env('APP_URL', ''), '/');
        $canonical = self::$canonical ?: ($appUrl . ($_SERVER['REQUEST_URI'] ?? '/'));

        $ogImage = self::$ogImage ?? $appUrl . '/assets/img/og-default.jpg';

        $html  = "    <!-- SEO Meta -->\n";
        $html .= '    <title>' . self::esc(self::$title) . "</title>\n";
        $html .= '    <meta name="description" content="' . self::esc(self::$description) . "\">\n";
        $html .= '    <meta name="robots" content="' . self::esc(self::$robots) . "\">\n";
        $html .= '    <link rel="canonical" href="' . self::esc($canonical) . "\">\n";

        // OpenGraph
        $html .= "\n    <!-- OpenGraph -->\n";
        $html .= '    <meta property="og:type"        content="' . self::esc(self::$ogType) . "\">\n";
        $html .= '    <meta property="og:site_name"   content="' . self::esc(self::$siteName) . "\">\n";
        $html .= '    <meta property="og:title"       content="' . self::esc(self::$title) . "\">\n";
        $html .= '    <meta property="og:description" content="' . self::esc(self::$description) . "\">\n";
        $html .= '    <meta property="og:url"         content="' . self::esc($canonical) . "\">\n";
        $html .= '    <meta property="og:image"       content="' . self::esc($ogImage) . "\">\n";
        $html .= '    <meta property="og:locale"      content="' . self::esc(self::localeCode($locale)) . "\">\n";

        // Twitter Card
        $html .= "\n    <!-- Twitter Card -->\n";
        $html .= "    <meta name=\"twitter:card\"        content=\"summary_large_image\">\n";
        $html .= '    <meta name="twitter:title"       content="' . self::esc(self::$title) . "\">\n";
        $html .= '    <meta name="twitter:description" content="' . self::esc(self::$description) . "\">\n";
        $html .= '    <meta name="twitter:image"       content="' . self::esc($ogImage) . "\">\n";

        // Extra properties (product:price etc.)
        if (!empty(self::$extra)) {
            $html .= "\n    <!-- Extra Meta -->\n";
            foreach (self::$extra as $key => $value) {
                $prop = str_starts_with($key, 'og:') || str_starts_with($key, 'product:') || str_starts_with($key, 'article:')
                    ? 'property' : 'name';
                $html .= "    <meta {$prop}=\"" . self::esc($key) . '" content="' . self::esc($value) . "\">\n";
            }
        }

        // JSON-LD schemas
        foreach (self::$schemas as $schema) {
            $json  = json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $html .= "\n    <script type=\"application/ld+json\">\n{$json}\n    </script>\n";
        }

        // Reset state for next render
        self::reset();

        return $html;
    }

    /**
     * Render hreflang alternate links (call from layout).
     *
     * @param string $currentPath  Current path without locale prefix
     */
    public static function hreflang(string $currentPath = ''): string
    {
        if ($currentPath === '') {
            $uri  = $_SERVER['REQUEST_URI'] ?? '/';
            $uri  = parse_url($uri, PHP_URL_PATH) ?? '/';
            // strip locale prefix
            $currentPath = preg_replace('#^/(fr|en|es)(/|$)#', '$2', $uri) ?? $uri;
        }

        $appUrl = rtrim(env('APP_URL', ''), '/');
        $html   = "\n    <!-- Hreflang -->\n";
        foreach (['fr', 'en', 'es'] as $lc) {
            $href = $appUrl . '/' . $lc . '/' . ltrim((string)$currentPath, '/');
            $html .= "    <link rel=\"alternate\" hreflang=\"{$lc}\" href=\"" . self::esc($href) . "\">\n";
        }
        $html .= "    <link rel=\"alternate\" hreflang=\"x-default\" href=\"" . self::esc($appUrl . '/fr/' . ltrim((string)$currentPath, '/')) . "\">\n";
        return $html;
    }

    // ── Internal ─────────────────────────────────────────────────

    private static function esc(string $v): string
    {
        return htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private static function localeCode(string $lc): string
    {
        return match($lc) { 'en' => 'en_GB', 'es' => 'es_ES', default => 'fr_FR' };
    }

    private static function reset(): void
    {
        self::$title       = 'Vanilla Groupe Madagascar';
        self::$description = 'Vanille pure de Madagascar — producteur et exportateur premium.';
        self::$canonical   = '';
        self::$robots      = 'index,follow';
        self::$ogType      = 'website';
        self::$ogImage     = null;
        self::$extra       = [];
        self::$schemas     = [];
    }
}
