<?php
// admin/orders.php
require_once __DIR__ . '/../config/database.php';
requireAdmin();
$db = getDB();

// Update order status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
  $db->prepare("UPDATE orders SET order_status=? WHERE id=?")
    ->execute([$_POST['order_status'], (int)$_POST['order_id']]);
  $success = 'Order status updated.';
}

// View single order
$viewOrder = null;
$orderItems = [];
if (isset($_GET['view'])) {
  $st = $db->prepare("SELECT o.*, u.name as cname, u.email as cemail FROM orders o LEFT JOIN users u ON u.id=o.user_id WHERE o.id=?");
  $st->execute([(int)$_GET['view']]);
  $viewOrder = $st->fetch();
  if ($viewOrder) {
    $ist = $db->prepare("SELECT oi.*, p.product_name FROM order_items oi LEFT JOIN products p ON p.id=oi.product_id WHERE oi.order_id=?");
    $ist->execute([$viewOrder['id']]);
    $orderItems = $ist->fetchAll();
  }
}

$orders = $db->query("
    SELECT o.*, u.name as customer_name FROM orders o
    LEFT JOIN users u ON u.id = o.user_id
    ORDER BY o.created_at DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Orders – Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
  <link href="<?= APP_URL ?>/public/css/style.css" rel="stylesheet" />
</head>

<body>
  <nav class="navbar navbar-dark admin-navbar fixed-top" style="height:60px;z-index:200">
    <div class="container-fluid">
      <span class="navbar-brand fw-bold">🐟 Sunbis AgroFish — Admin</span>
      <a href="<?= APP_URL ?>/logout.php" class="btn btn-sm btn-danger">Logout</a>
    </div>
  </nav>

  <div class="admin-sidebar pt-2">
    <nav class="nav flex-column mt-2">
      <?php foreach ([['bi-speedometer2', 'Dashboard', 'index.php'], ['bi-box-seam', 'Products', 'products.php'], ['bi-tags', 'Categories', 'categories.php'], ['bi-cart-check', 'Orders', 'orders.php'], ['bi-people', 'Customers', 'customers.php']] as [$icon, $label, $href]): ?>
        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === $href ? 'active' : '' ?>" href="<?= APP_URL ?>/admin/<?= $href ?>"><i class="bi <?= $icon ?>"></i> <?= $label ?></a>
      <?php endforeach; ?>
    </nav>
  </div>

  <div class="admin-content" style="padding-top:80px">
    <?php if (!empty($success)): ?><div class="alert alert-success-custom rounded-3 mb-3"><?= $success ?></div><?php endif; ?>

    <?php if ($viewOrder): ?>
      <!-- SINGLE ORDER VIEW -->
      <div class="d-flex align-items-center gap-3 mb-4">
        <a href="orders.php" class="btn btn-outline-secondary btn-sm">← Back</a>
        <h4 class="fw-bold mb-0">Order #<?= $viewOrder['id'] ?></h4>
      </div>
      <div class="row g-4">
        <div class="col-lg-8">
          <div class="card border-0 shadow-sm rounded-4 p-4 mb-3">
            <h6 class="fw-bold mb-3">Order Items</h6>
            <table class="table align-middle">
              <thead class="table-light">
                <tr>
                  <th>Product</th>
                  <th>Qty</th>
                  <th>Price</th>
                  <th>Subtotal</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($orderItems as $item): ?>
                  <tr>
                    <td><?= htmlspecialchars($item['product_name'] ?? '—') ?></td>
                    <td><?= $item['quantity'] ?></td>
                    <td>Rs <?= number_format($item['price'], 0, ',', '.') ?></td>
                    <td>Rs <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?></td>
                  </tr>
                <?php endforeach; ?>
                <tr class="fw-bold">
                  <td colspan="3" class="text-end">Total</td>
                  <td>Rs <?= number_format($viewOrder['total_amount'], 0, ',', '.') ?></td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="card border-0 shadow-sm rounded-4 p-4">
            <h6 class="fw-bold mb-3">Shipping Details</h6>
            <p class="mb-1"><strong>Name:</strong> <?= htmlspecialchars($viewOrder['shipping_name']) ?></p>
            <p class="mb-1"><strong>Phone:</strong> <?= htmlspecialchars($viewOrder['shipping_phone']) ?></p>
            <p class="mb-1"><strong>Address:</strong> <?= htmlspecialchars($viewOrder['shipping_address']) ?>, <?= htmlspecialchars($viewOrder['shipping_city']) ?> <?= htmlspecialchars($viewOrder['shipping_zip']) ?></p>
            <?php if ($viewOrder['notes']): ?>
              <p class="mb-0"><strong>Notes:</strong> <?= htmlspecialchars($viewOrder['notes']) ?></p>
            <?php endif; ?>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="cart-summary-box">
            <h6 class="fw-bold mb-3">Order Status</h6>
            <p><span class="badge status-<?= $viewOrder['payment_status'] ?>"><?= ucfirst($viewOrder['payment_status']) ?></span> Payment</p>
            <p><span class="badge status-<?= $viewOrder['order_status'] ?>"><?= ucfirst($viewOrder['order_status']) ?></span> Order</p>
            <p class="text-muted small">Transaction: <?= htmlspecialchars($viewOrder['transaction_id']) ?></p>
            <form method="POST" class="mt-3">
              <input type="hidden" name="order_id" value="<?= $viewOrder['id'] ?>" />
              <label class="fw-semibold mb-2 d-block">Update Order Status</label>
              <select name="order_status" class="form-select mb-2">
                <?php foreach (['pending', 'processing', 'shipped', 'delivered', 'cancelled'] as $s): ?>
                  <option value="<?= $s ?>" <?= $viewOrder['order_status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
              </select>
              <button name="update_status" class="btn btn-primary-custom w-100">Update Status</button>
            </form>
          </div>
        </div>
      </div>

    <?php else: ?>
      <!-- ORDERS LIST -->
      <h4 class="fw-bold mb-4">All Orders (<?= count($orders) ?>)</h4>
      <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Customer</th>
                  <th>Amount</th>
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
                    <td><?= htmlspecialchars($o['customer_name'] ?? 'Guest') ?></td>
                    <td>Rs <?= number_format($o['total_amount'], 0, ',', '.') ?></td>
                    <td><span class="badge status-<?= $o['payment_status'] ?>"><?= ucfirst($o['payment_status']) ?></span></td>
                    <td><span class="badge status-<?= $o['order_status'] ?>"><?= ucfirst($o['order_status']) ?></span></td>
                    <td><small><?= date('d M Y H:i', strtotime($o['created_at'])) ?></small></td>
                    <td><a href="orders.php?view=<?= $o['id'] ?>" class="btn btn-sm btn-outline-primary">View</a></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>