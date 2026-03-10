<?php
$pageTitle = 'My Orders – Sunbis AgroFish';
require_once __DIR__ . '/includes/header.php';
requireLogin();

$orders = $db->prepare("SELECT * FROM orders WHERE user_id=? ORDER BY created_at DESC");
$orders->execute([$user['id']]);
$orders = $orders->fetchAll();
?>
<meta name="app-url" content="<?= APP_URL ?>" />

<div class="container my-5">
  <h2 class="section-title mb-4">My Orders</h2>
  <?php if (!$orders): ?>
    <div class="text-center py-5">
      <div style="font-size:5rem">📦</div>
      <h4 class="mt-3">No orders yet</h4>
      <a href="catalog.php" class="btn btn-primary-custom mt-3">Start Shopping</a>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover align-middle bg-white shadow-sm rounded-4">
        <thead class="table-light">
          <tr>
            <th>Order #</th>
            <th>Total</th>
            <th>Payment</th>
            <th>Status</th>
            <th>Date</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($orders as $o): ?>
            <tr>
              <td><strong>#<?= $o['id'] ?></strong></td>
              <td>Rs <?= number_format($o['total_amount'], 0, ',', '.') ?></td>
              <td><span class="badge status-<?= $o['payment_status'] ?>"><?= ucfirst($o['payment_status']) ?></span></td>
              <td><span class="badge status-<?= $o['order_status'] ?>"><?= ucfirst($o['order_status']) ?></span></td>
              <td><?= date('d M Y', strtotime($o['created_at'])) ?></td>
              <td>
                <?php if ($o['payment_status'] === 'pending'): ?>
                  <a href="payments/payment_request.php" class="btn btn-sm btn-warning">Pay Now</a>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>