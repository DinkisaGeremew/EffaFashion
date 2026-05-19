<?php
$page_title = 'Customers';
require_once __DIR__ . '/includes/admin_header.php';

// Toggle active status
if (isset($_GET['toggle'])) {
    $uid = (int)$_GET['toggle'];
    $conn->query("UPDATE users SET is_active = 1 - is_active WHERE id=$uid AND role='customer'");
    setFlash('success', 'User status updated.');
    header('Location: ' . SITE_URL . '/admin/users.php');
    exit;
}

$search = sanitize($_GET['search'] ?? '');
$where  = "WHERE role='customer'";
if ($search) $where .= " AND (full_name LIKE '%" . $conn->real_escape_string($search) . "%' OR email LIKE '%" . $conn->real_escape_string($search) . "%')";

$users = $conn->query("SELECT u.*, (SELECT COUNT(*) FROM orders WHERE user_id=u.id) as order_count, (SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE user_id=u.id AND status != 'cancelled') as total_spent FROM users u $where ORDER BY u.created_at DESC")->fetch_all(MYSQLI_ASSOC);
?>

<div class="admin-page-header">
    <div>
        <h1>Customers</h1>
        <div class="admin-breadcrumb"><a href="<?= SITE_URL ?>/admin/dashboard.php">Dashboard</a> / Customers</div>
    </div>
</div>

<div class="admin-card">
    <div class="admin-card-header">
        <h3>All Customers (<?= count($users) ?>)</h3>
        <form method="GET" style="display:flex;gap:8px;">
            <input type="text" name="search" class="form-control" placeholder="Search by name or email..." value="<?= htmlspecialchars($search) ?>" style="width:260px;">
            <button type="submit" class="btn btn-dark btn-sm"><i class="fas fa-search"></i></button>
            <?php if ($search): ?><a href="<?= SITE_URL ?>/admin/users.php" class="btn btn-outline btn-sm">Clear</a><?php endif; ?>
        </form>
    </div>
    <div class="admin-card-body" style="padding:0;overflow-x:auto;">
        <table class="admin-table">
            <thead>
                <tr><th>Name</th><th>Email</th><th>Phone</th><th>Orders</th><th>Total Spent</th><th>Joined</th><th>Status</th><th>Action</th></tr>
            </thead>
            <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
                <td>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:36px;height:36px;border-radius:50%;background:rgba(212,175,55,0.15);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i class="fas fa-user" style="color:#D4AF37;font-size:14px;"></i>
                        </div>
                        <strong><?= htmlspecialchars($u['full_name']) ?></strong>
                    </div>
                </td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars($u['phone'] ?? '—') ?></td>
                <td><span class="badge badge-info"><?= $u['order_count'] ?></span></td>
                <td><strong style="color:#D4AF37;"><?= formatPrice($u['total_spent']) ?></strong></td>
                <td style="font-size:13px;color:#999;"><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                <td>
                    <span class="badge <?= $u['is_active'] ? 'badge-success' : 'badge-danger' ?>">
                        <?= $u['is_active'] ? 'Active' : 'Inactive' ?>
                    </span>
                </td>
                <td>
                    <a href="?toggle=<?= $u['id'] ?>" class="btn-edit table-actions"
                       style="display:inline-flex;width:32px;height:32px;border-radius:6px;align-items:center;justify-content:center;"
                       title="<?= $u['is_active'] ? 'Deactivate' : 'Activate' ?>">
                        <i class="fas fa-<?= $u['is_active'] ? 'ban' : 'check' ?>"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($users)): ?>
            <tr><td colspan="8" style="text-align:center;padding:30px;color:#999;">No customers found</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
