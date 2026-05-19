<?php
require_once 'includes/functions.php';

$slug    = sanitize($_GET['slug'] ?? '');
$product = $slug ? getProductBySlug($slug) : null;

if (!$product) {
    setFlash('error', 'Product not found.');
    header('Location: ' . SITE_URL . '/products.php');
    exit;
}

$page_title = $product['name'];
$page_desc  = substr(strip_tags($product['description']), 0, 160);

$sizes    = $product['sizes']  ? json_decode($product['sizes'],  true) : [];
$colors   = $product['colors'] ? json_decode($product['colors'], true) : [];
$images   = $product['images'] ? json_decode($product['images'], true) : [];
$main_img = getProductImage($product['image']);

$reviews    = getProductReviews($product['id']);
$rating_data = getAverageRating($product['id']);
$discount   = getDiscountPercent($product['price'], $product['sale_price']);
$price      = $product['sale_price'] ? $product['sale_price'] : $product['price'];

// Related products
$related = getProducts(4, 0, $product['category_id']);
$related = array_filter($related, fn($p) => $p['id'] != $product['id']);

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    requireLogin();
    $rating  = (int)($_POST['rating'] ?? 0);
    $title   = sanitize($_POST['review_title'] ?? '');
    $comment = sanitize($_POST['comment'] ?? '');
    if ($rating >= 1 && $rating <= 5 && strlen($comment) >= 10) {
        $uid = (int)$_SESSION['user_id'];
        $pid = (int)$product['id'];
        $t   = $conn->real_escape_string($title);
        $c   = $conn->real_escape_string($comment);
        $conn->query("INSERT INTO reviews (product_id, user_id, rating, title, comment) VALUES ($pid, $uid, $rating, '$t', '$c') ON DUPLICATE KEY UPDATE rating=$rating, title='$t', comment='$c'");
        setFlash('success', 'Review submitted successfully!');
        header('Location: ' . SITE_URL . '/product-details.php?slug=' . urlencode($slug) . '#reviews');
        exit;
    }
}
?>
<?php require_once 'includes/header.php'; ?>
<?php require_once 'includes/navbar.php'; ?>

<!-- Breadcrumb -->
<div class="breadcrumb">
    <div class="container">
        <ul class="breadcrumb-list">
            <li><a href="<?= SITE_URL ?>">Home</a></li>
            <li><a href="<?= SITE_URL ?>/products.php">Shop</a></li>
            <li><a href="<?= SITE_URL ?>/products.php?category=<?= $product['category_id'] ?>"><?= htmlspecialchars($product['category_name']) ?></a></li>
            <li class="active"><?= htmlspecialchars($product['name']) ?></li>
        </ul>
    </div>
</div>

<!-- Product Detail -->
<section class="product-detail">
    <div class="container">
        <div class="product-detail-grid">

            <!-- Gallery -->
            <div class="product-gallery">
                <div class="gallery-main">
                    <img id="galleryMain" src="<?= $main_img ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                </div>
                <?php if (!empty($images)): ?>
                <div class="gallery-thumbs">
                    <div class="gallery-thumb active" data-src="<?= $main_img ?>">
                        <img src="<?= $main_img ?>" alt="Main">
                    </div>
                    <?php foreach ($images as $img): ?>
                    <div class="gallery-thumb" data-src="<?= getProductImage($img) ?>">
                        <img src="<?= getProductImage($img) ?>" alt="Product image">
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Info -->
            <div class="product-info">
                <div class="product-card-category mb-2"><?= htmlspecialchars($product['category_name']) ?></div>
                <h1 class="product-info-title"><?= htmlspecialchars($product['name']) ?></h1>

                <!-- Rating Summary -->
                <div class="d-flex align-center gap-2 mb-3">
                    <div class="stars">
                        <?php
                        $avg = round($rating_data['avg_rating']);
                        for ($s = 1; $s <= 5; $s++):
                        ?><i class="<?= $s <= $avg ? 'fas' : 'far' ?> fa-star"></i><?php endfor; ?>
                    </div>
                    <span style="font-size:14px;color:#999;">(<?= $rating_data['total'] ?> reviews)</span>
                    <a href="#reviews" style="font-size:13px;color:#D4AF37;">Write a review</a>
                </div>

                <!-- Price -->
                <div class="product-info-price">
                    <span class="price-current <?= $product['sale_price'] ? 'price-sale' : '' ?>"><?= formatPrice($price) ?></span>
                    <?php if ($product['sale_price']): ?>
                        <span class="price-original"><?= formatPrice($product['price']) ?></span>
                        <span class="discount-badge">-<?= $discount ?>% OFF</span>
                    <?php endif; ?>
                </div>

                <!-- Stock -->
                <div class="product-info-stock <?= $product['stock'] > 0 ? 'in-stock' : 'out-stock' ?>">
                    <i class="fas fa-<?= $product['stock'] > 0 ? 'check-circle' : 'times-circle' ?>"></i>
                    <?= $product['stock'] > 0 ? "In Stock ({$product['stock']} available)" : 'Out of Stock' ?>
                </div>

                <!-- Description -->
                <p style="font-size:15px;color:#666;line-height:1.8;margin-bottom:24px;">
                    <?= nl2br(htmlspecialchars($product['description'])) ?>
                </p>

                <form id="addToCartForm" data-product="<?= $product['id'] ?>">
                    <!-- Sizes -->
                    <?php if (!empty($sizes)): ?>
                    <div class="product-options">
                        <h4>Size: <span id="sizeLabel" style="color:#D4AF37;font-weight:400;text-transform:none;">Select</span></h4>
                        <div class="size-options">
                            <?php foreach ($sizes as $size): ?>
                            <button type="button" class="size-btn" data-size="<?= htmlspecialchars($size) ?>"><?= htmlspecialchars($size) ?></button>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" id="selectedSize" name="size" value="">
                    </div>
                    <?php endif; ?>

                    <!-- Colors -->
                    <?php if (!empty($colors)): ?>
                    <div class="product-options">
                        <h4>Color: <span id="colorLabel" style="color:#D4AF37;font-weight:400;text-transform:none;">Select</span></h4>
                        <div class="color-options">
                            <?php
                            $color_map = ['Black'=>'#000','White'=>'#fff','Gold'=>'#D4AF37','Navy'=>'#001f5b','Brown'=>'#8B4513','Tan'=>'#D2B48C','Cream'=>'#FFFDD0','Silver'=>'#C0C0C0','Charcoal'=>'#36454F'];
                            foreach ($colors as $color):
                                $bg = $color_map[$color] ?? '#ccc';
                            ?>
                            <button type="button" class="color-btn" data-color="<?= htmlspecialchars($color) ?>"
                                    style="background:<?= $bg ?>;" title="<?= htmlspecialchars($color) ?>"></button>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" id="selectedColor" name="color" value="">
                    </div>
                    <?php endif; ?>

                    <!-- Quantity -->
                    <div class="qty-selector mb-4">
                        <button type="button" class="qty-btn" data-action="minus">−</button>
                        <input type="number" id="qtyInput" class="qty-input" value="1" min="1" max="<?= $product['stock'] ?>">
                        <button type="button" class="qty-btn" data-action="plus">+</button>
                    </div>

                    <!-- Actions -->
                    <div class="product-actions">
                        <?php if ($product['stock'] > 0): ?>
                        <button type="submit" class="btn btn-gold btn-lg">
                            <i class="fas fa-shopping-bag"></i> Add to Cart
                        </button>
                        <?php else: ?>
                        <button type="button" class="btn btn-dark btn-lg" disabled>Out of Stock</button>
                        <?php endif; ?>
                        <button type="button" class="product-wishlist btn btn-outline" data-id="<?= $product['id'] ?>"
                                style="width:auto;padding:12px 20px;border-radius:30px;position:static;box-shadow:none;">
                            <i class="<?= isInWishlist($product['id']) ? 'fas' : 'far' ?> fa-heart"></i>
                        </button>
                    </div>
                </form>

                <!-- Meta -->
                <div class="product-meta">
                    <span><strong>SKU:</strong> EFF-<?= str_pad($product['id'], 4, '0', STR_PAD_LEFT) ?></span>
                    <span><strong>Category:</strong> <?= htmlspecialchars($product['category_name']) ?></span>
                    <span><strong>Views:</strong> <?= number_format($product['views']) ?></span>
                    <div class="d-flex align-center gap-2 mt-2">
                        <strong style="font-size:13px;">Share:</strong>
                        <a href="#" style="color:#3b5998;font-size:18px;"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" style="color:#1da1f2;font-size:18px;"><i class="fab fa-twitter"></i></a>
                        <a href="#" style="color:#e1306c;font-size:18px;"><i class="fab fa-instagram"></i></a>
                        <a href="#" style="color:#25d366;font-size:18px;"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="product-tabs" id="reviews">
            <div class="tabs-nav">
                <button class="tab-btn active" data-tab="tab-desc">Description</button>
                <button class="tab-btn" data-tab="tab-reviews">Reviews (<?= count($reviews) ?>)</button>
                <button class="tab-btn" data-tab="tab-shipping">Shipping & Returns</button>
            </div>

            <div id="tab-desc" class="tab-content active">
                <p style="font-size:15px;line-height:1.9;color:#555;max-width:800px;">
                    <?= nl2br(htmlspecialchars($product['description'])) ?>
                </p>
                <?php if (!empty($sizes)): ?>
                <h4 style="margin-top:24px;margin-bottom:12px;">Available Sizes</h4>
                <div class="size-options"><?php foreach ($sizes as $s): ?><span class="size-btn" style="cursor:default;"><?= $s ?></span><?php endforeach; ?></div>
                <?php endif; ?>
            </div>

            <div id="tab-reviews" class="tab-content">
                <?php if (empty($reviews)): ?>
                    <p style="color:#999;font-size:15px;">No reviews yet. Be the first to review this product!</p>
                <?php else: ?>
                    <?php foreach ($reviews as $review): ?>
                    <div class="review-card">
                        <div class="review-header">
                            <div>
                                <div class="reviewer-name"><?= htmlspecialchars($review['full_name']) ?></div>
                                <div class="stars" style="margin:4px 0;">
                                    <?php for ($s = 1; $s <= 5; $s++): ?>
                                        <i class="<?= $s <= $review['rating'] ? 'fas' : 'far' ?> fa-star"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <div class="review-date"><?= timeAgo($review['created_at']) ?></div>
                        </div>
                        <?php if ($review['title']): ?><div class="review-title"><?= htmlspecialchars($review['title']) ?></div><?php endif; ?>
                        <p class="review-text"><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- Review Form -->
                <?php if (isLoggedIn()): ?>
                <div class="review-form">
                    <h4 style="font-family:'Playfair Display',serif;font-size:20px;margin-bottom:20px;">Write a Review</h4>
                    <form id="reviewForm" method="POST">
                        <div class="form-group">
                            <label class="form-label">Your Rating <span class="required">*</span></label>
                            <div class="star-rating-input">
                                <?php for ($s = 5; $s >= 1; $s--): ?>
                                <input type="radio" name="rating" id="star<?= $s ?>" value="<?= $s ?>">
                                <label for="star<?= $s ?>"><i class="fas fa-star"></i></label>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Review Title</label>
                            <input type="text" name="review_title" class="form-control" placeholder="Summarize your experience">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Your Review <span class="required">*</span></label>
                            <textarea name="comment" class="form-control" rows="4" placeholder="Share your thoughts about this product..." required></textarea>
                            <div class="form-error"></div>
                        </div>
                        <button type="submit" name="submit_review" class="btn btn-gold">
                            <i class="fas fa-paper-plane"></i> Submit Review
                        </button>
                    </form>
                </div>
                <?php else: ?>
                <div style="background:#f9f9f9;border-radius:8px;padding:24px;text-align:center;margin-top:20px;">
                    <p style="color:#666;margin-bottom:12px;">Please log in to write a review.</p>
                    <a href="<?= SITE_URL ?>/login.php" class="btn btn-gold">Login to Review</a>
                </div>
                <?php endif; ?>
            </div>

            <div id="tab-shipping" class="tab-content">
                <div style="max-width:700px;">
                    <h4 style="margin-bottom:12px;color:#D4AF37;"><i class="fas fa-shipping-fast"></i> Shipping Information</h4>
                    <ul style="list-style:disc;padding-left:20px;color:#555;line-height:2;font-size:15px;">
                        <li>Free delivery on all orders</li>
                        <li>Standard delivery: 3-5 business days</li>
                        <li>Express delivery: 1-2 business days (additional fee)</li>
                        <li>Same-day delivery available in Lagos</li>
                    </ul>
                    <h4 style="margin:20px 0 12px;color:#D4AF37;"><i class="fas fa-undo"></i> Returns & Exchanges</h4>
                    <ul style="list-style:disc;padding-left:20px;color:#555;line-height:2;font-size:15px;">
                        <li>30-day return policy for unworn items</li>
                        <li>Items must be in original condition with tags attached</li>
                        <li>Free exchanges on size issues</li>
                        <li>Refunds processed within 5-7 business days</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        <?php if (!empty($related)): ?>
        <div style="margin-top:70px;">
            <div class="section-header">
                <h2>You May Also <span>Like</span></h2>
            </div>
            <div class="products-grid">
                <?php foreach (array_slice($related, 0, 4) as $rp):
                    $rp_price = $rp['sale_price'] ? $rp['sale_price'] : $rp['price'];
                    $rp_img   = getProductImage($rp['image']);
                ?>
                <div class="product-card">
                    <div class="product-card-image">
                        <a href="<?= SITE_URL ?>/product-details.php?slug=<?= urlencode($rp['slug']) ?>">
                            <img src="<?= $rp_img ?>" alt="<?= htmlspecialchars($rp['name']) ?>" loading="lazy">
                        </a>
                        <button class="product-wishlist" data-id="<?= $rp['id'] ?>"><i class="far fa-heart"></i></button>
                        <div class="product-card-overlay">
                            <button class="quick-add-btn" data-id="<?= $rp['id'] ?>"><i class="fas fa-shopping-bag"></i> Quick Add</button>
                        </div>
                    </div>
                    <div class="product-card-body">
                        <div class="product-card-category"><?= htmlspecialchars($rp['category_name']) ?></div>
                        <h3 class="product-card-name"><a href="<?= SITE_URL ?>/product-details.php?slug=<?= urlencode($rp['slug']) ?>"><?= htmlspecialchars($rp['name']) ?></a></h3>
                        <div class="product-card-price">
                            <span class="price-current"><?= formatPrice($rp_price) ?></span>
                            <?php if ($rp['sale_price']): ?><span class="price-original"><?= formatPrice($rp['price']) ?></span><?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<script>
// Update size/color label on select
document.querySelectorAll('.size-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('sizeLabel').textContent = this.dataset.size;
    });
});
document.querySelectorAll('.color-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('colorLabel').textContent = this.dataset.color;
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
