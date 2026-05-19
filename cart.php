<?php
$page_title = 'Shopping Cart';
require_once 'includes/functions.php';

$cart_items = getCartItems();
$subtotal   = getCartTotal();
$shipping   = 0;
$discount   = 0;
$coupon_code = $_SESSION['coupon_code'] ?? '';
if ($coupon_code) {
    $coupon_result = validateCoupon($coupon_code, $subtotal);
    if ($coupon_result['valid']) $discount = $coupon_result['discount'];
}
$total = $subtotal - $discount + $shipping;
?>
<?php require_once 'includes/header.php'; ?>
<?php require_once 'includes/navbar.php'; ?>

<!-- Breadcrumb -->
<div class="breadcrumb">
    <div class="container">
        <ul class="breadcrumb-list">
            <li><a href="<?= SITE_URL ?>">Home</a></li>
            <li class="active">Shopping Cart</li>
        </ul>
    </div>
</div>

<section class="section section-gray">
    <div class="container">
        <?php if (empty($cart_items)): ?>
        <div class="empty-cart">
            <i class="fas fa-shopping-bag"></i>
            <h3>Your cart is empty</h3>
            <p>Looks like you haven't added anything to your cart yet.</p>
            <a href="<?= SITE_URL ?>/products.php" class="btn btn-gold btn-lg">
                <i class="fas fa-shopping-bag"></i> Start Shopping
            </a>
        </div>
        <?php else: ?>
        <div style="display:grid;grid-template-columns:1fr 360px;gap:30px;align-items:start;">

            <!-- Cart Items -->
            <div>
                <h2 style="font-family:'Playfair Display',serif;font-size:26px;margin-bottom:24px;">
                    Shopping Cart <span style="color:#999;font-size:16px;font-weight:400;">(<?= count($cart_items) ?> items)</span>
                </h2>
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item):
                            $item_price = isset($item['sale_price']) && $item['sale_price'] ? $item['sale_price'] : $item['price'];
                            $item_qty   = isset($item['cart_quantity']) ? $item['cart_quantity'] : $item['quantity'];
                            $item_id    = isset($item['id']) ? $item['id'] : 0;
                            $item_img   = getProductImage($item['image']);
                        ?>
                        <tr>
                            <td>
                                <div class="cart-product">
                                    <img src="<?= $item_img ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                                    <div>
                                        <div class="cart-product-name">
                                            <a href="<?= SITE_URL ?>/product-details.php?slug=<?= urlencode($item['slug'] ?? '') ?>">
                                                <?= htmlspecialchars($item['name']) ?>
                                            </a>
                                        </div>
                                        <div class="cart-product-meta">
                                            <?php if (!empty($item['size'] ?? $item['cart_size'])): ?>Size: <?= htmlspecialchars($item['size'] ?? $item['cart_size']) ?><?php endif; ?>
                                            <?php if (!empty($item['color'] ?? $item['cart_color'])): ?> | Color: <?= htmlspecialchars($item['color'] ?? $item['cart_color']) ?><?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td><?= formatPrice($item_price) ?></td>
                            <td>
                                <div class="qty-selector" style="border-radius:6px;">
                                    <button type="button" class="cart-qty-btn qty-btn" data-action="minus">−</button>
                                    <input type="number" class="cart-qty-input qty-input" value="<?= $item_qty ?>"
                                           min="1" max="<?= $item['stock'] ?? 99 ?>"
                                           data-cart-id="<?= $item_id ?>" style="width:50px;">
                                    <button type="button" class="cart-qty-btn qty-btn" data-action="plus">+</button>
                                </div>
                            </td>
                            <td class="cart-subtotal" data-cart-id="<?= $item_id ?>">
                                <?= formatPrice($item_price * $item_qty) ?>
                            </td>
                            <td>
                                <button class="cart-remove" data-cart-id="<?= $item_id ?>" title="Remove">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div style="display:flex;justify-content:space-between;margin-top:20px;flex-wrap:wrap;gap:12px;">
                    <a href="<?= SITE_URL ?>/products.php" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i> Continue Shopping
                    </a>
                </div>
            </div>

            <!-- Cart Summary -->
            <div class="cart-summary">
                <h3>Order Summary</h3>

                <div class="summary-row">
                    <span>Subtotal</span>
                    <span id="cartSubtotal"><?= formatPrice($subtotal) ?></span>
                </div>
                <?php if ($discount > 0): ?>
                <div class="summary-row" id="discountRow">
                    <span>Discount (<?= htmlspecialchars($coupon_code) ?>)</span>
                    <span style="color:#28a745;" id="discountAmount">-<?= formatPrice($discount) ?></span>
                </div>
                <?php else: ?>
                <div class="summary-row d-none" id="discountRow">
                    <span>Discount</span>
                    <span style="color:#28a745;" id="discountAmount">-ETB 0.00</span>
                </div>
                <?php endif; ?>
                <div class="summary-row">
                    <span>Shipping</span>
                    <span style="color:#28a745;"><i class="fas fa-check-circle"></i> Free</span>
                </div>
                <div class="summary-row total">
                    <span>Total</span>
                    <span id="cartTotal" data-total="<?= $total ?>"><?= formatPrice($total) ?></span>
                </div>

                <!-- Coupon -->
                <div class="coupon-form">
                    <input type="text" id="couponCode" class="form-control" placeholder="Coupon code"
                           value="<?= htmlspecialchars($coupon_code) ?>">
                    <button id="applyCouponBtn" class="btn btn-dark">Apply</button>
                </div>
                <input type="hidden" id="couponHidden" value="<?= htmlspecialchars($coupon_code) ?>">

                <a href="<?= SITE_URL ?>/checkout.php" class="btn btn-gold btn-block btn-lg" style="margin-top:16px;">
                    <i class="fas fa-lock"></i> Proceed to Checkout
                </a>

                <div style="text-align:center;margin-top:16px;">
                    <small style="color:#999;font-size:12px;">
                        <i class="fas fa-shield-alt" style="color:#D4AF37;"></i> Secure checkout guaranteed
                    </small>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
