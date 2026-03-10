<?php
// payments/payment_success.php
// ICICI Orange Pay calls this URL after successful payment
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/payment.php';

$db = getDB();

// ── 1. Read gateway response ──────────────────────────────────
// Orange Pay typically POSTs back; also handle GET for redirect-based flows
$gatewayData = array_merge($_GET, $_POST);

$orderId       = (int)($gatewayData['order_id']       ?? 0);
$transactionId = $gatewayData['transaction_id']        ?? '';
$gwTxnId       = $gatewayData['gateway_transaction_id'] ?? $transactionId;
$amount        = (float)($gatewayData['amount']        ?? 0);
$receivedChk   = $gatewayData['checksum']              ?? '';
$status        = strtolower($gatewayData['status']     ?? '');

// ── 2. Verify checksum ────────────────────────────────────────
$expectedString = implode('|', [
  ORANGEPAY_MERCHANT_ID,
  $orderId,
  number_format($amount, 2, '.', ''),
  ORANGEPAY_CURRENCY,
  ORANGEPAY_MERCHANT_KEY,
]);
$expectedChk = hash('sha256', $expectedString);

$checksumValid = hash_equals($expectedChk, $receivedChk);

// ── 3. Fetch the order ────────────────────────────────────────
$st = $db->prepare('SELECT * FROM orders WHERE id=? AND transaction_id=?');
$st->execute([$orderId, $transactionId]);
$order = $st->fetch();

if (!$order || !$checksumValid || $status !== 'success') {
  // Mark as failed and redirect
  if ($order) {
    $db->prepare("UPDATE orders   SET payment_status='failed', order_status='cancelled' WHERE id=?")
      ->execute([$orderId]);
    $db->prepare("UPDATE payments SET payment_status='failed', gateway_response=? WHERE order_id=?")
      ->execute([json_encode($gatewayData), $orderId]);
  }
  header('Location: ' . APP_URL . '/payments/payment_failure.php?order_id=' . $orderId);
  exit;
}

// ── 4. Update tables ─────────────────────────────────────────
$db->prepare("UPDATE orders   SET payment_status='success', order_status='processing' WHERE id=?")
  ->execute([$orderId]);
$db->prepare("UPDATE payments SET payment_status='success', transaction_id=?, gateway_response=? WHERE order_id=?")
  ->execute([$gwTxnId, json_encode($gatewayData), $orderId]);

// ── 5. Show success page ──────────────────────────────────────
$pageTitle = 'Payment Successful – Sunbis AgroFish';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container my-5" style="max-width:680px;text-align:center">
  <div style="font-size:6rem;animation:bounceIn .6s ease">✅</div>
  <h2 class="section-title mt-3">Payment Successful!</h2>
  <p class="text-muted lead">Thank you, <strong><?= htmlspecialchars($order['shipping_name']) ?></strong>!</p>

  <div class="cart-summary-box text-start mt-4">
    <h5 class="fw-bold mb-3">Order Details</h5>
    <div class="order-line"><span>Order ID</span><strong>#<?= $orderId ?></strong></div>
    <div class="order-line"><span>Transaction ID</span><strong><?= htmlspecialchars($gwTxnId) ?></strong></div>
    <div class="order-line"><span>Amount Paid</span><strong>Rs <?= number_format($order['total_amount'], 0, ',', '.') ?></strong></div>
    <div class="order-line"><span>Payment Status</span><span class="badge status-success">Success</span></div>
    <div class="order-line"><span>Order Status</span><span class="badge status-processing">Processing</span></div>
  </div>

  <div class="alert mt-4" style="background:rgba(43,191,160,0.1);border:1px solid var(--secondary);border-radius:12px;color:var(--primary-dark)">
    📧 A confirmation has been sent to <strong><?= htmlspecialchars($order['shipping_phone']) ?></strong>.
    Our team will contact you shortly.
  </div>

  <div class="d-flex gap-3 justify-content-center mt-4">
    <a href="<?= APP_URL ?>/index.php" class="btn btn-primary-custom">Continue Shopping</a>
    <a href="<?= APP_URL ?>/orders.php" class="btn btn-outline-secondary">View My Orders</a>
  </div>
</div>

<style>
  @keyframes bounceIn {
    from {
      transform: scale(0.3);
      opacity: 0;
    }

    50% {
      transform: scale(1.1);
    }

    to {
      transform: scale(1);
      opacity: 1;
    }
  }
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>