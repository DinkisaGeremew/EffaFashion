<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'effafashion');

// Site Configuration
define('SITE_NAME', 'EffaFashion');
define('SITE_URL', 'http://localhost/EffaFashion');
define('SITE_EMAIL', 'info@effafashion.com');
define('CURRENCY', 'ETB ');
define('CURRENCY_CODE', 'ETB');

// Upload paths (admin-uploaded images)
define('UPLOAD_PATH', $_SERVER['DOCUMENT_ROOT'] . '/EffaFashion/uploads/products/');
define('UPLOAD_URL', SITE_URL . '/uploads/products/');

// Static product images (assets/images/products/)
define('STATIC_IMG_PATH', $_SERVER['DOCUMENT_ROOT'] . '/EffaFashion/assets/images/products/');
define('STATIC_IMG_URL',  SITE_URL . '/assets/images/products/');

// Create database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die('<div style="font-family:sans-serif;padding:40px;text-align:center;">
        <h2 style="color:#D4AF37;">⚠ Database Connection Failed</h2>
        <p>Please make sure XAMPP is running and the database is imported.</p>
        <p style="color:#999;">Error: ' . $conn->connect_error . '</p>
    </div>');
}

// Set charset
$conn->set_charset('utf8mb4');

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
