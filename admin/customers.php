<?php
// admin/customers.php
require_once __DIR__ . '/../config/database.php';
requireAdmin();
$db = getDB();
$customers = $db->query("SELECT u.*, COUNT(o.id) as order_count FROM users u LEFT JOIN orders o ON o.user_id=u.id WHERE u.role='customer' GROUP BY u.id ORDER BY u.created_at DESC")->fetchAll();
?>
<!DOCTYPE html><html lang="en"><head>
  <meta charset="UTF-8"/><title>Customers – Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"/>
  <link href="<?= APP_URL ?>/public/css/style.css" rel="stylesheet"/>
</head><body>
<nav class="navbar navbar-dark admin-navbar fixed-top" style="height:60px;z-index:200">
  <div class="container-fluid"><span class="navbar-brand fw-bold">🐟 Sunbis AgroFish — Admin</span>
  <a href="<?= APP_URL ?>/logout.php" class="btn btn-sm btn-danger">Logout</a></div>
</nav>
<div class="admin-sidebar pt-2"><nav class="nav flex-column mt-2">
  <?php foreach ([['bi-speedometer2','Dashboard','index.php'],['bi-box-seam','Products','products.php'],['bi-tags','Categories','categories.php'],['bi-cart-check','Orders','orders.php'],['bi-people','Customers','customers.php']] as [$icon,$label,$href]): ?>
  <a class="nav-link <?= basename($_SERVER['PHP_SELF'])===$href?'active':'' ?>" href="<?= APP_URL ?>/admin/<?= $href ?>"><i class="bi <?= $icon ?>"></i> <?= $label ?></a>
  <?php endforeach; ?>
</nav></div>
<div class="admin-content" style="padding-top:80px">
  <h4 class="fw-bold mb-4">Customers (<?= count($customers) ?>)</h4>
  <div class="card border-0 shadow-sm rounded-4">
    <div class="card-body">
      <table class="table table-hover align-middle">
        <thead class="table-light"><tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Orders</th><th>Joined</th></tr></thead>
        <tbody>
          <?php foreach ($customers as $c): ?>
          <tr>
            <td><?= $c['id'] ?></td>
            <td class="fw-semibold"><?= htmlspecialchars($c['name']) ?></td>
            <td><?= htmlspecialchars($c['email']) ?></td>
            <td><?= htmlspecialchars($c['phone'] ?? '—') ?></td>
            <td><span class="badge bg-primary"><?= $c['order_count'] ?></span></td>
            <td><small><?= date('d M Y', strtotime($c['created_at'])) ?></small></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body></html>
