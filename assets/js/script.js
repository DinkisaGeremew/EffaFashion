/* ============================================================
   EffaFashion - Main JavaScript
   ============================================================ */

const SITE_URL = document.querySelector('meta[name="site-url"]')?.content || '';

/* ── Navbar Scroll Effect ────────────────────────────────────── */
const navbar = document.getElementById('mainNavbar');
window.addEventListener('scroll', () => {
  if (navbar) {
    navbar.classList.toggle('scrolled', window.scrollY > 50);
  }
  // Back to top
  const btn = document.getElementById('backToTop');
  if (btn) btn.classList.toggle('show', window.scrollY > 400);
});

/* ── Mobile Menu Toggle ──────────────────────────────────────── */
const mobileToggle = document.getElementById('mobileMenuToggle');
const navbarLinks  = document.getElementById('navbarLinks');
const overlay      = document.getElementById('overlay');

mobileToggle?.addEventListener('click', () => {
  mobileToggle.classList.toggle('active');
  navbarLinks?.classList.toggle('open');
  overlay?.classList.toggle('show');
});

overlay?.addEventListener('click', closeMenu);

function closeMenu() {
  mobileToggle?.classList.remove('active');
  navbarLinks?.classList.remove('open');
  overlay?.classList.remove('show');
}

/* ── Dropdown Toggles (mobile) ───────────────────────────────── */
document.querySelectorAll('.has-dropdown > a').forEach(link => {
  link.addEventListener('click', function(e) {
    if (window.innerWidth <= 991) {
      e.preventDefault();
      this.parentElement.classList.toggle('open');
    }
  });
});

/* ── Back to Top ─────────────────────────────────────────────── */
document.getElementById('backToTop')?.addEventListener('click', () => {
  window.scrollTo({ top: 0, behavior: 'smooth' });
});

/* ── Product Image Gallery ───────────────────────────────────── */
function initGallery() {
  const mainImg = document.getElementById('galleryMain');
  const thumbs  = document.querySelectorAll('.gallery-thumb');
  thumbs.forEach(thumb => {
    thumb.addEventListener('click', function() {
      if (mainImg) mainImg.src = this.dataset.src;
      thumbs.forEach(t => t.classList.remove('active'));
      this.classList.add('active');
    });
  });
}
initGallery();

/* ── Quantity Selector ───────────────────────────────────────── */
document.querySelectorAll('.qty-btn').forEach(btn => {
  btn.addEventListener('click', function() {
    const input = this.closest('.qty-selector')?.querySelector('.qty-input');
    if (!input) return;
    let val = parseInt(input.value) || 1;
    const max = parseInt(input.max) || 999;
    if (this.dataset.action === 'plus'  && val < max) input.value = val + 1;
    if (this.dataset.action === 'minus' && val > 1)   input.value = val - 1;
    input.dispatchEvent(new Event('change'));
  });
});

/* ── Size / Color Selector ───────────────────────────────────── */
document.querySelectorAll('.size-btn').forEach(btn => {
  btn.addEventListener('click', function() {
    this.closest('.size-options')?.querySelectorAll('.size-btn').forEach(b => b.classList.remove('active'));
    this.classList.add('active');
    const hidden = document.getElementById('selectedSize');
    if (hidden) hidden.value = this.dataset.size;
  });
});

document.querySelectorAll('.color-btn').forEach(btn => {
  btn.addEventListener('click', function() {
    this.closest('.color-options')?.querySelectorAll('.color-btn').forEach(b => b.classList.remove('active'));
    this.classList.add('active');
    const hidden = document.getElementById('selectedColor');
    if (hidden) hidden.value = this.dataset.color;
  });
});

/* ── Wishlist Toggle (AJAX) ──────────────────────────────────── */
document.querySelectorAll('.product-wishlist').forEach(btn => {
  btn.addEventListener('click', function(e) {
    e.preventDefault();
    const productId = this.dataset.id;
    fetch(`${SITE_URL}/ajax/wishlist.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `product_id=${productId}`
    })
    .then(r => r.json())
    .then(data => {
      if (data.status === 'added') {
        this.classList.add('active');
        this.querySelector('i').classList.replace('far', 'fas');
        Swal.fire({ icon: 'success', title: 'Added to Wishlist', timer: 1500, showConfirmButton: false, toast: true, position: 'top-end' });
      } else if (data.status === 'removed') {
        this.classList.remove('active');
        this.querySelector('i').classList.replace('fas', 'far');
        Swal.fire({ icon: 'info', title: 'Removed from Wishlist', timer: 1500, showConfirmButton: false, toast: true, position: 'top-end' });
      } else {
        window.location.href = `${SITE_URL}/login.php`;
      }
      // Update wishlist badge
      const badge = document.querySelector('.nav-icon[href*="wishlist"] .badge');
      if (badge && data.count !== undefined) badge.textContent = data.count;
    })
    .catch(() => Swal.fire({ icon: 'error', title: 'Something went wrong', timer: 2000, showConfirmButton: false }));
  });
});

/* ── Add to Cart (AJAX) ──────────────────────────────────────── */
window.addToCart = function(productId, qty = 1, size = '', color = '') {
  fetch(`${SITE_URL}/ajax/cart.php`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `action=add&product_id=${productId}&quantity=${qty}&size=${encodeURIComponent(size)}&color=${encodeURIComponent(color)}`
  })
  .then(r => r.json())
  .then(data => {
    if (data.status === 'success') {
      updateCartBadge(data.cart_count);
      Swal.fire({
        icon: 'success', title: 'Added to Cart!',
        html: `<small>${data.product_name}</small>`,
        timer: 2000, showConfirmButton: false,
        toast: true, position: 'top-end'
      });
    } else {
      Swal.fire({ icon: 'error', title: data.message || 'Error', timer: 2000, showConfirmButton: false });
    }
  });
};

// Quick add buttons on product cards
document.querySelectorAll('.quick-add-btn').forEach(btn => {
  btn.addEventListener('click', function(e) {
    e.preventDefault();
    addToCart(this.dataset.id);
  });
});

// Full add-to-cart form on product detail page
document.getElementById('addToCartForm')?.addEventListener('submit', function(e) {
  e.preventDefault();
  const productId = this.dataset.product;
  const qty   = document.getElementById('qtyInput')?.value || 1;
  const size  = document.getElementById('selectedSize')?.value || '';
  const color = document.getElementById('selectedColor')?.value || '';
  addToCart(productId, qty, size, color);
});

/* ── Update Cart Badge ───────────────────────────────────────── */
window.updateCartBadge = function(count) {
  const badge = document.getElementById('cartBadge');
  if (badge) { badge.textContent = count; badge.style.display = count > 0 ? 'flex' : 'none'; }
};

/* ── Tab Switching ───────────────────────────────────────────── */
document.querySelectorAll('.tab-btn').forEach(btn => {
  btn.addEventListener('click', function() {
    const target = this.dataset.tab;
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
    this.classList.add('active');
    document.getElementById(target)?.classList.add('active');
  });
});

/* ── Smooth Scroll ───────────────────────────────────────────── */
document.querySelectorAll('a[href^="#"]').forEach(a => {
  a.addEventListener('click', function(e) {
    const target = document.querySelector(this.getAttribute('href'));
    if (target) { e.preventDefault(); target.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
  });
});

/* ── Lazy Loading Images ─────────────────────────────────────── */
if ('IntersectionObserver' in window) {
  const lazyImgs = document.querySelectorAll('img[data-src]');
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const img = entry.target;
        img.src = img.dataset.src;
        img.removeAttribute('data-src');
        observer.unobserve(img);
      }
    });
  }, { rootMargin: '200px' });
  lazyImgs.forEach(img => observer.observe(img));
}

/* ── Filter Sidebar Toggle (mobile) ─────────────────────────── */
document.getElementById('filterToggle')?.addEventListener('click', () => {
  document.querySelector('.filter-sidebar')?.classList.toggle('open');
  overlay?.classList.toggle('show');
});

/* ── Price Range ─────────────────────────────────────────────── */
const minPrice = document.getElementById('minPrice');
const maxPrice = document.getElementById('maxPrice');
[minPrice, maxPrice].forEach(input => {
  input?.addEventListener('change', () => {
    const min = minPrice?.value || 0;
    const max = maxPrice?.value || 0;
    const url = new URL(window.location.href);
    if (min) url.searchParams.set('min_price', min);
    if (max) url.searchParams.set('max_price', max);
    url.searchParams.set('page', 1);
    window.location.href = url.toString();
  });
});

/* ── Sort Select ─────────────────────────────────────────────── */
document.getElementById('sortSelect')?.addEventListener('change', function() {
  const url = new URL(window.location.href);
  url.searchParams.set('sort', this.value);
  url.searchParams.set('page', 1);
  window.location.href = url.toString();
});

/* ── Animate on Scroll ───────────────────────────────────────── */
if ('IntersectionObserver' in window) {
  const animEls = document.querySelectorAll('.product-card, .category-card, .stat-card, .testimonial-card');
  const animObs = new IntersectionObserver((entries) => {
    entries.forEach((entry, i) => {
      if (entry.isIntersecting) {
        setTimeout(() => entry.target.classList.add('animate-fade'), i * 80);
        animObs.unobserve(entry.target);
      }
    });
  }, { threshold: 0.1 });
  animEls.forEach(el => animObs.observe(el));
}

/* ── Hero Slider Dots ────────────────────────────────────────── */
const heroDots = document.querySelectorAll('.hero-dots span');
heroDots.forEach((dot, i) => {
  dot.addEventListener('click', () => {
    heroDots.forEach(d => d.classList.remove('active'));
    dot.classList.add('active');
  });
});

/* ── Countdown Timer ─────────────────────────────────────────── */
function initCountdown() {
  const el = document.getElementById('saleCountdown');
  if (!el) return;
  const end = new Date(el.dataset.end).getTime();
  const tick = setInterval(() => {
    const diff = end - Date.now();
    if (diff <= 0) { el.innerHTML = 'Sale Ended'; clearInterval(tick); return; }
    const h = Math.floor(diff / 3600000);
    const m = Math.floor((diff % 3600000) / 60000);
    const s = Math.floor((diff % 60000) / 1000);
    el.innerHTML = `<span>${String(h).padStart(2,'0')}</span>:<span>${String(m).padStart(2,'0')}</span>:<span>${String(s).padStart(2,'0')}</span>`;
  }, 1000);
}
initCountdown();
