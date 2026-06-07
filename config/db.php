<?php
// ── Environment helper — checks getenv, $_ENV, $_SERVER ──
function env($key, $default = '') {
    $val = getenv($key);
    if ($val !== false) return $val;
    if (isset($_ENV[$key]))    return $_ENV[$key];
    if (isset($_SERVER[$key])) return $_SERVER[$key];
    return $default;
}

// ── Database Configuration ────────────────────────────────
define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_PORT', env('DB_PORT', '3306'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));
define('DB_NAME', env('DB_NAME', 'effafashion'));
define('DB_SSL',  env('DB_SSL',  'false'));

// ── Site Configuration ────────────────────────────────────
$default_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
               . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');

define('SITE_NAME',      'EffaFashion');
define('SITE_URL',       rtrim(env('SITE_URL', $default_url), '/'));
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
    $conn = mysqli_init();
    mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
    @mysqli_real_connect(
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
    $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, (int)DB_PORT);
}

if ($conn->connect_error) {
    $err = htmlspecialchars($conn->connect_error);
    die('
    <div style="font-family:sans-serif;padding:40px;text-align:center;background:#000;color:#fff;min-height:100vh;display:flex;align-items:center;justify-content:center;">
        <div>
            <h2 style="color:#D4AF37;font-family:Georgia,serif;">&#9888; Database Connection Failed</h2>
            <p style="color:#999;">Check your environment variables on Render.</p>
            <p style="color:#e57373;font-size:13px;background:#1a0000;padding:12px 20px;border-radius:6px;display:inline-block;">'.$err.'</p>
            <p style="color:#555;font-size:11px;margin-top:16px;">
                DB_HOST='.htmlspecialchars(DB_HOST).' &nbsp;|&nbsp;
                DB_PORT='.DB_PORT.' &nbsp;|&nbsp;
                DB_USER='.htmlspecialchars(DB_USER).' &nbsp;|&nbsp;
                DB_NAME='.htmlspecialchars(DB_NAME).' &nbsp;|&nbsp;
                DB_SSL='.DB_SSL.'
            </p>
        </div>
    </div>');
}

$conn->set_charset('utf8mb4');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
