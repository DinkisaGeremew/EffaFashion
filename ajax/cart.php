<?php
require_once '../includes/functions.php';
header('Content-Type: application/json');

$action     = $_POST['action'] ?? '';
$product_id = (int)($_POST['product_id'] ?? 0);
$cart_id    = (int)($_POST['cart_id']    ?? 0);
$quantity   = max(1, (int)($_POST['quantity'] ?? 1));
$size       = sanitize($_POST['size']  ?? '');
$color      = sanitize($_POST['color'] ?? '');

switch ($action) {

    case 'add':
        if (!$product_id) { echo json_encode(['status'=>'error','message'=>'Invalid product']); exit; }
        $product = getProductById($product_id);
        if (!$product) { echo json_encode(['status'=>'error','message'=>'Product not found']); exit; }
        if ($product['stock'] < 1) { echo json_encode(['status'=>'error','message'=>'Out of stock']); exit; }
        addToCart($product_id, $quantity, $size, $color);
        echo json_encode([
            'status'       => 'success',
            'message'      => 'Added to cart',
            'product_name' => $product['name'],
            'cart_count'   => getCartCount()
        ]);
        break;

    case 'update':
        if (!isLoggedIn()) { echo json_encode(['status'=>'error','message'=>'Not logged in']); exit; }
        $uid = (int)$_SESSION['user_id'];
        $conn->query("UPDATE cart SET quantity=$quantity WHERE id=$cart_id AND user_id=$uid");
        $items    = getCartItems();
        $subtotal = getCartTotal();
        $shipping = $subtotal >= 20000 ? 0 : 2500;
        $total    = $subtotal + $shipping;
        // Get updated row subtotal
        $row = $conn->query("SELECT c.quantity, p.price, p.sale_price FROM cart c JOIN products p ON c.product_id=p.id WHERE c.id=$cart_id LIMIT 1");
        $r   = $row ? $row->fetch_assoc() : null;
        $sub = $r ? (($r['sale_price'] ?: $r['price']) * $r['quantity']) : 0;
        echo json_encode([
            'status'             => 'success',
            'subtotal'           => $subtotal,
            'subtotal_formatted' => formatPrice($subtotal),
            'total'              => $total,
            'total_formatted'    => formatPrice($total),
            'subtotal_row'       => $sub,
            'cart_count'         => getCartCount()
        ]);
        break;

    case 'remove':
        if (!isLoggedIn()) { echo json_encode(['status'=>'error','message'=>'Not logged in']); exit; }
        $uid = (int)$_SESSION['user_id'];
        $conn->query("DELETE FROM cart WHERE id=$cart_id AND user_id=$uid");
        $subtotal = getCartTotal();
        $shipping = $subtotal >= 20000 ? 0 : 2500;
        $total    = $subtotal + $shipping;
        echo json_encode([
            'status'             => 'success',
            'subtotal'           => $subtotal,
            'subtotal_formatted' => formatPrice($subtotal),
            'total'              => $total,
            'total_formatted'    => formatPrice($total),
            'cart_count'         => getCartCount()
        ]);
        break;

    case 'count':
        echo json_encode(['count' => getCartCount()]);
        break;

    default:
        echo json_encode(['status'=>'error','message'=>'Invalid action']);
}
