<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Request;

/**
 * Handles the public home page.
 */
class HomeController extends Controller
{
    /**
     * GET /
     */
    public function index(Request $request): void
    {
        $this->render('home/index', [
            'title'   => env('APP_NAME', 'Vanilla Groupe Madagascar'),
            'tagline' => 'Excellence naturelle depuis Madagascar',
        ]);
    }
}
