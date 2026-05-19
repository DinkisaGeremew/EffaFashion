<?php
$page_title = 'Dashboard';
require_once __DIR__ . '/includes/admin_header.php';

// Recent orders
$recent_orders = $conn->query("SELECT o.*, u.full_name FROM orders o JOIN users u ON o.user_id=u.id ORDER BY o.created_at DESC LIMIT 8")->fetch_all(MYSQLI_ASSOC);

// Top products
$top_products = $conn->query("SELECT p.name, p.image, SUM(oi.quantity) as sold, SUM(oi.quantity * oi.price) as revenue FROM order_items oi JOIN products p ON oi.product_id=p.id GROUP BY oi.product_id ORDER BY sold DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);

// Monthly revenue for chart (last 6 months)
$chart_labels = $chart_data = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $label = date('M Y', strtotime("-$i months"));
    $rev   = $conn->query("SELECT COALESCE(SUM(total_amount),0) as r FROM orders WHERE DATE_FORMAT(created_at,'%Y-%m')='$month' AND status != 'cancelled'")->fetch_assoc()['r'];
    $chart_labels[] = $label;
    $chart_data[]   = (float)$rev;
}

// Orders by status for pie chart
$status_data = [];
foreach (['pending','processing','shipped','delivered','cancelled'] as $s) {
    $status_data[$s] = (int)$conn->query("SELECT COUNT(*) as c FROM orders WHERE status='$s'")->fetch_assoc()['c'];
}
?>

<!-- Stat Cards -->
<div class="admin-stats-grid">
    <div class="admin-stat-card">
        <div class="stat-icon gold"><i class="fas fa-shopping-cart"></i></div>
        <div class="stat-info">
            <h3><?= number_format($admin_stats['total_orders']) ?></h3>
            <p>Total Orders</p>
            <span class="stat-change up"><i class="fas fa-arrow-up"></i> <?= $admin_stats['pending_orders'] ?> pending</span>
        </div>
    </div>
    <div class="admin-stat-card">
        <div class="stat-icon green"><i class="fas fa-naira-sign"></i></div>
        <div class="stat-info">
            <h3><?= formatPrice($admin_stats['total_revenue']) ?></h3>
            <p>Total Revenue</p>
            <span class="stat-change up"><i class="fas fa-arrow-up"></i> This month: <?= formatPrice($admin_stats['monthly_revenue']) ?></span>
        </div>
    </div>
    <div class="admin-stat-card">
        <div class="stat-icon blue"><i class="fas fa-box"></i></div>
        <div class="stat-info">
            <h3><?= number_format($admin_stats['total_products']) ?></h3>
            <p>Active Products</p>
        </div>
    </div>
    <div class="admin-stat-card">
        <div class="stat-icon red"><i class="fas fa-users"></i></div>
        <div class="stat-info">
            <h3><?= number_format($admin_stats['total_users']) ?></h3>
            <p>Customers</p>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;margin-bottom:24px;">
    <div class="admin-card">
        <div class="admin-card-header">
            <h3><i class="fas fa-chart-line" style="color:#D4AF37;margin-right:8px;"></i> Revenue (Last 6 Months)</h3>
        </div>
        <div class="admin-card-body">
            <div class="chart-container"><canvas id="revenueChart"></canvas></div>
        </div>
    </div>
    <div class="admin-card">
        <div class="admin-card-header">
            <h3><i class="fas fa-chart-pie" style="color:#D4AF37;margin-right:8px;"></i> Orders by Status</h3>
        </div>
        <div class="admin-card-body">
            <div class="chart-container"><canvas id="statusChart"></canvas></div>
        </div>
    </div>
</div>

<!-- Recent Orders + Top Products -->
<div style="display:grid;grid-template-columns:3fr 2fr;gap:24px;">
    <div class="admin-card">
        <div class="admin-card-header">
            <h3><i class="fas fa-clock" style="color:#D4AF37;margin-right:8px;"></i> Recent Orders</h3>
            <a href="<?= SITE_URL ?>/admin/orders.php" class="btn btn-outline btn-sm">View All</a>
        </div>
        <div class="admin-card-body" style="padding:0;">
            <table class="admin-table">
                <thead><tr><th>Order #</th><th>Customer</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                <?php foreach ($recent_orders as $o): ?>
                <tr>
                    <td><a href="<?= SITE_URL ?>/admin/orders.php?id=<?= $o['id'] ?>" style="color:#D4AF37;font-weight:600;"><?= htmlspecialchars($o['order_number']) ?></a></td>
                    <td><?= htmlspecialchars($o['full_name']) ?></td>
                    <td><strong><?= formatPrice($o['total_amount']) ?></strong></td>
                    <td><?= getOrderStatusBadge($o['status']) ?></td>
                    <td style="color:#999;font-size:13px;"><?= date('M d', strtotime($o['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-header">
            <h3><i class="fas fa-fire" style="color:#D4AF37;margin-right:8px;"></i> Top Products</h3>
        </div>
        <div class="admin-card-body" style="padding:0;">
            <table class="admin-table">
                <thead><tr><th>Product</th><th>Sold</th><th>Revenue</th></tr></thead>
                <tbody>
                <?php foreach ($top_products as $tp): ?>
                <tr>
                    <td style="font-size:13px;font-weight:600;"><?= htmlspecialchars($tp['name']) ?></td>
                    <td><?= $tp['sold'] ?></td>
                    <td style="color:#D4AF37;font-weight:600;"><?= formatPrice($tp['revenue']) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($top_products)): ?>
                <tr><td colspan="3" style="text-align:center;color:#999;padding:20px;">No sales data yet</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Revenue Chart
new Chart(document.getElementById('revenueChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode($chart_labels) ?>,
        datasets: [{
            label: 'Revenue (ETB)',
            data: <?= json_encode($chart_data) ?>,
            borderColor: '#D4AF37',
            backgroundColor: 'rgba(212,175,55,0.1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#D4AF37',
            pointRadius: 5
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, grid: { color: '#f0f0f0' }, ticks: { callback: v => 'ETB ' + v.toLocaleString() } },
            x: { grid: { display: false } }
        }
    }
});

// Status Pie Chart
new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
        labels: ['Pending','Processing','Shipped','Delivered','Cancelled'],
        datasets: [{
            data: <?= json_encode(array_values($status_data)) ?>,
            backgroundColor: ['#ffc107','#17a2b8','#007bff','#28a745','#dc3545'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom', labels: { padding: 12, font: { size: 12 } } } },
        cutout: '65%'
    }
});
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
