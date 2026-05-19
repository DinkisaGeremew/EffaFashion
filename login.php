<?php
$page_title = 'Login';
require_once 'includes/functions.php';

if (isLoggedIn()) { header('Location: ' . SITE_URL . '/index.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = sanitize($_POST['email']    ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $email_esc = $conn->real_escape_string($email);
        $result    = $conn->query("SELECT * FROM users WHERE email='$email_esc' AND is_active=1 LIMIT 1");
        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id']    = $user['id'];
                $_SESSION['user_name']  = $user['full_name'];
                $_SESSION['user_role']  = $user['role'];
                $_SESSION['user_email'] = $user['email'];
                if (!empty($_SESSION['cart'])) {
                    foreach ($_SESSION['cart'] as $item) {
                        addToCart($item['product_id'], $item['quantity'], $item['size'] ?? '', $item['color'] ?? '');
                    }
                    unset($_SESSION['cart']);
                }
                setFlash('success', 'Welcome back, ' . $user['full_name'] . '!');
                $redirect = $_GET['redirect'] ?? ($user['role'] === 'admin' ? SITE_URL . '/admin/dashboard.php' : SITE_URL . '/index.php');
                header('Location: ' . $redirect);
                exit;
            } else {
                $error = 'Invalid email or password.';
            }
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<?php require_once 'includes/header.php'; ?>

<style>
/* ── Split Auth Layout ───────────────────────────────────── */
.auth-split { display:grid; grid-template-columns:1fr 1fr; min-height:100vh; }

/* Left panel */
.auth-left { background:#000; position:relative; display:flex; flex-direction:column;
             justify-content:center; padding:60px 50px; overflow:hidden; }
.auth-left-bg { position:absolute; inset:0; background-size:cover; background-position:center;
                opacity:0.25; }
.auth-left-overlay { position:absolute; inset:0;
    background:linear-gradient(135deg,rgba(0,0,0,0.95) 0%,rgba(26,18,0,0.85) 100%); }
.auth-left-content { position:relative; z-index:2; }
.auth-left-logo { font-family:'Playfair Display',serif; font-size:32px; letter-spacing:3px;
                  margin-bottom:48px; display:block; }
.auth-left-logo .brand-effa    { color:#D4AF37; }
.auth-left-logo .brand-fashion { color:#fff; font-weight:400; }
.auth-left h2 { font-family:'Playfair Display',serif; font-size:clamp(28px,3.5vw,44px);
                color:#fff; line-height:1.25; margin-bottom:20px; }
.auth-left h2 span { color:#D4AF37; }
.auth-left p  { color:rgba(255,255,255,0.55); font-size:15px; line-height:1.9;
                max-width:420px; margin-bottom:36px; }
.auth-feature { display:flex; align-items:center; gap:14px; margin-bottom:16px; }
.auth-feature-icon { width:42px; height:42px; border-radius:50%;
                     background:rgba(212,175,55,0.15); border:1px solid rgba(212,175,55,0.3);
                     display:flex; align-items:center; justify-content:center;
                     color:#D4AF37; font-size:16px; flex-shrink:0; }
.auth-feature-text strong { display:block; color:#fff; font-size:14px; margin-bottom:2px; }
.auth-feature-text span   { color:rgba(255,255,255,0.45); font-size:13px; }
.auth-left-bottom { margin-top:48px; padding-top:28px;
                    border-top:1px solid rgba(255,255,255,0.08); }
.auth-left-bottom p { color:rgba(255,255,255,0.35); font-size:13px; margin:0; }
.auth-left-bottom strong { color:#D4AF37; }

/* Floating particles */
.auth-particle { position:absolute; border-radius:50%;
                 background:rgba(212,175,55,0.12);
                 animation:authFloat linear infinite; pointer-events:none; }
@keyframes authFloat {
    0%   { transform:translateY(100vh) rotate(0deg); opacity:0; }
    10%  { opacity:1; }
    90%  { opacity:0.4; }
    100% { transform:translateY(-100px) rotate(360deg); opacity:0; }
}

/* Right panel */
.auth-right { background:#fff; display:flex; align-items:center; justify-content:center;
              padding:40px 50px; }
.auth-form-wrap { width:100%; max-width:420px; }
.auth-form-wrap .auth-title    { font-family:'Playfair Display',serif; font-size:28px;
                                  color:#111; margin-bottom:6px; }
.auth-form-wrap .auth-subtitle { color:#999; font-size:14px; margin-bottom:32px; }
.auth-form-wrap .auth-footer   { text-align:center; margin-top:24px; font-size:14px; color:#999; }
.auth-form-wrap .auth-footer a { color:#D4AF37; font-weight:600; }
.auth-form-wrap .auth-footer a:hover { text-decoration:underline; }
.auth-divider { display:flex; align-items:center; gap:12px; margin:20px 0;
                color:#ccc; font-size:13px; }
.auth-divider::before, .auth-divider::after { content:''; flex:1; height:1px; background:#eee; }

@media(max-width:768px) {
    .auth-split { grid-template-columns:1fr; }
    .auth-left  { display:none; }
    .auth-right { padding:40px 24px; min-height:100vh; }
}
</style>

<div class="auth-split">

    <!-- ── LEFT: Intro Panel ── -->
    <div class="auth-left">
        <div class="auth-left-bg"
             style="background-image:url('https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=800&h=1200&fit=crop&q=80');"></div>
        <div class="auth-left-overlay"></div>

        <!-- Particles -->
        <div id="authParticles"></div>

        <div class="auth-left-content">
            <a href="<?= SITE_URL ?>/index.php" class="auth-left-logo">
                <span class="brand-effa">EFFA</span><span class="brand-fashion">FASHION</span>
            </a>

            <h2>Welcome<br>Back to <span>Luxury</span></h2>
            <p>Sign in to access your exclusive fashion collection, track your orders, and enjoy a personalised shopping experience.</p>

            <div class="auth-feature">
                <div class="auth-feature-icon"><i class="fas fa-shipping-fast"></i></div>
                <div class="auth-feature-text">
                    <strong>Free Delivery</strong>
                    <span>On every order, always</span>
                </div>
            </div>
            <div class="auth-feature">
                <div class="auth-feature-icon"><i class="fas fa-heart"></i></div>
                <div class="auth-feature-text">
                    <strong>Wishlist & Favourites</strong>
                    <span>Save items you love</span>
                </div>
            </div>
            <div class="auth-feature">
                <div class="auth-feature-icon"><i class="fas fa-box"></i></div>
                <div class="auth-feature-text">
                    <strong>Order Tracking</strong>
                    <span>Real-time order updates</span>
                </div>
            </div>
            <div class="auth-feature">
                <div class="auth-feature-icon"><i class="fas fa-tag"></i></div>
                <div class="auth-feature-text">
                    <strong>Exclusive Deals</strong>
                    <span>Member-only discounts & offers</span>
                </div>
            </div>

            <div class="auth-left-bottom">
                <p>&copy; <?= date('Y') ?> <strong>EffaFashion</strong> · Burayu Dire, Ethiopia</p>
            </div>
        </div>
    </div>

    <!-- ── RIGHT: Login Form ── -->
    <div class="auth-right">
        <div class="auth-form-wrap">
            <!-- Mobile logo -->
            <a href="<?= SITE_URL ?>/index.php" style="display:none;font-family:'Playfair Display',serif;font-size:26px;letter-spacing:2px;margin-bottom:28px;text-decoration:none;" class="mobile-logo">
                <span style="color:#D4AF37;">EFFA</span><span style="color:#000;">FASHION</span>
            </a>

            <h2 class="auth-title">Sign In</h2>
            <p class="auth-subtitle">Enter your credentials to access your account</p>

            <?php if ($error): ?>
                <div class="alert alert-error"><i class="fas fa-times-circle"></i> <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form id="loginForm" method="POST">
                <div class="form-group">
                    <label class="form-label">Email Address <span class="required">*</span></label>
                    <div style="position:relative;">
                        <input type="email" name="email" class="form-control" placeholder="your@email.com"
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required
                               style="padding-left:42px;">
                        <i class="fas fa-envelope" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#ccc;font-size:14px;"></i>
                    </div>
                    <div class="form-error"></div>
                </div>

                <div class="form-group">
                    <label class="form-label">Password <span class="required">*</span></label>
                    <div class="password-toggle" style="position:relative;">
                        <input type="password" name="password" class="form-control"
                               placeholder="Enter your password" required style="padding-left:42px;">
                        <i class="fas fa-lock" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#ccc;font-size:14px;"></i>
                        <i class="fas fa-eye toggle-eye" style="position:absolute;right:14px;top:50%;transform:translateY(-50%);color:#ccc;cursor:pointer;"></i>
                    </div>
                    <div class="form-error"></div>
                </div>

                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
                    <label style="display:flex;align-items:center;gap:8px;font-size:14px;cursor:pointer;color:#555;">
                        <input type="checkbox" name="remember" style="accent-color:#D4AF37;"> Remember me
                    </label>
                    <a href="#" style="font-size:14px;color:#D4AF37;font-weight:500;">Forgot password?</a>
                </div>

                <button type="submit" class="btn btn-gold btn-block btn-lg" style="border-radius:10px;">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>
            </form>

            <div class="auth-divider">or</div>

            <div class="auth-footer">
                Don't have an account?
                <a href="<?= SITE_URL ?>/register.php">Create one free <i class="fas fa-arrow-right" style="font-size:11px;"></i></a>
            </div>
        </div>
    </div>
</div>

<script>
// Particles
(function() {
    const wrap = document.getElementById('authParticles');
    if (!wrap) return;
    for (let i = 0; i < 12; i++) {
        const p = document.createElement('div');
        p.className = 'auth-particle';
        const size = Math.random() * 10 + 4;
        p.style.cssText = `width:${size}px;height:${size}px;left:${Math.random()*100}%;bottom:-20px;
            animation-duration:${Math.random()*14+8}s;animation-delay:${Math.random()*6}s;`;
        wrap.appendChild(p);
    }
})();

// Show/hide password
document.querySelectorAll('.toggle-eye').forEach(icon => {
    icon.addEventListener('click', function() {
        const input = this.closest('.password-toggle')?.querySelector('input');
        if (!input) return;
        input.type = input.type === 'password' ? 'text' : 'password';
        this.classList.toggle('fa-eye');
        this.classList.toggle('fa-eye-slash');
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
