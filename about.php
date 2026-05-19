<?php
$page_title = 'About Us';
require_once 'includes/functions.php';
?>
<?php require_once 'includes/header.php'; ?>
<?php require_once 'includes/navbar.php'; ?>

<!-- About Hero -->
<div class="about-hero">
    <div class="container">
        <h1>About <span>EffaFashion</span></h1>
        <p>Redefining luxury fashion with timeless elegance, modern style, and a passion for quality craftsmanship.</p>
    </div>
</div>

<!-- Story Section -->
<section class="section">
    <div class="container">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center;">
            <div>
                <div style="color:#D4AF37;font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;margin-bottom:12px;">Our Story</div>
                <h2 style="font-family:'Playfair Display',serif;font-size:36px;margin-bottom:20px;line-height:1.3;">
                    Born from a Passion for <span style="color:#D4AF37;">Luxury</span>
                </h2>
                <p style="color:#666;line-height:1.9;margin-bottom:16px;font-size:15px;">
                    EffaFashion was founded with a singular vision: to bring world-class luxury fashion to the modern African consumer. We believe that style knows no boundaries, and that every individual deserves to dress with confidence and elegance.
                </p>
                <p style="color:#666;line-height:1.9;margin-bottom:24px;font-size:15px;">
                    From our carefully curated collections to our premium customer service, every aspect of EffaFashion is designed to deliver an exceptional experience. We source only the finest fabrics and work with skilled artisans to create pieces that stand the test of time.
                </p>
                <div style="display:flex;gap:30px;">
                    <div style="text-align:center;">
                        <div style="font-family:'Playfair Display',serif;font-size:36px;color:#D4AF37;font-weight:700;">10+</div>
                        <div style="font-size:13px;color:#999;">Years in Fashion</div>
                    </div>
                    <div style="text-align:center;">
                        <div style="font-family:'Playfair Display',serif;font-size:36px;color:#D4AF37;font-weight:700;">5K+</div>
                        <div style="font-size:13px;color:#999;">Happy Customers</div>
                    </div>
                    <div style="text-align:center;">
                        <div style="font-family:'Playfair Display',serif;font-size:36px;color:#D4AF37;font-weight:700;">500+</div>
                        <div style="font-size:13px;color:#999;">Products</div>
                    </div>
                </div>
            </div>
            <div style="background:#f5f5f5;border-radius:16px;aspect-ratio:1;display:flex;align-items:center;justify-content:center;">
                <i class="fas fa-store" style="font-size:120px;color:#D4AF37;opacity:0.3;"></i>
            </div>
        </div>
    </div>
</section>

<!-- Values -->
<section class="section section-dark">
    <div class="container">
        <div class="section-header">
            <h2>Our <span>Values</span></h2>
            <p>The principles that guide everything we do</p>
        </div>
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:30px;">
            <?php
            $values = [
                ['icon'=>'gem',         'title'=>'Quality',     'text'=>'We never compromise on quality. Every piece is crafted with premium materials and attention to detail.'],
                ['icon'=>'heart',       'title'=>'Passion',     'text'=>'Fashion is our passion. We pour love and creativity into every collection we curate.'],
                ['icon'=>'users',       'title'=>'Community',   'text'=>'We celebrate diversity and believe fashion should be inclusive and accessible to all.'],
                ['icon'=>'leaf',        'title'=>'Sustainability','text'=>'We are committed to ethical sourcing and sustainable fashion practices.'],
            ];
            foreach ($values as $v): ?>
            <div style="text-align:center;padding:30px 20px;">
                <div style="width:70px;height:70px;background:rgba(212,175,55,0.15);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
                    <i class="fas fa-<?= $v['icon'] ?>" style="font-size:28px;color:#D4AF37;"></i>
                </div>
                <h4 style="font-family:'Playfair Display',serif;font-size:20px;color:#fff;margin-bottom:12px;"><?= $v['title'] ?></h4>
                <p style="color:rgba(255,255,255,0.55);font-size:14px;line-height:1.8;"><?= $v['text'] ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Team -->
<section class="section section-gray">
    <div class="container">
        <div class="section-header">
            <h2>Meet Our <span>Team</span></h2>
            <p>The passionate people behind EffaFashion</p>
        </div>
        <div class="team-grid">
            <?php
            $team = [
                ['name'=>'Effa Johnson',    'role'=>'Founder & CEO',        'img'=>''],
                ['name'=>'Amara Okafor',    'role'=>'Creative Director',    'img'=>''],
                ['name'=>'Chidi Nwosu',     'role'=>'Head of Operations',   'img'=>''],
                ['name'=>'Fatima Bello',    'role'=>'Lead Stylist',         'img'=>''],
            ];
            foreach ($team as $member): ?>
            <div class="team-card">
                <div style="width:140px;height:140px;border-radius:50%;background:#e0e0e0;border:4px solid #D4AF37;margin:0 auto 16px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-user" style="font-size:50px;color:#bbb;"></i>
                </div>
                <h4><?= $member['name'] ?></h4>
                <p><?= $member['role'] ?></p>
                <div style="display:flex;justify-content:center;gap:10px;margin-top:12px;">
                    <a href="#" style="color:#3b5998;"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" style="color:#1da1f2;"><i class="fab fa-twitter"></i></a>
                    <a href="#" style="color:#0077b5;"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
