<?php
// ── Database Configuration ────────────────────────────────
// Production (Render): reads from environment variables
// Local (XAMPP):       falls back to localhost defaults

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'effafashion');
define('DB_SSL',  getenv('DB_SSL')  ?: 'false'); // 'true' on Aiven

// ── Site Configuration ────────────────────────────────────
$default_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
               . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');

define('SITE_NAME',      'EffaFashion');
define('SITE_URL',       rtrim(getenv('SITE_URL') ?: $default_url, '/'));
define('SITE_EMAIL',     'info@effafashion.com');
define('CURRENCY',       'ETB ');
define('CURRENCY_CODE',  'ETB');

// ── Upload Paths ──────────────────────────────────────────
define('UPLOAD_PATH',     $_SERVER['DOCUMENT_ROOT'] . '/uploads/products/');
define('UPLOAD_URL',      SITE_URL . '/uploads/products/');
define('STATIC_IMG_PATH', $_SERVER['DOCUMENT_ROOT'] . '/assets/images/products/');
define('STATIC_IMG_URL',  SITE_URL . '/assets/images/products/');

// ── Database Connection ───────────────────────────────────
if (DB_SSL === 'true') {
    // Aiven requires SSL — use MySQLi with SSL
    $conn = mysqli_init();
    mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
    mysqli_real_connect(
        $conn,
        DB_HOST,
        DB_USER,
        DB_PASS,
        DB_NAME,
        (int)DB_PORT,
        NULL,
        MYSQLI_CLIENT_SSL
    );
} else {
    // Local XAMPP — no SSL needed
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, (int)DB_PORT);
}

if ($conn->connect_error) {
    die('<div style="font-family:sans-serif;padding:40px;text-align:center;background:#000;color:#fff;min-height:100vh;display:flex;align-items:center;justify-content:center;">
        <div>
            <h2 style="color:#D4AF37;font-family:Georgia,serif;">⚠ Database Connection Failed</h2>
            <p style="color:#999;">Check your environment variables.</p>
            <p style="color:#555;font-size:12px;">' . htmlspecialchars($conn->connect_error) . '</p>
        </div>
    </div>');
}

$conn->set_charset('utf8mb4');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
