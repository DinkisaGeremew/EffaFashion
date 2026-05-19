<?php
$page_title = 'Settings';
require_once __DIR__ . '/includes/admin_header.php';

$active_tab = $_GET['tab'] ?? 'profile';
$error = $success = '';

function getAdminAvatarUrl($user) {
    if (!empty($user['profile_image'])) {
        $path = $_SERVER['DOCUMENT_ROOT'] . '/EffaFashion/uploads/avatars/' . $user['profile_image'];
        if (file_exists($path)) return SITE_URL . '/uploads/avatars/' . rawurlencode($user['profile_image']);
    }
    return 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($user['email']))) . '?s=200&d=mp';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    /* ── Update Profile + Avatar ── */
    if ($action === 'update_profile') {
        $full_name = sanitize($_POST['full_name'] ?? '');
        $phone     = sanitize($_POST['phone']     ?? '');

        if (empty($full_name)) {
            $error = 'Full name is required.';
        } else {
            $uid        = (int)$_SESSION['user_id'];
            $img_update = '';

            if (!empty($_FILES['avatar']['name'])) {
                $ext     = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg','jpeg','png','webp','gif'];
                if (!in_array($ext, $allowed)) {
                    $error = 'Invalid image format. Use JPG, PNG or WebP.';
                } elseif ($_FILES['avatar']['size'] > 3 * 1024 * 1024) {
                    $error = 'Image too large. Max 3MB.';
                } else {
                    $avatar_dir = $_SERVER['DOCUMENT_ROOT'] . '/EffaFashion/uploads/avatars/';
                    if (!is_dir($avatar_dir)) mkdir($avatar_dir, 0755, true);
                    if (!empty($admin_user['profile_image']) && file_exists($avatar_dir . $admin_user['profile_image'])) {
                        unlink($avatar_dir . $admin_user['profile_image']);
                    }
                    $avatar_name = 'avatar_' . $uid . '_' . time() . '.' . $ext;
                    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $avatar_dir . $avatar_name)) {
                        $img_update = ", profile_image='" . $conn->real_escape_string($avatar_name) . "'";
                    } else {
                        $error = 'Upload failed. Please try again.';
                    }
                }
            }

            if (!$error) {
                $n = $conn->real_escape_string($full_name);
                $p = $conn->real_escape_string($phone);
                $conn->query("UPDATE users SET full_name='$n', phone='$p' $img_update WHERE id=$uid");
                $_SESSION['user_name'] = $full_name;
                unset($_SESSION['user_cache']);
                $success    = 'Profile updated successfully!';
                $admin_user = getCurrentUser();
                $active_tab = 'profile';
            }
        }
    }

    /* ── Change Password ── */
    if ($action === 'change_password') {
        $current  = $_POST['current_password'] ?? '';
        $new_pass = $_POST['new_password']      ?? '';
        $confirm  = $_POST['confirm_password']  ?? '';

        if (!password_verify($current, $admin_user['password'])) {
            $error = 'Current password is incorrect.';
        } elseif (strlen($new_pass) < 8) {
            $error = 'New password must be at least 8 characters.';
        } elseif (!preg_match('/[A-Za-z]/', $new_pass) || !preg_match('/[0-9]/', $new_pass)) {
            $error = 'Password must contain at least one letter and one number.';
        } elseif ($new_pass !== $confirm) {
            $error = 'Passwords do not match.';
        } else {
            $uid    = (int)$_SESSION['user_id'];
            $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
            $conn->query("UPDATE users SET password='$hashed' WHERE id=$uid");
            $success    = 'Password changed successfully!';
            $active_tab = 'password';
        }
    }
}

$avatar_url = getAdminAvatarUrl($admin_user);
?>

<div class="admin-page-header">
    <div>
        <h1>Settings</h1>
        <div class="admin-breadcrumb">
            <a href="<?= SITE_URL ?>/admin/dashboard.php">Dashboard</a> / Settings
        </div>
    </div>
</div>

<?php if ($error):   ?><div class="alert alert-error"  style="margin-bottom:20px;"><i class="fas fa-times-circle"></i> <?= htmlspecialchars($error) ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success" style="margin-bottom:20px;"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div><?php endif; ?>

<div style="display:grid;grid-template-columns:260px 1fr;gap:24px;align-items:start;">

    <!-- Sidebar -->
    <div class="admin-card" style="overflow:hidden;">
        <!-- Avatar header -->
        <div style="background:linear-gradient(135deg,#0f0f0f,#1a1a1a);padding:28px 20px;text-align:center;">
            <div style="position:relative;width:90px;height:90px;margin:0 auto 14px;cursor:pointer;"
                 onclick="document.getElementById('quickAvatarInput').click()" title="Click to change photo">
                <img src="<?= $avatar_url ?>" id="sidebarAvatar"
                     style="width:90px;height:90px;border-radius:50%;object-fit:cover;border:3px solid #D4AF37;" alt="">
                <div style="position:absolute;inset:0;border-radius:50%;background:rgba(0,0,0,0.5);
                            display:flex;align-items:center;justify-content:center;opacity:0;transition:all 0.3s;"
                     onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0">
                    <i class="fas fa-camera" style="color:#D4AF37;font-size:20px;"></i>
                </div>
            </div>
            <!-- Quick upload form -->
            <form id="quickAvatarForm" method="POST" enctype="multipart/form-data" style="display:none;">
                <input type="hidden" name="action"     value="update_profile">
                <input type="hidden" name="full_name"  value="<?= htmlspecialchars($admin_user['full_name']) ?>">
                <input type="hidden" name="phone"      value="<?= htmlspecialchars($admin_user['phone'] ?? '') ?>">
                <input type="file"   id="quickAvatarInput" name="avatar" accept="image/*" style="display:none;"
                       onchange="previewAndSubmitAvatar(this)">
            </form>
            <div style="color:#fff;font-family:'Playfair Display',serif;font-size:16px;margin-bottom:4px;">
                <?= htmlspecialchars($admin_user['full_name']) ?>
            </div>
            <div style="color:#D4AF37;font-size:12px;">Administrator</div>
            <div style="color:rgba(255,255,255,0.4);font-size:12px;margin-top:4px;">
                <?= htmlspecialchars($admin_user['email']) ?>
            </div>
        </div>
        <!-- Nav -->
        <div>
            <a href="?tab=profile"
               style="display:flex;align-items:center;gap:12px;padding:14px 20px;font-size:14px;
                      color:<?= $active_tab==='profile' ? '#D4AF37' : '#333' ?>;
                      background:<?= $active_tab==='profile' ? '#fafafa' : '#fff' ?>;
                      border-bottom:1px solid #f0f0f0;border-left:3px solid <?= $active_tab==='profile' ? '#D4AF37' : 'transparent' ?>;
                      text-decoration:none;transition:all 0.2s;">
                <i class="fas fa-user-edit" style="color:#D4AF37;width:16px;"></i> Change Profile
            </a>
            <a href="?tab=password"
               style="display:flex;align-items:center;gap:12px;padding:14px 20px;font-size:14px;
                      color:<?= $active_tab==='password' ? '#D4AF37' : '#333' ?>;
                      background:<?= $active_tab==='password' ? '#fafafa' : '#fff' ?>;
                      border-bottom:1px solid #f0f0f0;border-left:3px solid <?= $active_tab==='password' ? '#D4AF37' : 'transparent' ?>;
                      text-decoration:none;transition:all 0.2s;">
                <i class="fas fa-lock" style="color:#D4AF37;width:16px;"></i> Change Password
            </a>
            <a href="<?= SITE_URL ?>/logout.php"
               style="display:flex;align-items:center;gap:12px;padding:14px 20px;font-size:14px;
                      color:#dc3545;border-left:3px solid transparent;text-decoration:none;">
                <i class="fas fa-sign-out-alt" style="color:#dc3545;width:16px;"></i> Logout
            </a>
        </div>
    </div>

    <!-- Content -->
    <div class="admin-card">
        <div class="admin-card-body">

            <?php if ($active_tab === 'profile'): ?>
            <!-- ── PROFILE TAB ── -->
            <h3 style="font-family:'Playfair Display',serif;font-size:20px;margin-bottom:6px;">Change Profile</h3>
            <p style="color:#999;font-size:14px;margin-bottom:24px;padding-bottom:16px;border-bottom:2px solid #D4AF37;">
                Update your admin profile information and photo
            </p>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update_profile">

                <!-- Avatar upload area -->
                <div style="display:flex;align-items:center;gap:24px;background:#f9f9f9;border-radius:10px;padding:20px;margin-bottom:24px;">
                    <img src="<?= $avatar_url ?>" id="avatarPreviewLarge"
                         style="width:100px;height:100px;border-radius:50%;object-fit:cover;border:3px solid #D4AF37;flex-shrink:0;" alt="">
                    <div>
                        <label for="avatarInput"
                               style="display:inline-flex;align-items:center;gap:8px;padding:10px 20px;
                                      background:#000;color:#fff;border-radius:30px;font-size:13px;
                                      font-weight:600;cursor:pointer;transition:all 0.3s;">
                            <i class="fas fa-camera"></i> Upload New Photo
                        </label>
                        <input type="file" id="avatarInput" name="avatar" accept="image/*" style="display:none;"
                               onchange="previewAvatar(this)">
                        <p style="font-size:12px;color:#999;margin-top:8px;">JPG, PNG, WebP · Max 3MB</p>
                        <div id="avatarFileName" style="font-size:13px;color:#D4AF37;margin-top:6px;display:none;">
                            <i class="fas fa-check-circle"></i> <span></span>
                        </div>
                    </div>
                </div>

                <div class="admin-form-grid">
                    <div class="form-group">
                        <label class="form-label">Full Name <span style="color:#dc3545;">*</span></label>
                        <input type="text" name="full_name" class="form-control" required
                               value="<?= htmlspecialchars($admin_user['full_name']) ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-control" value="<?= htmlspecialchars($admin_user['email']) ?>"
                               disabled style="background:#f5f5f5;cursor:not-allowed;">
                        <small style="color:#999;font-size:12px;">Email cannot be changed</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="tel" name="phone" class="form-control" placeholder="+251 900 000 000"
                               value="<?= htmlspecialchars($admin_user['phone'] ?? '') ?>">
                    </div>
                </div>

                <button type="submit" class="btn btn-gold">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </form>

            <?php elseif ($active_tab === 'password'): ?>
            <!-- ── PASSWORD TAB ── -->
            <h3 style="font-family:'Playfair Display',serif;font-size:20px;margin-bottom:6px;">Change Password</h3>
            <p style="color:#999;font-size:14px;margin-bottom:24px;padding-bottom:16px;border-bottom:2px solid #D4AF37;">
                Keep your admin account secure with a strong password
            </p>

            <form method="POST" id="passwordForm" style="max-width:480px;">
                <input type="hidden" name="action" value="change_password">

                <div class="form-group">
                    <label class="form-label">Current Password <span style="color:#dc3545;">*</span></label>
                    <div class="password-toggle" style="position:relative;">
                        <input type="password" name="current_password" class="form-control"
                               placeholder="Enter current password" required>
                        <i class="fas fa-eye toggle-eye" style="position:absolute;right:14px;top:50%;transform:translateY(-50%);color:#999;cursor:pointer;"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">New Password <span style="color:#dc3545;">*</span></label>
                    <div class="password-toggle" style="position:relative;">
                        <input type="password" name="new_password" id="newPass" class="form-control"
                               placeholder="Min. 8 characters" required oninput="checkStrength(this.value)">
                        <i class="fas fa-eye toggle-eye" style="position:absolute;right:14px;top:50%;transform:translateY(-50%);color:#999;cursor:pointer;"></i>
                    </div>
                    <div style="height:5px;background:#eee;border-radius:3px;margin-top:8px;overflow:hidden;">
                        <div id="strengthFill" style="height:100%;width:0;border-radius:3px;transition:all 0.3s;"></div>
                    </div>
                    <div id="strengthLabel" style="font-size:12px;margin-top:4px;color:#999;"></div>
                </div>

                <div class="form-group">
                    <label class="form-label">Confirm New Password <span style="color:#dc3545;">*</span></label>
                    <div class="password-toggle" style="position:relative;">
                        <input type="password" name="confirm_password" id="confirmPass" class="form-control"
                               placeholder="Repeat new password" required oninput="checkMatch()">
                        <i class="fas fa-eye toggle-eye" style="position:absolute;right:14px;top:50%;transform:translateY(-50%);color:#999;cursor:pointer;"></i>
                    </div>
                    <div id="matchMsg" style="font-size:12px;margin-top:4px;"></div>
                </div>

                <!-- Requirements checklist -->
                <div style="background:#f9f9f9;border-radius:8px;padding:16px;margin-bottom:20px;">
                    <p style="font-size:13px;font-weight:600;margin-bottom:10px;color:#444;">Requirements:</p>
                    <div id="req-length"  style="font-size:13px;color:#bbb;margin-bottom:6px;display:flex;align-items:center;gap:8px;transition:all 0.3s;"><i class="fas fa-circle" style="font-size:8px;"></i> At least 8 characters</div>
                    <div id="req-letter"  style="font-size:13px;color:#bbb;margin-bottom:6px;display:flex;align-items:center;gap:8px;transition:all 0.3s;"><i class="fas fa-circle" style="font-size:8px;"></i> Contains a letter</div>
                    <div id="req-number"  style="font-size:13px;color:#bbb;margin-bottom:6px;display:flex;align-items:center;gap:8px;transition:all 0.3s;"><i class="fas fa-circle" style="font-size:8px;"></i> Contains a number</div>
                    <div id="req-special" style="font-size:13px;color:#bbb;display:flex;align-items:center;gap:8px;transition:all 0.3s;"><i class="fas fa-circle" style="font-size:8px;"></i> Special character (bonus)</div>
                </div>

                <button type="submit" class="btn btn-gold">
                    <i class="fas fa-lock"></i> Update Password
                </button>
            </form>
            <?php endif; ?>

        </div>
    </div>
</div>

<script>
/* ── Avatar preview ──────────────────────────────────────── */
function previewAvatar(input) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('avatarPreviewLarge').src = e.target.result;
        document.getElementById('sidebarAvatar').src      = e.target.result;
    };
    reader.readAsDataURL(input.files[0]);
    const fn = document.getElementById('avatarFileName');
    fn.style.display = 'block';
    fn.querySelector('span').textContent = input.files[0].name;
}

/* ── Quick avatar (sidebar click) ───────────────────────── */
function previewAndSubmitAvatar(input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];
    const reader = new FileReader();
    reader.onload = e => { document.getElementById('sidebarAvatar').src = e.target.result; };
    reader.readAsDataURL(file);
    Swal.fire({
        title: 'Upload this photo?',
        html: `<img src="${URL.createObjectURL(file)}" style="width:110px;height:110px;border-radius:50%;object-fit:cover;border:3px solid #D4AF37;margin:10px auto;display:block;">
               <p style="font-size:13px;color:#666;margin-top:8px;">${file.name}</p>`,
        showCancelButton: true,
        confirmButtonColor: '#D4AF37',
        confirmButtonText: '<i class="fas fa-upload"></i> Upload',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then(r => {
        if (r.isConfirmed) {
            Swal.fire({ title:'Uploading...', allowOutsideClick:false, didOpen:()=>Swal.showLoading() });
            document.getElementById('quickAvatarForm').submit();
        }
    });
}

/* ── Password strength ───────────────────────────────────── */
function checkStrength(val) {
    const fill  = document.getElementById('strengthFill');
    const label = document.getElementById('strengthLabel');
    const checks = { length: val.length>=8, letter:/[A-Za-z]/.test(val), number:/[0-9]/.test(val), special:/[^A-Za-z0-9]/.test(val) };
    const score  = Object.values(checks).filter(Boolean).length;
    const levels = [
        {w:'0%',  bg:'#e5e7eb',text:''},
        {w:'25%', bg:'#ef4444',text:'Weak'},
        {w:'50%', bg:'#f59e0b',text:'Fair'},
        {w:'75%', bg:'#3b82f6',text:'Good'},
        {w:'100%',bg:'#22c55e',text:'Strong'}
    ];
    fill.style.width = levels[score].w; fill.style.background = levels[score].bg;
    label.textContent = levels[score].text; label.style.color = levels[score].bg;
    const setReq = (id, met) => {
        const el = document.getElementById(id);
        if (!el) return;
        el.style.color = met ? '#22c55e' : '#bbb';
        el.querySelector('i').style.fontSize = met ? '14px' : '8px';
    };
    setReq('req-length', checks.length); setReq('req-letter', checks.letter);
    setReq('req-number', checks.number); setReq('req-special', checks.special);
}

/* ── Password match ──────────────────────────────────────── */
function checkMatch() {
    const np = document.getElementById('newPass').value;
    const cp = document.getElementById('confirmPass').value;
    const msg = document.getElementById('matchMsg');
    if (!cp) { msg.textContent=''; return; }
    msg.innerHTML = np===cp
        ? '<i class="fas fa-check-circle" style="color:#22c55e;"></i> <span style="color:#22c55e;">Passwords match</span>'
        : '<i class="fas fa-times-circle" style="color:#ef4444;"></i> <span style="color:#ef4444;">Passwords do not match</span>';
}

/* ── Show/hide password ──────────────────────────────────── */
document.querySelectorAll('.toggle-eye').forEach(icon => {
    icon.addEventListener('click', function() {
        const input = this.closest('.password-toggle')?.querySelector('input');
        if (!input) return;
        input.type = input.type==='password' ? 'text' : 'password';
        this.classList.toggle('fa-eye'); this.classList.toggle('fa-eye-slash');
    });
});

/* ── Password form guard ─────────────────────────────────── */
document.getElementById('passwordForm')?.addEventListener('submit', function(e) {
    const np = document.getElementById('newPass')?.value;
    const cp = document.getElementById('confirmPass')?.value;
    if (np !== cp) {
        e.preventDefault();
        Swal.fire({ icon:'error', title:'Passwords do not match', confirmButtonColor:'#D4AF37' });
    }
});
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
