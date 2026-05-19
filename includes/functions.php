<?php
require_once __DIR__ . '/../config/db.php';

// ─── Security Helpers ────────────────────────────────────────────────────────

function sanitize($data) {
    global $conn;
    return htmlspecialchars(strip_tags(trim($conn->real_escape_string($data))));
}

function clean($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// ─── Auth Helpers ─────────────────────────────────────────────────────────────

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . '/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: ' . SITE_URL . '/index.php');
        exit;
    }
}

function getCurrentUser() {
    global $conn;
    if (!isLoggedIn()) return null;
    // Cache in session to avoid repeated DB hits
    if (isset($_SESSION['user_cache'])) return $_SESSION['user_cache'];
    $id     = (int)$_SESSION['user_id'];
    $result = $conn->query("SELECT * FROM users WHERE id=$id LIMIT 1");
    $user   = $result ? $result->fetch_assoc() : null;
    if ($user) $_SESSION['user_cache'] = $user;
    return $user;
}

// ─── Product Helpers ──────────────────────────────────────────────────────────

function getProducts($limit = 12, $offset = 0, $category_id = null, $search = '', $min_price = 0, $max_price = 0, $featured = false) {
    global $conn;
    $where = ['p.is_active = 1'];
    if ($category_id) $where[] = 'p.category_id = ' . (int)$category_id;
    if ($search)      $where[] = "(p.name LIKE '%" . $conn->real_escape_string($search) . "%' OR p.description LIKE '%" . $conn->real_escape_string($search) . "%')";
    if ($min_price > 0) $where[] = 'p.price >= ' . (float)$min_price;
    if ($max_price > 0) $where[] = 'p.price <= ' . (float)$max_price;
    if ($featured)    $where[] = 'p.is_featured = 1';
    $whereStr = implode(' AND ', $where);
    $sql    = "SELECT p.id, p.name, p.slug, p.price, p.sale_price, p.stock, p.image,
                      p.is_featured, p.is_active, p.views, p.created_at,
                      c.name AS category_name
               FROM products p
               LEFT JOIN categories c ON p.category_id = c.id
               WHERE $whereStr
               ORDER BY p.created_at DESC
               LIMIT $limit OFFSET $offset";
    $result = $conn->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function getProductById($id) {
    global $conn;
    $id     = (int)$id;
    $result = $conn->query("SELECT p.*, c.name AS category_name
                            FROM products p
                            LEFT JOIN categories c ON p.category_id = c.id
                            WHERE p.id=$id AND p.is_active=1 LIMIT 1");
    if ($result && $result->num_rows > 0) {
        // Async view increment — fire and forget
        $conn->query("UPDATE products SET views=views+1 WHERE id=$id");
        return $result->fetch_assoc();
    }
    return null;
}

function getProductBySlug($slug) {
    global $conn;
    $slug   = $conn->real_escape_string($slug);
    $result = $conn->query("SELECT p.*, c.name AS category_name
                            FROM products p
                            LEFT JOIN categories c ON p.category_id = c.id
                            WHERE p.slug='$slug' AND p.is_active=1 LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $product = $result->fetch_assoc();
        $conn->query("UPDATE products SET views=views+1 WHERE id=" . $product['id']);
        return $product;
    }
    return null;
}

function countProducts($category_id = null, $search = '') {
    global $conn;
    $where = ['p.is_active = 1'];
    if ($category_id) $where[] = 'p.category_id = ' . (int)$category_id;
    if ($search)      $where[] = "(p.name LIKE '%" . $conn->real_escape_string($search) . "%')";
    $whereStr = implode(' AND ', $where);
    $result   = $conn->query("SELECT COUNT(*) AS total FROM products p WHERE $whereStr");
    return $result ? (int)$result->fetch_assoc()['total'] : 0;
}

/**
 * Returns image URL. Uses a static in-memory cache so file_exists()
 * is only called once per image filename per request.
 */
function getProductImage($image) {
    static $cache = [];
    if (!$image) return STATIC_IMG_URL . 'placeholder.jpg';
    if (isset($cache[$image])) return $cache[$image];

    if (file_exists(UPLOAD_PATH . $image)) {
        $url = UPLOAD_URL . rawurlencode($image);
    } elseif (file_exists(STATIC_IMG_PATH . $image)) {
        $url = STATIC_IMG_URL . rawurlencode($image);
    } else {
        $url = STATIC_IMG_URL . 'placeholder.jpg';
    }
    $cache[$image] = $url;
    return $url;
}

function formatPrice($price) {
    return CURRENCY . number_format((float)$price, 2);
}

function getDiscountPercent($price, $sale_price) {
    if ($sale_price && $sale_price < $price) {
        return round((($price - $sale_price) / $price) * 100);
    }
    return 0;
}

// ─── Category Helpers ─────────────────────────────────────────────────────────

function getCategories($active_only = true) {
    global $conn;
    static $cat_cache = [];
    $key = $active_only ? 'active' : 'all';
    if (isset($cat_cache[$key])) return $cat_cache[$key];
    $where            = $active_only ? 'WHERE is_active=1' : '';
    $result           = $conn->query("SELECT * FROM categories $where ORDER BY name ASC");
    $cat_cache[$key]  = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    return $cat_cache[$key];
}

function getCategoryById($id) {
    global $conn;
    $result = $conn->query("SELECT * FROM categories WHERE id=" . (int)$id . " LIMIT 1");
    return $result ? $result->fetch_assoc() : null;
}

// ─── Wishlist Helpers (batch-friendly) ───────────────────────────────────────

/**
 * Returns a SET of product IDs in the current user's wishlist.
 * Call once per page, then check with isset() — zero extra queries per product.
 */
function getWishlistIds() {
    global $conn;
    static $wl_ids = null;
    if ($wl_ids !== null) return $wl_ids;
    $wl_ids = [];
    if (!isLoggedIn()) return $wl_ids;
    $uid    = (int)$_SESSION['user_id'];
    $result = $conn->query("SELECT product_id FROM wishlist WHERE user_id=$uid");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $wl_ids[$row['product_id']] = true;
        }
    }
    return $wl_ids;
}

function isInWishlist($product_id) {
    $ids = getWishlistIds();
    return isset($ids[(int)$product_id]);
}

function getWishlistCount() {
    return count(getWishlistIds());
}

function toggleWishlist($product_id) {
    global $conn;
    if (!isLoggedIn()) return ['status' => 'error', 'message' => 'Please login first'];
    $uid        = (int)$_SESSION['user_id'];
    $product_id = (int)$product_id;
    if (isInWishlist($product_id)) {
        $conn->query("DELETE FROM wishlist WHERE user_id=$uid AND product_id=$product_id");
        return ['status' => 'removed', 'message' => 'Removed from wishlist'];
    } else {
        $conn->query("INSERT INTO wishlist (user_id, product_id) VALUES ($uid, $product_id)");
        return ['status' => 'added', 'message' => 'Added to wishlist'];
    }
}

// ─── Cart Helpers ─────────────────────────────────────────────────────────────

function getCartCount() {
    global $conn;
    // Cache in session — only re-query when cart changes
    if (isset($_SESSION['cart_count'])) return (int)$_SESSION['cart_count'];
    if (!isLoggedIn()) {
        $count = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0;
        $_SESSION['cart_count'] = $count;
        return $count;
    }
    $uid    = (int)$_SESSION['user_id'];
    $result = $conn->query("SELECT COALESCE(SUM(quantity),0) AS total FROM cart WHERE user_id=$uid");
    $count  = $result ? (int)$result->fetch_assoc()['total'] : 0;
    $_SESSION['cart_count'] = $count;
    return $count;
}

function invalidateCartCount() {
    unset($_SESSION['cart_count']);
}

function getCartItems() {
    global $conn;
    if (!isLoggedIn()) {
        if (empty($_SESSION['cart'])) return [];
        $items = [];
        foreach ($_SESSION['cart'] as $item) {
            $product = getProductById($item['product_id']);
            if ($product) {
                $product['cart_quantity'] = $item['quantity'];
                $product['cart_size']     = $item['size']  ?? '';
                $product['cart_color']    = $item['color'] ?? '';
                $items[] = $product;
            }
        }
        return $items;
    }
    $uid    = (int)$_SESSION['user_id'];
    $result = $conn->query("SELECT c.id, c.quantity, c.size, c.color, c.product_id,
                                   p.name, p.price, p.sale_price, p.image, p.stock, p.slug
                            FROM cart c
                            JOIN products p ON c.product_id = p.id
                            WHERE c.user_id=$uid");
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function getCartTotal() {
    $items = getCartItems();
    $total = 0;
    foreach ($items as $item) {
        $price  = isset($item['sale_price']) && $item['sale_price'] ? $item['sale_price'] : $item['price'];
        $qty    = isset($item['cart_quantity']) ? $item['cart_quantity'] : $item['quantity'];
        $total += $price * $qty;
    }
    return $total;
}

function addToCart($product_id, $quantity = 1, $size = '', $color = '') {
    global $conn;
    $product_id = (int)$product_id;
    $quantity   = (int)$quantity;
    if (!isLoggedIn()) {
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
        $key = $product_id . '_' . $size . '_' . $color;
        if (isset($_SESSION['cart'][$key])) {
            $_SESSION['cart'][$key]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$key] = ['product_id' => $product_id, 'quantity' => $quantity, 'size' => $size, 'color' => $color];
        }
        invalidateCartCount();
        return true;
    }
    $uid       = (int)$_SESSION['user_id'];
    $size_esc  = $conn->real_escape_string($size);
    $color_esc = $conn->real_escape_string($color);
    $check     = $conn->query("SELECT id, quantity FROM cart WHERE user_id=$uid AND product_id=$product_id AND size='$size_esc' AND color='$color_esc' LIMIT 1");
    if ($check && $check->num_rows > 0) {
        $row     = $check->fetch_assoc();
        $new_qty = $row['quantity'] + $quantity;
        $conn->query("UPDATE cart SET quantity=$new_qty WHERE id=" . $row['id']);
    } else {
        $conn->query("INSERT INTO cart (user_id, product_id, quantity, size, color) VALUES ($uid, $product_id, $quantity, '$size_esc', '$color_esc')");
    }
    invalidateCartCount();
    return true;
}

function removeFromCart($cart_id) {
    global $conn;
    if (!isLoggedIn()) {
        unset($_SESSION['cart'][$cart_id]);
        invalidateCartCount();
        return true;
    }
    $uid = (int)$_SESSION['user_id'];
    $conn->query("DELETE FROM cart WHERE id=" . (int)$cart_id . " AND user_id=$uid");
    invalidateCartCount();
    return true;
}

// ─── Review Helpers ───────────────────────────────────────────────────────────

function getProductReviews($product_id) {
    global $conn;
    $result = $conn->query("SELECT r.rating, r.title, r.comment, r.created_at, u.full_name
                            FROM reviews r
                            JOIN users u ON r.user_id = u.id
                            WHERE r.product_id=" . (int)$product_id . " AND r.is_approved=1
                            ORDER BY r.created_at DESC");
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function getAverageRating($product_id) {
    global $conn;
    $result = $conn->query("SELECT AVG(rating) AS avg_rating, COUNT(*) AS total
                            FROM reviews WHERE product_id=" . (int)$product_id . " AND is_approved=1");
    return $result ? $result->fetch_assoc() : ['avg_rating' => 0, 'total' => 0];
}

// ─── Order Helpers ────────────────────────────────────────────────────────────

function generateOrderNumber() {
    return 'EFF-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
}

function getOrdersByUser($user_id) {
    global $conn;
    $result = $conn->query("SELECT * FROM orders WHERE user_id=" . (int)$user_id . " ORDER BY created_at DESC");
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function getOrderById($order_id, $user_id = null) {
    global $conn;
    $where  = "id=" . (int)$order_id;
    if ($user_id) $where .= " AND user_id=" . (int)$user_id;
    $result = $conn->query("SELECT * FROM orders WHERE $where LIMIT 1");
    return $result ? $result->fetch_assoc() : null;
}

function getOrderItems($order_id) {
    global $conn;
    $result = $conn->query("SELECT * FROM order_items WHERE order_id=" . (int)$order_id);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function getOrderStatusBadge($status) {
    $badges = [
        'pending'    => '<span class="badge badge-warning">Pending</span>',
        'processing' => '<span class="badge badge-info">Processing</span>',
        'shipped'    => '<span class="badge badge-primary">Shipped</span>',
        'delivered'  => '<span class="badge badge-success">Delivered</span>',
        'cancelled'  => '<span class="badge badge-danger">Cancelled</span>',
        'refunded'   => '<span class="badge badge-secondary">Refunded</span>',
    ];
    return $badges[$status] ?? '<span class="badge badge-secondary">' . ucfirst($status) . '</span>';
}

// ─── Coupon Helpers ───────────────────────────────────────────────────────────

function validateCoupon($code, $order_total) {
    global $conn;
    $code   = $conn->real_escape_string(strtoupper(trim($code)));
    $result = $conn->query("SELECT * FROM coupons WHERE code='$code' AND is_active=1
                            AND (expires_at IS NULL OR expires_at >= CURDATE())
                            AND (max_uses IS NULL OR used_count < max_uses) LIMIT 1");
    if (!$result || $result->num_rows === 0) return ['valid' => false, 'message' => 'Invalid or expired coupon code'];
    $coupon   = $result->fetch_assoc();
    if ($order_total < $coupon['min_order']) return ['valid' => false, 'message' => 'Minimum order of ' . formatPrice($coupon['min_order']) . ' required'];
    $discount = $coupon['discount_type'] === 'percentage'
        ? ($order_total * $coupon['discount_value'] / 100)
        : $coupon['discount_value'];
    return ['valid' => true, 'coupon' => $coupon, 'discount' => $discount, 'message' => 'Coupon applied successfully'];
}

// ─── Pagination Helper ────────────────────────────────────────────────────────

function paginate($total, $per_page, $current_page, $url_pattern) {
    $total_pages = (int)ceil($total / $per_page);
    if ($total_pages <= 1) return '';
    $html = '<nav class="pagination-nav"><ul class="pagination">';
    if ($current_page > 1) {
        $html .= '<li><a href="' . sprintf($url_pattern, $current_page - 1) . '"><i class="fas fa-chevron-left"></i></a></li>';
    }
    for ($i = 1; $i <= $total_pages; $i++) {
        $active = $i === $current_page ? ' active' : '';
        $html  .= '<li><a class="' . $active . '" href="' . sprintf($url_pattern, $i) . '">' . $i . '</a></li>';
    }
    if ($current_page < $total_pages) {
        $html .= '<li><a href="' . sprintf($url_pattern, $current_page + 1) . '"><i class="fas fa-chevron-right"></i></a></li>';
    }
    $html .= '</ul></nav>';
    return $html;
}

// ─── Flash Message Helpers ────────────────────────────────────────────────────

function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function showFlash() {
    $flash = getFlash();
    if ($flash) {
        $icon = $flash['type'] === 'success' ? 'check-circle' : ($flash['type'] === 'error' ? 'times-circle' : 'info-circle');
        echo '<div class="alert alert-' . $flash['type'] . '"><i class="fas fa-' . $icon . '"></i> ' . htmlspecialchars($flash['message']) . '</div>';
    }
}

// ─── Slug Generator ───────────────────────────────────────────────────────────

function createSlug($string) {
    $string = strtolower(trim($string));
    $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
    $string = preg_replace('/[\s-]+/', '-', $string);
    return trim($string, '-');
}

// ─── Time Ago ─────────────────────────────────────────────────────────────────

function timeAgo($datetime) {
    $diff = time() - strtotime($datetime);
    if ($diff < 60)     return 'just now';
    if ($diff < 3600)   return floor($diff / 60)   . ' minutes ago';
    if ($diff < 86400)  return floor($diff / 3600)  . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    return date('M d, Y', strtotime($datetime));
}

// ─── Admin Stats (single query) ───────────────────────────────────────────────

function getAdminStats() {
    global $conn;
    static $stats_cache = null;
    if ($stats_cache !== null) return $stats_cache;

    // Combine into as few queries as possible
    $r1 = $conn->query("SELECT
        (SELECT COUNT(*) FROM orders)                                                          AS total_orders,
        (SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status != 'cancelled')        AS total_revenue,
        (SELECT COUNT(*) FROM products WHERE is_active=1)                                     AS total_products,
        (SELECT COUNT(*) FROM users WHERE role='customer')                                    AS total_users,
        (SELECT COUNT(*) FROM orders WHERE status='pending')                                  AS pending_orders,
        (SELECT COALESCE(SUM(total_amount),0) FROM orders
         WHERE MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())
         AND status != 'cancelled')                                                            AS monthly_revenue"
    );
    $stats_cache = $r1 ? $r1->fetch_assoc() : [
        'total_orders'    => 0, 'total_revenue'   => 0,
        'total_products'  => 0, 'total_users'     => 0,
        'pending_orders'  => 0, 'monthly_revenue' => 0,
    ];
    return $stats_cache;
}
?>
