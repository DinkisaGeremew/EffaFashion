<?php
require_once __DIR__ . '/includes/admin_header.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: ' . SITE_URL . '/admin/edit-product.php'); exit; }

$result  = $conn->query("SELECT * FROM products WHERE id=$id LIMIT 1");
$product = $result ? $result->fetch_assoc() : null;

if (!$product) {
    setFlash('error', 'Product not found.');
    header('Location: ' . SITE_URL . '/admin/edit-product.php');
    exit;
}

// Delete image file
if ($product['image'] && file_exists(UPLOAD_PATH . $product['image'])) {
    unlink(UPLOAD_PATH . $product['image']);
}

$conn->query("DELETE FROM products WHERE id=$id");
setFlash('success', "Product \"{$product['name']}\" deleted successfully.");
header('Location: ' . SITE_URL . '/admin/edit-product.php');
exit;
