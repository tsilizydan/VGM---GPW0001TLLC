# Vanilla Groupe Madagascar — PHP 8.4 MVC Skeleton

A lightweight custom MVC framework built for **Vanilla Groupe Madagascar**, designed for Namecheap shared hosting (Apache + `mod_rewrite`). No frameworks, no large dependencies — just clean PHP 8.4+.

---

## Project Structure

```
.
├── .env                   # Environment variables (DO NOT commit)
├── .env.example           # Template for .env
├── .gitignore
├── composer.json          # PSR-4 map (for local dev only)
│
├── app/
│   ├── controllers/       # App controllers (extend Core\Controller)
│   ├── models/            # App models (extend Core\Model)
│   └── views/
│       ├── layouts/       # Layout templates
│       └── home/          # View files per route
│
├── config/
│   ├── app.php            # App settings
│   └── database.php       # PDO DB settings (reads .env)
│
├── core/                  # Framework internals
│   ├── Autoloader.php     # PSR-4 autoloader (no Composer needed)
│   ├── Application.php    # App bootstrap
│   ├── Router.php         # GET/POST router
│   ├── Request.php        # HTTP request abstraction
│   ├── Response.php       # Redirect / JSON / abort helpers
│   ├── Controller.php     # Base controller
│   ├── Model.php          # Base model + PDO connection
│   ├── View.php           # View renderer
│   └── helpers.php        # Global functions (env, base_path, e, …)
│
├── public/                # Web root – point your domain here
│   ├── .htaccess          # URL rewriting
│   ├── index.php          # Front controller
│   └── assets/
│       └── css/style.css
│
├── routes/
│   └── web.php            # Route definitions
│
└── storage/
    └── logs/              # Error logs (writable by web server)
```

---

## Quick Start (Local)

```bash
cd public
php -S localhost:8000
# Open http://localhost:8000
```

---

## Adding a New Route

1. **Register the route** in `routes/web.php`:
   ```php
   $router->get('/about', 'AboutController@index');
   $router->post('/contact', 'ContactController@store');
   ```

2. **Create the controller** in `app/controllers/`:
   ```php
   namespace App\Controllers;
   use Core\Controller;
   use Core\Request;

   class AboutController extends Controller {
       public function index(Request $request): void {
           $this->render('about/index', ['title' => 'À propos']);
       }
   }
   ```

3. **Create the view** in `app/views/about/index.php`.

---

## Adding a Model

```php
namespace App\Models;
use Core\Model;

class Product extends Model {
    public static function all(): array {
        return self::query('SELECT * FROM products ORDER BY created_at DESC');
    }

    public static function find(int $id): ?array {
        return self::queryOne('SELECT * FROM products WHERE id = ?', [$id]);
    }
}
```

---

## Deploying to Namecheap Shared Hosting

1. Upload all project files to your hosting root (e.g. `public_html/myproject/`).
2. In cPanel → **Subdomains / Document Root**, point the domain to `myproject/public/`.
3. Create a `.env` file on the server (copy `.env.example`, fill in real DB credentials).
4. Make `storage/logs/` writable: `chmod 775 storage/logs`.
5. Ensure `mod_rewrite` is enabled (it is on Namecheap by default).
6. Visit your domain — the home page should appear. ✅

---

## Key Helpers

| Function | Description |
|---|---|
| `env($key, $default)` | Read a `.env` value |
| `base_path($path)` | Absolute path from project root |
| `view_path($path)` | Absolute path inside `/app/views/` |
| `asset($path)` | URL to `/public/assets/` file |
| `url($path)` | Full URL from APP_URL |
| `e($string)` | HTML-safe output (XSS escape) |
| `dd(...$values)` | Dump & die (debug only) |
