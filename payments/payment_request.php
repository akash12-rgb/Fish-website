<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/payment.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . APP_URL . '/checkout.php');
    exit;
}

if (!verifyCsrf($_POST['csrf'] ?? '')) {
    die('Invalid CSRF token');
}

$db   = getDB();
$user = currentUser();

/* -----------------------------
   CHECK IF BUY NOW MODE
------------------------------*/

$buyNowId  = (int)($_POST['buy_now_id'] ?? 0);
$buyNowQty = max(1, (int)($_POST['buy_now_qty'] ?? 1));

$orderItems = [];

if ($buyNowId > 0) {

    /* BUY NOW FLOW */

    $st = $db->prepare("
        SELECT id, product_name, price, stock_quantity
        FROM products
        WHERE id = ? AND stock_quantity >= ?
    ");

    $st->execute([$buyNowId, $buyNowQty]);
    $product = $st->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        header('Location: ' . APP_URL . '/catalog.php');
        exit;
    }

    $orderItems[] = [
        'product_id'   => $product['id'],
        'product_name' => $product['product_name'],
        'price'        => (float)$product['price'],
        'quantity'     => $buyNowQty
    ];

} else {

    /* CART FLOW */

    $st = $db->prepare("
        SELECT c.quantity,
               p.id AS product_id,
               p.product_name,
               p.price,
               p.stock_quantity
        FROM cart c
        JOIN products p ON p.id = c.product_id
        WHERE c.user_id = ?
    ");

    $st->execute([$user['id']]);
    $orderItems = $st->fetchAll(PDO::FETCH_ASSOC);

    if (!$orderItems) {
        header('Location: ' . APP_URL . '/cart.php');
        exit;
    }
}

/* -----------------------------
   CALCULATE TOTAL
------------------------------*/

$subtotal = 0;

foreach ($orderItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$shipping = ($subtotal >= 2000) ? 0 : 50;
$total    = $subtotal + $shipping;

/* -----------------------------
   CREATE ORDER
------------------------------*/

$transactionId = 'ORD-' . time() . '-' . rand(1000,9999);

$db->beginTransaction();

try {

    $st = $db->prepare("
        INSERT INTO orders
        (user_id,total_amount,payment_status,order_status,transaction_id,
         shipping_name,shipping_phone,shipping_address,shipping_city,
         shipping_zip,notes)
        VALUES (?,?,?,?,?,?,?,?,?,?,?)
        RETURNING id
    ");

    $st->execute([
        $user['id'],
        $total,
        'pending',
        'pending',
        $transactionId,
        $_POST['shipping_name'],
        $_POST['shipping_phone'],
        $_POST['shipping_address'],
        $_POST['shipping_city'],
        $_POST['shipping_zip'],
        $_POST['notes']
    ]);

    $orderId = $st->fetchColumn();

    /* ORDER ITEMS */

    $itemInsert = $db->prepare("
        INSERT INTO order_items
        (order_id,product_id,quantity,price)
        VALUES (?,?,?,?)
    ");

    foreach ($orderItems as $item) {

        $itemInsert->execute([
            $orderId,
            $item['product_id'],
            $item['quantity'],
            $item['price']
        ]);

        $db->prepare("
            UPDATE products
            SET stock_quantity = stock_quantity - ?
            WHERE id = ?
        ")->execute([$item['quantity'], $item['product_id']]);
    }

    /* CLEAR CART ONLY IF CART CHECKOUT */

    if (!$buyNowId) {
        $db->prepare("DELETE FROM cart WHERE user_id=?")
           ->execute([$user['id']]);
    }

    $db->commit();

} catch (Exception $e) {

    $db->rollBack();
    die("Order failed");
}

/* -----------------------------
   PAYMENT REDIRECT
------------------------------*/

$checksumString = ORANGEPAY_MERCHANT_ID . "|" .
                  $orderId . "|" .
                  number_format($total,2,'.','') . "|" .
                  ORANGEPAY_MERCHANT_KEY;

$checksum = hash('sha256',$checksumString);

$params = [
    "merchant_id" => ORANGEPAY_MERCHANT_ID,
    "order_id" => $orderId,
    "transaction_id" => $transactionId,
    "amount" => number_format($total,2,'.',''),
    "currency" => "INR",
    "customer_name" => $_POST['shipping_name'],
    "customer_email" => $_POST['customer_email'],
    "customer_phone" => $_POST['shipping_phone'],
    "return_url" => ORANGEPAY_RETURN_URL,
    "cancel_url" => ORANGEPAY_CANCEL_URL,
    "checksum" => $checksum
];

?>

<html>
<body>

<form id="payform" method="POST" action="<?= ORANGEPAY_BASE_URL ?>">

<?php foreach($params as $k=>$v): ?>

<input type="hidden" name="<?= $k ?>" value="<?= htmlspecialchars($v) ?>">

<?php endforeach; ?>

</form>

<script>
document.getElementById('payform').submit();
</script>

</body>
</html>
