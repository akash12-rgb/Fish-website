<?php
// payments/payment_request.php
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

if ($buyNowId) {

    $st = $db->prepare('SELECT * FROM products WHERE id=? AND stock_quantity >= ?');
    $st->execute([$buyNowId, $buyNowQty]);
    $product = $st->fetch();

    if (!$product) {
        die('Product unavailable.');
    }

    $orderItems = [[
        'product_id'   => $product['id'],
        'product_name' => $product['product_name'],
        'price'        => $product['price'],
        'quantity'     => $buyNowQty
    ]];

} else {

    $st = $db->prepare('
        SELECT c.quantity, p.id as product_id, p.product_name, p.price, p.stock_quantity
        FROM cart c
        JOIN products p ON p.id = c.product_id
        WHERE c.user_id = ?
    ');

    $st->execute([$user['id']]);
    $orderItems = $st->fetchAll();

    if (!$orderItems) {
        header('Location: ' . APP_URL . '/cart.php');
        exit;
    }
}

/* ── Calculate totals ───────────────────────────────────── */

$subtotal = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $orderItems));

$shipping = ($subtotal >= 2000) ? 0 : 50;

$total = $subtotal + $shipping;

/* ── Transaction ID ─────────────────────────────────────── */

$transactionId = 'SAF-' . strtoupper(bin2hex(random_bytes(6))) . '-' . time();

/* ── Shipping data ─────────────────────────────────────── */

$shippingName    = trim($_POST['shipping_name']    ?? $user['name']);
$shippingPhone   = trim($_POST['shipping_phone']   ?? '');
$shippingAddress = trim($_POST['shipping_address'] ?? '');
$shippingCity    = trim($_POST['shipping_city']    ?? '');
$shippingZip     = trim($_POST['shipping_zip']     ?? '');
$notes           = trim($_POST['notes']            ?? '');
$customerEmail   = trim($_POST['customer_email']   ?? $user['email']);

try {

    $db->beginTransaction();

    /* Create order */

    $st = $db->prepare('
        INSERT INTO orders
        (user_id,total_amount,order_status,transaction_id,
         shipping_name,shipping_phone,shipping_address,shipping_city,
         shipping_zip,notes,payment_status)
        VALUES (?,?,?,?,?,?,?,?,?,?,?)
        RETURNING id
    ');

    $st->execute([
        $user['id'],
        $total,
        'pending',
        $transactionId,
        $shippingName,
        $shippingPhone,
        $shippingAddress,
        $shippingCity,
        $shippingZip,
        $notes,
        'pending'              
    ]);

    $orderId = $st->fetchColumn();

    /* Order items */

    $itemSt = $db->prepare('
        INSERT INTO order_items (order_id,product_id,quantity,price)
        VALUES (?,?,?,?)
    ');

    $stockSt = $db->prepare('
        UPDATE products
        SET stock_quantity = stock_quantity - ?
        WHERE id=?
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

    /* Payment record */

   $st = $db->prepare('
INSERT INTO payments
(
    order_id,
    amount,
    payment_gateway,
    transaction_id,
    payment_status,
    gateway_response
)
VALUES (?,?,?,?,?,?)
');

$st->execute([
    $orderId,
    $total,
    'ICICI_OrangePay',
    $transactionId,
    'pending',
    NULL
]);

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

/* ── Checksum ───────────────────────────────────────────── */

$checksumString = implode('|', [
    ORANGEPAY_MERCHANT_ID,
    $orderId,
    number_format($total,2,'.',''),
    ORANGEPAY_CURRENCY,
    ORANGEPAY_MERCHANT_KEY
]);

$checksum = hash('sha256',$checksumString);

/* ── Payment payload ────────────────────────────────────── */

$params = [
    'merchant_id'    => ORANGEPAY_MERCHANT_ID,
    'order_id'       => $orderId,
    'transaction_id' => $transactionId,
    'amount'         => number_format($total,2,'.',''),
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
<body>

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
