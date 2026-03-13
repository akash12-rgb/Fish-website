<?php
$pageTitle = 'Sunbis AgroFish – Fresh From Water to Table';
$pageDesc  = 'Premium aquaculture and agro-farming products delivered fresh. Shop Tilapia, Catfish, Shrimp, Organic Rice and more.';
require_once __DIR__ . '/includes/header.php';

// Featured products
$featured = $db->query("
    SELECT p.*, c.category_name
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    WHERE p.is_featured = TRUE
    ORDER BY p.created_at DESC LIMIT 8
")->fetchAll();
?>
<meta name="app-url" content="<?= APP_URL ?>" />

<!-- ── HERO ── -->
<section class="hero-section">
  <div class="container py-5">
    <div class="row align-items-center g-5">
      <div class="col-lg-6" data-aos="fade-right">
        <span class="badge-pill d-inline-block mb-3">🌿 Sustainable Aquaculture &amp; Agro-Farming</span>
        <h1 class="mb-3">Fresh From<br><em>Water to Your Table</em></h1>
        <p class="lead mb-4">
          Premium fish, shrimp, organic rice, and aquaponic vegetables — farmed responsibly,
          harvested at peak freshness, delivered to you.
        </p>
        <div class="d-flex gap-3 flex-wrap">
          <a href="catalog.php" class="btn btn-primary-custom">🛒 Shop Now</a>
          <a href="#about" class="btn-outline-custom">Learn More</a>
        </div>
        <!-- Stats -->
        <div class="row g-3 mt-4">
          <?php foreach ([['12+', 'Years Experience'], ['500+', 'Tons/Year'], ['8', 'Fish Varieties'], ['100%', 'Natural']] as $s): ?>
            <div class="col-6 col-sm-3">
              <div class="text-center" style="color:#fff">
                <div style="font-family:'Playfair Display',serif;font-size:1.8rem;font-weight:900;color:var(--secondary)"><?= $s[0] ?></div>
                <div style="font-size:0.75rem;opacity:0.7;text-transform:uppercase;letter-spacing:.06em"><?= $s[1] ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="col-lg-6 text-center">
        <div class="hero-emoji">🐠</div>
      </div>
    </div>
  </div>
</section>

<!-- ── FEATURES ── -->
<section class="py-5 bg-white">
  <div class="container">
    <div class="row g-4">
      <?php foreach (
        [
          ['🚚', 'Fast Delivery', 'Chilled logistics network — freshness guaranteed from farm to your door.'],
          ['🌿', '100% Natural', 'No harmful chemicals. All products use natural feeds and sustainable practices.'],
          ['🔒', 'Secure Payment', 'ICICI Orange Pay integration for safe and hassle-free transactions.'],
          ['📦', 'Bulk Orders', 'Special pricing for restaurants, hotels, and wholesale buyers.'],
        ] as $f
      ): ?>
        <div class="col-6 col-md-3">
          <div class="feature-card">
            <div class="feature-icon"><?= $f[0] ?></div>
            <h6 class="fw-bold"><?= $f[1] ?></h6>
            <p class="small text-muted mb-0"><?= $f[2] ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── FEATURED PRODUCTS ── -->
<section class="py-5" style="background:var(--light-bg)">
  <div class="container">
    <div class="text-center mb-4">
      <p class="section-label">Fresh Picks</p>
      <h2 class="section-title">Featured Products</h2>
    </div>
    <div class="row g-4">
      <?php foreach ($featured as $p): ?>
        <div class="col-6 col-md-4 col-lg-3">
          <div class="product-card">
          <?php if (!empty($p['image'])): ?>

<?php
$img = $p['image'];

if (is_resource($img)) {
    $img = stream_get_contents($img);
} elseif (is_string($img) && substr($img,0,2) === '\\x') {
    $img = hex2bin(substr($img,2));
}
?>

<img src="data:image/jpeg;base64,<?= base64_encode($img) ?>"
     class="card-img-top"
     alt="<?= htmlspecialchars($p['product_name']) ?>" />

<?php else: ?>

<div class="product-img-placeholder">🐟</div>

<?php endif; ?>
            <div class="card-body">
              <span class="badge-category mb-2 d-inline-block"><?= htmlspecialchars($p['category_name'] ?? '') ?></span>
              <h6 class="card-title"><?= htmlspecialchars($p['product_name']) ?></h6>
              <p class="small text-muted mb-2" style="overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical">
                <?= htmlspecialchars($p['description']) ?></p>
              <div class="product-price mb-2">Rs <?= number_format($p['price'], 0, ',', '.') ?></div>
              <div class="d-flex gap-2">
                <button class="btn-cart" onclick="addToCart(<?= $p['id'] ?>)"><i class="bi bi-cart-plus me-1"></i>Cart</button>
                <a href="product.php?id=<?= $p['id'] ?>" class="btn-buy text-center text-decoration-none">Buy Now</a>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-4">
      <a href="catalog.php" class="btn btn-primary-custom px-4">View All Products <i class="bi bi-arrow-right ms-1"></i></a>
    </div>
  </div>
</section>

<!-- ── ABOUT ── -->
<section class="about-section py-5" id="about">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-5">
        <div class="about-visual">🌿</div>
      </div>
      <div class="col-lg-7">
        <p class="section-label">Who We Are</p>
        <h2 class="section-title mb-3">Grown With Nature,<br>Delivered With Pride</h2>
        <p class="text-muted">Sunbis AgroFish is a leading integrated aquaculture and agro-farming company committed to sustainable food production. We combine traditional farming wisdom with modern aquaculture techniques to raise healthy, traceable produce.</p>
        <ul class="list-unstyled mt-3">
          <?php foreach (['Certified sustainable aquaculture practices', 'Natural feed — no harmful additives', 'Direct farm-to-market supply chain', 'Supporting 200+ local farmers'] as $item): ?>
            <li class="mb-2"><i class="bi bi-check-circle-fill me-2" style="color:var(--primary)"></i><?= $item ?></li>
          <?php endforeach; ?>
        </ul>
        <a href="contact.php" class="btn btn-primary-custom mt-3">Get In Touch</a>
      </div>
    </div>
  </div>
</section>

<!-- ── NEWSLETTER ── -->
<section class="newsletter-section py-5">
  <div class="container">

    <div class="newsletter-box text-center">

      <h2 class="mb-2">Stay Updated</h2>
      <p class="text-muted mb-4">
        Subscribe to get fresh seafood deals, farm updates, and exclusive offers.
      </p>

      <form id="newsletterForm" class="newsletter-form">

        <div class="input-group justify-content-center">

          <input type="email"
            class="form-control newsletter-input"
            id="email"
            placeholder="Enter your email address"
            required>

          <button class="btn btn-primary-custom" type="submit">
            Subscribe
          </button>

        </div>

      </form>

      <div id="newsletterMsg" class="mt-3"></div>

    </div>

  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
