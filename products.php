<?php
$page_title = 'Shop';
require_once 'includes/functions.php';

$category_id = isset($_GET['category']) ? (int)$_GET['category'] : null;
$search      = sanitize($_GET['search'] ?? '');
$min_price   = (float)($_GET['min_price'] ?? 0);
$max_price   = (float)($_GET['max_price'] ?? 0);
$featured    = isset($_GET['featured']);
$sale        = isset($_GET['sale']);
$sort        = sanitize($_GET['sort'] ?? 'newest');
$page        = max(1, (int)($_GET['page'] ?? 1));
$per_page    = 12;
$offset      = ($page - 1) * $per_page;

$categories  = getCategories();
$current_cat = $category_id ? getCategoryById($category_id) : null;

// Build WHERE
$where = ['p.is_active = 1'];
if ($category_id) $where[] = "p.category_id = $category_id";
if ($search)      $where[] = "(p.name LIKE '%" . $conn->real_escape_string($search) . "%' OR p.description LIKE '%" . $conn->real_escape_string($search) . "%')";
if ($min_price)   $where[] = "p.price >= $min_price";
if ($max_price)   $where[] = "p.price <= $max_price";
if ($featured)    $where[] = "p.is_featured = 1";
if ($sale)        $where[] = "p.sale_price IS NOT NULL";
$whereStr = implode(' AND ', $where);

// Sort
$order = match($sort) {
    'price_asc'  => 'p.price ASC',
    'price_desc' => 'p.price DESC',
    'name_asc'   => 'p.name ASC',
    'popular'    => 'p.views DESC',
    default      => 'p.created_at DESC',
};

$total    = $conn->query("SELECT COUNT(*) as c FROM products p WHERE $whereStr")->fetch_assoc()['c'];
$products = $conn->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE $whereStr ORDER BY $order LIMIT $per_page OFFSET $offset")->fetch_all(MYSQLI_ASSOC);

$page_title = $current_cat ? $current_cat['name'] : ($search ? "Search: $search" : 'Shop All');
// Load wishlist IDs once for the whole grid
$wl_ids = getWishlistIds();
?>
<?php require_once 'includes/header.php'; ?>
<?php require_once 'includes/navbar.php'; ?>

<!-- Breadcrumb -->
<div class="breadcrumb">
    <div class="container">
        <ul class="breadcrumb-list">
            <li><a href="<?= SITE_URL ?>">Home</a></li>
            <li class="active"><?= htmlspecialchars($page_title) ?></li>
        </ul>
    </div>
</div>

<section class="section section-gray">
    <div class="container">
        <div class="shop-layout">

            <!-- Filter Sidebar -->
            <aside class="filter-sidebar">
                <h3 style="font-family:'Playfair Display',serif;font-size:20px;margin-bottom:24px;padding-bottom:12px;border-bottom:2px solid #D4AF37;">
                    <i class="fas fa-filter" style="color:#D4AF37;margin-right:8px;"></i> Filters
                </h3>

                <form method="GET" action="">
                    <?php if ($search): ?><input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>"><?php endif; ?>

                    <!-- Categories -->
                    <div class="filter-section">
                        <h4>Categories</h4>
                        <div class="filter-option">
                            <input type="radio" name="category" id="cat_all" value="" <?= !$category_id ? 'checked' : '' ?> onchange="this.form.submit()">
                            <label for="cat_all">All Categories</label>
                        </div>
                        <?php foreach ($categories as $cat): ?>
                        <div class="filter-option">
                            <input type="radio" name="category" id="cat_<?= $cat['id'] ?>" value="<?= $cat['id'] ?>" <?= $category_id == $cat['id'] ? 'checked' : '' ?> onchange="this.form.submit()">
                            <label for="cat_<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></label>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Price Range -->
                    <div class="filter-section">
                        <h4>Price Range</h4>
                        <div class="price-range">
                            <input type="number" id="minPrice" name="min_price" class="form-control" placeholder="Min" value="<?= $min_price ?: '' ?>">
                            <span style="color:#999;">—</span>
                            <input type="number" id="maxPrice" name="max_price" class="form-control" placeholder="Max" value="<?= $max_price ?: '' ?>">
                        </div>
                        <button type="submit" class="btn btn-gold btn-sm btn-block" style="margin-top:12px;">Apply</button>
                    </div>

                    <!-- Special -->
                    <div class="filter-section">
                        <h4>Special</h4>
                        <div class="filter-option">
                            <input type="checkbox" id="chk_featured" name="featured" value="1" <?= $featured ? 'checked' : '' ?> onchange="this.form.submit()">
                            <label for="chk_featured">New Arrivals</label>
                        </div>
                        <div class="filter-option">
                            <input type="checkbox" id="chk_sale" name="sale" value="1" <?= $sale ? 'checked' : '' ?> onchange="this.form.submit()">
                            <label for="chk_sale">On Sale</label>
                        </div>
                    </div>

                    <?php if ($category_id || $search || $min_price || $max_price || $featured || $sale): ?>
                    <a href="<?= SITE_URL ?>/products.php" class="btn btn-outline btn-sm btn-block">
                        <i class="fas fa-times"></i> Clear Filters
                    </a>
                    <?php endif; ?>
                </form>
            </aside>

            <!-- Products Area -->
            <div>
                <div class="shop-header">
                    <p>Showing <strong><?= count($products) ?></strong> of <strong><?= $total ?></strong> products
                        <?= $search ? " for \"<em>" . htmlspecialchars($search) . "</em>\"" : '' ?>
                    </p>
                    <select id="sortSelect" class="sort-select">
                        <option value="newest"     <?= $sort === 'newest'     ? 'selected' : '' ?>>Newest First</option>
                        <option value="price_asc"  <?= $sort === 'price_asc'  ? 'selected' : '' ?>>Price: Low to High</option>
                        <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                        <option value="name_asc"   <?= $sort === 'name_asc'   ? 'selected' : '' ?>>Name A-Z</option>
                        <option value="popular"    <?= $sort === 'popular'    ? 'selected' : '' ?>>Most Popular</option>
                    </select>
                </div>

                <?php if (empty($products)): ?>
                <div class="empty-cart" style="padding:60px 20px;">
                    <i class="fas fa-search" style="font-size:56px;color:#ddd;margin-bottom:20px;display:block;"></i>
                    <h3>No products found</h3>
                    <p style="color:#999;margin-bottom:24px;">Try adjusting your filters or search terms.</p>
                    <a href="<?= SITE_URL ?>/products.php" class="btn btn-gold">Browse All Products</a>
                </div>
                <?php else: ?>
                <div class="products-grid">
                    <?php foreach ($products as $product):
                        $price    = $product['sale_price'] ? $product['sale_price'] : $product['price'];
                        $discount = getDiscountPercent($product['price'], $product['sale_price']);
                        $img      = getProductImage($product['image']);
                        $in_wl    = isset($wl_ids[$product['id']]);
                    ?>
                    <div class="product-card">
                        <div class="product-card-image">
                            <a href="<?= SITE_URL ?>/product-details.php?slug=<?= urlencode($product['slug']) ?>">
                                <img src="<?= $img ?>" alt="<?= htmlspecialchars($product['name']) ?>" loading="lazy">
                            </a>
                            <div class="product-card-badges">
                                <?php if ($discount > 0): ?><span class="product-badge-sale">-<?= $discount ?>%</span><?php endif; ?>
                                <?php if ($product['is_featured']): ?><span class="product-badge-new">New</span><?php endif; ?>
                                <?php if ($product['stock'] == 0): ?><span class="product-badge-out">Sold Out</span><?php endif; ?>
                            </div>
                            <button class="product-wishlist <?= $in_wl ? 'active' : '' ?>" data-id="<?= $product['id'] ?>">
                                <i class="<?= $in_wl ? 'fas' : 'far' ?> fa-heart"></i>
                            </button>
                            <div class="product-card-overlay">
                                <button class="quick-add-btn" data-id="<?= $product['id'] ?>">
                                    <i class="fas fa-shopping-bag"></i> Quick Add
                                </button>
                            </div>
                        </div>
                        <div class="product-card-body">
                            <div class="product-card-category"><?= htmlspecialchars($product['category_name']) ?></div>
                            <h3 class="product-card-name">
                                <a href="<?= SITE_URL ?>/product-details.php?slug=<?= urlencode($product['slug']) ?>">
                                    <?= htmlspecialchars($product['name']) ?>
                                </a>
                            </h3>
                            <div class="product-card-price">
                                <span class="price-current <?= $product['sale_price'] ? 'price-sale' : '' ?>"><?= formatPrice($price) ?></span>
                                <?php if ($product['sale_price']): ?>
                                    <span class="price-original"><?= formatPrice($product['price']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php
                $url_pattern = SITE_URL . '/products.php?' . http_build_query(array_merge($_GET, ['page' => '%d']));
                echo paginate($total, $per_page, $page, $url_pattern);
                ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script>
document.getElementById('sortSelect')?.addEventListener('change', function() {
    const url = new URL(window.location.href);
    url.searchParams.set('sort', this.value);
    url.searchParams.set('page', 1);
    window.location.href = url.toString();
});
</script>

<?php require_once 'includes/footer.php'; ?>
