<?php
$page_title = 'Settings';
require_once 'includes/functions.php';
requireLogin();

$user       = getCurrentUser();
$active_tab = $_GET['tab'] ?? 'profile';
$error      = $success = '';

// ── Avatar URL helper ─────────────────────────────────────
function getAvatarUrl($user) {
    if (!empty($user['profile_image'])) {
        $path = $_SERVER['DOCUMENT_ROOT'] . '/EffaFashion/uploads/avatars/' . $user['profile_image'];
        if (file_exists($path)) return SITE_URL . '/uploads/avatars/' . rawurlencode($user['profile_image']);
    }
    // Gravatar fallback
    return 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($user['email']))) . '?s=200&d=mp';
}

// ── Handle POST ───────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    /* ── Update Profile + Avatar ── */
    if ($action === 'update_profile') {
        $full_name = sanitize($_POST['full_name'] ?? '');
        $phone     = sanitize($_POST['phone']     ?? '');
        $address   = sanitize($_POST['address']   ?? '');
        $city      = sanitize($_POST['city']      ?? '');
        $country   = sanitize($_POST['country']   ?? 'Ethiopia');

        if (empty($full_name)) {
            $error = 'Full name is required.';
        } else {
            $uid       = (int)$_SESSION['user_id'];
            $img_update = '';

            // Handle avatar upload
            if (!empty($_FILES['avatar']['name'])) {
                $ext     = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg','jpeg','png','webp','gif'];
                $max_size = 3 * 1024 * 1024; // 3MB

                if (!in_array($ext, $allowed)) {
                    $error = 'Invalid image format. Use JPG, PNG, WebP or GIF.';
                } elseif ($_FILES['avatar']['size'] > $max_size) {
                    $error = 'Image too large. Maximum size is 3MB.';
                } else {
                    $avatar_dir  = $_SERVER['DOCUMENT_ROOT'] . '/EffaFashion/uploads/avatars/';
                    if (!is_dir($avatar_dir)) mkdir($avatar_dir, 0755, true);

                    // Delete old avatar
                    if (!empty($user['profile_image']) && file_exists($avatar_dir . $user['profile_image'])) {
                        unlink($avatar_dir . $user['profile_image']);
                    }

                    $avatar_name = 'avatar_' . $uid . '_' . time() . '.' . $ext;
                    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $avatar_dir . $avatar_name)) {
                        $img_update = ", profile_image='" . $conn->real_escape_string($avatar_name) . "'";
                    } else {
                        $error = 'Failed to upload image. Please try again.';
                    }
                }
            }

            if (!$error) {
                $n  = $conn->real_escape_string($full_name);
                $p  = $conn->real_escape_string($phone);
                $a  = $conn->real_escape_string($address);
                $c  = $conn->real_escape_string($city);
                $co = $conn->real_escape_string($country);
                $conn->query("UPDATE users SET full_name='$n', phone='$p', address='$a', city='$c', country='$co' $img_update WHERE id=$uid");
                $_SESSION['user_name']  = $full_name;
                unset($_SESSION['user_cache']); // clear cache
                $success    = 'Profile updated successfully!';
                $user       = getCurrentUser();
                $active_tab = 'profile';
            }
        }
    }

    /* ── Change Password ── */
    if ($action === 'change_password') {
        $current  = $_POST['current_password']  ?? '';
        $new_pass = $_POST['new_password']       ?? '';
        $confirm  = $_POST['confirm_password']   ?? '';

        if (!password_verify($current, $user['password'])) {
            $error = 'Current password is incorrect.';
        } elseif (strlen($new_pass) < 8) {
            $error = 'New password must be at least 8 characters.';
        } elseif (!preg_match('/[A-Za-z]/', $new_pass) || !preg_match('/[0-9]/', $new_pass)) {
            $error = 'Password must contain at least one letter and one number.';
        } elseif ($new_pass !== $confirm) {
            $error = 'New passwords do not match.';
        } else {
            $uid    = (int)$_SESSION['user_id'];
            $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
            $conn->query("UPDATE users SET password='$hashed' WHERE id=$uid");
            $success    = 'Password changed successfully! Please use your new password next time you log in.';
            $active_tab = 'password';
        }
    }
}

$avatar_url = getAvatarUrl($user);
?>
<?php require_once 'includes/header.php'; ?>
<?php require_once 'includes/navbar.php'; ?>

<div class="breadcrumb">
    <div class="container">
        <ul class="breadcrumb-list">
            <li><a href="<?= SITE_URL ?>">Home</a></li>
            <li class="active">Settings</li>
        </ul>
    </div>
</div>

<section class="section section-gray">
    <div class="container">
        <div class="settings-layout">

            <!-- Sidebar -->
            <div class="settings-sidebar">
                <div class="settings-sidebar-header">
                    <!-- Clickable avatar -->
                    <div class="settings-avatar-wrap" onclick="document.getElementById('quickAvatarInput').click()" title="Click to change photo">
                        <img src="<?= $avatar_url ?>" alt="<?= htmlspecialchars($user['full_name']) ?>" id="sidebarAvatar">
                        <div class="settings-avatar-overlay">
                            <i class="fas fa-camera"></i>
                        </div>
                    </div>
                    <!-- Quick avatar upload (sidebar click) -->
                    <form id="quickAvatarForm" method="POST" enctype="multipart/form-data" style="display:none;">
                        <input type="hidden" name="action" value="update_profile">
                        <input type="hidden" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>">
                        <input type="hidden" name="phone"     value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                        <input type="hidden" name="address"   value="<?= htmlspecialchars($user['address'] ?? '') ?>">
                        <input type="hidden" name="city"      value="<?= htmlspecialchars($user['city'] ?? '') ?>">
                        <input type="hidden" name="country"   value="<?= htmlspecialchars($user['country'] ?? 'Ethiopia') ?>">
                        <input type="file" id="quickAvatarInput" name="avatar" accept="image/*" style="display:none;"
                               onchange="previewAndSubmitAvatar(this)">
                    </form>
                    <h4><?= htmlspecialchars($user['full_name']) ?></h4>
                    <p><?= htmlspecialchars($user['email']) ?></p>
                </div>
                <nav class="settings-nav">
                    <a href="?tab=profile"  class="<?= $active_tab === 'profile'  ? 'active' : '' ?>">
                        <i class="fas fa-user-edit"></i> Change Profile
                    </a>
                    <a href="?tab=password" class="<?= $active_tab === 'password' ? 'active' : '' ?>">
                        <i class="fas fa-lock"></i> Change Password
                    </a>
                    <a href="<?= SITE_URL ?>/orders.php">
                        <i class="fas fa-box"></i> My Orders
                    </a>
                    <a href="<?= SITE_URL ?>/wishlist.php">
                        <i class="fas fa-heart"></i> Wishlist
                    </a>
                    <a href="<?= SITE_URL ?>/logout.php" style="color:#dc3545;">
                        <i class="fas fa-sign-out-alt" style="color:#dc3545;"></i> Logout
                    </a>
                </nav>
            </div>

            <!-- Content -->
            <div class="settings-content">
                <?php if ($error):   ?><div class="alert alert-error"><i class="fas fa-times-circle"></i> <?= htmlspecialchars($error) ?></div><?php endif; ?>
                <?php if ($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div><?php endif; ?>

                <!-- ── PROFILE TAB ── -->
                <?php if ($active_tab === 'profile'): ?>
                <h3>Change Profile</h3>
                <p class="subtitle">Update your personal information and profile picture</p>

                <form method="POST" enctype="multipart/form-data" id="profileForm">
                    <input type="hidden" name="action" value="update_profile">

                    <!-- Avatar Upload -->
                    <div class="avatar-upload-area">
                        <img src="<?= $avatar_url ?>" alt="Profile" class="avatar-preview-large" id="avatarPreviewLarge">
                        <div>
                            <label class="avatar-upload-btn" for="avatarInput">
                                <i class="fas fa-camera"></i> Upload New Photo
                            </label>
                            <input type="file" id="avatarInput" name="avatar" accept="image/*" style="display:none;"
                                   onchange="previewAvatar(this)">
                            <p style="font-size:12px;color:#999;margin-top:8px;">
                                JPG, PNG, WebP or GIF · Max 3MB<br>
                                Recommended: Square image, at least 200×200px
                            </p>
                            <div id="avatarFileName" style="font-size:13px;color:#D4AF37;margin-top:6px;display:none;">
                                <i class="fas fa-check-circle"></i> <span></span>
                            </div>
                        </div>
                    </div>

                    <!-- Profile Fields -->
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Full Name <span class="required">*</span></label>
                            <input type="text" name="full_name" class="form-control" required
                                   value="<?= htmlspecialchars($user['full_name']) ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>"
                                   disabled style="background:#f5f5f5;cursor:not-allowed;">
                            <small style="color:#999;font-size:12px;"><i class="fas fa-info-circle"></i> Email cannot be changed</small>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" name="phone" class="form-control" placeholder="+251 900 000 000"
                                   value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control" placeholder="Your city"
                                   value="<?= htmlspecialchars($user['city'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Delivery Address</label>
                        <textarea name="address" class="form-control" rows="3" placeholder="Your full address"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Country</label>
                        <select name="country" class="form-control">
                            <?php foreach (['Ethiopia','Kenya','Uganda','Tanzania','Sudan','Somalia','Eritrea','Djibouti','United Kingdom','United States'] as $co): ?>
                            <option value="<?= $co ?>" <?= ($user['country'] ?? 'Ethiopia') === $co ? 'selected' : '' ?>><?= $co ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="display:flex;gap:12px;flex-wrap:wrap;">
                        <button type="submit" class="btn btn-gold">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                        <a href="?tab=profile" class="btn btn-outline">
                            <i class="fas fa-undo"></i> Reset
                        </a>
                    </div>
                </form>

                <!-- ── PASSWORD TAB ── -->
                <?php elseif ($active_tab === 'password'): ?>
                <h3>Change Password</h3>
                <p class="subtitle">Choose a strong password to keep your account secure</p>

                <form method="POST" id="passwordForm" style="max-width:500px;">
                    <input type="hidden" name="action" value="change_password">

                    <div class="form-group">
                        <label class="form-label">Current Password <span class="required">*</span></label>
                        <div class="password-toggle">
                            <input type="password" name="current_password" id="currentPass" class="form-control"
                                   placeholder="Enter your current password" required>
                            <i class="fas fa-eye toggle-eye"></i>
                        </div>
                        <div class="form-error"></div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">New Password <span class="required">*</span></label>
                        <div class="password-toggle">
                            <input type="password" name="new_password" id="newPass" class="form-control"
                                   placeholder="Min. 8 characters with letters & numbers" required
                                   oninput="checkStrength(this.value)">
                            <i class="fas fa-eye toggle-eye"></i>
                        </div>
                        <!-- Strength bar -->
                        <div class="strength-bar">
                            <div class="strength-fill" id="strengthFill"></div>
                        </div>
                        <div id="strengthLabel" style="font-size:12px;margin-top:4px;color:#999;"></div>
                        <div class="form-error"></div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Confirm New Password <span class="required">*</span></label>
                        <div class="password-toggle">
                            <input type="password" name="confirm_password" id="confirmPass" class="form-control"
                                   placeholder="Repeat your new password" required
                                   oninput="checkMatch()">
                            <i class="fas fa-eye toggle-eye"></i>
                        </div>
                        <div id="matchMsg" style="font-size:12px;margin-top:4px;"></div>
                        <div class="form-error"></div>
                    </div>

                    <!-- Password requirements checklist -->
                    <div style="background:#f9f9f9;border-radius:8px;padding:16px;margin-bottom:20px;">
                        <p style="font-size:13px;font-weight:600;margin-bottom:10px;color:#444;">Password Requirements:</p>
                        <div id="req-length"  class="req-item"><i class="fas fa-circle"></i> At least 8 characters</div>
                        <div id="req-letter"  class="req-item"><i class="fas fa-circle"></i> Contains a letter</div>
                        <div id="req-number"  class="req-item"><i class="fas fa-circle"></i> Contains a number</div>
                        <div id="req-special" class="req-item"><i class="fas fa-circle"></i> Contains a special character (bonus)</div>
                    </div>

                    <button type="submit" class="btn btn-gold" id="savePassBtn">
                        <i class="fas fa-lock"></i> Update Password
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<style>
.req-item { font-size:13px; color:#bbb; margin-bottom:6px; display:flex; align-items:center; gap:8px; transition:all 0.3s; }
.req-item i { font-size:8px; }
.req-item.met { color:#22c55e; }
.req-item.met i::before { content:'\f058'; font-family:'Font Awesome 6 Free'; font-weight:900; font-size:14px; }
</style>

<script>
/* ── Avatar preview (profile form) ──────────────────────── */
function previewAvatar(input) {
    if (!input.files || !input.files[0]) return;
    const file   = input.files[0];
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('avatarPreviewLarge').src = e.target.result;
        document.getElementById('sidebarAvatar').src      = e.target.result;
    };
    reader.readAsDataURL(file);
    const fn = document.getElementById('avatarFileName');
    fn.style.display = 'block';
    fn.querySelector('span').textContent = file.name;
}

/* ── Quick avatar (sidebar click) ───────────────────────── */
function previewAndSubmitAvatar(input) {
    if (!input.files || !input.files[0]) return;
    const file   = input.files[0];
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('sidebarAvatar').src = e.target.result;
    };
    reader.readAsDataURL(file);
    Swal.fire({
        title: 'Upload this photo?',
        html: `<img src="${URL.createObjectURL(file)}" style="width:120px;height:120px;border-radius:50%;object-fit:cover;border:3px solid #D4AF37;margin:10px auto;display:block;">
               <p style="font-size:14px;color:#666;">${file.name}</p>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#D4AF37',
        confirmButtonText: '<i class="fas fa-upload"></i> Upload',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then(result => {
        if (result.isConfirmed) {
            Swal.fire({ title:'Uploading...', allowOutsideClick:false, didOpen:()=>Swal.showLoading() });
            document.getElementById('quickAvatarForm').submit();
        }
    });
}

/* ── Password strength checker ───────────────────────────── */
function checkStrength(val) {
    const fill  = document.getElementById('strengthFill');
    const label = document.getElementById('strengthLabel');
    let score   = 0;
    const checks = {
        length:  val.length >= 8,
        letter:  /[A-Za-z]/.test(val),
        number:  /[0-9]/.test(val),
        special: /[^A-Za-z0-9]/.test(val),
    };
    Object.values(checks).forEach(v => { if (v) score++; });

    const levels = [
        { w:'0%',   bg:'#e5e7eb', text:'' },
        { w:'25%',  bg:'#ef4444', text:'Weak' },
        { w:'50%',  bg:'#f59e0b', text:'Fair' },
        { w:'75%',  bg:'#3b82f6', text:'Good' },
        { w:'100%', bg:'#22c55e', text:'Strong' },
    ];
    const lvl = levels[score];
    fill.style.width      = lvl.w;
    fill.style.background = lvl.bg;
    label.textContent     = lvl.text;
    label.style.color     = lvl.bg;

    // Update checklist
    document.getElementById('req-length') .classList.toggle('met', checks.length);
    document.getElementById('req-letter') .classList.toggle('met', checks.letter);
    document.getElementById('req-number') .classList.toggle('met', checks.number);
    document.getElementById('req-special').classList.toggle('met', checks.special);
}

/* ── Password match checker ──────────────────────────────── */
function checkMatch() {
    const np  = document.getElementById('newPass').value;
    const cp  = document.getElementById('confirmPass').value;
    const msg = document.getElementById('matchMsg');
    if (!cp) { msg.textContent = ''; return; }
    if (np === cp) {
        msg.innerHTML = '<i class="fas fa-check-circle" style="color:#22c55e;"></i> <span style="color:#22c55e;">Passwords match</span>';
    } else {
        msg.innerHTML = '<i class="fas fa-times-circle" style="color:#ef4444;"></i> <span style="color:#ef4444;">Passwords do not match</span>';
    }
}

/* ── Show/hide password ──────────────────────────────────── */
document.querySelectorAll('.toggle-eye').forEach(icon => {
    icon.addEventListener('click', function() {
        const input = this.closest('.password-toggle')?.querySelector('input');
        if (!input) return;
        input.type = input.type === 'password' ? 'text' : 'password';
        this.classList.toggle('fa-eye');
        this.classList.toggle('fa-eye-slash');
    });
});

/* ── Password form validation ────────────────────────────── */
document.getElementById('passwordForm')?.addEventListener('submit', function(e) {
    const np = document.getElementById('newPass')?.value;
    const cp = document.getElementById('confirmPass')?.value;
    if (np !== cp) {
        e.preventDefault();
        Swal.fire({ icon:'error', title:'Passwords do not match', text:'Please make sure both passwords are the same.', confirmButtonColor:'#D4AF37' });
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
