<?php
// ── Database Configuration ────────────────────────────────
// Reads from environment variables on Render, falls back to
// XAMPP localhost defaults for local development.

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'effafashion');
define('DB_PORT', getenv('DB_PORT') ?: 3306);

// ── Site Configuration ────────────────────────────────────
// SITE_URL: set as env var on Render, auto-detect locally
if (getenv('SITE_URL')) {
    define('SITE_URL', rtrim(getenv('SITE_URL'), '/'));
} else {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
    define('SITE_URL', $protocol . '://' . $host . '/EffaFashion');
}

define('SITE_NAME',  'EffaFashion');
define('SITE_EMAIL', 'info@effafashion.com');
define('CURRENCY',   'ETB ');
define('CURRENCY_CODE', 'ETB');

// ── Upload Paths ──────────────────────────────────────────
define('UPLOAD_PATH',    $_SERVER['DOCUMENT_ROOT'] . '/uploads/products/');
define('UPLOAD_URL',     SITE_URL . '/uploads/products/');
define('STATIC_IMG_PATH', $_SERVER['DOCUMENT_ROOT'] . '/assets/images/products/');
define('STATIC_IMG_URL',  SITE_URL . '/assets/images/products/');

// ── Database Connection ───────────────────────────────────
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, (int)DB_PORT);

if ($conn->connect_error) {
    die('<div style="font-family:sans-serif;padding:40px;text-align:center;">
        <h2 style="color:#D4AF37;">⚠ Database Connection Failed</h2>
        <p>Please make sure the database is running.</p>
        <p style="color:#999;font-size:13px;">Error: ' . htmlspecialchars($conn->connect_error) . '</p>
    </div>');
}

$conn->set_charset('utf8mb4');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
