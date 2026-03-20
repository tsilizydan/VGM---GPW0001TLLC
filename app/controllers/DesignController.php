<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Request;

class DesignController extends Controller
{
    /** GET /design — component styleguide (dev only) */
    public function index(Request $request): void
    {
        if (env('APP_ENV', 'local') === 'production') {
            \Core\Response::abort(404, 'Not Found');
        }
        $this->render('design/index', ['title' => 'Design System'], 'app');
    }
}
