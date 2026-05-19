<?php
require_once __DIR__ . '/../includes/functions.php';
// Pre-compute once — navbar.php reuses these
$cart_count     = getCartCount();
$wishlist_count = getWishlistCount();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' | ' . SITE_NAME : SITE_NAME . ' - Luxury Fashion' ?></title>
    <meta name="description" content="<?= isset($page_desc) ? htmlspecialchars($page_desc) : 'EffaFashion - Premium luxury fashion. Shop the latest collections.' ?>">

    <!-- Preconnect to speed up external resources -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">

    <!-- Google Fonts — load async to avoid render blocking -->
    <link rel="preload" as="style"
          href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Poppins:wght@300;400;500;600&display=swap"
          onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Poppins:wght@300;400;500;600&display=swap">
    </noscript>

    <!-- Font Awesome — async load -->
    <link rel="preload" as="style"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
          onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    </noscript>

    <!-- SweetAlert2 CSS — async -->
    <link rel="preload" as="style"
          href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css"
          onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    </noscript>

    <!-- Critical CSS — loaded synchronously (only our own files) -->
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/responsive.css">

    <!-- Pass SITE_URL to JS once -->
    <meta name="site-url" content="<?= SITE_URL ?>">
</head>
<body>
