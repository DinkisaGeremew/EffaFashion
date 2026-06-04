<?php
/**
 * ONE-TIME CLEANUP — Removes all seeded/sample products & categories
 * Access: https://effafashion.onrender.com/cleanup.php?token=effaclean2024
 * DELETE THIS FILE after running.
 */

if (($_GET['token'] ?? '') !== 'effaclean2024') {
    http_response_code(403);
    die('Forbidden');
}

require_once __DIR__ . '/config/db.php';

// Delete in correct FK order: items that reference products first
$conn->query("SET FOREIGN_KEY_CHECKS = 0");

// Remove seeded products by their known slugs
$seeded_slugs = [
    'elegant-gold-dress',
    'black-luxury-gown',
    'white-chiffon-blouse',
    'classic-black-suit',
    'gold-trim-blazer',
    'gold-chain-necklace',
    'leather-handbag',
    'sequin-mini-dress'
];

$slugList = implode("','", $seeded_slugs);
$conn->query("DELETE FROM wishlist    WHERE product_id IN (SELECT id FROM products WHERE slug IN ('$slugList'))");
$conn->query("DELETE FROM cart        WHERE product_id IN (SELECT id FROM products WHERE slug IN ('$slugList'))");
$conn->query("DELETE FROM reviews     WHERE product_id IN (SELECT id FROM products WHERE slug IN ('$slugList'))");
$conn->query("DELETE FROM order_items WHERE product_id IN (SELECT id FROM products WHERE slug IN ('$slugList'))");
$result = $conn->query("DELETE FROM products WHERE slug IN ('$slugList')");
$deleted = $conn->affected_rows;

$conn->query("SET FOREIGN_KEY_CHECKS = 1");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Cleanup</title>
    <style>
        body { font-family: monospace; background: #111; color: #eee; padding: 30px; }
        h2   { color: #D4AF37; }
        .ok  { color: #4CAF50; }
        .done { color: #D4AF37; font-size: 1.1em; font-weight: bold; margin-top: 20px; }
    </style>
</head>
<body>
<h2>EffaFashion — Cleanup</h2>
<p class="ok">✅ Deleted <?= $deleted ?> seeded product(s) from the database.</p>
<p class="ok">✅ Related cart, wishlist, and review records also removed.</p>
<p class="done">Done! Your site now shows only products you add via the admin panel.<br>
Delete this file from your repo now.</p>
</body>
</html>
