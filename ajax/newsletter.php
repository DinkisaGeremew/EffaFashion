<?php
require_once '../includes/functions.php';
header('Content-Type: application/json');

$email = sanitize($_POST['email'] ?? '');

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Please enter a valid email address']);
    exit;
}

$e = $conn->real_escape_string($email);
$check = $conn->query("SELECT id FROM newsletter WHERE email='$e' LIMIT 1");

if ($check && $check->num_rows > 0) {
    echo json_encode(['status' => 'info', 'message' => 'You are already subscribed!']);
} else {
    $conn->query("INSERT INTO newsletter (email) VALUES ('$e')");
    echo json_encode(['status' => 'success', 'message' => 'Thank you for subscribing!']);
}
