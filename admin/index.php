<?php
// admin/index.php  –  Admin Dashboard
require_once __DIR__ . '/../config/database.php';
requireAdmin();

echo "Welcome Admin: " . $_SESSION['user']['name'];
$db = getDB();

$stats = [
  'orders'   => $db->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
  'products' => $db->query("SELECT COUNT(*) FROM products")->fetchColumn(),
  'users'    => $db->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetchColumn(),
  'revenue'  => $db->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE payment_status='success'")->fetchColumn(),
];
$recentOrders = $db->query("
    SELECT o.*, u.name as customer_name FROM orders o
    LEFT JOIN users u ON u.id = o.user_id
    ORDER BY o.created_at DESC LIMIT 8
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Admin Dashboard – Sunbis AgroFish</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
  <link href="<?= APP_URL ?>/public/css/style.css" rel="stylesheet" />
</head>

<body>

  <!-- Admin Navbar -->
  <nav class="navbar navbar-dark admin-navbar fixed-top" style="height:60px;z-index:200">
    <div class="container-fluid">
      <span class="navbar-brand fw-bold">🐟 Sunbis AgroFish — Admin</span>
      <div class="d-flex align-items-center gap-3">
        <a href="<?= APP_URL ?>/index.php" target="_blank" class="btn btn-sm btn-outline-light">View Site</a>
        <a href="<?= APP_URL ?>/logout.php" class="btn btn-sm btn-danger">Logout</a>
      </div>
    </div>
  </nav>

  <!-- Sidebar -->
  <div class="admin-sidebar pt-2">
    <nav class="nav flex-column mt-2">
      <?php
      $navItems = [
        ['bi-speedometer2', 'Dashboard',  'index.php'],
        ['bi-box-seam',    'Products',   'products.php'],
        ['bi-tags',        'Categories', 'categories.php'],
        ['bi-cart-check',  'Orders',     'orders.php'],
        ['bi-people',      'Customers',  'customers.php'],
      ];
      $current = basename($_SERVER['PHP_SELF']);
      foreach ($navItems as [$icon, $label, $href]): ?>
        <a class="nav-link <?= $current === $href ? 'active' : '' ?>" href="<?= APP_URL ?>/admin/<?= $href ?>">
          <i class="bi <?= $icon ?>"></i> <?= $label ?>
        </a>
      <?php endforeach; ?>
    </nav>
  </div>

  <!-- Main Content -->
  <div class="admin-content" style="padding-top:80px">
    <h4 class="fw-bold mb-4">Dashboard Overview</h4>

    <!-- Stats -->
    <div class="row g-4 mb-4">
      <?php foreach (
        [
          ['Total Orders',   $stats['orders'],   'bi-cart3',        '#1a7a6e'],
          ['Products',       $stats['products'], 'bi-box-seam',     '#0d3b5e'],
          ['Customers',      $stats['users'],    'bi-people',       '#2bbfa0'],
          ['Revenue (Rs)',   number_format($stats['revenue'], 0, ',', '.'), 'bi-cash-coin', '#f0b429'],
        ] as [$label, $val, $icon, $color]
      ): ?>
        <div class="col-6 col-md-3">
          <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <p class="text-muted small mb-1"><?= $label ?></p>
                <h3><?= $val ?></h3>
              </div>
              <i class="bi <?= $icon ?>" style="font-size:2rem;color:<?= $color ?>"></i>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Recent Orders -->
    <div class="card border-0 shadow-sm rounded-4">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="fw-bold mb-0">Recent Orders</h5>
          <a href="orders.php" class="btn btn-sm btn-outline-primary">View All</a>
        </div>
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
              <?php foreach ($recentOrders as $o): ?>
                <tr>
                  <td><strong>#<?= $o['id'] ?></strong></td>
                  <td><?= htmlspecialchars($o['customer_name'] ?? 'Guest') ?></td>
                  <td>Rs <?= number_format($o['total_amount'], 0, ',', '.') ?></td>
                  <td><span class="badge status-<?= $o['payment_status'] ?>"><?= ucfirst($o['payment_status']) ?></span></td>
                  <td><span class="badge status-<?= $o['order_status'] ?>"><?= ucfirst($o['order_status']) ?></span></td>
                  <td><small class="text-muted"><?= date('d M Y', strtotime($o['created_at'])) ?></small></td>
                  <td><a href="orders.php?view=<?= $o['id'] ?>" class="btn btn-sm btn-outline-secondary">View</a></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>