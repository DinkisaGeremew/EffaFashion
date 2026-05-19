<?php
$page_title = 'Order Confirmed';
require_once 'includes/functions.php';
requireLogin();

$order_id = (int)($_GET['order'] ?? 0);
$order    = getOrderById($order_id, $_SESSION['user_id']);
if (!$order) { header('Location: ' . SITE_URL . '/index.php'); exit; }
$items = getOrderItems($order_id);
?>
<?php require_once 'includes/header.php'; ?>
<?php require_once 'includes/navbar.php'; ?>

<section class="section">
    <div class="container" style="max-width:700px;">
        <div style="text-align:center;padding:50px 20px 30px;">
            <div style="width:90px;height:90px;background:rgba(40,167,69,0.1);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 24px;">
                <i class="fas fa-check-circle" style="font-size:48px;color:#28a745;"></i>
            </div>
            <h1 style="font-family:'Playfair Display',serif;font-size:32px;margin-bottom:10px;">Order Confirmed!</h1>
            <p style="color:#666;font-size:16px;margin-bottom:6px;">Thank you for your purchase, <strong><?= htmlspecialchars($order['shipping_name']) ?></strong>!</p>
            <p style="color:#999;font-size:14px;">Order number: <strong style="color:#D4AF37;"><?= htmlspecialchars($order['order_number']) ?></strong></p>
        </div>

        <div style="background:#f9f9f9;border-radius:12px;padding:28px;margin-bottom:24px;">
            <h3 style="font-family:'Playfair Display',serif;font-size:18px;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid #eee;">Order Details</h3>
            <?php foreach ($items as $item): ?>
            <div style="display:flex;gap:14px;margin-bottom:14px;padding-bottom:14px;border-bottom:1px solid #eee;">
                <img src="<?= getProductImage($item['product_image']) ?>" alt="" style="width:60px;height:60px;object-fit:cover;border-radius:6px;">
                <div style="flex:1;">
                    <div style="font-weight:600;font-size:14px;"><?= htmlspecialchars($item['product_name']) ?></div>
                    <div style="font-size:12px;color:#999;">Qty: <?= $item['quantity'] ?><?= $item['size'] ? " | Size: {$item['size']}" : '' ?></div>
                </div>
                <div style="font-weight:700;"><?= formatPrice($item['price'] * $item['quantity']) ?></div>
            </div>
            <?php endforeach; ?>
            <div style="display:flex;justify-content:space-between;font-size:18px;font-weight:700;margin-top:8px;">
                <span>Total</span>
                <span style="color:#D4AF37;"><?= formatPrice($order['total_amount']) ?></span>
            </div>
        </div>

        <div style="background:#fff;border:1px solid #eee;border-radius:12px;padding:24px;margin-bottom:24px;">
            <h3 style="font-family:'Playfair Display',serif;font-size:18px;margin-bottom:14px;">Shipping To</h3>
            <p style="color:#555;line-height:1.8;font-size:14px;">
                <?= htmlspecialchars($order['shipping_name']) ?><br>
                <?= htmlspecialchars($order['shipping_address']) ?><br>
                <?= htmlspecialchars($order['shipping_city']) ?>, <?= htmlspecialchars($order['shipping_country']) ?><br>
                <?= htmlspecialchars($order['shipping_phone']) ?>
            </p>
        </div>

        <!-- Note to buyer -->
        <div style="background:linear-gradient(135deg,#fffdf0,#fff8dc);border:1px solid #ffe082;border-left:5px solid #D4AF37;border-radius:12px;padding:24px;margin-bottom:24px;">
            <div style="display:flex;align-items:flex-start;gap:16px;">
                <div style="width:46px;height:46px;background:rgba(212,175,55,0.15);border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="fas fa-heart" style="color:#D4AF37;font-size:20px;"></i>
                </div>
                <div>
                    <div style="font-family:'Playfair Display',serif;font-weight:700;font-size:17px;color:#111;margin-bottom:8px;">
                        Thank you for your order!
                    </div>
                    <p style="font-size:14px;color:#555;line-height:1.9;margin:0 0 14px;">
                        Please be patient — your order is being carefully prepared and will be delivered to you soon.
                        Our team is working hard to ensure your items arrive in perfect condition.
                        You will be notified once your order has been shipped.
                    </p>
                    <!-- Progress steps -->
                    <div style="display:flex;align-items:center;gap:0;flex-wrap:wrap;">
                        <!-- Step 1 -->
                        <div style="display:flex;flex-direction:column;align-items:center;gap:6px;min-width:80px;">
                            <div style="width:36px;height:36px;border-radius:50%;background:#22c55e;display:flex;align-items:center;justify-content:center;">
                                <i class="fas fa-check" style="color:#fff;font-size:14px;"></i>
                            </div>
                            <span style="font-size:11px;color:#555;text-align:center;font-weight:600;">Order<br>Received</span>
                        </div>
                        <div style="flex:1;height:3px;background:linear-gradient(to right,#22c55e,#D4AF37);min-width:20px;margin-bottom:20px;"></div>
                        <!-- Step 2 -->
                        <div style="display:flex;flex-direction:column;align-items:center;gap:6px;min-width:80px;">
                            <div style="width:36px;height:36px;border-radius:50%;background:<?= $order['payment_status'] === 'paid' ? '#22c55e' : '#f59e0b' ?>;display:flex;align-items:center;justify-content:center;">
                                <i class="fas fa-<?= $order['payment_status'] === 'paid' ? 'check' : 'clock' ?>" style="color:#fff;font-size:14px;"></i>
                            </div>
                            <span style="font-size:11px;color:#555;text-align:center;font-weight:600;">Payment<br><?= $order['payment_status'] === 'paid' ? 'Verified' : 'Verifying' ?></span>
                        </div>
                        <div style="flex:1;height:3px;background:<?= in_array($order['status'],['processing','shipped','delivered']) ? 'linear-gradient(to right,#D4AF37,#D4AF37)' : '#e5e7eb' ?>;min-width:20px;margin-bottom:20px;"></div>
                        <!-- Step 3 -->
                        <div style="display:flex;flex-direction:column;align-items:center;gap:6px;min-width:80px;">
                            <div style="width:36px;height:36px;border-radius:50%;background:<?= in_array($order['status'],['processing','shipped','delivered']) ? '#D4AF37' : '#e5e7eb' ?>;display:flex;align-items:center;justify-content:center;">
                                <i class="fas fa-box" style="color:<?= in_array($order['status'],['processing','shipped','delivered']) ? '#000' : '#999' ?>;font-size:14px;"></i>
                            </div>
                            <span style="font-size:11px;color:#555;text-align:center;font-weight:600;">Order<br>Processing</span>
                        </div>
                        <div style="flex:1;height:3px;background:<?= in_array($order['status'],['shipped','delivered']) ? 'linear-gradient(to right,#D4AF37,#D4AF37)' : '#e5e7eb' ?>;min-width:20px;margin-bottom:20px;"></div>
                        <!-- Step 4 -->
                        <div style="display:flex;flex-direction:column;align-items:center;gap:6px;min-width:80px;">
                            <div style="width:36px;height:36px;border-radius:50%;background:<?= in_array($order['status'],['shipped','delivered']) ? '#D4AF37' : '#e5e7eb' ?>;display:flex;align-items:center;justify-content:center;">
                                <i class="fas fa-shipping-fast" style="color:<?= in_array($order['status'],['shipped','delivered']) ? '#000' : '#999' ?>;font-size:14px;"></i>
                            </div>
                            <span style="font-size:11px;color:#555;text-align:center;font-weight:600;">On The<br>Way</span>
                        </div>
                        <div style="flex:1;height:3px;background:<?= $order['status'] === 'delivered' ? 'linear-gradient(to right,#22c55e,#22c55e)' : '#e5e7eb' ?>;min-width:20px;margin-bottom:20px;"></div>
                        <!-- Step 5 -->
                        <div style="display:flex;flex-direction:column;align-items:center;gap:6px;min-width:80px;">
                            <div style="width:36px;height:36px;border-radius:50%;background:<?= $order['status'] === 'delivered' ? '#22c55e' : '#e5e7eb' ?>;display:flex;align-items:center;justify-content:center;">
                                <i class="fas fa-home" style="color:<?= $order['status'] === 'delivered' ? '#fff' : '#999' ?>;font-size:14px;"></i>
                            </div>
                            <span style="font-size:11px;color:#555;text-align:center;font-weight:600;">Delivered</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap;">
            <a href="<?= SITE_URL ?>/orders.php" class="btn btn-gold">
                <i class="fas fa-box"></i> Track Order
            </a>
            <a href="<?= SITE_URL ?>/products.php" class="btn btn-outline">
                <i class="fas fa-shopping-bag"></i> Continue Shopping
            </a>
        </div>
    </div>
</section>

<script>
Swal.fire({ icon:'success', title:'Order Placed!', text:'We will process your order shortly.', timer:3000, showConfirmButton:false });
</script>

<?php require_once 'includes/footer.php'; ?>
