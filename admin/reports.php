<?php
$page_title = 'Reports & Analytics';
require_once __DIR__ . '/includes/admin_header.php';

// Date range
$from = sanitize($_GET['from'] ?? date('Y-m-01'));
$to   = sanitize($_GET['to']   ?? date('Y-m-d'));

// Revenue in range
$revenue = $conn->query("SELECT COALESCE(SUM(total_amount),0) as r, COUNT(*) as c FROM orders WHERE DATE(created_at) BETWEEN '$from' AND '$to' AND status != 'cancelled'")->fetch_assoc();

// Daily revenue for chart
$daily = $conn->query("SELECT DATE(created_at) as day, SUM(total_amount) as rev, COUNT(*) as cnt FROM orders WHERE DATE(created_at) BETWEEN '$from' AND '$to' AND status != 'cancelled' GROUP BY DATE(created_at) ORDER BY day ASC")->fetch_all(MYSQLI_ASSOC);
$chart_days = array_column($daily, 'day');
$chart_rev  = array_column($daily, 'rev');

// Top selling products in range
$top_products = $conn->query("SELECT p.name, SUM(oi.quantity) as sold, SUM(oi.quantity*oi.price) as revenue FROM order_items oi JOIN products p ON oi.product_id=p.id JOIN orders o ON oi.order_id=o.id WHERE DATE(o.created_at) BETWEEN '$from' AND '$to' AND o.status != 'cancelled' GROUP BY oi.product_id ORDER BY sold DESC LIMIT 10")->fetch_all(MYSQLI_ASSOC);

// Top categories
$top_cats = $conn->query("SELECT c.name, SUM(oi.quantity) as sold, SUM(oi.quantity*oi.price) as revenue FROM order_items oi JOIN products p ON oi.product_id=p.id JOIN categories c ON p.category_id=c.id JOIN orders o ON oi.order_id=o.id WHERE DATE(o.created_at) BETWEEN '$from' AND '$to' AND o.status != 'cancelled' GROUP BY p.category_id ORDER BY revenue DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);

// Orders by status in range
$status_counts = [];
foreach (['pending','processing','shipped','delivered','cancelled'] as $s) {
    $status_counts[$s] = (int)$conn->query("SELECT COUNT(*) as c FROM orders WHERE status='$s' AND DATE(created_at) BETWEEN '$from' AND '$to'")->fetch_assoc()['c'];
}

// New customers in range
$new_customers = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='customer' AND DATE(created_at) BETWEEN '$from' AND '$to'")->fetch_assoc()['c'];
?>

<div class="admin-page-header">
    <div>
        <h1>Reports & Analytics</h1>
        <div class="admin-breadcrumb"><a href="<?= SITE_URL ?>/admin/dashboard.php">Dashboard</a> / Reports</div>
    </div>
</div>

<!-- Date Filter -->
<div class="admin-card" style="margin-bottom:24px;">
    <div class="admin-card-body">
        <form method="GET" style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
            <div style="display:flex;align-items:center;gap:8px;">
                <label style="font-size:14px;font-weight:600;color:#444;">From:</label>
                <input type="date" name="from" class="form-control" value="<?= $from ?>" style="width:160px;">
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
                <label style="font-size:14px;font-weight:600;color:#444;">To:</label>
                <input type="date" name="to" class="form-control" value="<?= $to ?>" style="width:160px;">
            </div>
            <button type="submit" class="btn btn-gold"><i class="fas fa-filter"></i> Apply Filter</button>
            <div style="display:flex;gap:8px;margin-left:auto;">
                <a href="?from=<?= date('Y-m-d') ?>&to=<?= date('Y-m-d') ?>" class="btn btn-outline btn-sm">Today</a>
                <a href="?from=<?= date('Y-m-01') ?>&to=<?= date('Y-m-d') ?>" class="btn btn-outline btn-sm">This Month</a>
                <a href="?from=<?= date('Y-01-01') ?>&to=<?= date('Y-m-d') ?>" class="btn btn-outline btn-sm">This Year</a>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="admin-stats-grid" style="margin-bottom:24px;">
    <div class="admin-stat-card">
        <div class="stat-icon gold"><i class="fas fa-coins"></i></div>
        <div class="stat-info">
            <h3><?= formatPrice($revenue['r']) ?></h3>
            <p>Total Revenue</p>
        </div>
    </div>
    <div class="admin-stat-card">
        <div class="stat-icon green"><i class="fas fa-shopping-cart"></i></div>
        <div class="stat-info">
            <h3><?= number_format($revenue['c']) ?></h3>
            <p>Total Orders</p>
        </div>
    </div>
    <div class="admin-stat-card">
        <div class="stat-icon blue"><i class="fas fa-chart-line"></i></div>
        <div class="stat-info">
            <h3><?= $revenue['c'] > 0 ? formatPrice($revenue['r'] / $revenue['c']) : 'ETB 0' ?></h3>
            <p>Avg. Order Value</p>
        </div>
    </div>
    <div class="admin-stat-card">
        <div class="stat-icon red"><i class="fas fa-user-plus"></i></div>
        <div class="stat-info">
            <h3><?= number_format($new_customers) ?></h3>
            <p>New Customers</p>
        </div>
    </div>
</div>

<!-- Revenue Chart -->
<div class="admin-card" style="margin-bottom:24px;">
    <div class="admin-card-header">
        <h3><i class="fas fa-chart-area" style="color:#D4AF37;margin-right:8px;"></i> Daily Revenue</h3>
    </div>
    <div class="admin-card-body">
        <div class="chart-container" style="height:280px;">
            <canvas id="dailyChart"></canvas>
        </div>
    </div>
</div>

<!-- Tables Row -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
    <!-- Top Products -->
    <div class="admin-card">
        <div class="admin-card-header"><h3><i class="fas fa-trophy" style="color:#D4AF37;margin-right:8px;"></i> Top Products</h3></div>
        <div class="admin-card-body" style="padding:0;">
            <table class="admin-table">
                <thead><tr><th>#</th><th>Product</th><th>Sold</th><th>Revenue</th></tr></thead>
                <tbody>
                <?php foreach ($top_products as $i => $tp): ?>
                <tr>
                    <td style="color:#D4AF37;font-weight:700;"><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars($tp['name']) ?></td>
                    <td><span class="badge badge-info"><?= $tp['sold'] ?></span></td>
                    <td style="color:#D4AF37;font-weight:600;"><?= formatPrice($tp['revenue']) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($top_products)): ?>
                <tr><td colspan="4" style="text-align:center;padding:20px;color:#999;">No data for this period</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Orders by Status + Top Categories -->
    <div>
        <div class="admin-card" style="margin-bottom:20px;">
            <div class="admin-card-header"><h3><i class="fas fa-chart-pie" style="color:#D4AF37;margin-right:8px;"></i> Orders by Status</h3></div>
            <div class="admin-card-body">
                <?php foreach ($status_counts as $s => $c): ?>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                    <span style="font-size:14px;text-transform:capitalize;"><?= $s ?></span>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:120px;height:6px;background:#eee;border-radius:3px;overflow:hidden;">
                            <div style="height:100%;background:#D4AF37;width:<?= $revenue['c'] > 0 ? round($c/$revenue['c']*100) : 0 ?>%;border-radius:3px;"></div>
                        </div>
                        <span class="badge badge-secondary"><?= $c ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="admin-card">
            <div class="admin-card-header"><h3><i class="fas fa-tags" style="color:#D4AF37;margin-right:8px;"></i> Top Categories</h3></div>
            <div class="admin-card-body" style="padding:0;">
                <table class="admin-table">
                    <thead><tr><th>Category</th><th>Sold</th><th>Revenue</th></tr></thead>
                    <tbody>
                    <?php foreach ($top_cats as $tc): ?>
                    <tr>
                        <td><?= htmlspecialchars($tc['name']) ?></td>
                        <td><?= $tc['sold'] ?></td>
                        <td style="color:#D4AF37;font-weight:600;"><?= formatPrice($tc['revenue']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($top_cats)): ?>
                    <tr><td colspan="3" style="text-align:center;padding:20px;color:#999;">No data</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
new Chart(document.getElementById('dailyChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($chart_days) ?>,
        datasets: [{
            label: 'Revenue (ETB)',
            data: <?= json_encode(array_map('floatval', $chart_rev)) ?>,
            backgroundColor: 'rgba(212,175,55,0.7)',
            borderColor: '#D4AF37',
            borderWidth: 1,
            borderRadius: 4
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { callback: v => 'ETB ' + v.toLocaleString() }, grid: { color: '#f0f0f0' } },
            x: { grid: { display: false } }
        }
    }
});
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
