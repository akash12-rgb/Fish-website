<?php
// payments/payment_failure.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/payment.php';

$db      = getDB();
$orderId = (int)($_REQUEST['order_id'] ?? 0);

if ($orderId) {
    $db->prepare("UPDATE orders   SET payment_status='failed', order_status='cancelled' WHERE id=? AND payment_status='pending'")
       ->execute([$orderId]);
    $db->prepare("UPDATE payments SET payment_status='failed', gateway_response=? WHERE order_id=?")
       ->execute([json_encode($_REQUEST), $orderId]);
}

$pageTitle = 'Payment Failed – Sunbis AgroFish';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container my-5" style="max-width:640px;text-align:center">
  <div style="font-size:6rem">❌</div>
  <h2 class="section-title mt-3">Payment Failed</h2>
  <p class="text-muted lead">Unfortunately, your payment could not be processed.</p>

  <?php if ($orderId): ?>
  <div class="cart-summary-box text-start mt-4">
    <div class="order-line"><span>Order ID</span><strong>#<?= $orderId ?></strong></div>
    <div class="order-line"><span>Status</span><span class="badge status-failed">Failed</span></div>
  </div>
  <?php endif; ?>

  <div class="alert mt-4" style="background:rgba(220,53,69,0.08);border:1px solid #dc3545;border-radius:12px;color:#842029">
    💡 Your order has been cancelled. No amount has been deducted.
    Please try again or contact us if the issue persists.
  </div>

  <div class="d-flex gap-3 justify-content-center mt-4 flex-wrap">
    <a href="<?= APP_URL ?>/checkout.php" class="btn btn-primary-custom">Try Again</a>
    <a href="<?= APP_URL ?>/cart.php"     class="btn btn-outline-secondary">Back to Cart</a>
    <a href="<?= APP_URL ?>/contact.php"  class="btn btn-outline-secondary">Contact Support</a>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
