<?php
$page_title = 'Home - Luxury Fashion';
$page_desc  = 'EffaFashion - Premium luxury fashion for men and women. Shop the latest collections.';
require_once 'includes/functions.php';
$featured_products = getProducts(8, 0, null, '', 0, 0, true);
$categories        = getCategories();
$new_arrivals      = getProducts(4);
$wl_ids            = getWishlistIds();
?>
<?php require_once 'includes/header.php'; ?>
<?php require_once 'includes/navbar.php'; ?>

<!-- ═══════════════════════════════════════════════════════
     GLOBAL ANIMATION STYLES
═══════════════════════════════════════════════════════ -->
<style>
/* ── Scroll Reveal ───────────────────────────────────────── */
.reveal { opacity:0; transform:translateY(50px); transition:opacity 0.8s ease, transform 0.8s ease; }
.reveal.from-left  { transform:translateX(-60px); }
.reveal.from-right { transform:translateX(60px); }
.reveal.from-scale { transform:scale(0.85); }
.reveal.visible    { opacity:1; transform:none; }
.reveal-delay-1 { transition-delay:0.1s; }
.reveal-delay-2 { transition-delay:0.2s; }
.reveal-delay-3 { transition-delay:0.3s; }
.reveal-delay-4 { transition-delay:0.4s; }
.reveal-delay-5 { transition-delay:0.5s; }

/* ── 3D Card Tilt ────────────────────────────────────────── */
.tilt-card { transform-style:preserve-3d; transition:transform 0.1s ease; will-change:transform; }
.tilt-card .tilt-inner { transform:translateZ(20px); }

/* ── Parallax Section ────────────────────────────────────── */
.parallax-section { position:relative; overflow:hidden; }
.parallax-bg { position:absolute; inset:-20%; background-size:cover; background-position:center;
               will-change:transform; transition:transform 0.05s linear; }

/* ── Floating particles ──────────────────────────────────── */
.particles-wrap { position:absolute; inset:0; overflow:hidden; pointer-events:none; z-index:1; }
.particle { position:absolute; border-radius:50%; background:rgba(212,175,55,0.15);
            animation:floatUp linear infinite; }
@keyframes floatUp {
    0%   { transform:translateY(100%) rotate(0deg); opacity:0; }
    10%  { opacity:1; }
    90%  { opacity:0.5; }
    100% { transform:translateY(-100vh) rotate(720deg); opacity:0; }
}

/* ── Typewriter ──────────────────────────────────────────── */
.typewriter { border-right:3px solid #D4AF37; white-space:nowrap; overflow:hidden;
              animation:blink 0.75s step-end infinite; }
@keyframes blink { 50% { border-color:transparent; } }

/* ── Magnetic Button ─────────────────────────────────────── */
.btn-magnetic { transition:transform 0.2s ease, box-shadow 0.2s ease; }
.btn-magnetic:hover { box-shadow:0 20px 40px rgba(212,175,55,0.4); }

/* ── Glowing text ────────────────────────────────────────── */
@keyframes goldGlow {
    0%,100% { text-shadow:0 0 10px rgba(212,175,55,0.3), 0 0 20px rgba(212,175,55,0.1); }
    50%     { text-shadow:0 0 20px rgba(212,175,55,0.8), 0 0 40px rgba(212,175,55,0.4), 0 0 60px rgba(212,175,55,0.2); }
}
.glow-text { animation:goldGlow 3s ease-in-out infinite; }

/* ── Storytelling strip ──────────────────────────────────── */
.story-section { background:#000; padding:100px 0; overflow:hidden; }
.story-grid { display:grid; grid-template-columns:1fr 1fr; gap:80px; align-items:center; }
.story-grid.reverse { direction:rtl; }
.story-grid.reverse > * { direction:ltr; }
.story-image-wrap { position:relative; border-radius:20px; overflow:hidden; aspect-ratio:4/5; }
.story-image-wrap img { width:100%; height:100%; object-fit:cover; transition:transform 0.6s ease; }
.story-image-wrap:hover img { transform:scale(1.05); }
.story-image-wrap::before { content:''; position:absolute; inset:0; background:linear-gradient(135deg,rgba(212,175,55,0.2),transparent); z-index:1; pointer-events:none; }
.story-number { font-family:'Playfair Display',serif; font-size:120px; font-weight:700;
                color:rgba(212,175,55,0.08); line-height:1; position:absolute; top:-20px; left:-10px; z-index:0; }
.story-content { position:relative; z-index:1; }
.story-tag { display:inline-block; background:rgba(212,175,55,0.15); color:#D4AF37;
             padding:6px 16px; border-radius:20px; font-size:12px; font-weight:700;
             letter-spacing:2px; text-transform:uppercase; margin-bottom:16px; }
.story-content h2 { font-family:'Playfair Display',serif; font-size:clamp(28px,4vw,48px);
                    color:#fff; line-height:1.2; margin-bottom:16px; }
.story-content p { color:rgba(255,255,255,0.6); font-size:15px; line-height:1.9; margin-bottom:28px; }
.story-feature { display:flex; align-items:center; gap:12px; margin-bottom:12px; }
.story-feature i { width:36px; height:36px; background:rgba(212,175,55,0.15); border-radius:50%;
                   display:flex; align-items:center; justify-content:center; color:#D4AF37; font-size:14px; flex-shrink:0; }
.story-feature span { color:rgba(255,255,255,0.7); font-size:14px; }

/* ── Counter animation ───────────────────────────────────── */
.stat-number { transition:all 0.3s ease; }

/* ── Product card 3D ─────────────────────────────────────── */
.product-card { transition:transform 0.3s ease, box-shadow 0.3s ease; }
.product-card:hover { transform:translateY(-8px) rotateX(2deg); box-shadow:0 20px 50px rgba(0,0,0,0.15); }

/* ── Category card parallax ──────────────────────────────── */
.category-card img { transition:transform 0.6s ease; }
.category-card:hover img { transform:scale(1.12) translateY(-4px); }

/* ── Scroll progress bar ─────────────────────────────────── */
#scrollProgress { position:fixed; top:0; left:0; height:3px; background:linear-gradient(to right,#D4AF37,#f0d060);
                  z-index:9999; width:0%; transition:width 0.1s linear; }

/* ── Hero text animation ─────────────────────────────────── */
.hero-content .tag   { animation:slideDown 0.8s ease 0.2s both; }
.hero-content h1     { animation:slideDown 0.8s ease 0.4s both; }
.hero-content p      { animation:slideDown 0.8s ease 0.6s both; }
.hero-content .hero-buttons { animation:slideDown 0.8s ease 0.8s both; }
@keyframes slideDown { from { opacity:0; transform:translateY(-30px); } to { opacity:1; transform:translateY(0); } }

/* ── Marquee ticker ──────────────────────────────────────── */
.marquee-wrap { background:#D4AF37; padding:10px 0; overflow:hidden; }
.marquee-track { display:flex; gap:0; animation:marquee 20s linear infinite; white-space:nowrap; }
.marquee-track span { padding:0 40px; font-size:13px; font-weight:700; color:#000;
                      letter-spacing:1px; text-transform:uppercase; }
.marquee-track span::after { content:'✦'; margin-left:40px; }
@keyframes marquee { from { transform:translateX(0); } to { transform:translateX(-50%); } }

@media(max-width:768px) {
    .story-grid, .story-grid.reverse { grid-template-columns:1fr; gap:30px; direction:ltr; }
    .story-number { font-size:70px; }
}
</style>

<!-- Scroll progress bar -->
<div id="scrollProgress"></div>

<!-- ═══════════════════════════════════════════════════════
     HERO — Auto-sliding with parallax overlay
═══════════════════════════════════════════════════════ -->
<section class="hero" id="heroSection">
    <div class="hero-slider" id="heroSlider">
        <div class="hero-slide active" style="background-image:url('https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=1600&h=900&fit=crop&q=80');"></div>
        <div class="hero-slide" style="background-image:url('https://images.unsplash.com/photo-1600185365483-26d7a4cc7519?w=1600&h=900&fit=crop&q=80');"></div>
        <div class="hero-slide" style="background-image:url('https://images.unsplash.com/photo-1617137968427-85924c800a22?w=1600&h=900&fit=crop&q=80');"></div>
        <div class="hero-slide" style="background-image:url('https://images.unsplash.com/photo-1490114538077-0a7f8cb49891?w=1600&h=900&fit=crop&q=80');"></div>
        <div class="hero-slide" style="background-image:url('https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=1600&h=900&fit=crop&q=80');"></div>
        <div class="hero-slide" style="background-image:url('https://images.unsplash.com/photo-1539109136881-3be0616acf4b?w=1600&h=900&fit=crop&q=80');"></div>
    </div>
    <div class="hero-overlay"></div>
    <!-- Floating particles -->
    <div class="particles-wrap" id="particles"></div>

    <div class="container" style="position:relative;z-index:2;">
        <div class="hero-content">
            <span class="tag">New Collection 2025</span>
            <h1>Dress to <span class="glow-text" style="color:#D4AF37;">Impress</span><br>
                <span id="typewriterText" class="typewriter"></span>
            </h1>
            <p>Discover our exclusive luxury fashion collections crafted for the modern individual who values elegance and sophistication.</p>
            <div class="hero-buttons">
                <a href="<?= SITE_URL ?>/products.php" class="btn btn-gold btn-lg btn-magnetic">
                    <i class="fas fa-shopping-bag"></i> Shop Now
                </a>
                <a href="<?= SITE_URL ?>/products.php?featured=1" class="btn btn-outline btn-lg btn-magnetic">
                    New Arrivals
                </a>
            </div>
        </div>
    </div>

    <div class="hero-dots" id="heroDots">
        <span class="active" onclick="goToSlide(0)"></span>
        <span onclick="goToSlide(1)"></span>
        <span onclick="goToSlide(2)"></span>
        <span onclick="goToSlide(3)"></span>
        <span onclick="goToSlide(4)"></span>
        <span onclick="goToSlide(5)"></span>
    </div>
    <button class="hero-arrow hero-arrow-left" onclick="changeSlide(-1)" aria-label="Previous"><i class="fas fa-chevron-left"></i></button>
    <button class="hero-arrow hero-arrow-right" onclick="changeSlide(1)" aria-label="Next"><i class="fas fa-chevron-right"></i></button>
    <a href="#marquee" class="hero-scroll"><i class="fas fa-chevron-down"></i></a>
</section>

<style>
.hero { position:relative; min-height:90vh; display:flex; align-items:center; overflow:hidden; background:#000; }
.hero-slider { position:absolute; inset:0; }
.hero-slide { position:absolute; inset:0; background-size:cover; background-position:center; opacity:0; transition:opacity 1.2s ease; transform:scale(1.06); transition:opacity 1.2s ease, transform 8s ease; }
.hero-slide.active { opacity:0.5; transform:scale(1); }
.hero-slide.slide-out { opacity:0; }
.hero-overlay { position:absolute; inset:0; background:linear-gradient(to right,rgba(0,0,0,0.85) 0%,rgba(0,0,0,0.4) 60%,rgba(0,0,0,0.1) 100%); z-index:1; }
.hero-arrow { position:absolute; top:50%; transform:translateY(-50%); z-index:3; width:48px; height:48px; border-radius:50%; background:rgba(212,175,55,0.2); border:2px solid rgba(212,175,55,0.5); color:#D4AF37; font-size:16px; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:all 0.3s ease; backdrop-filter:blur(4px); }
.hero-arrow:hover { background:rgba(212,175,55,0.9); color:#000; }
.hero-arrow-left { left:24px; } .hero-arrow-right { right:24px; }
.hero-dots { position:absolute; bottom:32px; left:50%; transform:translateX(-50%); display:flex; gap:10px; z-index:3; }
.hero-dots span { width:28px; height:4px; border-radius:2px; background:rgba(255,255,255,0.35); cursor:pointer; transition:all 0.4s ease; }
.hero-dots span.active { background:#D4AF37; width:48px; }
</style>

<!-- ═══════════════════════════════════════════════════════
     MARQUEE TICKER
═══════════════════════════════════════════════════════ -->
<div class="marquee-wrap" id="marquee">
    <div class="marquee-track">
        <span>Free Delivery</span><span>New Arrivals 2025</span><span>Premium Quality</span>
        <span>Exclusive Designs</span><span>Shop Now</span><span>Luxury Fashion</span>
        <span>Men's Collection</span><span>Women's Collection</span><span>Accessories</span>
        <!-- duplicate for seamless loop -->
        <span>Free Delivery</span><span>New Arrivals 2025</span><span>Premium Quality</span>
        <span>Exclusive Designs</span><span>Shop Now</span><span>Luxury Fashion</span>
        <span>Men's Collection</span><span>Women's Collection</span><span>Accessories</span>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     STATS — Counter animation on scroll
═══════════════════════════════════════════════════════ -->
<section class="section-dark section-sm">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-card reveal from-scale reveal-delay-1">
                <div class="stat-number" data-target="5000">0</div>
                <div class="stat-label">Happy Customers</div>
            </div>
            <div class="stat-card reveal from-scale reveal-delay-2">
                <div class="stat-number" data-target="500">0</div>
                <div class="stat-label">Products</div>
            </div>
            <div class="stat-card reveal from-scale reveal-delay-3">
                <div class="stat-number" data-target="50">0</div>
                <div class="stat-label">Brands</div>
            </div>
            <div class="stat-card reveal from-scale reveal-delay-4">
                <div class="stat-number" data-target="10">0</div>
                <div class="stat-label">Years Experience</div>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════
     CATEGORIES — Parallax cards
═══════════════════════════════════════════════════════ -->
<section class="section section-gray" id="categories">
    <div class="container">
        <div class="section-header reveal">
            <h2>Shop by <span>Category</span></h2>
            <p>Explore our curated collections for every style and occasion</p>
        </div>
        <div class="categories-grid">
            <?php
            $cat_images = [
                'women'        => 'https://images.unsplash.com/photo-1543163521-1bf539c55dd2?w=400&h=530&fit=crop&q=80',
                'men'          => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400&h=530&fit=crop&q=80',
                'accessories'  => 'https://images.unsplash.com/photo-1523293182086-7651a899d37f?w=400&h=530&fit=crop&q=80',
                'new-arrivals' => 'https://images.unsplash.com/photo-1473966968600-fa801b869a1a?w=400&h=530&fit=crop&q=80',
                'sale'         => 'https://images.unsplash.com/photo-1600185365483-26d7a4cc7519?w=400&h=530&fit=crop&q=80',
            ];
            foreach (array_slice($categories, 0, 5) as $i => $cat):
                $slug = $cat['slug'];
                $img  = $cat_images[$slug] ?? 'https://images.unsplash.com/photo-1445205170230-053b83016050?w=400&h=530&fit=crop&q=80';
            ?>
            <a href="<?= SITE_URL ?>/products.php?category=<?= $cat['id'] ?>"
               class="category-card reveal reveal-delay-<?= $i + 1 ?>">
                <img src="<?= $img ?>" alt="<?= htmlspecialchars($cat['name']) ?>" loading="lazy"
                     onerror="this.src='<?= SITE_URL ?>/assets/images/products/placeholder.jpg'">
                <div class="category-card-overlay">
                    <h3><?= htmlspecialchars($cat['name']) ?></h3>
                    <span>Shop Now <i class="fas fa-arrow-right"></i></span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════
     STORYTELLING — Brand story sections
═══════════════════════════════════════════════════════ -->
<section class="story-section">
    <div class="container">

        <!-- Story 1 -->
        <div class="story-grid" style="margin-bottom:100px;">
            <div class="story-image-wrap reveal from-left">
                <span class="story-number">01</span>
                <img src="https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600&h=750&fit=crop&q=80" alt="Premium Shoes">
            </div>
            <div class="story-content reveal from-right">
                <span class="story-tag">Our Craft</span>
                <h2>Every Step Tells<br>a <span style="color:#D4AF37;">Story</span></h2>
                <p>We source only the finest footwear from premium manufacturers. Each pair is selected for its craftsmanship, comfort, and style — because what you wear on your feet defines how you walk through the world.</p>
                <div class="story-feature"><i class="fas fa-check"></i><span>Premium quality materials</span></div>
                <div class="story-feature"><i class="fas fa-check"></i><span>Handpicked by fashion experts</span></div>
                <div class="story-feature"><i class="fas fa-check"></i><span>Comfort meets elegance</span></div>
                <a href="<?= SITE_URL ?>/products.php" class="btn btn-gold btn-magnetic" style="margin-top:10px;">
                    <i class="fas fa-shoe-prints"></i> Shop Shoes
                </a>
            </div>
        </div>

        <!-- Story 2 -->
        <div class="story-grid reverse" style="margin-bottom:100px;">
            <div class="story-image-wrap reveal from-right">
                <span class="story-number">02</span>
                <img src="https://images.unsplash.com/photo-1473966968600-fa801b869a1a?w=600&h=750&fit=crop&q=80" alt="Premium Trousers">
            </div>
            <div class="story-content reveal from-left">
                <span class="story-tag">The Collection</span>
                <h2>Tailored for the<br><span style="color:#D4AF37;">Modern Man</span></h2>
                <p>From slim-fit denim to tailored trousers, our men's collection is designed for the individual who demands both style and substance. Every cut, every stitch — made to make you stand out.</p>
                <div class="story-feature"><i class="fas fa-check"></i><span>Slim, regular & relaxed fits</span></div>
                <div class="story-feature"><i class="fas fa-check"></i><span>Premium denim & fabric blends</span></div>
                <div class="story-feature"><i class="fas fa-check"></i><span>Sizes 28 to 38 available</span></div>
                <a href="<?= SITE_URL ?>/products.php?category=2" class="btn btn-gold btn-magnetic" style="margin-top:10px;">
                    <i class="fas fa-tshirt"></i> Shop Men's
                </a>
            </div>
        </div>

        <!-- Story 3 -->
        <div class="story-grid">
            <div class="story-image-wrap reveal from-left">
                <span class="story-number">03</span>
                <img src="https://images.unsplash.com/photo-1523293182086-7651a899d37f?w=600&h=750&fit=crop&q=80" alt="Accessories">
            </div>
            <div class="story-content reveal from-right">
                <span class="story-tag">Accessories</span>
                <h2>The Finishing<br><span style="color:#D4AF37;">Touch</span></h2>
                <p>A great outfit is never complete without the right accessories. From luxury fragrances to statement pieces — our accessories collection adds that final layer of sophistication to every look.</p>
                <div class="story-feature"><i class="fas fa-check"></i><span>Luxury fragrances & deodorants</span></div>
                <div class="story-feature"><i class="fas fa-check"></i><span>Statement jewellery & bags</span></div>
                <div class="story-feature"><i class="fas fa-check"></i><span>Curated for the discerning buyer</span></div>
                <a href="<?= SITE_URL ?>/products.php?category=3" class="btn btn-gold btn-magnetic" style="margin-top:10px;">
                    <i class="fas fa-gem"></i> Shop Accessories
                </a>
            </div>
        </div>

    </div>
</section>

<!-- ═══════════════════════════════════════════════════════
     FEATURED PRODUCTS — 3D tilt cards
═══════════════════════════════════════════════════════ -->
<section class="section">
    <div class="container">
        <div class="section-header reveal">
            <h2>Featured <span>Products</span></h2>
            <p>Handpicked luxury pieces for the discerning fashion lover</p>
        </div>
        <div class="products-grid">
            <?php foreach ($featured_products as $idx => $product):
                $price       = $product['sale_price'] ? $product['sale_price'] : $product['price'];
                $discount    = getDiscountPercent($product['price'], $product['sale_price']);
                $img         = getProductImage($product['image']);
                $in_wishlist = isset($wl_ids[$product['id']]);
                $delay       = ($idx % 4) + 1;
            ?>
            <div class="product-card tilt-card reveal reveal-delay-<?= $delay ?>">
                <div class="product-card-image tilt-inner">
                    <a href="<?= SITE_URL ?>/product-details.php?slug=<?= urlencode($product['slug']) ?>">
                        <img src="<?= $img ?>" alt="<?= htmlspecialchars($product['name']) ?>" loading="lazy">
                    </a>
                    <div class="product-card-badges">
                        <?php if ($discount > 0): ?><span class="product-badge-sale">-<?= $discount ?>%</span><?php endif; ?>
                        <?php if ($product['is_featured']): ?><span class="product-badge-new">New</span><?php endif; ?>
                        <?php if ($product['stock'] == 0): ?><span class="product-badge-out">Sold Out</span><?php endif; ?>
                    </div>
                    <button class="product-wishlist <?= $in_wishlist ? 'active' : '' ?>" data-id="<?= $product['id'] ?>">
                        <i class="<?= $in_wishlist ? 'fas' : 'far' ?> fa-heart"></i>
                    </button>
                    <div class="product-card-overlay">
                        <button class="quick-add-btn" data-id="<?= $product['id'] ?>">
                            <i class="fas fa-shopping-bag"></i> Quick Add
                        </button>
                    </div>
                </div>
                <div class="product-card-body">
                    <div class="product-card-category"><?= htmlspecialchars($product['category_name']) ?></div>
                    <h3 class="product-card-name">
                        <a href="<?= SITE_URL ?>/product-details.php?slug=<?= urlencode($product['slug']) ?>">
                            <?= htmlspecialchars($product['name']) ?>
                        </a>
                    </h3>
                    <div class="stars"><?php for ($s=1;$s<=5;$s++): ?><i class="fas fa-star"></i><?php endfor; ?></div>
                    <div class="product-card-price">
                        <span class="price-current <?= $product['sale_price'] ? 'price-sale' : '' ?>"><?= formatPrice($price) ?></span>
                        <?php if ($product['sale_price']): ?><span class="price-original"><?= formatPrice($product['price']) ?></span><?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-5 reveal">
            <a href="<?= SITE_URL ?>/products.php" class="btn btn-dark btn-lg btn-magnetic">
                View All Products <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════
     PARALLAX CTA BANNER
═══════════════════════════════════════════════════════ -->
<section class="parallax-section" style="min-height:500px;display:flex;align-items:center;">
    <div class="parallax-bg" id="parallaxBg"
         style="background-image:url('https://images.unsplash.com/photo-1490114538077-0a7f8cb49891?w=1600&h=900&fit=crop&q=80');opacity:0.35;"></div>
    <div style="position:absolute;inset:0;background:linear-gradient(135deg,rgba(0,0,0,0.9),rgba(26,18,0,0.85));z-index:1;"></div>
    <div class="container text-center" style="position:relative;z-index:2;">
        <div class="reveal">
            <h2 style="font-family:'Playfair Display',serif;font-size:clamp(28px,5vw,52px);color:#fff;margin-bottom:16px;">
                Exclusive <span class="glow-text" style="color:#D4AF37;">Members</span> Sale
            </h2>
            <p style="color:rgba(255,255,255,0.6);font-size:16px;max-width:500px;margin:0 auto 32px;">
                Sign up today and get 20% off your first order. Plus free delivery on all orders.
            </p>
            <div style="display:flex;gap:16px;justify-content:center;flex-wrap:wrap;">
                <a href="<?= SITE_URL ?>/register.php" class="btn btn-gold btn-lg btn-magnetic">Join Now — It's Free</a>
                <a href="<?= SITE_URL ?>/products.php?sale=1" class="btn btn-outline btn-lg btn-magnetic">View Sale Items</a>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════
     NEW ARRIVALS
═══════════════════════════════════════════════════════ -->
<section class="section section-gray">
    <div class="container">
        <div class="section-header reveal">
            <h2>New <span>Arrivals</span></h2>
            <p>The latest additions to our luxury collection</p>
        </div>
        <div class="products-grid">
            <?php foreach ($new_arrivals as $idx => $product):
                $price    = $product['sale_price'] ? $product['sale_price'] : $product['price'];
                $discount = getDiscountPercent($product['price'], $product['sale_price']);
                $img      = getProductImage($product['image']);
                $delay    = $idx + 1;
            ?>
            <div class="product-card tilt-card reveal reveal-delay-<?= $delay ?>">
                <div class="product-card-image">
                    <a href="<?= SITE_URL ?>/product-details.php?slug=<?= urlencode($product['slug']) ?>">
                        <img src="<?= $img ?>" alt="<?= htmlspecialchars($product['name']) ?>" loading="lazy">
                    </a>
                    <div class="product-card-badges">
                        <span class="product-badge-new">New</span>
                        <?php if ($discount > 0): ?><span class="product-badge-sale">-<?= $discount ?>%</span><?php endif; ?>
                    </div>
                    <button class="product-wishlist" data-id="<?= $product['id'] ?>"><i class="far fa-heart"></i></button>
                    <div class="product-card-overlay">
                        <button class="quick-add-btn" data-id="<?= $product['id'] ?>"><i class="fas fa-shopping-bag"></i> Quick Add</button>
                    </div>
                </div>
                <div class="product-card-body">
                    <div class="product-card-category"><?= htmlspecialchars($product['category_name']) ?></div>
                    <h3 class="product-card-name">
                        <a href="<?= SITE_URL ?>/product-details.php?slug=<?= urlencode($product['slug']) ?>"><?= htmlspecialchars($product['name']) ?></a>
                    </h3>
                    <div class="product-card-price">
                        <span class="price-current <?= $product['sale_price'] ? 'price-sale' : '' ?>"><?= formatPrice($price) ?></span>
                        <?php if ($product['sale_price']): ?><span class="price-original"><?= formatPrice($product['price']) ?></span><?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════
     TESTIMONIALS
═══════════════════════════════════════════════════════ -->
<section class="section">
    <div class="container">
        <div class="section-header reveal">
            <h2>What Our <span>Customers</span> Say</h2>
            <p>Real reviews from our valued customers</p>
        </div>
        <div class="testimonials-grid">
            <?php
            $testimonials = [
                ['name'=>'Amara Tadesse','role'=>'Fashion Blogger','text'=>'EffaFashion has completely transformed my wardrobe. The quality is exceptional and every piece is simply stunning.','rating'=>5],
                ['name'=>'Kebede Alemu','role'=>'Business Executive','text'=>'I ordered the Classic Black Suit and it arrived perfectly. The attention to detail is remarkable. Will definitely order again.','rating'=>5],
                ['name'=>'Fatuma Hassen','role'=>'Stylist','text'=>'As a professional stylist, I recommend EffaFashion to all my clients. Always on trend and the prices are fair for the quality.','rating'=>5],
            ];
            foreach ($testimonials as $i => $t): ?>
            <div class="testimonial-card reveal reveal-delay-<?= $i+1 ?>">
                <div class="stars mb-3"><?php for ($s=0;$s<$t['rating'];$s++): ?><i class="fas fa-star"></i><?php endfor; ?></div>
                <p class="testimonial-text">"<?= $t['text'] ?>"</p>
                <div class="testimonial-author">
                    <img src="<?= SITE_URL ?>/assets/images/products/placeholder.jpg" alt="<?= $t['name'] ?>">
                    <div>
                        <div class="testimonial-author-name"><?= $t['name'] ?></div>
                        <div class="testimonial-author-role"><?= $t['role'] ?></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════
     ALL JAVASCRIPT EFFECTS
═══════════════════════════════════════════════════════ -->
<script>
/* ── Hero Slider ─────────────────────────────────────────── */
(function() {
    const slides = document.querySelectorAll('.hero-slide');
    const dots   = document.querySelectorAll('#heroDots span');
    let current  = 0, timer;
    function goToSlide(n) {
        slides[current].classList.remove('active');
        dots[current].classList.remove('active');
        current = (n + slides.length) % slides.length;
        slides[current].classList.add('active');
        dots[current].classList.add('active');
    }
    function changeSlide(dir) { clearInterval(timer); goToSlide(current + dir); startAuto(); }
    function startAuto() { timer = setInterval(() => goToSlide(current + 1), 4500); }
    window.goToSlide   = n => { clearInterval(timer); goToSlide(n); startAuto(); };
    window.changeSlide = changeSlide;
    startAuto();
})();

/* ── Typewriter Effect ───────────────────────────────────── */
(function() {
    const words  = ['Live in Style', 'Own the Look', 'Define Your Era', 'Walk with Confidence'];
    const el     = document.getElementById('typewriterText');
    if (!el) return;
    let wi = 0, ci = 0, deleting = false;
    function type() {
        const word = words[wi];
        el.textContent = deleting ? word.substring(0, ci--) : word.substring(0, ci++);
        if (!deleting && ci > word.length)      { deleting = true; setTimeout(type, 1800); return; }
        if (deleting  && ci < 0)                { deleting = false; wi = (wi+1) % words.length; }
        setTimeout(type, deleting ? 60 : 100);
    }
    setTimeout(type, 1200);
})();

/* ── Floating Particles ──────────────────────────────────── */
(function() {
    const wrap = document.getElementById('particles');
    if (!wrap) return;
    for (let i = 0; i < 18; i++) {
        const p = document.createElement('div');
        p.className = 'particle';
        const size = Math.random() * 12 + 4;
        p.style.cssText = `width:${size}px;height:${size}px;left:${Math.random()*100}%;
            animation-duration:${Math.random()*12+8}s;animation-delay:${Math.random()*8}s;
            opacity:${Math.random()*0.4+0.1};`;
        wrap.appendChild(p);
    }
})();

/* ── Scroll Progress Bar ─────────────────────────────────── */
window.addEventListener('scroll', () => {
    const bar  = document.getElementById('scrollProgress');
    const pct  = (window.scrollY / (document.body.scrollHeight - window.innerHeight)) * 100;
    if (bar) bar.style.width = pct + '%';
});

/* ── Scroll Reveal ───────────────────────────────────────── */
(function() {
    const obs = new IntersectionObserver((entries) => {
        entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); obs.unobserve(e.target); } });
    }, { threshold: 0.12 });
    document.querySelectorAll('.reveal').forEach(el => obs.observe(el));
})();

/* ── Parallax Background ─────────────────────────────────── */
(function() {
    const bg = document.getElementById('parallaxBg');
    if (!bg) return;
    window.addEventListener('scroll', () => {
        const section = bg.closest('.parallax-section');
        if (!section) return;
        const rect   = section.getBoundingClientRect();
        const offset = rect.top * 0.35;
        bg.style.transform = `translateY(${offset}px)`;
    }, { passive: true });
})();

/* ── Counter Animation ───────────────────────────────────── */
(function() {
    const counters = document.querySelectorAll('.stat-number[data-target]');
    const obs = new IntersectionObserver((entries) => {
        entries.forEach(e => {
            if (!e.isIntersecting) return;
            const el     = e.target;
            const target = parseInt(el.dataset.target);
            const suffix = target >= 1000 ? 'K+' : '+';
            const display = target >= 1000 ? target / 1000 : target;
            let start = 0;
            const step = display / 60;
            const tick = setInterval(() => {
                start += step;
                if (start >= display) { el.textContent = display + suffix; clearInterval(tick); }
                else el.textContent = Math.floor(start) + suffix;
            }, 25);
            obs.unobserve(el);
        });
    }, { threshold: 0.5 });
    counters.forEach(c => obs.observe(c));
})();

/* ── 3D Card Tilt ────────────────────────────────────────── */
document.querySelectorAll('.tilt-card').forEach(card => {
    card.addEventListener('mousemove', function(e) {
        const rect   = this.getBoundingClientRect();
        const x      = (e.clientX - rect.left) / rect.width  - 0.5;
        const y      = (e.clientY - rect.top)  / rect.height - 0.5;
        this.style.transform = `perspective(800px) rotateY(${x * 10}deg) rotateX(${-y * 10}deg) translateZ(4px)`;
    });
    card.addEventListener('mouseleave', function() {
        this.style.transform = 'perspective(800px) rotateY(0) rotateX(0) translateZ(0)';
    });
});

/* ── Magnetic Button ─────────────────────────────────────── */
document.querySelectorAll('.btn-magnetic').forEach(btn => {
    btn.addEventListener('mousemove', function(e) {
        const rect = this.getBoundingClientRect();
        const x    = (e.clientX - rect.left - rect.width  / 2) * 0.25;
        const y    = (e.clientY - rect.top  - rect.height / 2) * 0.25;
        this.style.transform = `translate(${x}px, ${y}px)`;
    });
    btn.addEventListener('mouseleave', function() {
        this.style.transform = 'translate(0,0)';
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
