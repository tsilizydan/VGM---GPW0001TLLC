<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Request;
use Core\Auth;

/**
 * Protected dashboard — requires authentication.
 */
class DashboardController extends Controller
{
    /**
     * GET /dashboard
     */
    public function index(Request $request): void
    {
        $this->requireAuth();

        $user = Auth::user();

        $this->render('dashboard/index', [
            'title' => 'Tableau de bord',
            'user'  => $user,
        ]);
    }
}
