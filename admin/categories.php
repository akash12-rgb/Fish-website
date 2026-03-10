<?php
// admin/categories.php
require_once __DIR__ . '/../config/database.php';
requireAdmin();
$db = getDB();
$success = $error = '';

if (isset($_GET['delete'])) {
    $db->prepare('DELETE FROM categories WHERE id=?')->execute([(int)$_GET['delete']]);
    $success = 'Category deleted.';
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['category_name'] ?? '');
    $id   = (int)($_POST['id'] ?? 0);
    $slug = strtolower(preg_replace('/[^a-z0-9]+/i','-',$name));
    if (!$name) { $error = 'Name required.'; }
    elseif ($id) { $db->prepare('UPDATE categories SET category_name=?,slug=? WHERE id=?')->execute([$name,$slug,$id]); $success='Updated.'; }
    else         { $db->prepare('INSERT INTO categories (category_name,slug) VALUES (?,?)')->execute([$name,$slug]); $success='Added.'; }
}
$cats = $db->query('SELECT * FROM categories ORDER BY category_name')->fetchAll();
?>
<!DOCTYPE html><html lang="en"><head>
  <meta charset="UTF-8"/><title>Categories – Admin</title>
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
  <?php if ($success): ?><div class="alert alert-success-custom rounded-3 mb-3"><?= $success ?></div><?php endif; ?>
  <?php if ($error):   ?><div class="alert alert-error-custom rounded-3 mb-3"><?= $error ?></div><?php endif; ?>
  <div class="row g-4">
    <div class="col-md-5">
      <div class="card border-0 shadow-sm rounded-4 p-4">
        <h5 class="fw-bold mb-3">Add Category</h5>
        <form method="POST">
          <input type="hidden" name="id" value=""/>
          <div class="mb-3">
            <label class="form-label fw-semibold">Category Name</label>
            <input type="text" name="category_name" class="form-control" required placeholder="e.g. Fresh Fish"/>
          </div>
          <button class="btn btn-primary-custom">Add Category</button>
        </form>
      </div>
    </div>
    <div class="col-md-7">
      <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body">
          <h5 class="fw-bold mb-3">All Categories</h5>
          <table class="table table-hover">
            <thead class="table-light"><tr><th>#</th><th>Name</th><th>Slug</th><th></th></tr></thead>
            <tbody>
              <?php foreach ($cats as $c): ?>
              <tr>
                <td><?= $c['id'] ?></td>
                <td><?= htmlspecialchars($c['category_name']) ?></td>
                <td><small class="text-muted"><?= htmlspecialchars($c['slug'] ?? '') ?></small></td>
                <td><a href="categories.php?delete=<?= $c['id'] ?>" class="btn btn-sm btn-outline-danger confirm-delete">Delete</a></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= APP_URL ?>/public/js/app.js"></script>
</body></html>
