<?php
if (($_GET['token'] ?? '') !== 'resetadmin2024') { http_response_code(403); die('Forbidden'); }
require_once __DIR__ . '/config/db.php';
$hash = password_hash('admin123', PASSWORD_BCRYPT, ['cost' => 12]);
$h = $conn->real_escape_string($hash);
$res = $conn->query("SELECT id FROM users WHERE email='admin@effafashion.com' LIMIT 1");
if ($res && $res->num_rows > 0) {
    $conn->query("UPDATE users SET password='$h', is_active=1, role='admin' WHERE email='admin@effafashion.com'");
    echo "✅ Password updated. <a href='/login.php'>Login now</a>";
} else {
    $conn->query("INSERT INTO users (full_name,email,password,role,is_active) VALUES ('Admin','admin@effafashion.com','$h','admin',1)");
    echo "✅ Admin created. <a href='/login.php'>Login now</a>";
}
