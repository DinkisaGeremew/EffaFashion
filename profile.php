<?php
$page_title = 'My Profile';
require_once 'includes/functions.php';
requireLogin();

$user  = getCurrentUser();
$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $full_name = sanitize($_POST['full_name'] ?? '');
        $phone     = sanitize($_POST['phone']     ?? '');
        $address   = sanitize($_POST['address']   ?? '');
        $city      = sanitize($_POST['city']      ?? '');
        $country   = sanitize($_POST['country']   ?? '');

        if (empty($full_name)) {
            $error = 'Full name is required.';
        } else {
            $uid = (int)$_SESSION['user_id'];
            $n   = $conn->real_escape_string($full_name);
            $p   = $conn->real_escape_string($phone);
            $a   = $conn->real_escape_string($address);
            $c   = $conn->real_escape_string($city);
            $co  = $conn->real_escape_string($country);
            $conn->query("UPDATE users SET full_name='$n', phone='$p', address='$a', city='$c', country='$co' WHERE id=$uid");
            $_SESSION['user_name'] = $full_name;
            $success = 'Profile updated successfully!';
            $user    = getCurrentUser();
        }
    }

    if ($action === 'change_password') {
        $current  = $_POST['current_password']  ?? '';
        $new_pass = $_POST['new_password']       ?? '';
        $confirm  = $_POST['confirm_password']   ?? '';

        if (!password_verify($current, $user['password'])) {
            $error = 'Current password is incorrect.';
        } elseif (strlen($new_pass) < 8) {
            $error = 'New password must be at least 8 characters.';
        } elseif ($new_pass !== $confirm) {
            $error = 'New passwords do not match.';
        } else {
            $uid    = (int)$_SESSION['user_id'];
            $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
            $conn->query("UPDATE users SET password='$hashed' WHERE id=$uid");
            $success = 'Password changed successfully!';
        }
    }
}

$orders      = getOrdersByUser((int)$_SESSION['user_id']);
$wl_count    = getWishlistCount();
$order_count = count($orders);
$active_tab  = $_GET['tab'] ?? 'profile';
?>
<?php require_once 'includes/header.php'; ?>
<?php require_once 'includes/navbar.php'; ?>

<div class="breadcrumb">
    <div class="container">
        <ul class="breadcrumb-list">
            <li><a href="<?= SITE_URL ?>">Home</a></li>
            <li class="active">My Account</li>
        </ul>
    </div>
</div>

<section class="section section-gray">
    <div class="container">
        <div class="profile-grid">

            <!-- Sidebar -->
            <div class="profile-sidebar">
                <div class="profile-avatar">
                    <?php
                    $avatar_url = '';
                    if (!empty($user['profile_image'])) {
                        $ap = $_SERVER['DOCUMENT_ROOT'] . '/EffaFashion/uploads/avatars/' . $user['profile_image'];
                        if (file_exists($ap)) $avatar_url = SITE_URL . '/uploads/avatars/' . rawurlencode($user['profile_image']);
                    }
                    if (!$avatar_url) $avatar_url = 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($user['email']))) . '?s=200&d=mp';
                    ?>
                    <img src="<?= $avatar_url ?>" alt="<?= htmlspecialchars($user['full_name']) ?>"
                         style="width:90px;height:90px;border-radius:50%;object-fit:cover;border:3px solid #D4AF37;margin:0 auto 12px;display:block;">
                    <h4><?= htmlspecialchars($user['full_name']) ?></h4>
                    <p><?= htmlspecialchars($user['email']) ?></p>
                </div>
                <nav class="profile-nav">
                    <a href="?tab=profile"   class="<?= $active_tab === 'profile'   ? 'active' : '' ?>"><i class="fas fa-user"></i> My Profile</a>
                    <a href="?tab=orders"    class="<?= $active_tab === 'orders'    ? 'active' : '' ?>"><i class="fas fa-box"></i> My Orders <span class="badge badge-gold" style="margin-left:auto;"><?= $order_count ?></span></a>
                    <a href="?tab=wishlist"  class="<?= $active_tab === 'wishlist'  ? 'active' : '' ?>"><i class="fas fa-heart"></i> Wishlist <span class="badge badge-gold" style="margin-left:auto;"><?= $wl_count ?></span></a>
                    <a href="?tab=password"  class="<?= $active_tab === 'password'  ? 'active' : '' ?>"><i class="fas fa-lock"></i> Change Password</a>
                    <!-- Settings -->
                    <a href="<?= SITE_URL ?>/settings.php" style="color:#D4AF37;font-weight:600;">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                    <a href="<?= SITE_URL ?>/settings.php?tab=profile" style="padding-left:32px;font-size:13px;">
                        <i class="fas fa-user-edit"></i> Change Profile & Photo
                    </a>
                    <a href="<?= SITE_URL ?>/settings.php?tab=password" style="padding-left:32px;font-size:13px;">
                        <i class="fas fa-lock"></i> Change Password
                    </a>
                    <a href="<?= SITE_URL ?>/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </nav>
            </div>

            <!-- Content -->
            <div class="profile-content">
                <?php if ($error):   ?><div class="alert alert-error"><i class="fas fa-times-circle"></i> <?= htmlspecialchars($error) ?></div><?php endif; ?>
                <?php if ($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div><?php endif; ?>

                <!-- Profile Tab -->
                <?php if ($active_tab === 'profile'): ?>
                <h3 style="font-family:'Playfair Display',serif;font-size:22px;margin-bottom:24px;padding-bottom:12px;border-bottom:2px solid #D4AF37;">
                    Personal Information
                </h3>
                <form method="POST">
                    <input type="hidden" name="action" value="update_profile">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Full Name <span class="required">*</span></label>
                            <input type="text" name="full_name" class="form-control" required
                                   value="<?= htmlspecialchars($user['full_name']) ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled
                                   style="background:#f5f5f5;cursor:not-allowed;">
                            <small style="color:#999;font-size:12px;">Email cannot be changed</small>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" name="phone" class="form-control" placeholder="+234 800 000 0000"
                                   value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control" placeholder="Your city"
                                   value="<?= htmlspecialchars($user['city'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Delivery Address</label>
                        <textarea name="address" class="form-control" rows="3" placeholder="Your full address"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Country</label>
                        <select name="country" class="form-control">
                            <?php foreach (['Ethiopia','Kenya','Uganda','Tanzania','Sudan','Somalia','Eritrea','Djibouti','United Kingdom','United States'] as $c): ?>
                            <option value="<?= $c ?>" <?= ($user['country'] ?? 'Ethiopia') === $c ? 'selected' : '' ?>><?= $c ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-gold">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </form>

                <!-- Orders Tab -->
                <?php elseif ($active_tab === 'orders'): ?>
                <h3 style="font-family:'Playfair Display',serif;font-size:22px;margin-bottom:24px;padding-bottom:12px;border-bottom:2px solid #D4AF37;">
                    Order History
                </h3>
                <?php if (empty($orders)): ?>
                    <div style="text-align:center;padding:40px 20px;">
                        <i class="fas fa-box-open" style="font-size:48px;color:#ddd;margin-bottom:16px;display:block;"></i>
                        <p style="color:#999;margin-bottom:16px;">You haven't placed any orders yet.</p>
                        <a href="<?= SITE_URL ?>/products.php" class="btn btn-gold">Start Shopping</a>
                    </div>
                <?php else: ?>
                    <div style="overflow-x:auto;">
                        <table class="orders-table" style="width:100%;">
                            <thead><tr><th>Order #</th><th>Date</th><th>Total</th><th>Status</th><th></th></tr></thead>
                            <tbody>
                            <?php foreach ($orders as $o): ?>
                            <tr>
                                <td><strong style="color:#D4AF37;"><?= htmlspecialchars($o['order_number']) ?></strong></td>
                                <td><?= date('M d, Y', strtotime($o['created_at'])) ?></td>
                                <td><strong><?= formatPrice($o['total_amount']) ?></strong></td>
                                <td><?= getOrderStatusBadge($o['status']) ?></td>
                                <td><a href="<?= SITE_URL ?>/orders.php?id=<?= $o['id'] ?>" class="btn btn-outline btn-sm">View</a></td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <!-- Wishlist Tab -->
                <?php elseif ($active_tab === 'wishlist'): ?>
                <h3 style="font-family:'Playfair Display',serif;font-size:22px;margin-bottom:24px;padding-bottom:12px;border-bottom:2px solid #D4AF37;">
                    My Wishlist
                </h3>
                <?php
                $uid = (int)$_SESSION['user_id'];
                $wl  = $conn->query("SELECT w.*, p.name, p.slug, p.price, p.sale_price, p.image FROM wishlist w JOIN products p ON w.product_id=p.id WHERE w.user_id=$uid ORDER BY w.created_at DESC");
                $wl_items = $wl ? $wl->fetch_all(MYSQLI_ASSOC) : [];
                ?>
                <?php if (empty($wl_items)): ?>
                    <div style="text-align:center;padding:40px 20px;">
                        <i class="fas fa-heart" style="font-size:48px;color:#ddd;margin-bottom:16px;display:block;"></i>
                        <p style="color:#999;margin-bottom:16px;">Your wishlist is empty.</p>
                        <a href="<?= SITE_URL ?>/products.php" class="btn btn-gold">Discover Products</a>
                    </div>
                <?php else: ?>
                    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;">
                    <?php foreach ($wl_items as $wi):
                        $wp = $wi['sale_price'] ? $wi['sale_price'] : $wi['price'];
                        $wi_img = getProductImage($wi['image']);
                    ?>
                    <div class="product-card">
                        <div class="product-card-image">
                            <a href="<?= SITE_URL ?>/product-details.php?slug=<?= urlencode($wi['slug']) ?>">
                                <img src="<?= $wi_img ?>" alt="<?= htmlspecialchars($wi['name']) ?>" loading="lazy">
                            </a>
                            <button class="product-wishlist active" data-id="<?= $wi['product_id'] ?>"><i class="fas fa-heart"></i></button>
                        </div>
                        <div class="product-card-body">
                            <h3 class="product-card-name" style="font-size:14px;">
                                <a href="<?= SITE_URL ?>/product-details.php?slug=<?= urlencode($wi['slug']) ?>"><?= htmlspecialchars($wi['name']) ?></a>
                            </h3>
                            <div class="product-card-price">
                                <span class="price-current"><?= formatPrice($wp) ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Password Tab -->
                <?php elseif ($active_tab === 'password'): ?>
                <h3 style="font-family:'Playfair Display',serif;font-size:22px;margin-bottom:24px;padding-bottom:12px;border-bottom:2px solid #D4AF37;">
                    Change Password
                </h3>
                <form method="POST" style="max-width:480px;">
                    <input type="hidden" name="action" value="change_password">
                    <div class="form-group">
                        <label class="form-label">Current Password <span class="required">*</span></label>
                        <div class="password-toggle">
                            <input type="password" name="current_password" class="form-control" placeholder="Enter current password" required>
                            <i class="fas fa-eye toggle-eye"></i>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">New Password <span class="required">*</span></label>
                        <div class="password-toggle">
                            <input type="password" name="new_password" class="form-control" placeholder="Min. 8 characters" required>
                            <i class="fas fa-eye toggle-eye"></i>
                        </div>
                        <div style="height:4px;background:#eee;border-radius:2px;margin-top:8px;overflow:hidden;">
                            <div id="passwordStrength" style="height:100%;width:0;transition:all 0.3s;border-radius:2px;"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm New Password <span class="required">*</span></label>
                        <div class="password-toggle">
                            <input type="password" name="confirm_password" class="form-control" placeholder="Repeat new password" required>
                            <i class="fas fa-eye toggle-eye"></i>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-gold">
                        <i class="fas fa-lock"></i> Update Password
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
