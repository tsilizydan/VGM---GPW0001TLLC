<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Controller;
use Core\Request;
use Core\Model;

/**
 * Admin Dashboard — overview KPIs, chart data endpoint.
 */
class DashboardController extends Controller
{
    public function index(Request $request): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        $period = $request->input('period', 'monthly');  // daily|monthly|yearly
        $stats  = $this->getStats();

        $this->render('admin/dashboard/index', [
            'title'        => 'Tableau de bord',
            'stats'        => $stats,
            'recentOrders' => $this->recentOrders(8),
            'topProducts'  => $this->topProducts(5),
            'period'       => $period,
            // Embed chart data as JSON for initial render (no extra round-trip)
            'chartData'    => json_encode($this->buildChartData($period), JSON_UNESCAPED_UNICODE),
        ], 'admin');
    }

    /**
     * AJAX endpoint — returns chart data as JSON.
     * GET /admin/charts?period=daily|monthly|yearly
     */
    public function charts(Request $request): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        $period = $request->input('period', 'monthly');

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($this->buildChartData($period), JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ── Chart data builder ───────────────────────────────────────

    /**
     * Build all 4 chart datasets for the requested period.
     *
     * @return array<string,mixed>
     */
    private function buildChartData(string $period): array
    {
        return [
            'salesOverTime'    => $this->salesOverTime($period),
            'topProducts'      => $this->topProductsChart(8),
            'conversionRate'   => $this->conversionRate($period),
            'revenueBreakdown' => $this->revenueBreakdown(),
        ];
    }

    /**
     * Sales (revenue + order count) over time.
     *
     * @return array{labels:list<string>, revenue:list<float>, orders:list<int>}
     */
    private function salesOverTime(string $period): array
    {
        [$selectExpr, $groupBy, $interval, $format] = match($period) {
            'daily'   => [
                "DATE_FORMAT(created_at, '%d/%m')",
                "DATE(created_at)",
                "INTERVAL 30 DAY",
                '%d/%m',
            ],
            'yearly'  => [
                "YEAR(created_at)",
                "YEAR(created_at)",
                "INTERVAL 5 YEAR",
                null,
            ],
            default   => [                          // monthly
                "DATE_FORMAT(created_at, '%b %Y')",
                "DATE_FORMAT(created_at, '%Y-%m')",
                "INTERVAL 12 MONTH",
                null,
            ],
        };

        $rows = Model::rawQuery(
            "SELECT
                {$selectExpr}       AS label,
                SUM(total)          AS revenue,
                COUNT(*)            AS order_count
             FROM orders
             WHERE created_at >= DATE_SUB(NOW(), {$interval})
               AND status NOT IN ('cancelled','pending')
             GROUP BY {$groupBy}
             ORDER BY MIN(created_at)"
        );

        return [
            'labels'  => array_column($rows, 'label'),
            'revenue' => array_map(fn($r) => round((float)$r['revenue'], 2), $rows),
            'orders'  => array_map(fn($r) => (int)$r['order_count'], $rows),
        ];
    }

    /**
     * Top products by revenue (bar chart).
     *
     * @return array{labels:list<string>, revenue:list<float>, qty:list<int>}
     */
    private function topProductsChart(int $limit = 8): array
    {
        $rows = Model::rawQuery(
            "SELECT
                COALESCE(pt.name, p.slug) AS name,
                SUM(oi.qty)               AS qty,
                SUM(oi.subtotal)          AS revenue
             FROM order_items oi
             JOIN products p ON p.id = oi.product_id
             LEFT JOIN product_translations pt
                    ON pt.product_id = p.id AND pt.locale = 'fr'
             GROUP BY p.id, pt.name, p.slug
             ORDER BY revenue DESC
             LIMIT ?",
            [$limit]
        );

        return [
            'labels'  => array_column($rows, 'name'),
            'revenue' => array_map(fn($r) => round((float)$r['revenue'], 2), $rows),
            'qty'     => array_map(fn($r) => (int)$r['qty'], $rows),
        ];
    }

    /**
     * Conversion rate: registered users who placed ≥1 order, per period.
     *
     * @return array{labels:list<string>, rate:list<float>, visitors:list<int>, converted:list<int>}
     */
    private function conversionRate(string $period): array
    {
        [$selectExpr, $groupBy, $interval] = match($period) {
            'daily'  => [
                "DATE_FORMAT(created_at, '%d/%m')",
                "DATE(created_at)",
                "INTERVAL 30 DAY",
            ],
            'yearly' => [
                "YEAR(created_at)",
                "YEAR(created_at)",
                "INTERVAL 5 YEAR",
            ],
            default  => [
                "DATE_FORMAT(created_at, '%b %Y')",
                "DATE_FORMAT(created_at, '%Y-%m')",
                "INTERVAL 12 MONTH",
            ],
        };

        // Registrations per period
        $regRows = Model::rawQuery(
            "SELECT {$selectExpr} AS label, COUNT(*) AS registrations
             FROM users
             WHERE created_at >= DATE_SUB(NOW(), {$interval})
             GROUP BY {$groupBy}
             ORDER BY MIN(created_at)"
        );

        // Orders (unique guest + user emails) per period
        $ordRows = Model::rawQuery(
            "SELECT {$selectExpr} AS label, COUNT(DISTINCT billing_email) AS payers
             FROM orders
             WHERE created_at >= DATE_SUB(NOW(), {$interval})
               AND status NOT IN ('cancelled','pending')
             GROUP BY {$groupBy}
             ORDER BY MIN(created_at)"
        );

        // Merge by label
        $payers = array_column($ordRows, 'payers', 'label');
        $labels = array_column($regRows, 'label');
        $regs   = array_column($regRows, 'registrations');
        $rates  = [];
        $converted = [];

        foreach ($regRows as $row) {
            $p = (int)($payers[$row['label']] ?? 0);
            $r = (int)$row['registrations'];
            $converted[] = $p;
            $rates[] = $r > 0 ? round($p / $r * 100, 1) : 0;
        }

        return [
            'labels'    => $labels,
            'rate'      => $rates,
            'visitors'  => $regs,
            'converted' => $converted,
        ];
    }

    /**
     * Revenue breakdown by order status (doughnut chart).
     *
     * @return array{labels:list<string>, values:list<float>}
     */
    private function revenueBreakdown(): array
    {
        $statusLabels = [
            'completed'  => 'Complétées',
            'shipped'    => 'Expédiées',
            'processing' => 'En traitement',
            'paid'       => 'Payées',
            'pending'    => 'En attente',
            'cancelled'  => 'Annulées',
        ];

        $rows = Model::rawQuery(
            "SELECT status, COALESCE(SUM(total), 0) AS revenue, COUNT(*) AS cnt
             FROM orders
             GROUP BY status
             ORDER BY revenue DESC"
        );

        $labels = [];
        $values = [];
        foreach ($rows as $row) {
            $labels[] = $statusLabels[$row['status']] ?? $row['status'];
            $values[] = round((float)$row['revenue'], 2);
        }

        return ['labels' => $labels, 'values' => $values];
    }

    // ── KPI helpers (unchanged) ──────────────────────────────────

    /** @return array<string,mixed> */
    private function getStats(): array
    {
        $revenue = Model::rawQuery(
            "SELECT COALESCE(SUM(total), 0) AS total, COUNT(*) AS count
             FROM orders WHERE status NOT IN ('cancelled','pending')"
        )[0] ?? ['total' => 0, 'count' => 0];

        $ordersThisMonth = (int)(Model::rawQuery(
            "SELECT COUNT(*) AS count FROM orders
             WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())"
        )[0]['count'] ?? 0);

        $ordersLastMonth = (int)(Model::rawQuery(
            "SELECT COUNT(*) AS count FROM orders
             WHERE MONTH(created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
               AND YEAR(created_at)  = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))"
        )[0]['count'] ?? 0);

        $customers = (int)(Model::rawQuery("SELECT COUNT(*) AS count FROM users")[0]['count'] ?? 0);
        $products  = (int)(Model::rawQuery("SELECT COUNT(*) AS count FROM products WHERE status = 'active'")[0]['count'] ?? 0);
        $pending   = (int)(Model::rawQuery("SELECT COUNT(*) AS count FROM orders WHERE status = 'pending'")[0]['count'] ?? 0);

        $orderChange = $ordersLastMonth > 0
            ? round(($ordersThisMonth - $ordersLastMonth) / $ordersLastMonth * 100, 1)
            : 0;

        return [
            'revenue'           => (float)$revenue['total'],
            'total_orders'      => (int)$revenue['count'],
            'orders_this_month' => $ordersThisMonth,
            'order_change'      => $orderChange,
            'customers'         => $customers,
            'products'          => $products,
            'pending'           => $pending,
        ];
    }

    /** @return list<array<string,mixed>> */
    private function recentOrders(int $limit = 8): array
    {
        return Model::rawQuery(
            "SELECT id, reference, billing_name, billing_email, total, status, created_at
             FROM orders ORDER BY created_at DESC LIMIT ?",
            [$limit]
        );
    }

    /** @return list<array<string,mixed>> */
    private function topProducts(int $limit = 5): array
    {
        return Model::rawQuery(
            "SELECT p.id, COALESCE(pt.name, p.slug) AS name,
                    SUM(oi.qty) AS sold, SUM(oi.subtotal) AS revenue
             FROM order_items oi
             JOIN products p ON p.id = oi.product_id
             LEFT JOIN product_translations pt ON pt.product_id = p.id AND pt.locale = 'fr'
             GROUP BY p.id, p.slug, pt.name
             ORDER BY sold DESC LIMIT ?",
            [$limit]
        );
    }
}
