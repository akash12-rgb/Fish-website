<?php
// admin/products.php – Add / Edit / Delete products (Binary Image Storage)

require_once __DIR__ . '/../config/database.php';
requireAdmin();
$db = getDB();

$error   = '';
$success = '';
$editing = null;

// ── DELETE ─────────────────────────────────────────────
if (isset($_GET['delete'])) {
  $pid = (int)$_GET['delete'];
  $db->prepare('DELETE FROM products WHERE id=?')->execute([$pid]);
  $success = 'Product deleted.';
}

// ── EDIT FETCH ─────────────────────────────────────────
if (isset($_GET['edit'])) {
  $st = $db->prepare('SELECT * FROM products WHERE id=?');
  $st->execute([(int)$_GET['edit']]);
  $editing = $st->fetch();
}

// ── SAVE (add / update) ────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $id          = (int)($_POST['id'] ?? 0);
  $name        = trim($_POST['product_name'] ?? '');
  $description = trim($_POST['description'] ?? '');
  $price       = (float)($_POST['price'] ?? 0);
  $stock       = (int)($_POST['stock_quantity'] ?? 0);
  $catId       = (int)($_POST['category_id'] ?? 0);
  $featured    = isset($_POST['is_featured']) ? 'TRUE' : 'FALSE';

  $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name)) . '-' . time();

  // ── IMAGE PROCESSING ────────────────────────────────
  $imageData = null;

  if (!empty($_FILES['image']['tmp_name'])) {

    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','webp','gif'];

    if (!in_array($ext,$allowed)) {
      $error = 'Invalid image format.';
    } else {
      $imageData = file_get_contents($_FILES['image']['tmp_name']);
    }

  } else {
    $imageData = $_POST['existing_image'] ?? null;
  }

  if (!$error) {

    if ($id) {

      $db->prepare("
        UPDATE products 
        SET product_name=?,slug=?,description=?,price=?,stock_quantity=?,category_id=?,image=?,is_featured=? 
        WHERE id=?
      ")->execute([$name,$slug,$description,$price,$stock,$catId,$imageData,$featured,$id]);

      $success = 'Product updated.';

    } else {

      $db->prepare("
        INSERT INTO products 
        (product_name,slug,description,price,stock_quantity,category_id,image,is_featured) 
        VALUES (?,?,?,?,?,?,?,?)
      ")->execute([$name,$slug,$description,$price,$stock,$catId,$imageData,$featured]);

      $success = 'Product added.';
    }

    $editing = null;
  }
}

$products = $db->query("
  SELECT p.*, c.category_name 
  FROM products p 
  LEFT JOIN categories c ON c.id=p.category_id 
  ORDER BY p.created_at DESC
")->fetchAll();

$categories = $db->query("
  SELECT * FROM categories 
  ORDER BY category_name
")->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Products – Admin</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
<link href="../assets/css/bootstrap.min.css" rel="stylesheet">
<link href="../assets/css/bootstrap-icons.min.css" rel="stylesheet">
<link href="<?= APP_URL ?>/public/css/style.css" rel="stylesheet">

</head>

<body>

<nav class="navbar navbar-dark admin-navbar fixed-top" style="height:60px;z-index:200">
<div class="container-fluid">

<span class="navbar-brand fw-bold">🐟 Sunbis AgroFish — Admin</span>

<div class="d-flex gap-2">
<a href="<?= APP_URL ?>/index.php" target="_blank" class="btn btn-sm btn-outline-light">View Site</a>
<a href="<?= APP_URL ?>/logout.php" class="btn btn-sm btn-danger">Logout</a>
</div>

</div>
</nav>

<div class="admin-sidebar pt-2">
<nav class="nav flex-column mt-2">

<?php foreach ([

['bi-speedometer2','Dashboard','index.php'],
['bi-box-seam','Products','products.php'],
['bi-tags','Categories','categories.php'],
['bi-cart-check','Orders','orders.php'],
['bi-people','Customers','customers.php']

] as [$icon,$label,$href]): ?>

<a class="nav-link <?= basename($_SERVER['PHP_SELF']) === $href ? 'active' : '' ?>"
href="<?= APP_URL ?>/admin/<?= $href ?>">

<i class="bi <?= $icon ?>"></i> <?= $label ?>

</a>

<?php endforeach; ?>

</nav>
</div>

<div class="admin-content" style="padding-top:80px">

<?php if ($success): ?>
<div class="alert alert-success-custom rounded-3 mb-3"><?= $success ?></div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-error-custom rounded-3 mb-3"><?= $error ?></div>
<?php endif; ?>

<div class="row g-4">

<!-- FORM -->

<div class="col-lg-4">

<div class="card border-0 shadow-sm rounded-4 p-4">

<h5 class="fw-bold mb-3"><?= $editing ? 'Edit Product' : 'Add New Product' ?></h5>

<form method="POST" enctype="multipart/form-data">

<input type="hidden" name="id" value="<?= $editing['id'] ?? '' ?>">
<input type="hidden" name="existing_image" value="<?= $editing['image'] ?? '' ?>">

<div class="mb-3">

<label class="form-label fw-semibold">Product Name *</label>

<input type="text" name="product_name" class="form-control" required
value="<?= htmlspecialchars($editing['product_name'] ?? '') ?>">

</div>

<div class="mb-3">

<label class="form-label fw-semibold">Description</label>

<textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($editing['description'] ?? '') ?></textarea>

</div>

<div class="row g-2 mb-3">

<div class="col">

<label class="form-label fw-semibold">Price (Rs)</label>

<input type="number" name="price" class="form-control"
value="<?= $editing['price'] ?? '' ?>">

</div>

<div class="col">

<label class="form-label fw-semibold">Stock</label>

<input type="number" name="stock_quantity" class="form-control"
value="<?= $editing['stock_quantity'] ?? '' ?>">

</div>

</div>

<div class="mb-3">

<label class="form-label fw-semibold">Category</label>

<select name="category_id" class="form-select">

<option value="">— Select —</option>

<?php foreach ($categories as $c): ?>

<option value="<?= $c['id'] ?>"
<?= ($editing['category_id'] ?? '') == $c['id'] ? 'selected' : '' ?>>

<?= htmlspecialchars($c['category_name']) ?>

</option>

<?php endforeach; ?>

</select>

</div>

<div class="mb-3">

<label class="form-label fw-semibold">Product Image</label>

<?php if (!empty($editing['image'])): ?>

<img src="data:image/jpeg;base64,<?= base64_encode($editing['image']) ?>"
style="width:100%;border-radius:10px;margin-bottom:.5rem">

<?php endif; ?>

<input type="file" name="image" class="form-control" accept="image/*">

</div>

<div class="form-check mb-3">

<input class="form-check-input" type="checkbox" name="is_featured"
<?= ($editing['is_featured'] ?? false) ? 'checked' : '' ?>>

<label class="form-check-label">Feature on Homepage</label>

</div>

<div class="d-flex gap-2">

<button class="btn btn-primary-custom flex-grow-1">
<?= $editing ? 'Update Product' : 'Add Product' ?>
</button>

<?php if ($editing): ?>
<a href="products.php" class="btn btn-outline-secondary">Cancel</a>
<?php endif; ?>

</div>

</form>

</div>

</div>

<!-- TABLE -->

<div class="col-lg-8">

<div class="card border-0 shadow-sm rounded-4">

<div class="card-body">

<h5 class="fw-bold mb-3">All Products (<?= count($products) ?>)</h5>

<div class="table-responsive">

<table class="table table-hover align-middle">

<thead class="table-light">

<tr>

<th>Image</th>
<th>Name</th>
<th>Price</th>
<th>Stock</th>
<th>Category</th>
<th>Featured</th>
<th>Actions</th>

</tr>

</thead>

<tbody>

<?php foreach ($products as $p): ?>

<tr>

<td>

<?php if (!empty($p['image'])): ?>

<img src="data:image/jpeg;base64,<?= base64_encode($p['image']) ?>"
style="width:50px;height:50px;object-fit:cover;border-radius:8px">

<?php else: ?>

<span style="font-size:2rem">🐟</span>

<?php endif; ?>

</td>

<td class="fw-semibold"><?= htmlspecialchars($p['product_name']) ?></td>

<td>Rs <?= number_format($p['price'],0,',','.') ?></td>

<td>
<span class="badge <?= $p['stock_quantity'] > 0 ? 'bg-success' : 'bg-danger' ?>">
<?= $p['stock_quantity'] ?>
</span>
</td>

<td><?= htmlspecialchars($p['category_name'] ?? '—') ?></td>

<td><?= $p['is_featured'] ? '⭐' : '—' ?></td>

<td>

<a href="products.php?edit=<?= $p['id'] ?>"
class="btn btn-sm btn-outline-primary me-1">Edit</a>

<a href="products.php?delete=<?= $p['id'] ?>"
class="btn btn-sm btn-outline-danger confirm-delete">Delete</a>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

</div>

</div>

</div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= APP_URL ?>/public/js/app.js"></script>

</body>
</html>
