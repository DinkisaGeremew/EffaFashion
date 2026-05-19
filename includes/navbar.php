<?php
$cart_count     = getCartCount();
$wishlist_count = getWishlistCount();
$categories     = getCategories();
$current_page   = basename($_SERVER['PHP_SELF']);
?>
<!-- Top Bar -->
<div class="top-bar">
    <div class="container">
        <div class="top-bar-left">
            <span><i class="fas fa-phone"></i> +251 910 624 704</span>
            <span><i class="fab fa-telegram"></i> <a href="https://t.me/FaashiniiIfaa" target="_blank" style="color:inherit;">t.me/FaashiniiIfaa</a></span>
        </div>
        <div class="top-bar-right">
            <span><i class="fas fa-shipping-fast"></i> Free delivery on all orders</span>
        </div>
    </div>
</div>

<!-- Main Navbar -->
<nav class="navbar" id="mainNavbar">
    <div class="container">
        <!-- Logo -->
        <a href="<?= SITE_URL ?>/index.php" class="navbar-brand">
            <span class="brand-effa">EFFA</span><span class="brand-fashion">FASHION</span>
        </a>

        <!-- Search Bar -->
        <form class="navbar-search" action="<?= SITE_URL ?>/products.php" method="GET">
            <input type="text" name="search" placeholder="Search for products..." 
                   value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
            <button type="submit"><i class="fas fa-search"></i></button>
        </form>

        <!-- Nav Icons -->
        <div class="navbar-icons">
            <?php if (isLoggedIn()): ?>
                <?php
                $nav_user = getCurrentUser();
                $nav_avatar = '';
                if (!empty($nav_user['profile_image'])) {
                    $nav_ap = $_SERVER['DOCUMENT_ROOT'] . '/EffaFashion/uploads/avatars/' . $nav_user['profile_image'];
                    if (file_exists($nav_ap)) $nav_avatar = SITE_URL . '/uploads/avatars/' . rawurlencode($nav_user['profile_image']);
                }
                if (!$nav_avatar) $nav_avatar = 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($nav_user['email']))) . '?s=40&d=mp';
                ?>
                <div class="nav-icon-dropdown">
                    <a href="#" class="nav-icon" title="My Account" style="position:relative;">
                        <div style="width:32px;height:32px;border-radius:50%;overflow:hidden;border:2px solid #D4AF37;display:flex;align-items:center;justify-content:center;background:#111;">
                            <img src="<?= $nav_avatar ?>" alt="" style="width:100%;height:100%;object-fit:cover;"
                                 onerror="this.style.display='none';this.nextSibling.style.display='flex'">
                            <i class="fas fa-user" style="color:#D4AF37;font-size:13px;display:none;"></i>
                        </div>
                        <span class="icon-label">Account</span>
                    </a>
                    <div class="dropdown-menu">
                        <a href="<?= SITE_URL ?>/profile.php"><i class="fas fa-user-circle"></i> My Profile</a>
                        <a href="<?= SITE_URL ?>/orders.php"><i class="fas fa-box"></i> My Orders</a>
                        <a href="<?= SITE_URL ?>/wishlist.php"><i class="fas fa-heart"></i> Wishlist</a>
                        <!-- Settings with sub-menu -->
                        <div class="dropdown-divider"></div>
                        <div class="dropdown-has-sub">
                            <a href="#" class="dropdown-sub-toggle">
                                <i class="fas fa-cog"></i> Settings
                                <i class="fas fa-chevron-right" style="margin-left:auto;font-size:10px;"></i>
                            </a>
                            <div class="dropdown-sub-menu">
                                <a href="<?= SITE_URL ?>/settings.php?tab=profile">
                                    <i class="fas fa-user-edit"></i> Change Profile
                                </a>
                                <a href="<?= SITE_URL ?>/settings.php?tab=password">
                                    <i class="fas fa-lock"></i> Change Password
                                </a>
                            </div>
                        </div>
                        <?php if (isAdmin()): ?>
                            <div class="dropdown-divider"></div>
                            <a href="<?= SITE_URL ?>/admin/dashboard.php"><i class="fas fa-tachometer-alt"></i> Admin Panel</a>
                        <?php endif; ?>
                        <div class="dropdown-divider"></div>
                        <a href="<?= SITE_URL ?>/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="<?= SITE_URL ?>/login.php" class="nav-icon" title="Login">
                    <i class="fas fa-user"></i>
                    <span class="icon-label">Login</span>
                </a>
            <?php endif; ?>

            <a href="<?= SITE_URL ?>/wishlist.php" class="nav-icon" title="Wishlist">
                <i class="fas fa-heart"></i>
                <?php if ($wishlist_count > 0): ?>
                    <span class="badge"><?= $wishlist_count ?></span>
                <?php endif; ?>
                <span class="icon-label">Wishlist</span>
            </a>

            <a href="<?= SITE_URL ?>/cart.php" class="nav-icon" title="Cart">
                <i class="fas fa-shopping-bag"></i>
                <span class="badge" id="cartBadge"><?= $cart_count ?></span>
                <span class="icon-label">Cart</span>
            </a>

            <!-- Mobile Menu Toggle -->
            <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle menu">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>

    <!-- Nav Links -->
    <div class="navbar-links" id="navbarLinks">
        <div class="container">
            <ul class="nav-menu">
                <li><a href="<?= SITE_URL ?>/index.php" class="<?= $current_page === 'index.php' ? 'active' : '' ?>">Home</a></li>
                <li class="has-dropdown">
                    <a href="<?= SITE_URL ?>/products.php" class="<?= $current_page === 'products.php' ? 'active' : '' ?>">
                        Shop <i class="fas fa-chevron-down"></i>
                    </a>
                    <ul class="dropdown">
                        <li><a href="<?= SITE_URL ?>/products.php">All Products</a></li>
                        <?php foreach ($categories as $cat): ?>
                            <li><a href="<?= SITE_URL ?>/products.php?category=<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </li>
                <li><a href="<?= SITE_URL ?>/products.php?featured=1">New Arrivals</a></li>
                <li><a href="<?= SITE_URL ?>/products.php?sale=1">Sale</a></li>
                <li><a href="<?= SITE_URL ?>/about.php" class="<?= $current_page === 'about.php' ? 'active' : '' ?>">About</a></li>
                <li><a href="<?= SITE_URL ?>/contact.php" class="<?= $current_page === 'contact.php' ? 'active' : '' ?>">Contact</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- Flash Messages -->
<div class="flash-container">
    <?php showFlash(); ?>
</div>
