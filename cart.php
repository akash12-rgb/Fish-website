<?php
$pageTitle = 'Your Cart – Sunbis AgroFish';
require_once __DIR__ . '/includes/header.php';

$user = currentUser();
$items = [];
$subtotal = 0;
$shipping = 0;
$total = 0;

if ($user) {

  $st = $db->prepare('
    SELECT c.id as cart_id, c.quantity, p.id as product_id, p.product_name,
           p.price, p.image, p.stock_quantity
    FROM cart c JOIN products p ON p.id = c.product_id
    WHERE c.user_id = ?
    ORDER BY c.created_at DESC
');

  $st->execute([$user['id']]);
  $items = $st->fetchAll();

  $subtotal = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $items));
  $shipping = $subtotal >= 200000 ? 0 : 15000;
  $total = $subtotal + $shipping;
}


$st = $db->prepare('
    SELECT c.id as cart_id, c.quantity, p.id as product_id, p.product_name,
           p.price, p.image, p.stock_quantity
    FROM cart c JOIN products p ON p.id = c.product_id
    WHERE c.user_id = ?
    ORDER BY c.created_at DESC
');
$st->execute([$user['id']]);
$items    = $st->fetchAll();
$subtotal = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $items));
$shipping = $subtotal >= 200000 ? 0 : 15000;
$total    = $subtotal + $shipping;
?>
<meta name="app-url" content="<?= APP_URL ?>" />

<div class="container my-5">
  <h2 class="section-title mb-4">🛒 Your Cart</h2>

  <?php if (!$user): ?>

    <div class="text-center py-5">

      <div style="font-size:5rem">🔐</div>

      <h4 class="mt-3">Please Login to View Your Cart</h4>

      <p class="text-muted">
        Login to add products and manage your cart.
      </p>

      <a href="login.php" class="btn btn-primary-custom mt-3">
        Login
      </a>

      <a href="catalog.php" class="btn btn-outline-secondary mt-3 ms-2">
        Browse Products
      </a>

    </div>

  <?php elseif (!$items): ?>

    <div class="text-center py-5">

      <div style="font-size:5rem">🛒</div>

      <h4 class="mt-3">Your cart is empty</h4>

      <p class="text-muted">
        Looks like you haven't added any products yet.
      </p>

      <a href="catalog.php" class="btn btn-primary-custom mt-3">
        Browse Products
      </a>

    </div>

  <?php else: ?>
    <div class="row g-4">
      <!-- CART ITEMS -->
      <div class="col-lg-8">
        <?php foreach ($items as $item): ?>
          <div class="cart-item" id="cart-item-<?= $item['cart_id'] ?>">
            <div class="cart-item-img">
             <?php if (!empty($item['image'])): ?>

<?php
$img = $item['image'];

if (is_resource($img)) {
    $img = stream_get_contents($img);
} elseif (is_string($img) && substr($img, 0, 2) === '\\x') {
    $img = hex2bin(substr($img, 2));
}
?>

<img src="data:image/jpeg;base64,<?= base64_encode($img) ?>"
     style="width:100%;height:100%;object-fit:cover;border-radius:10px"
     alt="<?= htmlspecialchars($item['product_name']) ?>" />

<?php else: ?>

🐟

<?php endif; ?>
            </div>
            <div class="flex-grow-1">
              <div class="fw-bold"><?= htmlspecialchars($item['product_name']) ?></div>
              <div class="product-price mt-1">Rs <?= number_format($item['price'], 0, ',', '.') ?></div>
              <div class="d-flex align-items-center gap-3 mt-2 flex-wrap">
                <div class="qty-control" style="scale:0.9;transform-origin:left">
                  <button class="qty-minus" onclick="changeQty(this, <?= $item['cart_id'] ?>, -1)">−</button>
                  <input type="number" value="<?= $item['quantity'] ?>" min="1" max="<?= $item['stock_quantity'] ?>" id="qty-<?= $item['cart_id'] ?>" />
                  <button class="qty-plus" onclick="changeQty(this, <?= $item['cart_id'] ?>, 1)">+</button>
                </div>
                <span class="text-muted small">× Rs <?= number_format($item['price'], 0, ',', '.') ?> =
                  <strong>Rs <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?></strong></span>
              </div>
            </div>
            <button class="btn btn-sm btn-outline-danger" onclick="removeFromCart(<?= $item['cart_id'] ?>)">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- SUMMARY -->
      <div class="col-lg-4">
        <div class="cart-summary-box">
          <h5 class="fw-bold mb-3">Order Summary</h5>
          <div class="order-line"><span>Subtotal</span><span>Rs <?= number_format($subtotal, 0, ',', '.') ?></span></div>
          <div class="order-line">
            <span>Shipping</span>
            <span><?= $shipping == 0 ? '<span class="text-success fw-bold">FREE</span>' : 'Rs ' . number_format($shipping, 0, ',', '.') ?></span>
          </div>
          <?php if ($shipping > 0): ?>
            <small class="text-muted">Free shipping for orders above Rs 2,000</small>
          <?php endif; ?>
          <div class="order-total mt-2">
            <span>Total</span><span>Rs <?= number_format($total, 0, ',', '.') ?></span>
          </div>
          <a href="checkout.php" class="btn btn-primary-custom w-100 mt-3 text-center d-block">
            Proceed to Checkout <i class="bi bi-arrow-right ms-1"></i>
          </a>
          <a href="catalog.php" class="btn btn-outline-secondary w-100 mt-2">Continue Shopping</a>
        </div>
      </div>
    </div>
  <?php endif; ?>
</div>

<script>
  function changeQty(btn, cartId, delta) {
    const inp = document.getElementById('qty-' + cartId);
    let val = +inp.value + delta;
    if (val < 1) return;
    inp.value = val;
    updateCartQty(cartId, val);
  }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
