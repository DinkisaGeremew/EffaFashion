<?php
/**
 * ONE-TIME ADMIN RESET — DELETE AFTER USE
 * https://effafashion.onrender.com/resetadmin.php?token=resetadmin2024
 */
if (($_GET['token'] ?? '') !== 'resetadmin2024') { http_response_code(403); die('Forbidden'); }

require_once __DIR__ . '/config/db.php';

$email    = 'admin@effafashion.com';
$password = 'admin123';
$hash     = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
$name     = 'Admin EffaFashion';

$e = $conn->real_escape_string($email);
$h = $conn->real_escape_string($hash);
$n = $conn->real_escape_string($name);

// Check if admin exists
$res = $conn->query("SELECT id FROM users WHERE email='$e' LIMIT 1");

if ($res && $res->num_rows > 0) {
    // Update password
    $conn->query("UPDATE users SET password='$h', is_active=1, role='admin' WHERE email='$e'");
    $action = 'Updated existing admin password.';
} else {
    // Insert admin
    $conn->query("INSERT INTO users (full_name, email, password, role, is_active) VALUES ('$n','$e','$h','admin',1)");
    $action = 'Created new admin user.';
}
?>
<!DOCTYPE html>
<html>
<head><title>Admin Reset</title>
<style>body{font-family:monospace;background:#111;color:#eee;padding:40px;} .ok{color:#4CAF50;font-size:1.2em;} strong{color:#D4AF37;}</style>
</head>
<body>
<h2 style="color:#D4AF37;">Admin Reset</h2>
<p class="ok">✅ <?= $action ?></p>
<p>Email: <strong><?= $email ?></strong></p>
<p>Password: <strong><?= $password ?></strong></p>
<p style="margin-top:30px;color:#f44336;"><strong>Delete this file from your repo immediately after logging in.</strong></p>
<p><a href="/login.php" style="color:#D4AF37;">Go to Login →</a></p>
</body>
</html>
