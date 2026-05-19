<?php
require_once '../includes/functions.php';
header('Content-Type: application/json');

$code  = sanitize($_POST['code']  ?? '');
$total = (float)($_POST['total']  ?? 0);

if (empty($code)) {
    echo json_encode(['valid' => false, 'message' => 'Please enter a coupon code']);
    exit;
}

$result = validateCoupon($code, $total);

if ($result['valid']) {
    $new_total = $total - $result['discount'];
    $_SESSION['coupon_code'] = strtoupper(trim($code));
    // Increment used count
    $code_esc = $conn->real_escape_string(strtoupper(trim($code)));
    $conn->query("UPDATE coupons SET used_count = used_count + 1 WHERE code = '$code_esc'");
    echo json_encode([
        'valid'               => true,
        'message'             => $result['message'],
        'discount'            => $result['discount'],
        'discount_formatted'  => '-' . formatPrice($result['discount']),
        'new_total'           => $new_total,
        'new_total_formatted' => formatPrice($new_total)
    ]);
} else {
    echo json_encode(['valid' => false, 'message' => $result['message']]);
}
