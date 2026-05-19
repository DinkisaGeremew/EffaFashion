<?php
$page_title = 'Create Account';
require_once 'includes/functions.php';

if (isLoggedIn()) { header('Location: ' . SITE_URL . '/index.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($_POST['full_name'] ?? '');
    $email     = sanitize($_POST['email']     ?? '');
    $phone     = sanitize($_POST['phone']     ?? '');
    $password  = $_POST['password']           ?? '';
    $confirm   = $_POST['confirm_password']   ?? '';

    if (empty($full_name) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $email_esc = $conn->real_escape_string($email);
        $check     = $conn->query("SELECT id FROM users WHERE email='$email_esc' LIMIT 1");
        if ($check && $check->num_rows > 0) {
            $error = 'An account with this email already exists.';
        } else {
            $hashed    = password_hash($password, PASSWORD_DEFAULT);
            $name_esc  = $conn->real_escape_string($full_name);
            $phone_esc = $conn->real_escape_string($phone);
            $conn->query("INSERT INTO users (full_name, email, phone, password, role) VALUES ('$name_esc','$email_esc','$phone_esc','$hashed','customer')");
            if ($conn->affected_rows > 0) {
                setFlash('success', 'Account created! Please log in.');
                header('Location: ' . SITE_URL . '/login.php');
                exit;
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>
<?php require_once 'includes/header.php'; ?>

<style>
.auth-split { display:grid; grid-template-columns:1fr 1fr; min-height:100vh; }
.auth-left  { background:#000; position:relative; display:flex; flex-direction:column;
              justify-content:center; padding:60px 50px; overflow:hidden; }
.auth-left-bg { position:absolute; inset:0; background-size:cover; background-position:center; opacity:0.25; }
.auth-left-overlay { position:absolute; inset:0; background:linear-gradient(135deg,rgba(0,0,0,0.95),rgba(26,18,0,0.85)); }
.auth-left-content { position:relative; z-index:2; }
.auth-left-logo { font-family:'Playfair Display',serif; font-size:32px; letter-spacing:3px; margin-bottom:48px; display:block; }
.auth-left-logo .brand-effa    { color:#D4AF37; }
.auth-left-logo .brand-fashion { color:#fff; font-weight:400; }
.auth-left h2 { font-family:'Playfair Display',serif; font-size:clamp(26px,3.5vw,42px); color:#fff; line-height:1.25; margin-bottom:20px; }
.auth-left h2 span { color:#D4AF37; }
.auth-left p  { color:rgba(255,255,255,0.55); font-size:15px; line-height:1.9; max-width:420px; margin-bottom:36px; }
.auth-feature { display:flex; align-items:center; gap:14px; margin-bottom:16px; }
.auth-feature-icon { width:42px; height:42px; border-radius:50%; background:rgba(212,175,55,0.15); border:1px solid rgba(212,175,55,0.3); display:flex; align-items:center; justify-content:center; color:#D4AF37; font-size:16px; flex-shrink:0; }
.auth-feature-text strong { display:block; color:#fff; font-size:14px; margin-bottom:2px; }
.auth-feature-text span   { color:rgba(255,255,255,0.45); font-size:13px; }
.auth-left-bottom { margin-top:48px; padding-top:28px; border-top:1px solid rgba(255,255,255,0.08); }
.auth-left-bottom p { color:rgba(255,255,255,0.35); font-size:13px; margin:0; }
.auth-left-bottom strong { color:#D4AF37; }
.auth-particle { position:absolute; border-radius:50%; background:rgba(212,175,55,0.12); animation:authFloat linear infinite; pointer-events:none; }
@keyframes authFloat { 0%{transform:translateY(100vh) rotate(0deg);opacity:0;} 10%{opacity:1;} 90%{opacity:0.4;} 100%{transform:translateY(-100px) rotate(360deg);opacity:0;} }
.auth-right { background:#fff; display:flex; align-items:center; justify-content:center; padding:40px 50px; }
.auth-form-wrap { width:100%; max-width:440px; }
.auth-form-wrap .auth-title    { font-family:'Playfair Display',serif; font-size:28px; color:#111; margin-bottom:6px; }
.auth-form-wrap .auth-subtitle { color:#999; font-size:14px; margin-bottom:28px; }
.auth-form-wrap .auth-footer   { text-align:center; margin-top:20px; font-size:14px; color:#999; }
.auth-form-wrap .auth-footer a { color:#D4AF37; font-weight:600; }
.auth-divider { display:flex; align-items:center; gap:12px; margin:16px 0; color:#ccc; font-size:13px; }
.auth-divider::before, .auth-divider::after { content:''; flex:1; height:1px; background:#eee; }
.strength-bar  { height:4px; background:#eee; border-radius:2px; margin-top:6px; overflow:hidden; }
.strength-fill { height:100%; width:0; border-radius:2px; transition:all 0.3s; }
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
             style="background-image:url('https://images.unsplash.com/photo-1600185365483-26d7a4cc7519?w=800&h=1200&fit=crop&q=80');"></div>
        <div class="auth-left-overlay"></div>
        <div id="authParticles"></div>

        <div class="auth-left-content">
            <a href="<?= SITE_URL ?>/index.php" class="auth-left-logo">
                <span class="brand-effa">EFFA</span><span class="brand-fashion">FASHION</span>
            </a>

            <h2>Join the <span>EffaFashion</span><br>Community</h2>
            <p>Create your free account today and unlock a world of premium fashion, exclusive deals, and a seamless shopping experience tailored just for you.</p>

            <div class="auth-feature">
                <div class="auth-feature-icon"><i class="fas fa-gift"></i></div>
                <div class="auth-feature-text">
                    <strong>Welcome Offer</strong>
                    <span>20% off your first order</span>
                </div>
            </div>
            <div class="auth-feature">
                <div class="auth-feature-icon"><i class="fas fa-shipping-fast"></i></div>
                <div class="auth-feature-text">
                    <strong>Free Delivery</strong>
                    <span>On every single order</span>
                </div>
            </div>
            <div class="auth-feature">
                <div class="auth-feature-icon"><i class="fas fa-star"></i></div>
                <div class="auth-feature-text">
                    <strong>Exclusive Access</strong>
                    <span>New arrivals & member-only sales</span>
                </div>
            </div>
            <div class="auth-feature">
                <div class="auth-feature-icon"><i class="fas fa-shield-alt"></i></div>
                <div class="auth-feature-text">
                    <strong>Secure & Private</strong>
                    <span>Your data is always protected</span>
                </div>
            </div>

            <!-- Testimonial snippet -->
            <div style="margin-top:36px;background:rgba(212,175,55,0.08);border:1px solid rgba(212,175,55,0.2);border-radius:12px;padding:18px 20px;">
                <div style="display:flex;gap:4px;margin-bottom:8px;">
                    <?php for($i=0;$i<5;$i++): ?><i class="fas fa-star" style="color:#D4AF37;font-size:12px;"></i><?php endfor; ?>
                </div>
                <p style="color:rgba(255,255,255,0.7);font-size:13px;line-height:1.7;margin:0 0 10px;">
                    "EffaFashion has the best quality shoes I've ever bought. Fast delivery and amazing style!"
                </p>
                <div style="font-size:12px;color:#D4AF37;font-weight:600;">— Kebede A., Addis Ababa</div>
            </div>

            <div class="auth-left-bottom">
                <p>&copy; <?= date('Y') ?> <strong>EffaFashion</strong> · Burayu Dire, Ethiopia</p>
            </div>
        </div>
    </div>

    <!-- ── RIGHT: Register Form ── -->
    <div class="auth-right">
        <div class="auth-form-wrap">
            <h2 class="auth-title">Create Account</h2>
            <p class="auth-subtitle">Join EffaFashion and enjoy exclusive member benefits</p>

            <?php if ($error): ?>
                <div class="alert alert-error"><i class="fas fa-times-circle"></i> <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form id="registerForm" method="POST">

                <div class="form-group">
                    <label class="form-label">Full Name <span class="required">*</span></label>
                    <div style="position:relative;">
                        <input type="text" name="full_name" class="form-control" placeholder="Your full name"
                               value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required style="padding-left:42px;">
                        <i class="fas fa-user" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#ccc;font-size:14px;"></i>
                    </div>
                    <div class="form-error"></div>
                </div>

                <div class="form-group">
                    <label class="form-label">Email Address <span class="required">*</span></label>
                    <div style="position:relative;">
                        <input type="email" name="email" class="form-control" placeholder="your@email.com"
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required style="padding-left:42px;">
                        <i class="fas fa-envelope" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#ccc;font-size:14px;"></i>
                    </div>
                    <div class="form-error"></div>
                </div>

                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <div style="position:relative;">
                        <input type="tel" name="phone" class="form-control" placeholder="+251 910 624 704"
                               value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" style="padding-left:42px;">
                        <i class="fas fa-phone" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#ccc;font-size:14px;"></i>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Password <span class="required">*</span></label>
                        <div class="password-toggle" style="position:relative;">
                            <input type="password" name="password" class="form-control"
                                   placeholder="Min. 8 characters" required style="padding-left:42px;"
                                   oninput="checkStrength(this.value)">
                            <i class="fas fa-lock" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#ccc;font-size:14px;"></i>
                            <i class="fas fa-eye toggle-eye" style="position:absolute;right:14px;top:50%;transform:translateY(-50%);color:#ccc;cursor:pointer;"></i>
                        </div>
                        <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
                        <div id="strengthLabel" style="font-size:11px;margin-top:3px;color:#999;"></div>
                        <div class="form-error"></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm Password <span class="required">*</span></label>
                        <div class="password-toggle" style="position:relative;">
                            <input type="password" name="confirm_password" class="form-control"
                                   placeholder="Repeat password" required style="padding-left:42px;"
                                   oninput="checkMatch()">
                            <i class="fas fa-lock" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#ccc;font-size:14px;"></i>
                            <i class="fas fa-eye toggle-eye" style="position:absolute;right:14px;top:50%;transform:translateY(-50%);color:#ccc;cursor:pointer;"></i>
                        </div>
                        <div id="matchMsg" style="font-size:11px;margin-top:3px;"></div>
                        <div class="form-error"></div>
                    </div>
                </div>

                <div style="margin-bottom:20px;">
                    <label style="display:flex;align-items:flex-start;gap:10px;font-size:13px;cursor:pointer;color:#666;">
                        <input type="checkbox" required style="accent-color:#D4AF37;margin-top:2px;flex-shrink:0;">
                        I agree to the <a href="#" style="color:#D4AF37;">Terms of Service</a> and <a href="#" style="color:#D4AF37;">Privacy Policy</a>
                    </label>
                </div>

                <button type="submit" class="btn btn-gold btn-block btn-lg" style="border-radius:10px;">
                    <i class="fas fa-user-plus"></i> Create Account
                </button>
            </form>

            <div class="auth-divider">or</div>

            <div class="auth-footer">
                Already have an account?
                <a href="<?= SITE_URL ?>/login.php">Sign in <i class="fas fa-arrow-right" style="font-size:11px;"></i></a>
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

// Password strength
function checkStrength(val) {
    const fill  = document.getElementById('strengthFill');
    const label = document.getElementById('strengthLabel');
    const score = [val.length>=8, /[A-Za-z]/.test(val), /[0-9]/.test(val), /[^A-Za-z0-9]/.test(val)].filter(Boolean).length;
    const levels = [{w:'0%',bg:'#eee',t:''},{w:'25%',bg:'#ef4444',t:'Weak'},{w:'50%',bg:'#f59e0b',t:'Fair'},{w:'75%',bg:'#3b82f6',t:'Good'},{w:'100%',bg:'#22c55e',t:'Strong'}];
    fill.style.width = levels[score].w; fill.style.background = levels[score].bg;
    label.textContent = levels[score].t; label.style.color = levels[score].bg;
}

// Password match
function checkMatch() {
    const np  = document.querySelector('[name="password"]').value;
    const cp  = document.querySelector('[name="confirm_password"]').value;
    const msg = document.getElementById('matchMsg');
    if (!cp) { msg.textContent=''; return; }
    msg.innerHTML = np===cp
        ? '<i class="fas fa-check-circle" style="color:#22c55e;"></i> <span style="color:#22c55e;">Match</span>'
        : '<i class="fas fa-times-circle" style="color:#ef4444;"></i> <span style="color:#ef4444;">No match</span>';
}
</script>

<?php require_once 'includes/footer.php'; ?>
