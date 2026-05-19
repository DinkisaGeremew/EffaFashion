/* ============================================================
   EffaFashion - Cart JavaScript
   ============================================================ */

const CART_URL = (typeof SITE_URL !== 'undefined' ? SITE_URL : '') + '/ajax/cart.php';

/* ── Update Quantity ─────────────────────────────────────────── */
document.querySelectorAll('.cart-qty-input').forEach(input => {
  input.addEventListener('change', function() {
    const cartId = this.dataset.cartId;
    const qty    = parseInt(this.value);
    if (qty < 1) { this.value = 1; return; }
    updateCartItem(cartId, qty);
  });
});

document.querySelectorAll('.cart-qty-btn').forEach(btn => {
  btn.addEventListener('click', function() {
    const row   = this.closest('tr') || this.closest('.cart-item');
    const input = row?.querySelector('.cart-qty-input');
    if (!input) return;
    let val = parseInt(input.value) || 1;
    const max = parseInt(input.max) || 999;
    if (this.dataset.action === 'plus'  && val < max) { input.value = val + 1; }
    if (this.dataset.action === 'minus' && val > 1)   { input.value = val - 1; }
    updateCartItem(input.dataset.cartId, parseInt(input.value));
  });
});

function updateCartItem(cartId, qty) {
  fetch(CART_URL, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `action=update&cart_id=${cartId}&quantity=${qty}`
  })
  .then(r => r.json())
  .then(data => {
    if (data.status === 'success') {
      // Update row subtotal
      const row = document.querySelector(`[data-cart-id="${cartId}"]`)?.closest('tr');
      if (row) {
        const subtotalEl = row.querySelector('.cart-subtotal');
        if (subtotalEl && data.subtotal) subtotalEl.textContent = data.subtotal;
      }
      updateCartTotals(data);
      updateCartBadge(data.cart_count);
    }
  })
  .catch(console.error);
}

/* ── Remove Item ─────────────────────────────────────────────── */
document.querySelectorAll('.cart-remove').forEach(btn => {
  btn.addEventListener('click', function() {
    const cartId = this.dataset.cartId;
    Swal.fire({
      title: 'Remove item?',
      text: 'Are you sure you want to remove this item from your cart?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#D4AF37',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Yes, remove it',
      cancelButtonText: 'Keep it'
    }).then(result => {
      if (result.isConfirmed) {
        fetch(CART_URL, {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `action=remove&cart_id=${cartId}`
        })
        .then(r => r.json())
        .then(data => {
          if (data.status === 'success') {
            const row = document.querySelector(`[data-cart-id="${cartId}"]`)?.closest('tr');
            if (row) { row.style.opacity = '0'; row.style.transition = 'opacity 0.3s'; setTimeout(() => row.remove(), 300); }
            updateCartTotals(data);
            updateCartBadge(data.cart_count);
            if (data.cart_count === 0) setTimeout(() => location.reload(), 400);
          }
        });
      }
    });
  });
});

/* ── Apply Coupon ────────────────────────────────────────────── */
document.getElementById('applyCouponBtn')?.addEventListener('click', function() {
  const code  = document.getElementById('couponCode')?.value?.trim();
  const total = parseFloat(document.getElementById('cartTotal')?.dataset.total || 0);
  if (!code) { Swal.fire({ icon: 'warning', title: 'Enter a coupon code', timer: 1500, showConfirmButton: false }); return; }

  this.disabled = true;
  this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

  fetch((typeof SITE_URL !== 'undefined' ? SITE_URL : '') + '/ajax/coupon.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `code=${encodeURIComponent(code)}&total=${total}`
  })
  .then(r => r.json())
  .then(data => {
    this.disabled = false;
    this.innerHTML = 'Apply';
    if (data.valid) {
      document.getElementById('discountRow')?.classList.remove('d-none');
      const discountEl = document.getElementById('discountAmount');
      if (discountEl) discountEl.textContent = data.discount_formatted;
      const totalEl = document.getElementById('cartTotal');
      if (totalEl) { totalEl.textContent = data.new_total_formatted; totalEl.dataset.total = data.new_total; }
      document.getElementById('couponHidden').value = code;
      Swal.fire({ icon: 'success', title: data.message, timer: 2000, showConfirmButton: false, toast: true, position: 'top-end' });
    } else {
      Swal.fire({ icon: 'error', title: data.message, timer: 2500, showConfirmButton: false });
    }
  })
  .catch(() => { this.disabled = false; this.innerHTML = 'Apply'; });
});

/* ── Update Cart Totals ──────────────────────────────────────── */
function updateCartTotals(data) {
  if (data.subtotal_formatted) {
    const el = document.getElementById('cartSubtotal');
    if (el) el.textContent = data.subtotal_formatted;
  }
  if (data.total_formatted) {
    const el = document.getElementById('cartTotal');
    if (el) { el.textContent = data.total_formatted; el.dataset.total = data.total; }
  }
}

/* ── Update Cart Badge ───────────────────────────────────────── */
function updateCartBadge(count) {
  const badge = document.getElementById('cartBadge');
  if (badge) badge.textContent = count;
  if (typeof window.updateCartBadge === 'function') window.updateCartBadge(count);
}
