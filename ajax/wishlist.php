<?php
require_once '../includes/functions.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['status' => 'login', 'message' => 'Please login first']);
    exit;
}

$product_id = (int)($_POST['product_id'] ?? 0);
if (!$product_id) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid product']);
    exit;
}

$result = toggleWishlist($product_id);
$result['count'] = getWishlistCount();
echo json_encode($result);
