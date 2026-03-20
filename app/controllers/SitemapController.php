<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Request;
use Core\Cache;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\Category;

/**
 * Sitemap + robots.txt generator.
 *
 * Routes:
 *   GET /sitemap.xml  → sitemap()
 *   GET /robots.txt   → robots()
 */
class SitemapController extends Controller
{
    private const CACHE_TTL = 3600; // 1 hour

    // ── Sitemap ──────────────────────────────────────────────────

    public function sitemap(Request $request): void
    {
        $xml = Cache::remember('sitemap_xml', self::CACHE_TTL, function () {
            return $this->buildSitemap();
        });

        header('Content-Type: application/xml; charset=UTF-8');
        header('X-Robots-Tag: noindex');
        echo $xml;
        exit;
    }

    // ── Robots.txt ───────────────────────────────────────────────

    public function robots(Request $request): void
    {
        $appUrl = rtrim(env('APP_URL', ''), '/');

        header('Content-Type: text/plain; charset=UTF-8');
        echo "User-agent: *\n";
        echo "Allow: /\n";
        echo "Disallow: /admin/\n";
        echo "Disallow: /checkout/\n";
        echo "Disallow: /cart\n";
        echo "Disallow: /dashboard\n";
        echo "Disallow: /login\n";
        echo "Disallow: /register\n";
        echo "\n";
        echo "Sitemap: {$appUrl}/sitemap.xml\n";
        exit;
    }

    // ── Private ──────────────────────────────────────────────────

    private function buildSitemap(): string
    {
        $appUrl  = rtrim(env('APP_URL', ''), '/');
        $locales = ['fr', 'en', 'es'];
        $today   = date('Y-m-d');

        $urls = [];

        // Static pages (all locales)
        $staticPaths = ['', 'shop', 'recipes'];
        foreach ($staticPaths as $path) {
            $locs = [];
            foreach ($locales as $lc) {
                $locs[$lc] = $appUrl . '/' . $lc . ($path !== '' ? '/' . $path : '/');
            }
            $urls[] = [
                'locs'      => $locs,
                'lastmod'   => $today,
                'changefreq'=> $path === '' ? 'daily' : 'weekly',
                'priority'  => $path === '' ? '1.0' : '0.8',
            ];
        }

        // Products (active)
        $products = Product::all(['status' => 'active']);
        foreach ($products as $p) {
            $locs = [];
            foreach ($locales as $lc) {
                $locs[$lc] = $appUrl . '/' . $lc . '/shop/' . $p['slug'];
            }
            $urls[] = [
                'locs'      => $locs,
                'lastmod'   => $today,
                'changefreq'=> 'weekly',
                'priority'  => '0.9',
            ];
        }

        // Recipes (published)
        try {
            $result = Recipe::paginate(['per_page' => 500]);
            foreach ($result['data'] as $r) {
                $locs = [];
                foreach ($locales as $lc) {
                    $locs[$lc] = $appUrl . '/' . $lc . '/recipes/' . $r['slug'];
                }
                $urls[] = [
                    'locs'      => $locs,
                    'lastmod'   => $today,
                    'changefreq'=> 'monthly',
                    'priority'  => '0.6',
                ];
            }
        } catch (\Throwable) {
            // Recipe table may not yet exist — skip gracefully
        }

        // Build XML
        $xml  = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $xml .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\"\n";
        $xml .= "        xmlns:xhtml=\"http://www.w3.org/1999/xhtml\">\n";

        foreach ($urls as $entry) {
            // Primary URL = French (default)
            $primary = $entry['locs']['fr'];
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($primary, ENT_XML1) . "</loc>\n";
            $xml .= "    <lastmod>{$entry['lastmod']}</lastmod>\n";
            $xml .= "    <changefreq>{$entry['changefreq']}</changefreq>\n";
            $xml .= "    <priority>{$entry['priority']}</priority>\n";
            // hreflang alternates
            foreach ($entry['locs'] as $lc => $loc) {
                $xml .= "    <xhtml:link rel=\"alternate\" hreflang=\"{$lc}\" href=\"" . htmlspecialchars($loc, ENT_XML1) . "\"/>\n";
            }
            $xml .= "    <xhtml:link rel=\"alternate\" hreflang=\"x-default\" href=\"" . htmlspecialchars($primary, ENT_XML1) . "\"/>\n";
            $xml .= "  </url>\n";
        }

        $xml .= "</urlset>\n";
        return $xml;
    }
}
