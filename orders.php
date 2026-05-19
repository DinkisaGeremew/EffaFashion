<?php
$page_title = 'My Orders';
require_once 'includes/functions.php';
requireLogin();

$uid    = (int)$_SESSION['user_id'];
$orders = getOrdersByUser($uid);

// Single order view
$view_order = null;
$view_items = [];
if (isset($_GET['id'])) {
    $view_order = getOrderById((int)$_GET['id'], $uid);
    if ($view_order) $view_items = getOrderItems($view_order['id']);
}
?>
<?php require_once 'includes/header.php'; ?>
<?php require_once 'includes/navbar.php'; ?>

<div class="breadcrumb">
    <div class="container">
        <ul class="breadcrumb-list">
            <li><a href="<?= SITE_URL ?>">Home</a></li>
            <li class="active">My Orders</li>
        </ul>
    </div>
</div>

<section class="section section-gray">
    <div class="container">
        <?php if ($view_order): ?>
        <!-- Single Order Detail -->
        <div style="max-width:800px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
                <h2 style="font-family:'Playfair Display',serif;font-size:26px;">
                    Order #<?= htmlspecialchars($view_order['order_number']) ?>
                </h2>
                <a href="<?= SITE_URL ?>/orders.php" class="btn btn-outline btn-sm">
                    <i class="fas fa-arrow-left"></i> All Orders
                </a>
            </div>

            <!-- Status -->
            <div style="background:#fff;border-radius:10px;padding:20px 24px;margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;box-shadow:0 2px 8px rgba(0,0,0,0.06);">
                <div>
                    <div style="font-size:13px;color:#999;margin-bottom:4px;">Order Status</div>
                    <?= getOrderStatusBadge($view_order['status']) ?>
                </div>
                <div>
                    <div style="font-size:13px;color:#999;margin-bottom:4px;">Payment</div>
                    <span class="badge <?= $view_order['payment_status'] === 'paid' ? 'badge-success' : 'badge-warning' ?>">
                        <?= ucfirst($view_order['payment_status']) ?>
                    </span>
                </div>
                <div>
                    <div style="font-size:13px;color:#999;margin-bottom:4px;">Date</div>
                    <strong><?= date('M d, Y', strtotime($view_order['created_at'])) ?></strong>
                </div>
                <div>
                    <div style="font-size:13px;color:#999;margin-bottom:4px;">Total</div>
                    <strong style="color:#D4AF37;font-size:18px;"><?= formatPrice($view_order['total_amount']) ?></strong>
                </div>
            </div>

            <!-- Items -->
            <div style="background:#fff;border-radius:10px;padding:24px;margin-bottom:20px;box-shadow:0 2px 8px rgba(0,0,0,0.06);">
                <h3 style="font-family:'Playfair Display',serif;font-size:18px;margin-bottom:16px;">Items Ordered</h3>
                <?php foreach ($view_items as $item): ?>
                <div style="display:flex;gap:14px;padding:14px 0;border-bottom:1px solid #f5f5f5;">
                    <img src="<?= getProductImage($item['product_image']) ?>" alt="" style="width:70px;height:70px;object-fit:cover;border-radius:8px;">
                    <div style="flex:1;">
                        <div style="font-weight:600;margin-bottom:4px;"><?= htmlspecialchars($item['product_name']) ?></div>
                        <div style="font-size:13px;color:#999;">
                            Qty: <?= $item['quantity'] ?>
                            <?= $item['size']  ? " | Size: {$item['size']}"   : '' ?>
                            <?= $item['color'] ? " | Color: {$item['color']}" : '' ?>
                        </div>
                    </div>
                    <div style="font-weight:700;"><?= formatPrice($item['price'] * $item['quantity']) ?></div>
                </div>
                <?php endforeach; ?>
                <div style="display:flex;justify-content:flex-end;margin-top:16px;font-size:18px;font-weight:700;">
                    Total: <span style="color:#D4AF37;margin-left:12px;"><?= formatPrice($view_order['total_amount']) ?></span>
                </div>
            </div>

            <!-- Shipping -->
            <div style="background:#fff;border-radius:10px;padding:24px;margin-bottom:20px;box-shadow:0 2px 8px rgba(0,0,0,0.06);">
                <h3 style="font-family:'Playfair Display',serif;font-size:18px;margin-bottom:14px;">Shipping Address</h3>
                <p style="color:#555;line-height:1.9;font-size:14px;">
                    <?= htmlspecialchars($view_order['shipping_name']) ?><br>
                    <?= htmlspecialchars($view_order['shipping_address']) ?><br>
                    <?= htmlspecialchars($view_order['shipping_city']) ?>, <?= htmlspecialchars($view_order['shipping_country']) ?><br>
                    <?= htmlspecialchars($view_order['shipping_phone']) ?>
                </p>
            </div>

            <!-- Note to buyer -->
            <?php if ($view_order['status'] !== 'cancelled'): ?>
            <div style="background:linear-gradient(135deg,#fffdf0,#fff8dc);border:1px solid #ffe082;border-left:5px solid #D4AF37;border-radius:12px;padding:24px;margin-bottom:20px;">
                <div style="display:flex;align-items:flex-start;gap:16px;">
                    <div style="width:46px;height:46px;background:rgba(212,175,55,0.15);border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fas fa-heart" style="color:#D4AF37;font-size:20px;"></i>
                    </div>
                    <div style="flex:1;">
                        <div style="font-family:'Playfair Display',serif;font-weight:700;font-size:17px;color:#111;margin-bottom:8px;">
                            Thank you for your order!
                        </div>
                        <p style="font-size:14px;color:#555;line-height:1.9;margin:0 0 20px;">
                            Please be patient — your order is being carefully prepared and will be delivered to you soon.
                            Our team is working hard to ensure your items arrive in perfect condition.
                            You will be notified once your order has been shipped.
                        </p>
                        <!-- Progress tracker -->
                        <div style="display:flex;align-items:center;gap:0;flex-wrap:wrap;">
                            <!-- Step 1: Order Received -->
                            <div style="display:flex;flex-direction:column;align-items:center;gap:6px;min-width:70px;">
                                <div style="width:36px;height:36px;border-radius:50%;background:#22c55e;display:flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-check" style="color:#fff;font-size:13px;"></i>
                                </div>
                                <span style="font-size:11px;color:#555;text-align:center;font-weight:600;">Order<br>Received</span>
                            </div>
                            <div style="flex:1;height:3px;background:linear-gradient(to right,#22c55e,#D4AF37);min-width:16px;margin-bottom:22px;"></div>
                            <!-- Step 2: Payment -->
                            <div style="display:flex;flex-direction:column;align-items:center;gap:6px;min-width:70px;">
                                <div style="width:36px;height:36px;border-radius:50%;background:<?= $view_order['payment_status'] === 'paid' ? '#22c55e' : '#f59e0b' ?>;display:flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-<?= $view_order['payment_status'] === 'paid' ? 'check' : 'clock' ?>" style="color:#fff;font-size:13px;"></i>
                                </div>
                                <span style="font-size:11px;color:#555;text-align:center;font-weight:600;">Payment<br><?= $view_order['payment_status'] === 'paid' ? 'Verified' : 'Verifying' ?></span>
                            </div>
                            <div style="flex:1;height:3px;background:<?= in_array($view_order['status'],['processing','shipped','delivered']) ? '#D4AF37' : '#e5e7eb' ?>;min-width:16px;margin-bottom:22px;"></div>
                            <!-- Step 3: Processing -->
                            <div style="display:flex;flex-direction:column;align-items:center;gap:6px;min-width:70px;">
                                <div style="width:36px;height:36px;border-radius:50%;background:<?= in_array($view_order['status'],['processing','shipped','delivered']) ? '#D4AF37' : '#e5e7eb' ?>;display:flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-box" style="color:<?= in_array($view_order['status'],['processing','shipped','delivered']) ? '#000' : '#999' ?>;font-size:13px;"></i>
                                </div>
                                <span style="font-size:11px;color:#555;text-align:center;font-weight:600;">Order<br>Processing</span>
                            </div>
                            <div style="flex:1;height:3px;background:<?= in_array($view_order['status'],['shipped','delivered']) ? '#D4AF37' : '#e5e7eb' ?>;min-width:16px;margin-bottom:22px;"></div>
                            <!-- Step 4: Shipped -->
                            <div style="display:flex;flex-direction:column;align-items:center;gap:6px;min-width:70px;">
                                <div style="width:36px;height:36px;border-radius:50%;background:<?= in_array($view_order['status'],['shipped','delivered']) ? '#D4AF37' : '#e5e7eb' ?>;display:flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-shipping-fast" style="color:<?= in_array($view_order['status'],['shipped','delivered']) ? '#000' : '#999' ?>;font-size:13px;"></i>
                                </div>
                                <span style="font-size:11px;color:#555;text-align:center;font-weight:600;">On The<br>Way</span>
                            </div>
                            <div style="flex:1;height:3px;background:<?= $view_order['status'] === 'delivered' ? '#22c55e' : '#e5e7eb' ?>;min-width:16px;margin-bottom:22px;"></div>
                            <!-- Step 5: Delivered -->
                            <div style="display:flex;flex-direction:column;align-items:center;gap:6px;min-width:70px;">
                                <div style="width:36px;height:36px;border-radius:50%;background:<?= $view_order['status'] === 'delivered' ? '#22c55e' : '#e5e7eb' ?>;display:flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-home" style="color:<?= $view_order['status'] === 'delivered' ? '#fff' : '#999' ?>;font-size:13px;"></i>
                                </div>
                                <span style="font-size:11px;color:#555;text-align:center;font-weight:600;">Delivered</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <?php else: ?>
        <!-- Orders List -->
        <h2 style="font-family:'Playfair Display',serif;font-size:28px;margin-bottom:24px;">My Orders</h2>

        <?php if (empty($orders)): ?>
        <div class="empty-cart">
            <i class="fas fa-box-open"></i>
            <h3>No orders yet</h3>
            <p>You haven't placed any orders yet. Start shopping!</p>
            <a href="<?= SITE_URL ?>/products.php" class="btn btn-gold btn-lg">Shop Now</a>
        </div>
        <?php else: ?>
        <div style="background:#fff;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.06);overflow:hidden;">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Date</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order):
                        $item_count = $conn->query("SELECT SUM(quantity) as c FROM order_items WHERE order_id=" . $order['id'])->fetch_assoc()['c'];
                    ?>
                    <tr>
                        <td><strong style="color:#D4AF37;"><?= htmlspecialchars($order['order_number']) ?></strong></td>
                        <td><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                        <td><?= $item_count ?> item<?= $item_count != 1 ? 's' : '' ?></td>
                        <td><strong><?= formatPrice($order['total_amount']) ?></strong></td>
                        <td><?= getOrderStatusBadge($order['status']) ?></td>
                        <td>
                            <a href="<?= SITE_URL ?>/orders.php?id=<?= $order['id'] ?>" class="btn btn-outline btn-sm">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
