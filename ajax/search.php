<?php
require_once '../includes/functions.php';
header('Content-Type: application/json');

$q = sanitize($_GET['q'] ?? '');
if (strlen($q) < 2) { echo json_encode([]); exit; }

$q_esc   = $conn->real_escape_string($q);
$result  = $conn->query("SELECT id, name, slug, price, sale_price, image FROM products WHERE is_active=1 AND (name LIKE '%$q_esc%' OR description LIKE '%$q_esc%') ORDER BY is_featured DESC, views DESC LIMIT 8");
$products = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $products[] = [
            'id'    => $row['id'],
            'name'  => $row['name'],
            'slug'  => $row['slug'],
            'price' => formatPrice($row['sale_price'] ?: $row['price']),
            'image' => getProductImage($row['image']),
            'url'   => SITE_URL . '/product-details.php?slug=' . urlencode($row['slug'])
        ];
    }
}

echo json_encode($products);
