/* ============================================================
   EffaFashion - Form Validation JavaScript
   ============================================================ */

/* ── Helpers ─────────────────────────────────────────────────── */
function showError(field, msg) {
  field.classList.add('is-invalid');
  field.classList.remove('is-valid');
  let err = field.parentElement.querySelector('.form-error');
  if (!err) { err = document.createElement('div'); err.className = 'form-error'; field.parentElement.appendChild(err); }
  err.textContent = msg;
  err.classList.add('show');
}

function showValid(field) {
  field.classList.remove('is-invalid');
  field.classList.add('is-valid');
  const err = field.parentElement.querySelector('.form-error');
  if (err) err.classList.remove('show');
}

function clearState(field) {
  field.classList.remove('is-invalid', 'is-valid');
  const err = field.parentElement.querySelector('.form-error');
  if (err) err.classList.remove('show');
}

const isEmail = v => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);
const isPhone = v => /^[\+]?[\d\s\-\(\)]{7,15}$/.test(v);

/* ── Register Form ───────────────────────────────────────────── */
const registerForm = document.getElementById('registerForm');
if (registerForm) {
  const fields = {
    full_name:        registerForm.querySelector('[name="full_name"]'),
    email:            registerForm.querySelector('[name="email"]'),
    phone:            registerForm.querySelector('[name="phone"]'),
    password:         registerForm.querySelector('[name="password"]'),
    confirm_password: registerForm.querySelector('[name="confirm_password"]'),
  };

  function validateFullName() {
    if (!fields.full_name) return true;
    const v = fields.full_name.value.trim();
    if (!v)          { showError(fields.full_name, 'Full name is required'); return false; }
    if (v.length < 3){ showError(fields.full_name, 'Name must be at least 3 characters'); return false; }
    showValid(fields.full_name); return true;
  }
  function validateEmail() {
    if (!fields.email) return true;
    const v = fields.email.value.trim();
    if (!v)          { showError(fields.email, 'Email is required'); return false; }
    if (!isEmail(v)) { showError(fields.email, 'Enter a valid email address'); return false; }
    showValid(fields.email); return true;
  }
  function validatePhone() {
    if (!fields.phone) return true;
    const v = fields.phone.value.trim();
    if (v && !isPhone(v)) { showError(fields.phone, 'Enter a valid phone number'); return false; }
    if (fields.phone.value) showValid(fields.phone); return true;
  }
  function validatePassword() {
    if (!fields.password) return true;
    const v = fields.password.value;
    if (!v)          { showError(fields.password, 'Password is required'); return false; }
    if (v.length < 8){ showError(fields.password, 'Password must be at least 8 characters'); return false; }
    if (!/[a-zA-Z]/.test(v)) { showError(fields.password, 'Password must contain at least one letter'); return false; }
    if (!/[0-9]/.test(v))    { showError(fields.password, 'Password must contain at least one number'); return false; }
    showValid(fields.password); return true;
  }
  function validateConfirm() {
    if (!fields.confirm_password) return true;
    const v = fields.confirm_password.value;
    if (!v) { showError(fields.confirm_password, 'Please confirm your password'); return false; }
    if (v !== fields.password?.value) { showError(fields.confirm_password, 'Passwords do not match'); return false; }
    showValid(fields.confirm_password); return true;
  }

  fields.full_name?.addEventListener('blur', validateFullName);
  fields.email?.addEventListener('blur', validateEmail);
  fields.phone?.addEventListener('blur', validatePhone);
  fields.password?.addEventListener('blur', validatePassword);
  fields.confirm_password?.addEventListener('blur', validateConfirm);

  registerForm.addEventListener('submit', function(e) {
    const valid = [validateFullName(), validateEmail(), validatePhone(), validatePassword(), validateConfirm()].every(Boolean);
    if (!valid) e.preventDefault();
  });
}

/* ── Login Form ──────────────────────────────────────────────── */
const loginForm = document.getElementById('loginForm');
if (loginForm) {
  loginForm.addEventListener('submit', function(e) {
    let valid = true;
    const email = this.querySelector('[name="email"]');
    const pass  = this.querySelector('[name="password"]');
    if (email && !email.value.trim()) { showError(email, 'Email is required'); valid = false; }
    else if (email && !isEmail(email.value.trim())) { showError(email, 'Enter a valid email'); valid = false; }
    else if (email) showValid(email);
    if (pass && !pass.value) { showError(pass, 'Password is required'); valid = false; }
    else if (pass) showValid(pass);
    if (!valid) e.preventDefault();
  });
}

/* ── Checkout Form ───────────────────────────────────────────── */
const checkoutForm = document.getElementById('checkoutForm');
if (checkoutForm) {
  checkoutForm.addEventListener('submit', function(e) {
    let valid = true;
    const required = this.querySelectorAll('[required]');
    required.forEach(field => {
      if (!field.value.trim()) { showError(field, `${field.placeholder || 'This field'} is required`); valid = false; }
      else if (field.type === 'email' && !isEmail(field.value.trim())) { showError(field, 'Enter a valid email'); valid = false; }
      else if (field.name === 'phone' && !isPhone(field.value.trim())) { showError(field, 'Enter a valid phone number'); valid = false; }
      else showValid(field);
    });
    if (!valid) { e.preventDefault(); this.querySelector('.is-invalid')?.scrollIntoView({ behavior: 'smooth', block: 'center' }); }
  });
  checkoutForm.querySelectorAll('[required]').forEach(field => {
    field.addEventListener('blur', function() {
      if (!this.value.trim()) showError(this, `This field is required`);
      else showValid(this);
    });
  });
}

/* ── Contact Form ────────────────────────────────────────────── */
const contactForm = document.getElementById('contactForm');
if (contactForm) {
  contactForm.addEventListener('submit', function(e) {
    let valid = true;
    const name    = this.querySelector('[name="name"]');
    const email   = this.querySelector('[name="email"]');
    const message = this.querySelector('[name="message"]');
    if (name && !name.value.trim())    { showError(name, 'Name is required'); valid = false; } else if (name) showValid(name);
    if (email && !email.value.trim())  { showError(email, 'Email is required'); valid = false; }
    else if (email && !isEmail(email.value.trim())) { showError(email, 'Enter a valid email'); valid = false; }
    else if (email) showValid(email);
    if (message && message.value.trim().length < 10) { showError(message, 'Message must be at least 10 characters'); valid = false; } else if (message) showValid(message);
    if (!valid) e.preventDefault();
  });
}

/* ── Review Form ─────────────────────────────────────────────── */
const reviewForm = document.getElementById('reviewForm');
if (reviewForm) {
  reviewForm.addEventListener('submit', function(e) {
    let valid = true;
    const rating  = this.querySelector('[name="rating"]:checked');
    const comment = this.querySelector('[name="comment"]');
    if (!rating) {
      const ratingGroup = this.querySelector('.star-rating-input');
      if (ratingGroup) { ratingGroup.style.outline = '2px solid #dc3545'; ratingGroup.style.borderRadius = '4px'; }
      Swal.fire({ icon: 'warning', title: 'Please select a rating', timer: 2000, showConfirmButton: false });
      valid = false;
    }
    if (comment && comment.value.trim().length < 10) { showError(comment, 'Review must be at least 10 characters'); valid = false; }
    else if (comment) showValid(comment);
    if (!valid) e.preventDefault();
  });
}

/* ── Show/Hide Password Toggle ───────────────────────────────── */
document.querySelectorAll('.toggle-eye').forEach(icon => {
  icon.addEventListener('click', function() {
    const input = this.closest('.password-toggle')?.querySelector('input');
    if (!input) return;
    if (input.type === 'password') {
      input.type = 'text';
      this.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
      input.type = 'password';
      this.classList.replace('fa-eye-slash', 'fa-eye');
    }
  });
});

/* ── Real-time password strength ────────────────────────────── */
const passInput = document.querySelector('#registerForm [name="password"]');
const strengthBar = document.getElementById('passwordStrength');
if (passInput && strengthBar) {
  passInput.addEventListener('input', function() {
    const v = this.value;
    let score = 0;
    if (v.length >= 8) score++;
    if (/[A-Z]/.test(v)) score++;
    if (/[0-9]/.test(v)) score++;
    if (/[^A-Za-z0-9]/.test(v)) score++;
    const labels = ['', 'Weak', 'Fair', 'Good', 'Strong'];
    const colors = ['', '#dc3545', '#ffc107', '#17a2b8', '#28a745'];
    strengthBar.style.width = (score * 25) + '%';
    strengthBar.style.background = colors[score];
    strengthBar.title = labels[score];
  });
}
