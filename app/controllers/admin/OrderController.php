<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Controller;
use Core\Request;
use Core\Model;
use Core\Session;
use Core\Csrf;

/**
 * Admin — Order management (list + detail + status update).
 */
class OrderController extends Controller
{
    private const STATUSES = [
        'pending'    => ['label' => 'En attente',   'class' => 'bg-amber-100 text-amber-700'],
        'paid'       => ['label' => 'Payée',         'class' => 'bg-blue-100 text-blue-700'],
        'processing' => ['label' => 'En traitement','class' => 'bg-indigo-100 text-indigo-700'],
        'shipped'    => ['label' => 'Expédiée',      'class' => 'bg-purple-100 text-purple-700'],
        'completed'  => ['label' => 'Complétée',     'class' => 'bg-forest-100 text-forest-700'],
        'cancelled'  => ['label' => 'Annulée',       'class' => 'bg-red-100 text-red-600'],
    ];

    public function index(Request $request): void
    {
        $this->requireAuth(); $this->requireRole('admin');

        $page     = max(1, (int) $request->input('page', 1));
        $status   = $request->input('status', '');
        $search   = $request->input('q', '');
        $perPage  = 20;

        // Build WHERE
        $where  = '1=1';
        $params = [];
        if ($status !== '') { $where .= ' AND o.status = ?'; $params[] = $status; }
        if ($search !== '') {
            $where .= ' AND (o.reference LIKE ? OR o.billing_name LIKE ? OR o.billing_email LIKE ?)';
            $s = "%{$search}%";
            $params = array_merge($params, [$s, $s, $s]);
        }

        $total  = (int) (Model::rawQuery("SELECT COUNT(*) AS c FROM orders o WHERE {$where}", $params)[0]['c'] ?? 0);
        $offset = ($page - 1) * $perPage;

        $orders = Model::rawQuery(
            "SELECT o.id, o.reference, o.billing_name, o.billing_email,
                    o.total, o.status, o.created_at,
                    (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) AS items_count
             FROM orders o
             WHERE {$where}
             ORDER BY o.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $this->render('admin/orders/index', [
            'title'    => 'Commandes',
            'orders'   => $orders,
            'statuses' => self::STATUSES,
            'filters'  => ['status' => $status, 'q' => $search, 'page' => $page],
            'pagination'=> ['total' => $total, 'pages' => (int) ceil($total / $perPage), 'page' => $page],
        ], 'admin');
    }

    public function show(Request $request): void
    {
        $this->requireAuth(); $this->requireRole('admin');

        $id = (int) $request->routeParam('id');
        $order = \App\Models\Order::find($id);
        if (!$order) \Core\Response::abort(404);

        $this->render('admin/orders/show', [
            'title'    => 'Commande ' . $order['reference'],
            'order'    => $order,
            'statuses' => self::STATUSES,
        ], 'admin');
    }

    public function updateStatus(Request $request): void
    {
        $this->requireAuth(); $this->requireRole('admin');
        if (!Csrf::validate($request->input('_token', ''))) \Core\Response::abort(403);

        $id     = (int) $request->routeParam('id');
        $status = $request->input('status', '');
        if (!array_key_exists($status, self::STATUSES)) \Core\Response::abort(422);

        \App\Models\Order::updateStatus($id, $status);
        Session::flash('success', 'Statut mis à jour.');
        header('Location: ' . locale_url("admin/orders/{$id}")); exit;
    }
}
