<?php
require_once __DIR__ . '/includes/header.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
  header('Location: catalog.php');
  exit;
}

$st = $db->prepare("SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON c.id=p.category_id WHERE p.id=?");
$st->execute([$id]);
$p = $st->fetch();

if (!$p) {
  header('Location: catalog.php');
  exit;
}

$pageTitle = htmlspecialchars($p['product_name']) . ' – Sunbis AgroFish';

// Related products
$related = $db->prepare("SELECT * FROM products WHERE category_id=? AND id!=? AND stock_quantity>0 LIMIT 4");
$related->execute([$p['category_id'], $id]);
$related = $related->fetchAll();
?>

<meta name="app-url" content="<?= APP_URL ?>" />

<div class="container my-5">

<nav aria-label="breadcrumb" class="mb-4">
<ol class="breadcrumb">
<li class="breadcrumb-item"><a href="index.php">Home</a></li>
<li class="breadcrumb-item"><a href="catalog.php">Shop</a></li>
<li class="breadcrumb-item active"><?= htmlspecialchars($p['product_name']) ?></li>
</ol>
</nav>

<div class="row g-5">

<!-- PRODUCT IMAGE -->
<div class="col-lg-5">

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
     class="product-detail-img w-100"
     alt="<?= htmlspecialchars($p['product_name']) ?>" />

<?php else: ?>

<div class="product-detail-placeholder">🐟</div>

<?php endif; ?>

</div>


<!-- PRODUCT DETAILS -->
<div class="col-lg-7">

<span class="badge-category mb-2 d-inline-block">
<?= htmlspecialchars($p['category_name'] ?? '') ?>
</span>

<h1 style="font-family:'Playfair Display',serif;font-weight:900;font-size:clamp(1.8rem,4vw,2.5rem)">
<?= htmlspecialchars($p['product_name']) ?>
</h1>

<div class="product-price mb-3" style="font-size:2rem">
Rs <?= number_format($p['price'],0,',','.') ?>
</div>

<p class="text-muted lh-lg">
<?= nl2br(htmlspecialchars($p['description'])) ?>
</p>

<div class="d-flex align-items-center gap-2 mb-3">
<i class="bi bi-box-seam text-primary"></i>

<span class="<?= $p['stock_quantity'] > 0 ? 'text-success' : 'text-danger' ?> fw-semibold">

<?= $p['stock_quantity'] > 0 ? "In Stock ({$p['stock_quantity']} available)" : 'Out of Stock' ?>

</span>
</div>

<?php if ($p['stock_quantity'] > 0): ?>

<div class="mb-4">

<label class="fw-semibold mb-2 d-block">Quantity</label>

<div class="qty-control">
<button class="qty-minus" type="button">−</button>

<input type="number"
       id="qty-input"
       value="1"
       min="1"
       max="<?= $p['stock_quantity'] ?>" />

<button class="qty-plus" type="button">+</button>
</div>

</div>

<div class="d-flex gap-3 flex-wrap">

<button class="btn btn-lg btn-primary-custom px-4"
onclick="addToCart(<?= $p['id'] ?>,+document.getElementById('qty-input').value)">

<i class="bi bi-cart-plus me-2"></i>Add to Cart

</button>

<a href="checkout.php?buy_now=<?= $p['id'] ?>&qty=1"
class="btn btn-lg text-decoration-none"
style="background:var(--accent);color:var(--deep);font-weight:600;border-radius:50px;padding:.8rem 2rem">

⚡ Buy Now

</a>

</div>

<?php endif; ?>


<div class="row g-2 mt-4">

<?php foreach ([

['🚚','Free delivery on orders above Rs 2000'],
['🔒','Secure ICICI Orange Pay'],
['✅','100% Fresh Guaranteed']

] as $b): ?>

<div class="col-12 col-sm-4">

<div class="d-flex align-items-center gap-2 p-2 rounded" style="background:var(--light-bg)">

<span style="font-size:1.3rem"><?= $b[0] ?></span>

<small class="text-muted"><?= $b[1] ?></small>

</div>

</div>

<?php endforeach; ?>

</div>

</div>

</div>


<!-- RELATED PRODUCTS -->

<?php if ($related): ?>

<div class="mt-5">

<h4 class="section-title mb-4">Related Products</h4>

<div class="row g-4">

<?php foreach ($related as $r): ?>

<div class="col-6 col-md-3">

<div class="product-card">

<a href="product.php?id=<?= $r['id'] ?>">

<?php if (!empty($r['image'])): ?>

<?php
$img = $r['image'];

if (is_resource($img)) {
    $img = stream_get_contents($img);
} elseif (is_string($img) && substr($img,0,2) === '\\x') {
    $img = hex2bin(substr($img,2));
}
?>

<img src="data:image/jpeg;base64,<?= base64_encode($img) ?>"
style="height:180px;width:100%;object-fit:cover;border-radius:10px;">

<?php else: ?>

<div class="product-img-placeholder">🐟</div>

<?php endif; ?>

</a>

<div class="card-body">

<h6 class="card-title small">

<a href="product.php?id=<?= $r['id'] ?>"
class="text-decoration-none text-dark">

<?= htmlspecialchars($r['product_name']) ?>

</a>

</h6>

<div class="product-price mb-2">

Rs <?= number_format($r['price'],0,',','.') ?>

</div>

<button class="btn-cart w-100"
onclick="addToCart(<?= $r['id'] ?>)">

Add to Cart

</button>

</div>

</div>

</div>

<?php endforeach; ?>

</div>

</div>

<?php endif; ?>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
