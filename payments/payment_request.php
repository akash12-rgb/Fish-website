<?php
// payments/payment_request.php
// Creates DB order then redirects to ICICI Orange Pay gateway

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/payment.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . APP_URL . '/checkout.php');
    exit;
}

if (!verifyCsrf($_POST['csrf'] ?? '')) {
    die('Invalid CSRF token.');
}

$db   = getDB();
$user = currentUser();

/* ── Collect cart / buy-now items ───────────────────────── */

$buyNowId  = (int)($_POST['buy_now_id'] ?? 0);
$buyNowQty = max(1, (int)($_POST['buy_now_qty'] ?? 1));

if ($buyNowId > 0) {

    // Buy Now checkout
    $st = $db->prepare('SELECT id, product_name, price, stock_quantity 
                        FROM products 
                        WHERE id=? AND stock_quantity >= ?');
    $st->execute([$buyNowId, $buyNowQty]);
    $product = $st->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        header('Location: ' . APP_URL . '/catalog.php?error=product_unavailable');
        exit;
    }

    $orderItems = [[
        'product_id'   => (int)$product['id'],
        'product_name' => $product['product_name'],
        'price'        => (float)$product['price'],
        'quantity'     => (int)$buyNowQty,
    ]];

} else {

    // Cart checkout
    $st = $db->prepare('
        SELECT c.quantity,
               p.id AS product_id,
               p.product_name,
               p.price,
               p.stock_quantity
        FROM cart c
        JOIN products p ON p.id = c.product_id
        WHERE c.user_id = ?
    ');

    $st->execute([$user['id']]);
    $orderItems = $st->fetchAll(PDO::FETCH_ASSOC);

    if (!$orderItems) {
        header('Location: ' . APP_URL . '/cart.php');
        exit;
    }
}

/* ── Calculate totals ───────────────────────────────────── */

$subtotal = 0;

foreach ($orderItems as $i) {
    $subtotal += ((float)$i['price'] * (int)$i['quantity']);
}

// Free shipping above 2000
$shipping = ($subtotal >= 2000) ? 0 : 50;

$total = $subtotal + $shipping;

/* ── Unique transaction ID ───────────────────────────────── */

$transactionId = 'SAF-' . strtoupper(bin2hex(random_bytes(6))) . '-' . time();

/* ── Sanitize shipping fields ───────────────────────────── */

$shippingName    = trim($_POST['shipping_name'] ?? $user['name']);
$shippingPhone   = trim($_POST['shipping_phone'] ?? '');
$shippingAddress = trim($_POST['shipping_address'] ?? '');
$shippingCity    = trim($_POST['shipping_city'] ?? '');
$shippingZip     = trim($_POST['shipping_zip'] ?? '');
$notes           = trim($_POST['notes'] ?? '');
$customerEmail   = trim($_POST['customer_email'] ?? $user['email']);

try {

    $db->beginTransaction();

    /* 1. Create order */

    $st = $db->prepare('
        INSERT INTO orders
          (user_id, total_amount, payment_status, order_status, transaction_id,
           shipping_name, shipping_phone, shipping_address, shipping_city,
           shipping_zip, notes)
        VALUES (?,?,?,?,?,?,?,?,?,?,?)
        RETURNING id
    ');

    $st->execute([
        $user['id'],
        $total,
        'pending',
        'pending',
        $transactionId,
        $shippingName,
        $shippingPhone,
        $shippingAddress,
        $shippingCity,
        $shippingZip,
        $notes
    ]);

    $orderId = $st->fetchColumn();

    /* 2. Insert order items */

    $itemSt = $db->prepare('
        INSERT INTO order_items (order_id, product_id, quantity, price)
        VALUES (?,?,?,?)
    ');

    $stockSt = $db->prepare('
        UPDATE products
        SET stock_quantity = stock_quantity - ?
        WHERE id = ?
    ');

    foreach ($orderItems as $item) {

        $itemSt->execute([
            $orderId,
            $item['product_id'],
            $item['quantity'],
            $item['price']
        ]);

        $stockSt->execute([
            $item['quantity'],
            $item['product_id']
        ]);
    }

    /* 3. Create payment record */

    $st = $db->prepare('
        INSERT INTO payments
        (order_id, payment_gateway, transaction_id, amount, payment_status)
        VALUES (?,?,?,?,?)
    ');

    $st->execute([
        $orderId,
        'ICICI_OrangePay',
        $transactionId,
        $total,
        'pending'
    ]);

    /* 4. Clear cart if cart checkout */

    if (!$buyNowId) {
        $db->prepare('DELETE FROM cart WHERE user_id=?')->execute([$user['id']]);
    }

    $db->commit();

} catch (Exception $e) {

    $db->rollBack();
    error_log('Order creation failed: ' . $e->getMessage());

    header('Location: ' . APP_URL . '/checkout.php?error=order_failed');
    exit;
}

/* ── Generate ICICI Orange Pay checksum ─────────────────── */

$checksumString = implode('|', [
    ORANGEPAY_MERCHANT_ID,
    $orderId,
    number_format($total, 2, '.', ''),
    ORANGEPAY_CURRENCY,
    ORANGEPAY_MERCHANT_KEY
]);

$checksum = hash('sha256', $checksumString);

/* ── Payment parameters ─────────────────────────────────── */

$params = [
    'merchant_id'    => ORANGEPAY_MERCHANT_ID,
    'order_id'       => $orderId,
    'transaction_id' => $transactionId,
    'amount'         => number_format($total, 2, '.', ''),
    'currency'       => ORANGEPAY_CURRENCY,
    'customer_name'  => $shippingName,
    'customer_email' => $customerEmail,
    'customer_phone' => $shippingPhone,
    'return_url'     => ORANGEPAY_RETURN_URL,
    'cancel_url'     => ORANGEPAY_CANCEL_URL,
    'checksum'       => $checksum,
    'description'    => 'Sunbis AgroFish Order #' . $orderId
];

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Redirecting to Payment...</title>

<style>
body{
font-family:sans-serif;
display:flex;
flex-direction:column;
align-items:center;
justify-content:center;
min-height:100vh;
background:#f0f9f7;
}

.spinner{
width:60px;
height:60px;
border:5px solid #e2e8f0;
border-top-color:#1a7a6e;
border-radius:50%;
animation:spin 1s linear infinite;
margin-bottom:20px;
}

@keyframes spin{
to{transform:rotate(360deg);}
}
</style>

</head>

<body>

<div class="spinner"></div>
<h3>Redirecting to ICICI Orange Pay...</h3>
<p>Please do not close or refresh this page.</p>

<form id="payment-form" method="POST" action="<?= htmlspecialchars(ORANGEPAY_BASE_URL) ?>">

<?php foreach ($params as $key => $val): ?>

<input type="hidden" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($val) ?>">

<?php endforeach; ?>

</form>

<script>
document.getElementById('payment-form').submit();
</script>

</body>
</html>
