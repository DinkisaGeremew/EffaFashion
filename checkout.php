<?php
$page_title = 'Checkout';
require_once 'includes/functions.php';
requireLogin();

$cart_items = getCartItems();
if (empty($cart_items)) {
    setFlash('error', 'Your cart is empty.');
    header('Location: ' . SITE_URL . '/cart.php');
    exit;
}

$subtotal    = getCartTotal();
$shipping    = 0;
$discount    = 0;
$coupon_code = $_SESSION['coupon_code'] ?? '';
if ($coupon_code) {
    $cr = validateCoupon($coupon_code, $subtotal);
    if ($cr['valid']) $discount = $cr['discount'];
}
$total = $subtotal - $discount + $shipping;
$user  = getCurrentUser();

// Payment account details
$payment_accounts = [
    'bank_transfer' => [
        'label'   => 'Bank Transfer',
        'icon'    => 'fas fa-university',
        'color'   => '#D4AF37',
        'details' => [
            'Bank Name'      => 'Commercial Bank of Ethiopia (CBE)',
            'Account Name'   => 'EffaFashion PLC',
            'Account Number' => '1000123456789',
        ],
        'note' => 'Transfer the exact amount and upload your payment screenshot below.',
    ],
    'telebirr' => [
        'label'   => 'Telebirr',
        'icon'    => 'fas fa-mobile-alt',
        'color'   => '#00a651',
        'details' => [
            'Telebirr Number' => '+251 900 000 000',
            'Account Name'    => 'EffaFashion',
        ],
        'note' => 'Send the exact amount via Telebirr and upload your payment screenshot below.',
    ],
    'crypto' => [
        'label'   => 'Crypto Transfer',
        'icon'    => 'fab fa-bitcoin',
        'color'   => '#f7931a',
        'details' => [
            'USDT (TRC20)' => 'TXxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
            'Bitcoin (BTC)' => 'bc1qxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
        ],
        'note' => 'Send the exact USDT or BTC equivalent and upload your transaction screenshot.',
    ],
];

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = sanitize($_POST['shipping_name']    ?? '');
    $email   = sanitize($_POST['shipping_email']   ?? '');
    $phone   = sanitize($_POST['shipping_phone']   ?? '');
    $address = sanitize($_POST['shipping_address'] ?? '');
    $city    = sanitize($_POST['shipping_city']    ?? '');
    $country = sanitize($_POST['shipping_country'] ?? 'Ethiopia');
    $payment = sanitize($_POST['payment_method']   ?? 'bank_transfer');
    $notes   = sanitize($_POST['notes']            ?? '');

    if (empty($name) || empty($email) || empty($phone) || empty($address) || empty($city)) {
        $error = 'Please fill in all required shipping fields.';
    } elseif (empty($_FILES['payment_screenshot']['name'])) {
        $error = 'Please upload your payment screenshot to confirm your payment.';
    } else {
        // Handle screenshot upload
        $ext     = strtolower(pathinfo($_FILES['payment_screenshot']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp','pdf'];
        if (!in_array($ext, $allowed)) {
            $error = 'Invalid file type. Please upload JPG, PNG, WebP or PDF.';
        } elseif ($_FILES['payment_screenshot']['size'] > 5 * 1024 * 1024) {
            $error = 'File too large. Maximum size is 5MB.';
        } else {
            $screenshot_name = 'pay_' . time() . '_' . uniqid() . '.' . $ext;
            $upload_dir      = $_SERVER['DOCUMENT_ROOT'] . '/EffaFashion/uploads/payments/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

            if (!move_uploaded_file($_FILES['payment_screenshot']['tmp_name'], $upload_dir . $screenshot_name)) {
                $error = 'Failed to upload screenshot. Please try again.';
            } else {
                // Place order
                $order_number = generateOrderNumber();
                $uid  = (int)$_SESSION['user_id'];
                $n    = $conn->real_escape_string($name);
                $e    = $conn->real_escape_string($email);
                $p    = $conn->real_escape_string($phone);
                $a    = $conn->real_escape_string($address);
                $c    = $conn->real_escape_string($city);
                $co   = $conn->real_escape_string($country);
                $pm   = $conn->real_escape_string($payment);
                $nt   = $conn->real_escape_string($notes);
                $on   = $conn->real_escape_string($order_number);
                $sc   = $conn->real_escape_string($screenshot_name);

                $conn->query("INSERT INTO orders (user_id, order_number, total_amount, shipping_amount,
                              discount_amount, payment_method, payment_status, payment_screenshot,
                              shipping_name, shipping_email, shipping_phone, shipping_address,
                              shipping_city, shipping_country, notes)
                              VALUES ($uid, '$on', $total, $shipping, $discount, '$pm',
                              'pending_verification', '$sc', '$n', '$e', '$p', '$a', '$c', '$co', '$nt')");
                $order_id = $conn->insert_id;

                if ($order_id) {
                    foreach ($cart_items as $item) {
                        $pid   = (int)($item['product_id'] ?? $item['id']);
                        $pname = $conn->real_escape_string($item['name']);
                        $pimg  = $conn->real_escape_string($item['image'] ?? '');
                        $qty   = (int)(isset($item['cart_quantity']) ? $item['cart_quantity'] : $item['quantity']);
                        $price = (float)(isset($item['sale_price']) && $item['sale_price'] ? $item['sale_price'] : $item['price']);
                        $size  = $conn->real_escape_string($item['size']  ?? $item['cart_size']  ?? '');
                        $color = $conn->real_escape_string($item['color'] ?? $item['cart_color'] ?? '');
                        $conn->query("INSERT INTO order_items (order_id, product_id, product_name, product_image, quantity, price, size, color)
                                      VALUES ($order_id, $pid, '$pname', '$pimg', $qty, $price, '$size', '$color')");
                        $conn->query("UPDATE products SET stock=stock-$qty WHERE id=$pid AND stock>=$qty");
                    }
                    // Clear cart
                    $conn->query("DELETE FROM cart WHERE user_id=$uid");
                    unset($_SESSION['cart'], $_SESSION['coupon_code'], $_SESSION['cart_count']);

                    setFlash('success', "Order #{$order_number} placed! We will verify your payment and process your order.");
                    header('Location: ' . SITE_URL . '/order-success.php?order=' . $order_id);
                    exit;
                } else {
                    $error = 'Failed to place order. Please try again.';
                }
            }
        }
    }
}

$selected_payment = $_POST['payment_method'] ?? 'bank_transfer';
?>
<?php require_once 'includes/header.php'; ?>
<?php require_once 'includes/navbar.php'; ?>

<div class="breadcrumb">
    <div class="container">
        <ul class="breadcrumb-list">
            <li><a href="<?= SITE_URL ?>">Home</a></li>
            <li><a href="<?= SITE_URL ?>/cart.php">Cart</a></li>
            <li class="active">Checkout</li>
        </ul>
    </div>
</div>

<section class="section section-gray">
    <div class="container">
        <h2 style="font-family:'Playfair Display',serif;font-size:28px;margin-bottom:8px;">Checkout</h2>
        <p style="color:#999;font-size:14px;margin-bottom:28px;">
            <i class="fas fa-info-circle" style="color:#D4AF37;"></i>
            Complete your shipping details, choose a payment method, pay, then upload your payment screenshot.
        </p>

        <?php if ($error): ?>
            <div class="alert alert-error"><i class="fas fa-times-circle"></i> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form id="checkoutForm" method="POST" enctype="multipart/form-data">
            <div class="checkout-grid">
                <div>

                    <!-- STEP 1: Shipping -->
                    <div class="checkout-section">
                        <h3>
                            <span style="background:#D4AF37;color:#000;width:26px;height:26px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;margin-right:10px;">1</span>
                            Shipping Information
                        </h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Full Name <span class="required">*</span></label>
                                <input type="text" name="shipping_name" class="form-control" placeholder="Full name" required
                                       value="<?= htmlspecialchars($_POST['shipping_name'] ?? $user['full_name'] ?? '') ?>">
                                <div class="form-error"></div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Email <span class="required">*</span></label>
                                <input type="email" name="shipping_email" class="form-control" placeholder="Email address" required
                                       value="<?= htmlspecialchars($_POST['shipping_email'] ?? $user['email'] ?? '') ?>">
                                <div class="form-error"></div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Phone <span class="required">*</span></label>
                                <input type="tel" name="shipping_phone" class="form-control" placeholder="+251 900 000 000" required
                                       value="<?= htmlspecialchars($_POST['shipping_phone'] ?? $user['phone'] ?? '') ?>">
                                <div class="form-error"></div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">City <span class="required">*</span></label>
                                <input type="text" name="shipping_city" class="form-control" placeholder="City" required
                                       value="<?= htmlspecialchars($_POST['shipping_city'] ?? $user['city'] ?? '') ?>">
                                <div class="form-error"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Address <span class="required">*</span></label>
                            <textarea name="shipping_address" class="form-control" rows="2" placeholder="Street address" required><?= htmlspecialchars($_POST['shipping_address'] ?? $user['address'] ?? '') ?></textarea>
                            <div class="form-error"></div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Country</label>
                            <select name="shipping_country" class="form-control">
                                <?php foreach (['Ethiopia','Kenya','Uganda','Tanzania','Sudan','Somalia','Eritrea','Djibouti','United Kingdom','United States'] as $co): ?>
                                <option value="<?= $co ?>" <?= ($user['country'] ?? 'Ethiopia') === $co ? 'selected' : '' ?>><?= $co ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Order Notes (optional)</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Any special instructions..."><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <!-- STEP 2: Payment Method -->
                    <div class="checkout-section">
                        <h3>
                            <span style="background:#D4AF37;color:#000;width:26px;height:26px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;margin-right:10px;">2</span>
                            Choose Payment Method
                        </h3>
                        <?php foreach ($payment_accounts as $key => $pm): ?>
                        <label class="payment-option <?= $selected_payment === $key ? 'active' : '' ?>" onclick="showPaymentDetails('<?= $key ?>')">
                            <input type="radio" name="payment_method" value="<?= $key ?>" <?= $selected_payment === $key ? 'checked' : '' ?>>
                            <i class="<?= $pm['icon'] ?>" style="color:<?= $pm['color'] ?>;font-size:22px;"></i>
                            <div>
                                <strong><?= $pm['label'] ?></strong>
                                <p style="font-size:12px;color:#999;margin:0;"><?= $pm['note'] ?></p>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>

                    <!-- STEP 3: Payment Details + Screenshot -->
                    <div class="checkout-section">
                        <h3>
                            <span style="background:#D4AF37;color:#000;width:26px;height:26px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;margin-right:10px;">3</span>
                            Pay &amp; Upload Screenshot
                        </h3>

                        <!-- Payment details panels -->
                        <?php foreach ($payment_accounts as $key => $pm): ?>
                        <div id="details_<?= $key ?>" class="payment-details-panel"
                             style="display:<?= $selected_payment === $key ? 'block' : 'none' ?>;">
                            <div style="background:#f9f9f9;border:1px solid #eee;border-left:4px solid <?= $pm['color'] ?>;border-radius:8px;padding:20px;margin-bottom:20px;">
                                <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
                                    <i class="<?= $pm['icon'] ?>" style="color:<?= $pm['color'] ?>;font-size:22px;"></i>
                                    <strong style="font-size:16px;"><?= $pm['label'] ?> Details</strong>
                                </div>
                                <?php foreach ($pm['details'] as $label => $value): ?>
                                <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid #eee;">
                                    <span style="font-size:13px;color:#666;"><?= $label ?></span>
                                    <div style="display:flex;align-items:center;gap:8px;">
                                        <strong style="font-size:14px;color:#111;" id="val_<?= $key ?>_<?= preg_replace('/\W+/','_',strtolower($label)) ?>"><?= $value ?></strong>
                                        <button type="button" onclick="copyText('<?= addslashes($value) ?>')"
                                                style="background:none;border:1px solid #ddd;border-radius:4px;padding:3px 8px;font-size:11px;color:#666;cursor:pointer;">
                                            <i class="fas fa-copy"></i> Copy
                                        </button>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                <!-- Amount to pay -->
                                <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 0 0;">
                                    <span style="font-size:13px;color:#666;">Amount to Pay</span>
                                    <strong style="font-size:20px;color:#D4AF37;"><?= formatPrice($total) ?></strong>
                                </div>
                            </div>
                            <div style="background:#fff8e1;border:1px solid #ffe082;border-radius:8px;padding:14px;margin-bottom:20px;font-size:13px;color:#795548;">
                                <i class="fas fa-exclamation-triangle" style="color:#f59e0b;margin-right:6px;"></i>
                                <strong>Important:</strong> Transfer exactly <strong><?= formatPrice($total) ?></strong> and keep your receipt.
                                Your order will only be processed after payment is verified by our team.
                            </div>
                        </div>
                        <?php endforeach; ?>

                        <!-- Screenshot Upload -->
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-camera" style="color:#D4AF37;margin-right:6px;"></i>
                                Upload Payment Screenshot <span class="required">*</span>
                            </label>
                            <div id="uploadBox" onclick="document.getElementById('screenshotInput').click()"
                                 style="border:2px dashed #D4AF37;border-radius:10px;padding:30px;text-align:center;cursor:pointer;background:#fffdf5;transition:all 0.3s;">
                                <i class="fas fa-cloud-upload-alt" style="font-size:36px;color:#D4AF37;margin-bottom:10px;display:block;"></i>
                                <p style="font-size:14px;color:#666;margin:0;">Click to upload your payment screenshot</p>
                                <small style="color:#bbb;">JPG, PNG, WebP or PDF — Max 5MB</small>
                            </div>
                            <input type="file" id="screenshotInput" name="payment_screenshot"
                                   accept="image/*,.pdf" style="display:none;" required
                                   onchange="previewScreenshot(this)">
                            <div id="screenshotPreview" style="margin-top:12px;display:none;">
                                <div style="display:flex;align-items:center;gap:12px;background:#f0fdf4;border:1px solid #86efac;border-radius:8px;padding:12px;">
                                    <i class="fas fa-check-circle" style="color:#22c55e;font-size:20px;"></i>
                                    <div>
                                        <div style="font-weight:600;font-size:14px;" id="screenshotName"></div>
                                        <div style="font-size:12px;color:#999;" id="screenshotSize"></div>
                                    </div>
                                    <button type="button" onclick="clearScreenshot()"
                                            style="margin-left:auto;background:none;border:none;color:#ef4444;font-size:16px;cursor:pointer;">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <img id="screenshotImg" src="" alt="Preview"
                                     style="max-width:100%;max-height:200px;border-radius:8px;margin-top:10px;display:none;">
                            </div>
                            <div class="form-error"></div>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="order-summary-box">
                    <h3 style="font-family:'Playfair Display',serif;font-size:20px;margin-bottom:20px;padding-bottom:12px;border-bottom:2px solid #D4AF37;">
                        Your Order
                    </h3>
                    <?php foreach ($cart_items as $item):
                        $ip  = isset($item['sale_price']) && $item['sale_price'] ? $item['sale_price'] : $item['price'];
                        $iq  = isset($item['cart_quantity']) ? $item['cart_quantity'] : $item['quantity'];
                        $img = getProductImage($item['image']);
                    ?>
                    <div class="order-item">
                        <img src="<?= $img ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                        <div class="order-item-info">
                            <div class="order-item-name"><?= htmlspecialchars($item['name']) ?></div>
                            <div class="order-item-meta">Qty: <?= $iq ?></div>
                        </div>
                        <div class="order-item-price"><?= formatPrice($ip * $iq) ?></div>
                    </div>
                    <?php endforeach; ?>

                    <div class="summary-row"><span>Subtotal</span><span><?= formatPrice($subtotal) ?></span></div>
                    <?php if ($discount > 0): ?>
                    <div class="summary-row"><span>Discount</span><span style="color:#28a745;">-<?= formatPrice($discount) ?></span></div>
                    <?php endif; ?>
                    <div class="summary-row"><span>Shipping</span><span style="color:#28a745;"><i class="fas fa-check-circle"></i> Free</span></div>
                    <div class="summary-row total"><span>Total</span><span style="color:#D4AF37;font-size:20px;"><?= formatPrice($total) ?></span></div>

                    <div style="background:#f9f9f9;border-radius:8px;padding:14px;margin:16px 0;font-size:13px;color:#555;line-height:1.8;">
                        <i class="fas fa-shield-alt" style="color:#D4AF37;margin-right:6px;"></i>
                        <strong>How it works:</strong><br>
                        1. Fill shipping details<br>
                        2. Choose payment method<br>
                        3. Transfer the amount<br>
                        4. Upload your screenshot<br>
                        5. We verify &amp; ship your order
                    </div>

                    <button type="submit" class="btn btn-gold btn-block btn-lg">
                        <i class="fas fa-paper-plane"></i> Place Order
                    </button>
                    <p style="text-align:center;font-size:12px;color:#999;margin-top:10px;">
                        <i class="fas fa-lock" style="color:#D4AF37;"></i> Your order is secure and encrypted
                    </p>
                </div>
            </div>
        </form>
    </div>
</section>

<script>
function showPaymentDetails(key) {
    document.querySelectorAll('.payment-details-panel').forEach(p => p.style.display = 'none');
    document.querySelectorAll('.payment-option').forEach(o => o.classList.remove('active'));
    const panel = document.getElementById('details_' + key);
    if (panel) panel.style.display = 'block';
    const radio = document.querySelector('input[value="' + key + '"]');
    if (radio) { radio.checked = true; radio.closest('.payment-option').classList.add('active'); }
}

function copyText(text) {
    navigator.clipboard.writeText(text).then(() => {
        Swal.fire({ icon:'success', title:'Copied!', text: text, timer:1500, showConfirmButton:false, toast:true, position:'top-end' });
    });
}

function previewScreenshot(input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];
    document.getElementById('screenshotName').textContent = file.name;
    document.getElementById('screenshotSize').textContent = (file.size / 1024).toFixed(1) + ' KB';
    document.getElementById('screenshotPreview').style.display = 'block';
    document.getElementById('uploadBox').style.borderColor = '#22c55e';
    document.getElementById('uploadBox').style.background  = '#f0fdf4';
    // Show image preview if it's an image
    if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = e => {
            const img = document.getElementById('screenshotImg');
            img.src = e.target.result;
            img.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
}

function clearScreenshot() {
    document.getElementById('screenshotInput').value = '';
    document.getElementById('screenshotPreview').style.display = 'none';
    document.getElementById('screenshotImg').style.display = 'none';
    document.getElementById('uploadBox').style.borderColor = '#D4AF37';
    document.getElementById('uploadBox').style.background  = '#fffdf5';
}
</script>

<?php require_once 'includes/footer.php'; ?>
