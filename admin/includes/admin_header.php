<?php
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();
$admin_user   = getCurrentUser();
$admin_page   = basename($_SERVER['PHP_SELF']);
$admin_stats  = getAdminStats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' | Admin' : 'Admin Panel' ?> — <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="admin-body">
<div class="admin-wrapper">

<!-- Sidebar -->
<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-logo">
        <a href="<?= SITE_URL ?>/admin/dashboard.php">
            <span class="brand-effa">EFFA</span><span class="brand-fashion">FASHION</span>
        </a>
        <small>Admin Panel</small>
    </div>

    <nav class="sidebar-nav">
        <div class="sidebar-nav-label">Main</div>
        <a href="<?= SITE_URL ?>/admin/dashboard.php" class="<?= $admin_page === 'dashboard.php' ? 'active' : '' ?>">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>

        <div class="sidebar-nav-label">Catalogue</div>
        <a href="<?= SITE_URL ?>/admin/add-product.php" class="<?= $admin_page === 'add-product.php' ? 'active' : '' ?>">
            <i class="fas fa-plus-circle"></i> Add Product
        </a>
        <a href="<?= SITE_URL ?>/admin/edit-product.php" class="<?= in_array($admin_page, ['edit-product.php','delete-product.php']) ? 'active' : '' ?>">
            <i class="fas fa-box"></i> Manage Products
        </a>

        <div class="sidebar-nav-label">Sales</div>
        <a href="<?= SITE_URL ?>/admin/orders.php" class="<?= $admin_page === 'orders.php' ? 'active' : '' ?>">
            <i class="fas fa-shopping-cart"></i> Orders
            <?php if ($admin_stats['pending_orders'] > 0): ?>
                <span class="badge" style="background:#dc3545;color:#fff;margin-left:auto;"><?= $admin_stats['pending_orders'] ?></span>
            <?php endif; ?>
        </a>
        <a href="<?= SITE_URL ?>/admin/reports.php" class="<?= $admin_page === 'reports.php' ? 'active' : '' ?>">
            <i class="fas fa-chart-bar"></i> Reports
        </a>

        <div class="sidebar-nav-label">Users</div>
        <a href="<?= SITE_URL ?>/admin/users.php" class="<?= $admin_page === 'users.php' ? 'active' : '' ?>">
            <i class="fas fa-users"></i> Customers
        </a>

        <div class="sidebar-nav-label">Account</div>
        <a href="<?= SITE_URL ?>/admin/settings.php" class="<?= $admin_page === 'settings.php' ? 'active' : '' ?>">
            <i class="fas fa-cog"></i> Settings
        </a>
        <a href="<?= SITE_URL ?>/admin/settings.php?tab=profile" class="<?= ($admin_page === 'settings.php' && ($_GET['tab']??'') === 'profile') ? 'active' : '' ?>"
           style="padding-left:36px;font-size:13px;">
            <i class="fas fa-user-edit"></i> Change Profile
        </a>
        <a href="<?= SITE_URL ?>/admin/settings.php?tab=password" class="<?= ($admin_page === 'settings.php' && ($_GET['tab']??'') === 'password') ? 'active' : '' ?>"
           style="padding-left:36px;font-size:13px;">
            <i class="fas fa-lock"></i> Change Password
        </a>
    </nav>

    <div class="sidebar-footer">
        <a href="<?= SITE_URL ?>/logout.php">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</aside>

<!-- Main -->
<div class="admin-main">

<!-- Header -->
<header class="admin-header">
    <div class="admin-header-left">
        <button onclick="document.getElementById('adminSidebar').classList.toggle('open')"
                style="background:none;border:none;font-size:20px;color:#666;cursor:pointer;display:none;" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        <div>
            <h2><?= isset($page_title) ? htmlspecialchars($page_title) : 'Dashboard' ?></h2>
        </div>
    </div>
    <div class="admin-header-right">

        <!-- Notification Bell -->
        <div class="admin-notif" id="notifBell" onclick="toggleNotif(event)">
            <i class="fas fa-bell"></i>
            <?php
            // Fetch real notification counts — only UNSEEN ones
            $notif_pending  = (int)$conn->query("SELECT COUNT(*) as c FROM orders o
                WHERE o.status='pending'
                AND NOT EXISTS (SELECT 1 FROM admin_notifications n WHERE n.type='order' AND n.reference_id=o.id AND n.is_read=1)")->fetch_assoc()['c'];

            $notif_verify   = (int)$conn->query("SELECT COUNT(*) as c FROM orders o
                WHERE o.payment_status='pending_verification'
                AND NOT EXISTS (SELECT 1 FROM admin_notifications n WHERE n.type='order' AND n.reference_id=o.id AND n.is_read=1)")->fetch_assoc()['c'];

            $notif_messages = (int)$conn->query("SELECT COUNT(*) as c FROM contact_messages WHERE is_read=0")->fetch_assoc()['c'];

            $notif_lowstock = (int)$conn->query("SELECT COUNT(*) as c FROM products p
                WHERE p.stock <= 3 AND p.is_active=1
                AND NOT EXISTS (SELECT 1 FROM admin_notifications n WHERE n.type='low_stock' AND n.reference_id=p.id AND n.is_read=1)")->fetch_assoc()['c'];

            $notif_total = $notif_pending + $notif_verify + $notif_messages + $notif_lowstock;

            // Recent 5 unread pending orders for dropdown
            $notif_orders = $conn->query("SELECT o.id, o.order_number, o.total_amount, o.created_at, o.payment_status, u.full_name
                FROM orders o JOIN users u ON o.user_id=u.id
                WHERE (o.status='pending' OR o.payment_status='pending_verification')
                AND NOT EXISTS (SELECT 1 FROM admin_notifications n WHERE n.type='order' AND n.reference_id=o.id AND n.is_read=1)
                ORDER BY o.created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
            ?>
            <?php if ($notif_total > 0): ?>
                <span class="badge"><?= $notif_total ?></span>
            <?php endif; ?>

            <!-- Dropdown Panel -->
            <div class="notif-dropdown" id="notifDropdown" onclick="event.stopPropagation()">
                <div class="notif-header">
                    <span><i class="fas fa-bell" style="color:#D4AF37;margin-right:6px;"></i> Notifications</span>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <?php if ($notif_total > 0): ?>
                        <span class="notif-count-badge"><?= $notif_total ?> new</span>
                        <button onclick="markAllRead(event)" style="background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.2);color:#ccc;font-size:11px;padding:3px 10px;border-radius:20px;cursor:pointer;transition:all 0.2s;">
                            Mark all read
                        </button>
                        <?php else: ?>
                        <span style="font-size:12px;color:#888;">All caught up</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="notif-body">
                    <?php if ($notif_total === 0): ?>
                    <div class="notif-empty">
                        <i class="fas fa-check-circle" style="font-size:32px;color:#22c55e;margin-bottom:10px;display:block;"></i>
                        <p>All caught up! No new notifications.</p>
                    </div>
                    <?php endif; ?>

                    <!-- Pending Verification -->
                    <?php if ($notif_verify > 0): ?>
                    <a href="<?= SITE_URL ?>/admin/orders.php?status=pending" class="notif-item notif-urgent">
                        <div class="notif-icon" style="background:rgba(245,158,11,0.15);">
                            <i class="fas fa-camera" style="color:#f59e0b;"></i>
                        </div>
                        <div class="notif-text">
                            <strong><?= $notif_verify ?> payment<?= $notif_verify > 1 ? 's' : '' ?> awaiting verification</strong>
                            <span>Review payment screenshots</span>
                        </div>
                        <i class="fas fa-chevron-right notif-arrow"></i>
                    </a>
                    <?php endif; ?>

                    <!-- Pending Orders -->
                    <?php if ($notif_pending > 0): ?>
                    <a href="<?= SITE_URL ?>/admin/orders.php?status=pending" class="notif-item">
                        <div class="notif-icon" style="background:rgba(212,175,55,0.15);">
                            <i class="fas fa-shopping-cart" style="color:#D4AF37;"></i>
                        </div>
                        <div class="notif-text">
                            <strong><?= $notif_pending ?> pending order<?= $notif_pending > 1 ? 's' : '' ?></strong>
                            <span>Need to be processed</span>
                        </div>
                        <i class="fas fa-chevron-right notif-arrow"></i>
                    </a>
                    <?php endif; ?>

                    <!-- Unread Messages -->
                    <?php if ($notif_messages > 0): ?>
                    <a href="<?= SITE_URL ?>/admin/dashboard.php" class="notif-item">
                        <div class="notif-icon" style="background:rgba(59,130,246,0.15);">
                            <i class="fas fa-envelope" style="color:#3b82f6;"></i>
                        </div>
                        <div class="notif-text">
                            <strong><?= $notif_messages ?> unread message<?= $notif_messages > 1 ? 's' : '' ?></strong>
                            <span>From contact form</span>
                        </div>
                        <i class="fas fa-chevron-right notif-arrow"></i>
                    </a>
                    <?php endif; ?>

                    <!-- Low Stock -->
                    <?php if ($notif_lowstock > 0): ?>
                    <a href="<?= SITE_URL ?>/admin/edit-product.php" class="notif-item">
                        <div class="notif-icon" style="background:rgba(239,68,68,0.15);">
                            <i class="fas fa-exclamation-triangle" style="color:#ef4444;"></i>
                        </div>
                        <div class="notif-text">
                            <strong><?= $notif_lowstock ?> product<?= $notif_lowstock > 1 ? 's' : '' ?> low on stock</strong>
                            <span>3 or fewer items remaining</span>
                        </div>
                        <i class="fas fa-chevron-right notif-arrow"></i>
                    </a>
                    <?php endif; ?>

                    <!-- Recent Orders List -->
                    <?php if (!empty($notif_orders)): ?>
                    <div class="notif-section-label">Recent Pending Orders</div>
                    <?php foreach ($notif_orders as $no): ?>
                    <a href="<?= SITE_URL ?>/admin/orders.php?id=<?= $no['id'] ?>" class="notif-item notif-order">
                        <div class="notif-icon" style="background:#f9f9f9;">
                            <i class="fas fa-receipt" style="color:#999;"></i>
                        </div>
                        <div class="notif-text">
                            <strong><?= htmlspecialchars($no['order_number']) ?></strong>
                            <span><?= htmlspecialchars($no['full_name']) ?> · <?= formatPrice($no['total_amount']) ?></span>
                            <span style="color:<?= $no['payment_status'] === 'pending_verification' ? '#f59e0b' : '#999' ?>;">
                                <?= $no['payment_status'] === 'pending_verification' ? '⏳ Awaiting payment verify' : '🕐 ' . timeAgo($no['created_at']) ?>
                            </span>
                        </div>
                    </a>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="notif-footer">
                    <a href="<?= SITE_URL ?>/admin/orders.php">View All Orders <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>

        <div class="admin-user" style="cursor:pointer;" onclick="window.location='<?= SITE_URL ?>/admin/settings.php'" title="Settings">
            <div style="width:36px;height:36px;border-radius:50%;background:rgba(212,175,55,0.15);border:2px solid #D4AF37;display:flex;align-items:center;justify-content:center;overflow:hidden;">
                <?php if (!empty($admin_user['profile_image']) && file_exists($_SERVER['DOCUMENT_ROOT'].'/EffaFashion/uploads/avatars/'.$admin_user['profile_image'])): ?>
                    <img src="<?= SITE_URL ?>/uploads/avatars/<?= rawurlencode($admin_user['profile_image']) ?>" style="width:100%;height:100%;object-fit:cover;" alt="">
                <?php else: ?>
                    <i class="fas fa-user" style="color:#D4AF37;font-size:14px;"></i>
                <?php endif; ?>
            </div>
            <span><?= htmlspecialchars($admin_user['full_name']) ?></span>
            <i class="fas fa-cog" style="color:#999;font-size:12px;margin-left:4px;"></i>
        </div>
    </div>
</header>

<div class="admin-content">

<style>
/* ── Notification Bell ───────────────────────────────────── */
.admin-notif { position:relative; cursor:pointer; padding:8px 10px; border-radius:8px; transition:background 0.2s; }
.admin-notif:hover { background:#f5f5f5; }
.admin-notif > .fas { font-size:20px; color:#555; }
.admin-notif .badge { position:absolute; top:4px; right:4px; background:#ef4444; color:#fff;
                      font-size:10px; font-weight:700; min-width:18px; height:18px;
                      border-radius:50%; display:flex; align-items:center; justify-content:center;
                      border:2px solid #fff; animation:pulse 2s infinite; }

/* Dropdown */
.notif-dropdown { position:absolute; top:calc(100% + 10px); right:0; width:360px;
                  background:#fff; border-radius:14px; box-shadow:0 8px 40px rgba(0,0,0,0.15);
                  border:1px solid #eee; z-index:9999; opacity:0; visibility:hidden;
                  transform:translateY(10px); transition:all 0.25s ease; overflow:hidden; }
.notif-dropdown.open { opacity:1; visibility:visible; transform:translateY(0); }

.notif-header { display:flex; justify-content:space-between; align-items:center;
                padding:16px 18px; border-bottom:1px solid #f0f0f0;
                background:linear-gradient(135deg,#0f0f0f,#1a1a1a); }
.notif-header span { color:#fff; font-weight:600; font-size:14px; }
.notif-count-badge { background:#D4AF37; color:#000; font-size:11px; font-weight:700;
                     padding:3px 10px; border-radius:20px; }

.notif-body { max-height:380px; overflow-y:auto; }
.notif-body::-webkit-scrollbar { width:4px; }
.notif-body::-webkit-scrollbar-thumb { background:#ddd; border-radius:2px; }

.notif-item { display:flex; align-items:flex-start; gap:12px; padding:14px 18px;
              border-bottom:1px solid #f8f8f8; text-decoration:none; color:inherit;
              transition:background 0.2s; }
.notif-item:hover { background:#fafafa; }
.notif-item.notif-urgent { background:#fffbeb; border-left:3px solid #f59e0b; }
.notif-item.notif-urgent:hover { background:#fef3c7; }

.notif-icon { width:38px; height:38px; border-radius:10px; display:flex;
              align-items:center; justify-content:center; flex-shrink:0; font-size:15px; }
.notif-text { flex:1; min-width:0; }
.notif-text strong { display:block; font-size:13px; color:#111; margin-bottom:2px;
                     white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.notif-text span { display:block; font-size:12px; color:#999; line-height:1.5; }
.notif-arrow { color:#ccc; font-size:11px; margin-top:4px; flex-shrink:0; }

.notif-section-label { padding:8px 18px 4px; font-size:11px; font-weight:700;
                       text-transform:uppercase; letter-spacing:1px; color:#bbb;
                       background:#fafafa; border-bottom:1px solid #f0f0f0; }

.notif-empty { text-align:center; padding:30px 20px; color:#999; font-size:14px; }

.notif-footer { padding:12px 18px; border-top:1px solid #f0f0f0; text-align:center; background:#fafafa; }
.notif-footer a { font-size:13px; color:#D4AF37; font-weight:600; text-decoration:none; }
.notif-footer a:hover { text-decoration:underline; }
</style>

<script>
let notifMarked = false;

function toggleNotif(e) {
    e.stopPropagation();
    const dd   = document.getElementById('notifDropdown');
    const bell = document.getElementById('notifBell');
    const isOpen = dd.classList.contains('open');

    if (!isOpen) {
        dd.classList.add('open');
        // Mark all as read when opened — only once per page load
        if (!notifMarked) {
            notifMarked = true;
            fetch('<?= SITE_URL ?>/ajax/mark_notifications.php', { method:'POST' })
            .then(() => {
                // Remove badge immediately
                const badge = bell.querySelector('.badge');
                if (badge) {
                    badge.style.transition = 'all 0.3s ease';
                    badge.style.transform  = 'scale(0)';
                    badge.style.opacity    = '0';
                    setTimeout(() => badge.remove(), 300);
                }
                // Stop pulse animation on bell icon
                bell.querySelector('.fas.fa-bell').style.animation = 'none';
            });
        }
    } else {
        dd.classList.remove('open');
    }
}

function markAllRead(e) {
    e.stopPropagation();
    const bell = document.getElementById('notifBell');
    fetch('<?= SITE_URL ?>/ajax/mark_notifications.php', { method:'POST' })
    .then(() => {
        // Remove badge
        const badge = bell.querySelector('.badge');
        if (badge) { badge.style.transform='scale(0)'; badge.style.opacity='0'; setTimeout(()=>badge.remove(),300); }
        // Replace dropdown body with all-clear message
        const body = document.querySelector('.notif-body');
        if (body) {
            body.innerHTML = `<div class="notif-empty">
                <i class="fas fa-check-circle" style="font-size:36px;color:#22c55e;margin-bottom:12px;display:block;"></i>
                <p style="font-weight:600;color:#111;margin-bottom:4px;">All caught up!</p>
                <p style="font-size:13px;">No new notifications.</p>
            </div>`;
        }
        // Update header
        const countBadge = document.querySelector('.notif-count-badge');
        if (countBadge) countBadge.remove();
        const markBtn = document.querySelector('.notif-header button');
        if (markBtn) markBtn.remove();
        notifMarked = true;
    });
}

// Close when clicking outside
document.addEventListener('click', function() {
    document.getElementById('notifDropdown')?.classList.remove('open');
});
</script>
<?php
// Show flash messages
$flash = getFlash();
if ($flash): ?>
<div class="alert alert-<?= $flash['type'] ?>" style="margin-bottom:20px;">
    <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'times-circle' ?>"></i>
    <?= htmlspecialchars($flash['message']) ?>
</div>
<?php endif; ?>
