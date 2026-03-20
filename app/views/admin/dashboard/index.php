<?php
/** @var array<string,mixed> $stats */
/** @var list<array<string,mixed>> $recentOrders */
/** @var list<array<string,mixed>> $topProducts */
/** @var string $period */
/** @var string $chartData JSON-encoded chart datasets */

$statusMap = [
    'pending'    => ['label' => 'En attente',   'class' => 'bg-amber-100 text-amber-700'],
    'paid'       => ['label' => 'Payée',         'class' => 'bg-blue-100 text-blue-700'],
    'processing' => ['label' => 'En traitement','class' => 'bg-indigo-100 text-indigo-700'],
    'shipped'    => ['label' => 'Expédiée',      'class' => 'bg-purple-100 text-purple-700'],
    'completed'  => ['label' => 'Complétée',     'class' => 'bg-forest-100 text-forest-700'],
    'cancelled'  => ['label' => 'Annulée',       'class' => 'bg-red-100 text-red-600'],
];
?>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

<div class="space-y-8" x-data="adminDashboard()" x-init="init()">

    <!-- ── Header + Period filter ── -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <p class="text-xs font-bold uppercase tracking-widest text-vanilla-400 mb-0.5">Bienvenue 👋</p>
            <h1 class="font-serif font-bold text-vanilla-900 text-2xl">Tableau de bord</h1>
        </div>

        <!-- Period pills -->
        <div class="flex gap-1 bg-vanilla-100 rounded-xl p-1 self-start sm:self-auto">
            <?php foreach (['daily' => 'Jour', 'monthly' => 'Mois', 'yearly' => 'Année'] as $key => $label): ?>
            <button
                type="button"
                @click="setPeriod('<?= $key ?>')"
                :class="period === '<?= $key ?>'
                    ? 'bg-vanilla-800 text-cream-100 shadow'
                    : 'text-vanilla-500 hover:text-vanilla-800'"
                class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all duration-200"
            ><?= $label ?></button>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ── KPI Cards ── -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
        <?php
        $kpis = [
            [
                'label'  => 'Chiffre d\'affaires',
                'value'  => number_format($stats['revenue'], 2, ',', ' ') . ' €',
                'icon'   => '💰',
                'color'  => 'from-gold-300/30 to-gold-100/10 border-gold-200',
                'sub'    => $stats['total_orders'] . ' commande' . ($stats['total_orders'] !== 1 ? 's' : '') . ' payées',
                'change' => null,
            ],
            [
                'label'  => 'Commandes ce mois',
                'value'  => (string)$stats['orders_this_month'],
                'icon'   => '📦',
                'color'  => 'from-vanilla-200/40 to-vanilla-100/10 border-vanilla-200',
                'sub'    => ($stats['order_change'] >= 0 ? '+' : '') . $stats['order_change'] . '% vs mois dernier',
                'change' => $stats['order_change'],
            ],
            [
                'label'  => 'Clients inscrits',
                'value'  => (string)$stats['customers'],
                'icon'   => '👥',
                'color'  => 'from-forest-100/40 to-forest-50/10 border-forest-200',
                'sub'    => 'Comptes enregistrés',
                'change' => null,
            ],
            [
                'label'  => 'En attente',
                'value'  => (string)$stats['pending'],
                'icon'   => '⏳',
                'color'  => $stats['pending'] > 0
                    ? 'from-amber-100/40 to-amber-50/10 border-amber-300'
                    : 'from-cream-200/40 to-cream-100/10 border-vanilla-200',
                'sub'    => 'Commande' . ($stats['pending'] !== 1 ? 's' : '') . ' à traiter',
                'change' => null,
            ],
        ];
        ?>
        <?php foreach ($kpis as $i => $kpi): ?>
        <div
            class="bg-gradient-to-br <?= $kpi['color'] ?> border rounded-2xl p-5 flex items-start gap-4 shadow-soft animate__animated animate__fadeInUp"
            style="animation-delay:<?= $i * 80 ?>ms"
        >
            <span class="text-3xl mt-0.5 shrink-0"><?= $kpi['icon'] ?></span>
            <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold text-vanilla-500 uppercase tracking-widest mb-1"><?= $kpi['label'] ?></p>
                <p class="font-serif font-bold text-vanilla-900 text-2xl leading-none mb-1"><?= $kpi['value'] ?></p>
                <p class="text-xs text-vanilla-400 flex items-center gap-1">
                    <?php if ($kpi['change'] !== null): ?>
                        <?php if ($kpi['change'] >= 0): ?>
                        <span class="text-forest-600 font-semibold">▲ <?= abs($kpi['change']) ?>%</span>
                        <?php else: ?>
                        <span class="text-red-500 font-semibold">▼ <?= abs($kpi['change']) ?>%</span>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?= $kpi['sub'] ?>
                </p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- ── Charts: Row 1 — Sales over time (wide) + Revenue breakdown (narrow) ── -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Sales over time -->
        <div class="lg:col-span-2 bg-white/80 backdrop-blur-md border border-vanilla-200/60 rounded-2xl shadow-soft p-6">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h2 class="font-semibold text-vanilla-800">Ventes dans le temps</h2>
                    <p class="text-xs text-vanilla-400 mt-0.5">Revenus & commandes</p>
                </div>
                <!-- Loading spinner -->
                <div x-show="loading" class="w-5 h-5 rounded-full border-2 border-vanilla-300 border-t-vanilla-700 animate-spin"></div>
            </div>
            <div class="relative h-64">
                <canvas id="chart-sales"></canvas>
            </div>
        </div>

        <!-- Revenue breakdown (doughnut) -->
        <div class="bg-white/80 backdrop-blur-md border border-vanilla-200/60 rounded-2xl shadow-soft p-6">
            <h2 class="font-semibold text-vanilla-800 mb-1">Répartition revenus</h2>
            <p class="text-xs text-vanilla-400 mb-5">Par statut de commande</p>
            <div class="relative h-52 flex items-center justify-center">
                <canvas id="chart-revenue"></canvas>
            </div>
        </div>
    </div>

    <!-- ── Charts: Row 2 — Top Products (bar) + Conversion Rate (line) ── -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Top products -->
        <div class="bg-white/80 backdrop-blur-md border border-vanilla-200/60 rounded-2xl shadow-soft p-6">
            <h2 class="font-semibold text-vanilla-800 mb-1">Top produits</h2>
            <p class="text-xs text-vanilla-400 mb-5">Par chiffre d'affaires</p>
            <div class="relative h-64">
                <canvas id="chart-products"></canvas>
            </div>
        </div>

        <!-- Conversion rate -->
        <div class="bg-white/80 backdrop-blur-md border border-vanilla-200/60 rounded-2xl shadow-soft p-6">
            <h2 class="font-semibold text-vanilla-800 mb-1">Taux de conversion</h2>
            <p class="text-xs text-vanilla-400 mb-5">Inscriptions → acheteurs</p>
            <div class="relative h-64">
                <canvas id="chart-conversion"></canvas>
            </div>
        </div>
    </div>

    <!-- ── Recent Orders ── -->
    <div class="bg-white/80 backdrop-blur-md border border-vanilla-200/60 rounded-2xl shadow-soft overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-vanilla-100">
            <h2 class="font-semibold text-vanilla-800 text-sm">Commandes récentes</h2>
            <a href="<?= locale_url('admin/orders') ?>" class="text-xs text-vanilla-500 hover:text-vanilla-800 font-semibold transition-colors">Voir toutes →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-xs uppercase tracking-wider text-vanilla-400 bg-cream-50">
                    <tr>
                        <th class="px-4 py-2.5 text-left">Référence</th>
                        <th class="px-4 py-2.5 text-left">Client</th>
                        <th class="px-4 py-2.5 text-right">Total</th>
                        <th class="px-4 py-2.5 text-center">Statut</th>
                        <th class="px-4 py-2.5 text-right">Date</th>
                        <th class="px-4 py-2.5 w-8"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-vanilla-50">
                <?php foreach ($recentOrders as $order): ?>
                <?php [$cls, $lbl] = array_values($statusMap[$order['status']] ?? ['bg-gray-100 text-gray-500', $order['status']]); ?>
                <tr class="hover:bg-cream-50 transition-colors">
                    <td class="px-4 py-3 font-mono text-xs font-bold text-vanilla-600"><?= e($order['reference']) ?></td>
                    <td class="px-4 py-3">
                        <p class="font-medium text-vanilla-800"><?= e($order['billing_name']) ?></p>
                        <p class="text-xs text-vanilla-400"><?= e($order['billing_email']) ?></p>
                    </td>
                    <td class="px-4 py-3 text-right font-bold text-vanilla-800"><?= number_format((float)$order['total'], 2, ',', ' ') ?> €</td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-block text-[11px] font-semibold px-2.5 py-0.5 rounded-full <?= $cls ?>"><?= $lbl ?></span>
                    </td>
                    <td class="px-4 py-3 text-right text-xs text-vanilla-400"><?= date('d/m/Y', strtotime($order['created_at'])) ?></td>
                    <td class="px-4 py-3">
                        <a href="<?= locale_url("admin/orders/{$order['id']}") ?>"
                           class="p-1 rounded hover:bg-vanilla-100 text-vanilla-400 hover:text-vanilla-700 transition-colors inline-block">→</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($recentOrders)): ?>
                <tr><td colspan="6" class="py-10 text-center text-vanilla-400 text-xs">Aucune commande pour le moment.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ── Quick actions ── -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
    <?php foreach ([
        ['href' => 'admin/products/create', 'label' => '+ Nouveau produit',   'icon' => '📦'],
        ['href' => 'admin/orders',           'label' => 'Gérer commandes',    'icon' => '🛍️'],
        ['href' => 'admin/content',          'label' => 'Éditeur contenu',    'icon' => '✏️'],
        ['href' => 'admin/settings',         'label' => 'Paramètres',         'icon' => '⚙️'],
    ] as $a): ?>
    <a href="<?= locale_url($a['href']) ?>"
       class="flex items-center gap-3 p-4 bg-white/80 border border-vanilla-200/60 rounded-xl hover:border-vanilla-400 hover:shadow-soft transition-all duration-200 group">
        <span class="text-2xl"><?= $a['icon'] ?></span>
        <span class="text-sm font-semibold text-vanilla-700 group-hover:text-vanilla-900"><?= $a['label'] ?></span>
    </a>
    <?php endforeach; ?>
    </div>

</div><!-- /x-data -->

<!-- ── Alpine component + Chart.js logic ── -->
<script>
(function () {
    // Design tokens for consistent chart colours
    const PALETTE = {
        brown:    { solid: '#4E342E', light: 'rgba(78,52,46,0.12)'  },
        green:    { solid: '#6A8F4E', light: 'rgba(106,143,78,0.15)' },
        gold:     { solid: '#C8A96A', light: 'rgba(200,169,106,0.2)' },
        cream:    { solid: '#DEB99E', light: 'rgba(222,185,158,0.2)' },
        indigo:   { solid: '#6366F1', light: 'rgba(99,102,241,0.15)' },
        red:      { solid: '#EF4444', light: 'rgba(239,68,68,0.15)'  },
    };

    const DONUT_COLORS = [
        PALETTE.green.solid,
        PALETTE.brown.solid,
        PALETTE.gold.solid,
        PALETTE.indigo.solid,
        PALETTE.cream.solid,
        PALETTE.red.solid,
    ];

    const CHART_FONT = { family: 'Inter, system-ui, sans-serif', size: 11 };

    // Initial server-rendered data — avoids first AJAX call
    const INITIAL_DATA  = <?= $chartData ?? '{}' ?>;
    const CHARTS_URL    = '<?= locale_url('admin/charts') ?>';

    // Global chart instances (so we can destroy/recreate on period change)
    let chartSales, chartRevenue, chartProducts, chartConversion;

    function buildSalesChart(data) {
        const ctx = document.getElementById('chart-sales');
        if (!ctx) return;
        if (chartSales) chartSales.destroy();

        chartSales = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [
                    {
                        type: 'bar',
                        label: 'Revenus (€)',
                        data: data.revenue,
                        backgroundColor: PALETTE.brown.light,
                        borderColor: PALETTE.brown.solid,
                        borderWidth: 2,
                        borderRadius: 6,
                        borderSkipped: false,
                        yAxisID: 'yRevenue',
                        order: 2,
                    },
                    {
                        type: 'line',
                        label: 'Commandes',
                        data: data.orders,
                        borderColor: PALETTE.green.solid,
                        backgroundColor: PALETTE.green.light,
                        borderWidth: 2.5,
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: PALETTE.green.solid,
                        fill: true,
                        yAxisID: 'yOrders',
                        order: 1,
                    },
                ],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'top', labels: { font: CHART_FONT, usePointStyle: true, padding: 16 } },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => {
                                const v = ctx.parsed.y;
                                return ctx.dataset.yAxisID === 'yRevenue'
                                    ? ` ${v.toLocaleString('fr-FR', { minimumFractionDigits: 2 })} €`
                                    : ` ${v} commande${v !== 1 ? 's' : ''}`;
                            }
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: CHART_FONT } },
                    yRevenue: {
                        position: 'left',
                        grid: { color: 'rgba(0,0,0,0.04)' },
                        ticks: { font: CHART_FONT, callback: v => v + ' €' },
                    },
                    yOrders: {
                        position: 'right',
                        grid: { drawOnChartArea: false },
                        ticks: { font: CHART_FONT, stepSize: 1 },
                    },
                },
            },
        });
    }

    function buildRevenueChart(data) {
        const ctx = document.getElementById('chart-revenue');
        if (!ctx) return;
        if (chartRevenue) chartRevenue.destroy();

        chartRevenue = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: data.labels,
                datasets: [{
                    data: data.values,
                    backgroundColor: DONUT_COLORS,
                    borderColor: '#fff',
                    borderWidth: 3,
                    hoverOffset: 8,
                }],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { font: CHART_FONT, usePointStyle: true, padding: 10 },
                    },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => {
                                const v = ctx.parsed;
                                const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                const pct = total > 0 ? ((v / total) * 100).toFixed(1) : 0;
                                return ` ${v.toLocaleString('fr-FR', { minimumFractionDigits: 2 })} € (${pct}%)`;
                            }
                        }
                    }
                },
            },
        });
    }

    function buildProductsChart(data) {
        const ctx = document.getElementById('chart-products');
        if (!ctx) return;
        if (chartProducts) chartProducts.destroy();

        chartProducts = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [
                    {
                        label: 'Revenus (€)',
                        data: data.revenue,
                        backgroundColor: data.labels.map((_, i) =>
                            i === 0 ? PALETTE.gold.solid : PALETTE.brown.light),
                        borderColor: data.labels.map((_, i) =>
                            i === 0 ? PALETTE.gold.solid : PALETTE.brown.solid),
                        borderWidth: 2,
                        borderRadius: 6,
                        borderSkipped: false,
                    },
                ],
            },
            options: {
                indexAxis: 'y',
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) =>
                                ` ${Number(ctx.parsed.x).toLocaleString('fr-FR', { minimumFractionDigits: 2 })} €`
                        }
                    }
                },
                scales: {
                    x: { grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { font: CHART_FONT, callback: v => v + ' €' } },
                    y: { grid: { display: false }, ticks: { font: CHART_FONT } },
                },
            },
        });
    }

    function buildConversionChart(data) {
        const ctx = document.getElementById('chart-conversion');
        if (!ctx) return;
        if (chartConversion) chartConversion.destroy();

        chartConversion = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [
                    {
                        label: 'Taux (%)',
                        data: data.rate,
                        borderColor: PALETTE.indigo.solid,
                        backgroundColor: 'rgba(99,102,241,0.08)',
                        borderWidth: 2.5,
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: PALETTE.indigo.solid,
                        fill: true,
                        yAxisID: 'yRate',
                    },
                    {
                        label: 'Inscriptions',
                        data: data.visitors,
                        borderColor: PALETTE.brown.solid,
                        borderWidth: 2,
                        borderDash: [5, 4],
                        tension: 0.4,
                        pointRadius: 3,
                        fill: false,
                        yAxisID: 'yCount',
                    },
                ],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'top', labels: { font: CHART_FONT, usePointStyle: true, padding: 14 } },
                    tooltip: {
                        callbacks: {
                            label: (ctx) =>
                                ctx.dataset.yAxisID === 'yRate'
                                    ? ` ${ctx.parsed.y}%`
                                    : ` ${ctx.parsed.y} inscriptions`
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: CHART_FONT } },
                    yRate:  {
                        position: 'left',
                        max: 100, min: 0,
                        ticks: { font: CHART_FONT, callback: v => v + '%' },
                        grid: { color: 'rgba(0,0,0,0.04)' },
                    },
                    yCount: {
                        position: 'right',
                        grid: { drawOnChartArea: false },
                        ticks: { font: CHART_FONT, stepSize: 1 },
                    },
                },
            },
        });
    }

    function renderAll(data) {
        buildSalesChart(data.salesOverTime    || { labels: [], revenue: [], orders: [] });
        buildRevenueChart(data.revenueBreakdown || { labels: [], values: [] });
        buildProductsChart(data.topProducts   || { labels: [], revenue: [], qty: [] });
        buildConversionChart(data.conversionRate || { labels: [], rate: [], visitors: [], converted: [] });
    }

    // ── Alpine component ──────────────────────────────────────────
    window.adminDashboard = function () {
        return {
            period: '<?= e($period) ?>',
            loading: false,

            init() {
                // Render with server-embedded data — instant, no flicker
                renderAll(INITIAL_DATA);
            },

            async setPeriod(p) {
                if (p === this.period) return;
                this.period = p;
                this.loading = true;
                try {
                    const res  = await fetch(`${CHARTS_URL}?period=${p}`);
                    const data = await res.json();
                    renderAll(data);
                } catch (e) {
                    console.error('Chart fetch error', e);
                } finally {
                    this.loading = false;
                }
            },
        };
    };
})();
</script>
