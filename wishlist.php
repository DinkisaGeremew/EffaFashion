<?php
$page_title = 'My Wishlist';
require_once 'includes/functions.php';
requireLogin();

$uid    = (int)$_SESSION['user_id'];
$result = $conn->query("SELECT w.*, p.name, p.slug, p.price, p.sale_price, p.image, p.stock, c.name as category_name
                        FROM wishlist w JOIN products p ON w.product_id = p.id
                        LEFT JOIN categories c ON p.category_id = c.id
                        WHERE w.user_id = $uid ORDER BY w.created_at DESC");
$items  = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>
<?php require_once 'includes/header.php'; ?>
<?php require_once 'includes/navbar.php'; ?>

<div class="breadcrumb">
    <div class="container">
        <ul class="breadcrumb-list">
            <li><a href="<?= SITE_URL ?>">Home</a></li>
            <li class="active">My Wishlist</li>
        </ul>
    </div>
</div>

<section class="section section-gray">
    <div class="container">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:30px;flex-wrap:wrap;gap:12px;">
            <h2 style="font-family:'Playfair Display',serif;font-size:28px;">
                My Wishlist <span style="color:#999;font-size:16px;font-weight:400;">(<?= count($items) ?> items)</span>
            </h2>
            <a href="<?= SITE_URL ?>/products.php" class="btn btn-outline">
                <i class="fas fa-shopping-bag"></i> Continue Shopping
            </a>
        </div>

        <?php if (empty($items)): ?>
        <div class="empty-cart">
            <i class="fas fa-heart"></i>
            <h3>Your wishlist is empty</h3>
            <p>Save items you love to your wishlist and shop them later.</p>
            <a href="<?= SITE_URL ?>/products.php" class="btn btn-gold btn-lg">Discover Products</a>
        </div>
        <?php else: ?>
        <div class="wishlist-grid">
            <?php foreach ($items as $item):
                $price    = $item['sale_price'] ? $item['sale_price'] : $item['price'];
                $discount = getDiscountPercent($item['price'], $item['sale_price']);
                $img      = getProductImage($item['image']);
            ?>
            <div class="product-card">
                <div class="product-card-image">
                    <a href="<?= SITE_URL ?>/product-details.php?slug=<?= urlencode($item['slug']) ?>">
                        <img src="<?= $img ?>" alt="<?= htmlspecialchars($item['name']) ?>" loading="lazy">
                    </a>
                    <?php if ($discount > 0): ?>
                    <div class="product-card-badges"><span class="product-badge-sale">-<?= $discount ?>%</span></div>
                    <?php endif; ?>
                    <button class="product-wishlist active" data-id="<?= $item['product_id'] ?>">
                        <i class="fas fa-heart"></i>
                    </button>
                    <div class="product-card-overlay">
                        <button class="quick-add-btn" data-id="<?= $item['product_id'] ?>">
                            <i class="fas fa-shopping-bag"></i> Add to Cart
                        </button>
                    </div>
                </div>
                <div class="product-card-body">
                    <div class="product-card-category"><?= htmlspecialchars($item['category_name']) ?></div>
                    <h3 class="product-card-name">
                        <a href="<?= SITE_URL ?>/product-details.php?slug=<?= urlencode($item['slug']) ?>">
                            <?= htmlspecialchars($item['name']) ?>
                        </a>
                    </h3>
                    <div class="product-card-price">
                        <span class="price-current <?= $item['sale_price'] ? 'price-sale' : '' ?>"><?= formatPrice($price) ?></span>
                        <?php if ($item['sale_price']): ?><span class="price-original"><?= formatPrice($item['price']) ?></span><?php endif; ?>
                    </div>
                    <div style="margin-top:12px;">
                        <?php if ($item['stock'] > 0): ?>
                        <button class="btn btn-gold btn-sm btn-block quick-add-btn" data-id="<?= $item['product_id'] ?>">
                            <i class="fas fa-shopping-bag"></i> Add to Cart
                        </button>
                        <?php else: ?>
                        <button class="btn btn-dark btn-sm btn-block" disabled>Out of Stock</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
