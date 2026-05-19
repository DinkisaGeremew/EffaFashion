<?php
$page_title = 'Orders';
require_once __DIR__ . '/includes/admin_header.php';

// Update order status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $oid    = (int)$_POST['order_id'];
    $status = sanitize($_POST['status']);
    $pay    = sanitize($_POST['payment_status']);
    $conn->query("UPDATE orders SET status='$status', payment_status='$pay' WHERE id=$oid");
    setFlash('success', 'Order status updated.');
    header('Location: ' . SITE_URL . '/admin/orders.php' . (isset($_GET['id']) ? '?id=' . (int)$_GET['id'] : ''));
    exit;
}

// Verify payment
if (isset($_GET['verify']) && isset($_GET['id'])) {
    $oid = (int)$_GET['id'];
    $conn->query("UPDATE orders SET payment_status='paid', status='processing' WHERE id=$oid");
    setFlash('success', 'Payment verified. Order is now processing.');
    header('Location: ' . SITE_URL . '/admin/orders.php?id=' . $oid);
    exit;
}

// Reject payment
if (isset($_GET['reject']) && isset($_GET['id'])) {
    $oid = (int)$_GET['id'];
    $conn->query("UPDATE orders SET payment_status='pending_verification', status='cancelled' WHERE id=$oid");
    setFlash('error', 'Payment rejected. Order has been cancelled.');
    header('Location: ' . SITE_URL . '/admin/orders.php?id=' . $oid);
    exit;
}

// Single order view
$view_order = null;
$view_items = [];
if (isset($_GET['id'])) {
    $oid        = (int)$_GET['id'];
    $r          = $conn->query("SELECT o.*, u.full_name, u.email FROM orders o JOIN users u ON o.user_id=u.id WHERE o.id=$oid LIMIT 1");
    $view_order = $r ? $r->fetch_assoc() : null;
    if ($view_order) $view_items = getOrderItems($oid);
}

// Filters
$status_filter = sanitize($_GET['status'] ?? '');
$search        = sanitize($_GET['search'] ?? '');
$where         = 'WHERE 1=1';
if ($status_filter) $where .= " AND o.status='" . $conn->real_escape_string($status_filter) . "'";
if ($search)        $where .= " AND (o.order_number LIKE '%" . $conn->real_escape_string($search) . "%' OR u.full_name LIKE '%" . $conn->real_escape_string($search) . "%')";

$orders = $conn->query("SELECT o.*, u.full_name FROM orders o JOIN users u ON o.user_id=u.id $where ORDER BY o.created_at DESC LIMIT 100")->fetch_all(MYSQLI_ASSOC);
?>

<div class="admin-page-header">
    <div>
        <h1><?= $view_order ? 'Order #' . htmlspecialchars($view_order['order_number']) : 'All Orders' ?></h1>
        <div class="admin-breadcrumb"><a href="<?= SITE_URL ?>/admin/dashboard.php">Dashboard</a> / Orders</div>
    </div>
    <?php if ($view_order): ?>
    <a href="<?= SITE_URL ?>/admin/orders.php" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i> All Orders</a>
    <?php endif; ?>
</div>

<?php if ($view_order): ?>
<!-- Single Order -->
<div style="display:grid;grid-template-columns:1fr 320px;gap:24px;align-items:start;">
    <div>
        <!-- Order Items -->
        <div class="admin-card" style="margin-bottom:20px;">
            <div class="admin-card-header"><h3>Items Ordered</h3></div>
            <div class="admin-card-body" style="padding:0;">
                <table class="admin-table">
                    <thead><tr><th>Product</th><th>Price</th><th>Qty</th><th>Subtotal</th></tr></thead>
                    <tbody>
                    <?php foreach ($view_items as $item): ?>
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:12px;">
                                <img src="<?= getProductImage($item['product_image']) ?>" style="width:48px;height:48px;object-fit:cover;border-radius:6px;" alt="">
                                <div>
                                    <strong><?= htmlspecialchars($item['product_name']) ?></strong>
                                    <?php if ($item['size'] || $item['color']): ?>
                                    <div style="font-size:12px;color:#999;"><?= $item['size'] ? "Size: {$item['size']}" : '' ?> <?= $item['color'] ? "| Color: {$item['color']}" : '' ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td><?= formatPrice($item['price']) ?></td>
                        <td><?= $item['quantity'] ?></td>
                        <td><strong><?= formatPrice($item['price'] * $item['quantity']) ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Payment Screenshot -->
        <?php if ($view_order['payment_screenshot']): ?>
        <div class="admin-card" style="margin-bottom:20px;">
            <div class="admin-card-header">
                <h3><i class="fas fa-camera" style="color:#D4AF37;margin-right:8px;"></i> Payment Screenshot</h3>
                <?php if ($view_order['payment_status'] === 'pending_verification'): ?>
                <span class="badge badge-warning" style="font-size:13px;padding:6px 14px;">
                    <i class="fas fa-clock"></i> Awaiting Verification
                </span>
                <?php else: ?>
                <span class="badge badge-success" style="font-size:13px;padding:6px 14px;">
                    <i class="fas fa-check-circle"></i> Payment Verified
                </span>
                <?php endif; ?>
            </div>
            <div class="admin-card-body">
                <div style="display:grid;grid-template-columns:auto 1fr;gap:24px;align-items:start;">
                    <?php
                    $sc_path = $_SERVER['DOCUMENT_ROOT'] . '/EffaFashion/uploads/payments/' . $view_order['payment_screenshot'];
                    $sc_url  = SITE_URL . '/uploads/payments/' . rawurlencode($view_order['payment_screenshot']);
                    $is_img  = in_array(strtolower(pathinfo($view_order['payment_screenshot'], PATHINFO_EXTENSION)), ['jpg','jpeg','png','webp']);
                    ?>
                    <div>
                        <?php if ($is_img): ?>
                        <a href="<?= $sc_url ?>" target="_blank">
                            <img src="<?= $sc_url ?>" alt="Payment Screenshot"
                                 style="max-width:320px;max-height:400px;border-radius:10px;border:2px solid #eee;cursor:zoom-in;">
                        </a>
                        <?php else: ?>
                        <a href="<?= $sc_url ?>" target="_blank" class="btn btn-outline btn-sm">
                            <i class="fas fa-file-pdf"></i> View PDF Receipt
                        </a>
                        <?php endif; ?>
                    </div>
                    <div>
                        <div style="font-size:14px;line-height:2;color:#555;">
                            <div><strong>Payment Method:</strong>
                                <?php
                                $pm_labels = ['bank_transfer'=>'Bank Transfer','telebirr'=>'Telebirr','crypto'=>'Crypto Transfer'];
                                echo $pm_labels[$view_order['payment_method']] ?? ucfirst($view_order['payment_method']);
                                ?>
                            </div>
                            <div><strong>Amount:</strong> <span style="color:#D4AF37;font-size:18px;font-weight:700;"><?= formatPrice($view_order['total_amount']) ?></span></div>
                            <div><strong>Status:</strong>
                                <?php if ($view_order['payment_status'] === 'pending_verification'): ?>
                                    <span style="color:#f59e0b;font-weight:600;">Pending Verification</span>
                                <?php else: ?>
                                    <span style="color:#22c55e;font-weight:600;">Verified ✓</span>
                                <?php endif; ?>
                            </div>
                            <div><strong>Customer:</strong> <?= htmlspecialchars($view_order['full_name']) ?></div>
                            <div><strong>Email:</strong> <?= htmlspecialchars($view_order['email']) ?></div>
                            <div><strong>Phone:</strong> <?= htmlspecialchars($view_order['shipping_phone']) ?></div>
                        </div>
                        <?php if ($view_order['payment_status'] === 'pending_verification'): ?>
                        <div style="margin-top:16px;display:flex;gap:10px;">
                            <button type="button" class="btn btn-gold" onclick="confirmVerify(<?= $view_order['id'] ?>)">
                                <i class="fas fa-check-circle"></i> Verify &amp; Approve
                            </button>
                            <button type="button" class="btn btn-dark" onclick="confirmReject(<?= $view_order['id'] ?>)">
                                <i class="fas fa-times-circle"></i> Reject Payment
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="admin-card" style="margin-bottom:20px;">
            <div class="admin-card-header"><h3><i class="fas fa-camera" style="color:#999;margin-right:8px;"></i> Payment Screenshot</h3></div>
            <div class="admin-card-body">
                <p style="color:#999;font-size:14px;"><i class="fas fa-exclamation-circle" style="color:#f59e0b;"></i> No payment screenshot uploaded yet.</p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Shipping -->
        <div class="admin-card">
            <div class="admin-card-header"><h3>Shipping Details</h3></div>
            <div class="admin-card-body">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;font-size:14px;">
                    <div><strong>Name:</strong><br><?= htmlspecialchars($view_order['shipping_name']) ?></div>
                    <div><strong>Email:</strong><br><?= htmlspecialchars($view_order['email']) ?></div>
                    <div><strong>Phone:</strong><br><?= htmlspecialchars($view_order['shipping_phone']) ?></div>
                    <div><strong>City:</strong><br><?= htmlspecialchars($view_order['shipping_city']) ?></div>
                    <div style="grid-column:1/-1;"><strong>Address:</strong><br><?= htmlspecialchars($view_order['shipping_address']) ?>, <?= htmlspecialchars($view_order['shipping_country']) ?></div>
                    <?php if ($view_order['notes']): ?>
                    <div style="grid-column:1/-1;"><strong>Notes:</strong><br><?= htmlspecialchars($view_order['notes']) ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Summary + Status Update -->
    <div>
        <div class="admin-card" style="margin-bottom:20px;">
            <div class="admin-card-header"><h3>Order Summary</h3></div>
            <div class="admin-card-body">
                <div style="font-size:14px;line-height:2.2;">
                    <div style="display:flex;justify-content:space-between;"><span>Subtotal</span><span><?= formatPrice($view_order['total_amount'] - $view_order['shipping_amount'] + $view_order['discount_amount']) ?></span></div>
                    <?php if ($view_order['discount_amount'] > 0): ?>
                    <div style="display:flex;justify-content:space-between;"><span>Discount</span><span style="color:#28a745;">-<?= formatPrice($view_order['discount_amount']) ?></span></div>
                    <?php endif; ?>
                    <div style="display:flex;justify-content:space-between;"><span>Shipping</span><span><?= $view_order['shipping_amount'] > 0 ? formatPrice($view_order['shipping_amount']) : '<span style="color:#28a745;">Free</span>' ?></span></div>
                    <div style="display:flex;justify-content:space-between;font-size:18px;font-weight:700;border-top:1px solid #eee;padding-top:8px;margin-top:4px;">
                        <span>Total</span><span style="color:#D4AF37;"><?= formatPrice($view_order['total_amount']) ?></span>
                    </div>
                </div>
                <div style="margin-top:12px;font-size:13px;color:#999;">
                    Payment: <strong><?php
                        $pm_labels = [
                            'bank_transfer' => 'Bank Transfer',
                            'telebirr'      => 'Telebirr',
                            'crypto'        => 'Crypto Transfer',
                        ];
                        echo $pm_labels[$view_order['payment_method']] ?? ucfirst(str_replace('_',' ',$view_order['payment_method']));
                    ?></strong><br>
                    Date: <strong><?= date('M d, Y H:i', strtotime($view_order['created_at'])) ?></strong>
                </div>
            </div>
        </div>

        <!-- Admin Note to Customer -->
        <div class="admin-card" style="margin-bottom:20px;">
            <div class="admin-card-header">
                <h3><i class="fas fa-sticky-note" style="color:#D4AF37;margin-right:8px;"></i> Order Note</h3>
            </div>
            <div class="admin-card-body">
                <div style="display:flex;align-items:flex-start;gap:16px;background:linear-gradient(135deg,#fffdf0,#fff8dc);border:1px solid #ffe082;border-left:5px solid #D4AF37;border-radius:10px;padding:20px;">
                    <div style="width:44px;height:44px;background:rgba(212,175,55,0.15);border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fas fa-clock" style="color:#D4AF37;font-size:20px;"></i>
                    </div>
                    <div>
                        <div style="font-weight:700;font-size:15px;color:#111;margin-bottom:6px;">
                            Thank you for your order!
                        </div>
                        <p style="font-size:14px;color:#555;line-height:1.8;margin:0;">
                            Please be patient — your order is being carefully prepared and will be delivered to you soon.
                            Our team is working hard to ensure your items arrive in perfect condition.
                            You will be notified once your order has been shipped.
                        </p>
                        <div style="margin-top:12px;display:flex;gap:20px;flex-wrap:wrap;">
                            <span style="font-size:13px;color:#888;display:flex;align-items:center;gap:6px;">
                                <i class="fas fa-check-circle" style="color:#22c55e;"></i> Order Received
                            </span>
                            <span style="font-size:13px;color:#888;display:flex;align-items:center;gap:6px;">
                                <i class="fas fa-<?= $view_order['payment_status'] === 'paid' ? 'check-circle' : 'clock' ?>"
                                   style="color:<?= $view_order['payment_status'] === 'paid' ? '#22c55e' : '#f59e0b' ?>;"></i>
                                Payment <?= $view_order['payment_status'] === 'paid' ? 'Verified' : 'Pending Verification' ?>
                            </span>
                            <span style="font-size:13px;color:#888;display:flex;align-items:center;gap:6px;">
                                <i class="fas fa-<?= $view_order['status'] === 'shipped' || $view_order['status'] === 'delivered' ? 'check-circle' : 'clock' ?>"
                                   style="color:<?= $view_order['status'] === 'shipped' || $view_order['status'] === 'delivered' ? '#22c55e' : '#f59e0b' ?>;"></i>
                                <?= $view_order['status'] === 'delivered' ? 'Delivered' : ($view_order['status'] === 'shipped' ? 'Shipped' : 'Preparing Order') ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="admin-card">
            <div class="admin-card-header"><h3>Update Status</h3></div>
            <div class="admin-card-body">
                <form method="POST">
                    <input type="hidden" name="update_status" value="1">
                    <input type="hidden" name="order_id" value="<?= $view_order['id'] ?>">
                    <div class="form-group">
                        <label class="form-label">Order Status</label>
                        <select name="status" class="form-control">
                            <?php foreach (['pending','processing','shipped','delivered','cancelled','refunded'] as $s): ?>
                            <option value="<?= $s ?>" <?= $view_order['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Payment Status</label>
                        <select name="payment_status" class="form-control">
                            <?php foreach (['unpaid','paid','refunded'] as $ps): ?>
                            <option value="<?= $ps ?>" <?= $view_order['payment_status'] === $ps ? 'selected' : '' ?>><?= ucfirst($ps) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-gold btn-block"><i class="fas fa-save"></i> Update</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php else: ?>
<!-- Orders List -->
<div class="admin-card">
    <div class="admin-card-header">
        <h3>Orders (<?= count($orders) ?>)</h3>
        <form method="GET" style="display:flex;gap:8px;flex-wrap:wrap;">
            <input type="text" name="search" class="form-control" placeholder="Search order / customer..." value="<?= htmlspecialchars($search) ?>" style="width:200px;">
            <select name="status" class="form-control" style="width:140px;" onchange="this.form.submit()">
                <option value="">All Status</option>
                <?php foreach (['pending','processing','shipped','delivered','cancelled'] as $s): ?>
                <option value="<?= $s ?>" <?= $status_filter === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-dark btn-sm"><i class="fas fa-search"></i></button>
        </form>
    </div>
    <div class="admin-card-body" style="padding:0;overflow-x:auto;">
        <table class="admin-table">
            <thead><tr><th>Order #</th><th>Customer</th><th>Amount</th><th>Payment</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>
            <tbody>
            <?php foreach ($orders as $o): ?>
            <tr>
                <td><strong style="color:#D4AF37;"><?= htmlspecialchars($o['order_number']) ?></strong></td>
                <td><?= htmlspecialchars($o['full_name']) ?></td>
                <td><strong><?= formatPrice($o['total_amount']) ?></strong></td>
                <td><span class="badge <?= $o['payment_status'] === 'paid' ? 'badge-success' : ($o['payment_status'] === 'pending_verification' ? 'badge-warning' : 'badge-secondary') ?>">
                    <?php
                    $ps_labels = ['paid'=>'Paid ✓','pending_verification'=>'Awaiting Verify','refunded'=>'Refunded'];
                    echo $ps_labels[$o['payment_status']] ?? ucfirst($o['payment_status']);
                    ?>
                </span></td>
                <td><?= getOrderStatusBadge($o['status']) ?></td>
                <td style="font-size:13px;color:#999;"><?= date('M d, Y', strtotime($o['created_at'])) ?></td>
                <td>
                    <a href="?id=<?= $o['id'] ?>" class="btn-view table-actions" style="display:inline-flex;width:32px;height:32px;border-radius:6px;align-items:center;justify-content:center;" title="View">
                        <i class="fas fa-eye"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($orders)): ?>
            <tr><td colspan="7" style="text-align:center;padding:30px;color:#999;">No orders found</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
<script>
function confirmVerify(orderId) {
    Swal.fire({
        title: 'Verify Payment?',
        html: `
            <div style="text-align:left;padding:10px 0;">
                <div style="display:flex;align-items:center;gap:12px;background:#f0fdf4;border:1px solid #86efac;border-radius:10px;padding:16px;margin-bottom:16px;">
                    <i class="fas fa-check-circle" style="font-size:28px;color:#22c55e;flex-shrink:0;"></i>
                    <div>
                        <div style="font-weight:700;font-size:15px;color:#111;">Payment Confirmed</div>
                        <div style="font-size:13px;color:#555;margin-top:4px;">You are about to mark this payment as verified and approve the order for processing.</div>
                    </div>
                </div>
                <div style="font-size:13px;color:#555;line-height:1.9;">
                    <div style="display:flex;gap:8px;margin-bottom:6px;">
                        <i class="fas fa-circle-check" style="color:#D4AF37;margin-top:3px;"></i>
                        Order status will change to <strong>Processing</strong>
                    </div>
                    <div style="display:flex;gap:8px;margin-bottom:6px;">
                        <i class="fas fa-circle-check" style="color:#D4AF37;margin-top:3px;"></i>
                        Payment status will change to <strong>Paid</strong>
                    </div>
                    <div style="display:flex;gap:8px;">
                        <i class="fas fa-circle-check" style="color:#D4AF37;margin-top:3px;"></i>
                        Customer will be notified of approval
                    </div>
                </div>
            </div>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#D4AF37',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-check-circle"></i> Yes, Verify & Approve',
        cancelButtonText: '<i class="fas fa-times"></i> Cancel',
        customClass: {
            popup:         'swal-custom-popup',
            title:         'swal-custom-title',
            confirmButton: 'swal-confirm-btn',
            cancelButton:  'swal-cancel-btn',
        },
        width: '480px',
        reverseButtons: true
    }).then(result => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Verifying...',
                text: 'Processing payment verification',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });
            window.location.href = '?id=' + orderId + '&verify=1';
        }
    });
}

function confirmReject(orderId) {
    Swal.fire({
        title: 'Reject Payment?',
        html: `
            <div style="text-align:left;padding:10px 0;">
                <div style="display:flex;align-items:center;gap:12px;background:#fef2f2;border:1px solid #fca5a5;border-radius:10px;padding:16px;margin-bottom:16px;">
                    <i class="fas fa-times-circle" style="font-size:28px;color:#ef4444;flex-shrink:0;"></i>
                    <div>
                        <div style="font-weight:700;font-size:15px;color:#111;">Reject This Payment</div>
                        <div style="font-size:13px;color:#555;margin-top:4px;">You are about to reject the payment screenshot and cancel this order.</div>
                    </div>
                </div>
                <div style="font-size:13px;color:#555;line-height:1.9;">
                    <div style="display:flex;gap:8px;margin-bottom:6px;">
                        <i class="fas fa-circle-xmark" style="color:#ef4444;margin-top:3px;"></i>
                        Order status will change to <strong>Cancelled</strong>
                    </div>
                    <div style="display:flex;gap:8px;margin-bottom:6px;">
                        <i class="fas fa-circle-xmark" style="color:#ef4444;margin-top:3px;"></i>
                        Payment will remain <strong>Unverified</strong>
                    </div>
                    <div style="display:flex;gap:8px;">
                        <i class="fas fa-circle-xmark" style="color:#ef4444;margin-top:3px;"></i>
                        This action <strong>cannot be undone</strong>
                    </div>
                </div>
            </div>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-times-circle"></i> Yes, Reject Payment',
        cancelButtonText: '<i class="fas fa-arrow-left"></i> Go Back',
        customClass: {
            popup:         'swal-custom-popup',
            title:         'swal-custom-title',
        },
        width: '480px',
        reverseButtons: true
    }).then(result => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Rejecting...',
                text: 'Cancelling the order',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });
            window.location.href = '?id=' + orderId + '&reject=1';
        }
    });
}
</script>

<style>
.swal-custom-popup  { border-radius: 16px !important; font-family: 'Poppins', sans-serif !important; }
.swal-custom-title  { font-family: 'Playfair Display', serif !important; font-size: 22px !important; color: #111 !important; }
.swal-confirm-btn,
.swal-cancel-btn    { border-radius: 30px !important; padding: 10px 24px !important; font-weight: 600 !important; font-size: 14px !important; }
</style>
