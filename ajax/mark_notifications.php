<?php
require_once '../includes/functions.php';
requireAdmin();
header('Content-Type: application/json');

// Mark contact messages as read
$conn->query("UPDATE contact_messages SET is_read=1 WHERE is_read=0");

// Insert seen records for all current pending orders
$orders = $conn->query("SELECT id FROM orders WHERE status='pending' OR payment_status='pending_verification'");
if ($orders) {
    while ($row = $orders->fetch_assoc()) {
        $conn->query("INSERT IGNORE INTO admin_notifications (type, reference_id, is_read)
                      VALUES ('order', {$row['id']}, 1)
                      ON DUPLICATE KEY UPDATE is_read=1");
    }
}

// Mark low stock as seen
$products = $conn->query("SELECT id FROM products WHERE stock <= 3 AND is_active=1");
if ($products) {
    while ($row = $products->fetch_assoc()) {
        $conn->query("INSERT IGNORE INTO admin_notifications (type, reference_id, is_read)
                      VALUES ('low_stock', {$row['id']}, 1)
                      ON DUPLICATE KEY UPDATE is_read=1");
    }
}

echo json_encode(['status' => 'success']);
