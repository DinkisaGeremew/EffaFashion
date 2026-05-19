<!-- Newsletter Section -->
<section class="newsletter-section">
    <div class="container">
        <div class="newsletter-content">
            <div class="newsletter-text">
                <h3>Subscribe to Our Newsletter</h3>
                <p>Get the latest fashion updates, exclusive offers and style tips.</p>
            </div>
            <form class="newsletter-form" id="newsletterForm">
                <input type="email" name="email" placeholder="Enter your email address" required>
                <button type="submit" class="btn btn-gold">Subscribe</button>
            </form>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <!-- Brand -->
            <div class="footer-col footer-brand">
                <a href="<?= SITE_URL ?>/index.php" class="footer-logo">
                    <span class="brand-effa">EFFA</span><span class="brand-fashion">FASHION</span>
                </a>
                <p>Redefining luxury fashion with timeless elegance and modern style. Premium collections for the discerning individual.</p>
                <div class="social-links">
                    <a href="https://t.me/FaashiniiIfaa" target="_blank" aria-label="Telegram Channel"><i class="fab fa-telegram"></i></a>
                    <a href="https://t.me/ipha_T" target="_blank" aria-label="Telegram Username"><i class="fas fa-user"></i></a>
                    <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" aria-label="TikTok"><i class="fab fa-tiktok"></i></a>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="footer-col">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="<?= SITE_URL ?>/index.php">Home</a></li>
                    <li><a href="<?= SITE_URL ?>/products.php">Shop</a></li>
                    <li><a href="<?= SITE_URL ?>/products.php?featured=1">New Arrivals</a></li>
                    <li><a href="<?= SITE_URL ?>/products.php?sale=1">Sale</a></li>
                    <li><a href="<?= SITE_URL ?>/about.php">About Us</a></li>
                    <li><a href="<?= SITE_URL ?>/contact.php">Contact</a></li>
                </ul>
            </div>

            <!-- Customer Service -->
            <div class="footer-col">
                <h4>Customer Service</h4>
                <ul>
                    <li><a href="<?= SITE_URL ?>/orders.php">Track Order</a></li>
                    <li><a href="#">Returns & Exchanges</a></li>
                    <li><a href="#">Shipping Policy</a></li>
                    <li><a href="#">Size Guide</a></li>
                    <li><a href="#">FAQ</a></li>
                    <li><a href="<?= SITE_URL ?>/contact.php">Help Center</a></li>
                </ul>
            </div>

            <!-- Contact Info -->
            <div class="footer-col">
                <h4>Contact Us</h4>
                <ul class="contact-list">
                    <li><i class="fas fa-map-marker-alt"></i> Burayu Dire, Infront of CBE, Ethiopia</li>
                    <li><i class="fas fa-phone"></i> +251 910 624 704</li>
                    <li><i class="fas fa-paper-plane"></i> <a href="https://t.me/FaashiniiIfaa" target="_blank" style="color:rgba(255,255,255,0.6);">t.me/FaashiniiIfaa</a></li>
                    <li><i class="fab fa-telegram"></i> <a href="https://t.me/ipha_T" target="_blank" style="color:rgba(255,255,255,0.6);">@ipha_T</a></li>
                    <li><i class="fas fa-clock"></i> Mon - Sat: 9AM - 6PM</li>
                </ul>
                <div class="payment-icons">
                    <img src="https://img.icons8.com/color/48/visa.png" alt="Visa" title="Visa">
                    <img src="https://img.icons8.com/color/48/mastercard.png" alt="Mastercard" title="Mastercard">
                    <img src="https://img.icons8.com/color/48/paypal.png" alt="PayPal" title="PayPal">
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved. Designed with <i class="fas fa-heart" style="color:#D4AF37"></i></p>
            <div class="footer-bottom-links">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
                <a href="#">Cookie Policy</a>
            </div>
        </div>
    </div>
</footer>

<!-- Back to Top -->
<button class="back-to-top" id="backToTop" aria-label="Back to top">
    <i class="fas fa-chevron-up"></i>
</button>

<!-- Cart Sidebar Overlay -->
<div class="overlay" id="overlay"></div>

<!-- Scripts — all deferred so they never block rendering -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
<script src="<?= SITE_URL ?>/assets/js/script.js" defer></script>
<script src="<?= SITE_URL ?>/assets/js/cart.js" defer></script>
<script src="<?= SITE_URL ?>/assets/js/validation.js" defer></script>

<script>
// Newsletter AJAX
document.getElementById('newsletterForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const email = this.querySelector('input[name="email"]').value;
    fetch('<?= SITE_URL ?>/ajax/newsletter.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'email=' + encodeURIComponent(email)
    })
    .then(r => r.json())
    .then(data => {
        Swal.fire({ icon: data.status, title: data.message, timer: 2000, showConfirmButton: false });
        if (data.status === 'success') this.reset();
    });
});
</script>
</body>
</html>
