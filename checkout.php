<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

requireLogin();

// Buy Now mode: single product
$buyNowId = (int)($_GET['buy_now'] ?? 0);
$buyQty   = max(1, (int)($_GET['qty'] ?? 1));

if ($buyNowId) {

  $st = $db->prepare('SELECT * FROM products WHERE id=? AND stock_quantity >= ?');
  $st->execute([$buyNowId, $buyQty]);
  $product = $st->fetch();

  if (!$product) {
    header('Location: catalog.php');
    exit;
  }

  $items = [[
    'cart_id' => 0,
    'product_id' => $product['id'],
    'product_name' => $product['product_name'],
    'price' => $product['price'],
    'quantity' => $buyQty,
    'image' => $product['image'],
    'stock_quantity' => $product['stock_quantity'],
  ]];

} else {

  $st = $db->prepare('
        SELECT c.id as cart_id, c.quantity, p.id as product_id, p.product_name,
               p.price, p.image, p.stock_quantity
        FROM cart c
        JOIN products p ON p.id = c.product_id
        WHERE c.user_id = ?
    ');
  $st->execute([$user['id']]);
  $items = $st->fetchAll();

  if (!$items) {
    header('Location: cart.php');
    exit;
  }
}


/* SAFE TOTAL CALCULATION */

$subtotal = 0;

foreach ($items as $item) {

  $price = (float)$item['price'];
  $qty   = (int)$item['quantity'];

  if ($price < 0) $price = 0;
  if ($qty < 1) $qty = 1;

  $subtotal += ($price * $qty);
}

if ($subtotal >= 2000) {
  $shipping = 0;
} else {
  $shipping = 50;
}

$total = $subtotal + $shipping;


$pageTitle = 'Checkout – Sunbis AgroFish';

require_once __DIR__ . '/includes/header.php';
?>

<meta name="app-url" content="<?= APP_URL ?>" />

<div class="container my-5" style="max-width:960px">

<h2 class="section-title mb-4">Checkout</h2>

<form action="payments/payment_request.php" method="POST" id="checkout-form">

<input type="hidden" name="buy_now_id" value="<?= $buyNowId ?>" />
<input type="hidden" name="buy_now_qty" value="<?= $buyQty ?>" />
<input type="hidden" name="csrf" value="<?= csrfToken() ?>" />

<div class="row g-4">

<!-- SHIPPING -->
<div class="col-lg-7">

<div class="checkout-step">

<div class="d-flex align-items-center gap-2 mb-3">
<div class="step-number">1</div>
<h5 class="mb-0 fw-bold">Shipping Information</h5>
</div>

<div class="row g-3">

<div class="col-12">
<label class="form-label fw-semibold">Full Name</label>
<input type="text" name="shipping_name" class="form-control" required
value="<?= htmlspecialchars($user['name']) ?>" />
</div>

<div class="col-md-6">
<label class="form-label fw-semibold">Phone</label>
<input type="tel" name="shipping_phone" class="form-control" required
value="<?= htmlspecialchars($user['phone'] ?? '') ?>" />
</div>

<div class="col-md-6">
<label class="form-label fw-semibold">Email</label>
<input type="email" name="customer_email" class="form-control" required
value="<?= htmlspecialchars($user['email']) ?>" />
</div>

<div class="col-12">
<label class="form-label fw-semibold">Address</label>
<textarea name="shipping_address" class="form-control" rows="2" required></textarea>
</div>

<div class="col-md-8">
<label class="form-label fw-semibold">City</label>
<input type="text" name="shipping_city" class="form-control" required />
</div>

<div class="col-md-4">
<label class="form-label fw-semibold">ZIP Code</label>
<input type="text" name="shipping_zip" class="form-control" />
</div>

<div class="col-12">
<label class="form-label fw-semibold">Notes (optional)</label>
<textarea name="notes" class="form-control" rows="2"
placeholder="Delivery instructions, etc."></textarea>
</div>

</div>
</div>


<div class="checkout-step">

<div class="d-flex align-items-center gap-2 mb-3">
<div class="step-number">2</div>
<h5 class="mb-0 fw-bold">Payment Method</h5>
</div>

<div class="p-3 rounded"
style="border:2px solid var(--primary);background:var(--light-bg)">

<div class="d-flex align-items-center gap-3">

<span style="font-size:2rem">🏦</span>

<div>
<div class="fw-bold">ICICI Bank Orange Pay</div>
<small class="text-muted">
Secure payment via ICICI Orange Pay Gateway
</small>
</div>

<i class="bi bi-check-circle-fill ms-auto"
style="color:var(--primary);font-size:1.4rem"></i>

</div>

</div>

</div>

</div>


<!-- ORDER SUMMARY -->
<div class="col-lg-5">

<div class="cart-summary-box" style="position:sticky;top:90px">

<h5 class="fw-bold mb-3">Order Summary</h5>

<?php foreach ($items as $item): ?>

<div class="d-flex justify-content-between align-items-center py-2 border-bottom">

<div>
<div class="small fw-semibold">
<?= htmlspecialchars($item['product_name']) ?>
</div>

<small class="text-muted">× <?= $item['quantity'] ?></small>
</div>

<div class="small fw-bold">
Rs <?= number_format($item['price'] * $item['quantity'], 2) ?>
</div>

</div>

<?php endforeach; ?>


<div class="order-line mt-2">
<span>Subtotal</span>
<span>Rs <?= number_format($subtotal, 2) ?></span>
</div>

<div class="order-line">

<span>Shipping</span>

<span>
<?= $shipping == 0
? '<span class="text-success fw-bold">FREE</span>'
: 'Rs ' . number_format($shipping, 2) ?>
</span>

</div>

<div class="order-total">

<span>Total</span>

<span style="color:var(--primary)">
Rs <?= number_format($total, 2) ?>
</span>

</div>

<button type="submit"
class="btn btn-primary-custom w-100 mt-3 py-3"
style="font-size:1.05rem">

🔒 Proceed to Payment

</button>

<p class="text-center text-muted small mt-2">
You will be redirected to ICICI Orange Pay
</p>

</div>

</div>

</div>

</form>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
