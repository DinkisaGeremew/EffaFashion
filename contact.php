<?php
$page_title = 'Contact Us';
require_once 'includes/functions.php';

$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = sanitize($_POST['name']    ?? '');
    $email   = sanitize($_POST['email']   ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $message = sanitize($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($message)) {
        $error = 'Please fill in all required fields.';
    } elseif (strlen($message) < 10) {
        $error = 'Message must be at least 10 characters.';
    } else {
        $n = $conn->real_escape_string($name);
        $e = $conn->real_escape_string($email);
        $s = $conn->real_escape_string($subject);
        $m = $conn->real_escape_string($message);
        $conn->query("INSERT INTO contact_messages (name, email, subject, message) VALUES ('$n','$e','$s','$m')");
        $success = 'Thank you! Your message has been sent. We will get back to you within 24 hours.';
    }
}
?>
<?php require_once 'includes/header.php'; ?>
<?php require_once 'includes/navbar.php'; ?>

<!-- Page Header -->
<div style="background:var(--black);padding:60px 0;text-align:center;">
    <div class="container">
        <h1 style="font-family:'Playfair Display',serif;font-size:42px;color:#fff;margin-bottom:10px;">
            Get In <span style="color:#D4AF37;">Touch</span>
        </h1>
        <p style="color:rgba(255,255,255,0.55);font-size:15px;">We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>
    </div>
</div>

<div class="breadcrumb">
    <div class="container">
        <ul class="breadcrumb-list">
            <li><a href="<?= SITE_URL ?>">Home</a></li>
            <li class="active">Contact</li>
        </ul>
    </div>
</div>

<section class="section section-gray">
    <div class="container">
        <div class="contact-grid">

            <!-- Contact Info -->
            <div class="contact-info-card">
                <h3>Contact Information</h3>
                <p>Fill out the form and our team will get back to you within 24 hours.</p>

                <div class="contact-info-item">
                    <div class="contact-info-icon"><i class="fas fa-map-marker-alt"></i></div>
                    <div class="contact-info-text">
                        <h5>Our Location</h5>
                        <p>Burayu Dire, Infront of CBE<br>Ethiopia</p>
                    </div>
                </div>
                <div class="contact-info-item">
                    <div class="contact-info-icon"><i class="fas fa-phone"></i></div>
                    <div class="contact-info-text">
                        <h5>Phone Number</h5>
                        <p>+251 910 624 704</p>
                    </div>
                </div>
                <div class="contact-info-item">
                    <div class="contact-info-icon"><i class="fab fa-telegram"></i></div>
                    <div class="contact-info-text">
                        <h5>Telegram Channel</h5>
                        <p><a href="https://t.me/FaashiniiIfaa" target="_blank" style="color:rgba(255,255,255,0.6);">t.me/FaashiniiIfaa</a></p>
                    </div>
                </div>
                <div class="contact-info-item">
                    <div class="contact-info-icon"><i class="fas fa-user"></i></div>
                    <div class="contact-info-text">
                        <h5>Telegram Username</h5>
                        <p><a href="https://t.me/ipha_T" target="_blank" style="color:rgba(255,255,255,0.6);">@ipha_T</a></p>
                    </div>
                </div>
                <div class="contact-info-item">
                    <div class="contact-info-icon"><i class="fas fa-envelope"></i></div>
                    <div class="contact-info-text">
                        <h5>Email Address</h5>
                        <p>info@effafashion.com<br>support@effafashion.com</p>
                    </div>
                </div>
                <div class="contact-info-item">
                    <div class="contact-info-icon"><i class="fas fa-clock"></i></div>
                    <div class="contact-info-text">
                        <h5>Working Hours</h5>
                        <p>Monday – Saturday: 9AM – 6PM<br>Sunday: 12PM – 4PM</p>
                    </div>
                </div>

                <div class="social-links" style="margin-top:30px;">
                    <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="contact-form-card">
                <h3 style="font-family:'Playfair Display',serif;font-size:26px;margin-bottom:6px;">Send a Message</h3>
                <p style="color:#999;font-size:14px;margin-bottom:28px;">We typically reply within 24 hours.</p>

                <?php if ($success): ?>
                    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-error"><i class="fas fa-times-circle"></i> <?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form id="contactForm" method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Your Name <span class="required">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="Full name" required
                                   value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                            <div class="form-error"></div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email Address <span class="required">*</span></label>
                            <input type="email" name="email" class="form-control" placeholder="your@email.com" required
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                            <div class="form-error"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Subject</label>
                        <input type="text" name="subject" class="form-control" placeholder="What is this about?"
                               value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Message <span class="required">*</span></label>
                        <textarea name="message" class="form-control" rows="6"
                                  placeholder="Write your message here..." required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                        <div class="form-error"></div>
                    </div>
                    <button type="submit" class="btn btn-gold btn-lg">
                        <i class="fas fa-paper-plane"></i> Send Message
                    </button>
                </form>
            </div>
        </div>

        <!-- Map Placeholder -->
        <div style="margin-top:50px;border-radius:12px;overflow:hidden;height:350px;background:#e0e0e0;display:flex;align-items:center;justify-content:center;">
            <div style="text-align:center;color:#999;">
                <i class="fas fa-map-marked-alt" style="font-size:48px;margin-bottom:12px;display:block;color:#D4AF37;"></i>
                <p style="font-size:15px;">123 Fashion Street, Victoria Island, Lagos</p>
                <a href="https://maps.google.com" target="_blank" class="btn btn-gold btn-sm" style="margin-top:12px;">
                    <i class="fas fa-directions"></i> Get Directions
                </a>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
