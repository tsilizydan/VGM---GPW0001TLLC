<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Controller;
use Core\Request;
use Core\Model;

/**
 * Admin — Customer (user) list and detail.
 */
class CustomerController extends Controller
{
    public function index(Request $request): void
    {
        $this->requireAuth(); $this->requireRole('admin');

        $page    = max(1, (int) $request->input('page', 1));
        $search  = $request->input('q', '');
        $perPage = 25;

        $where  = '1=1';
        $params = [];
        if ($search !== '') {
            $where  .= ' AND (u.name LIKE ? OR u.email LIKE ?)';
            $s       = "%{$search}%";
            $params  = [$s, $s];
        }

        $total  = (int) (Model::rawQuery("SELECT COUNT(*) AS c FROM users u WHERE {$where}", $params)[0]['c'] ?? 0);
        $offset = ($page - 1) * $perPage;

        $customers = Model::rawQuery(
            "SELECT u.id, u.name, u.email, u.role, u.created_at,
                    COUNT(o.id) AS order_count,
                    COALESCE(SUM(o.total), 0) AS lifetime_value
             FROM users u
             LEFT JOIN orders o ON o.billing_email = u.email AND o.status NOT IN ('cancelled','pending')
             WHERE {$where}
             GROUP BY u.id
             ORDER BY u.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $this->render('admin/customers/index', [
            'title'      => 'Clients',
            'customers'  => $customers,
            'filters'    => ['q' => $search, 'page' => $page],
            'pagination' => ['total' => $total, 'pages' => (int) ceil($total / $perPage), 'page' => $page],
        ], 'admin');
    }

    public function show(Request $request): void
    {
        $this->requireAuth(); $this->requireRole('admin');

        $id   = (int) $request->routeParam('id');
        $user = Model::rawQuery('SELECT * FROM users WHERE id = ?', [$id])[0] ?? null;
        if (!$user) \Core\Response::abort(404);

        $orders = Model::rawQuery(
            "SELECT id, reference, total, status, created_at FROM orders
             WHERE billing_email = ?
             ORDER BY created_at DESC LIMIT 20",
            [$user['email']]
        );

        $this->render('admin/customers/show', [
            'title'   => 'Client : ' . e($user['name']),
            'user'    => $user,
            'orders'  => $orders,
        ], 'admin');
    }
}
