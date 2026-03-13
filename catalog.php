<?php
$pageTitle = 'Shop – Sunbis AgroFish';
require_once __DIR__ . '/includes/header.php';

$catId = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;
$q     = trim($_GET['q'] ?? '');
$sort  = $_GET['sort'] ?? 'newest';
$page  = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;
$offset  = ($page - 1) * $perPage;

// Build query
$where  = ['p.stock_quantity > 0'];
$params = [];

if ($catId) {
  $where[] = 'p.category_id = ?';
  $params[] = $catId;
}
if ($q !== '') {
  $where[] = "(p.product_name ILIKE ? OR p.description ILIKE ?)";
  $params[] = "%$q%";
  $params[] = "%$q%";
}

$orderBy = match ($sort) {
  'price_asc'  => 'p.price ASC',
  'price_desc' => 'p.price DESC',
  'name'       => 'p.product_name ASC',
  default      => 'p.created_at DESC',
};

$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$countSt = $db->prepare("SELECT COUNT(*) FROM products p $whereSQL");
$countSt->execute($params);
$total = (int)$countSt->fetchColumn();
$pages = max(1, ceil($total / $perPage));

$sql = "SELECT p.*, c.category_name FROM products p
        LEFT JOIN categories c ON c.id = p.category_id
        $whereSQL ORDER BY $orderBy LIMIT $perPage OFFSET $offset";
$st  = $db->prepare($sql);
$st->execute($params);
$products = $st->fetchAll();
?>
<meta name="app-url" content="<?= APP_URL ?>" />

<div class="container my-5">
  <div class="row">
    <!-- SIDEBAR -->
    <div class="col-lg-3 mb-4">
      <div class="filter-sidebar">
        <h6 class="fw-bold mb-3">Categories</h6>
        <div class="list-group">
          <a href="catalog.php<?= $q ? '?q=' . urlencode($q) : '' ?>"
            class="list-group-item <?= !$catId ? 'active' : '' ?>">All Products
            <span class="float-end badge bg-secondary"><?= $total ?></span></a>
          <?php foreach ($cats as $c): ?>
            <a href="catalog.php?cat=<?= $c['id'] ?><?= $q ? '&q=' . urlencode($q) : '' ?>"
              class="list-group-item <?= $catId == $c['id'] ? 'active' : '' ?>">
              <?= htmlspecialchars($c['category_name']) ?></a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- MAIN -->
    <div class="col-lg-9">
      <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
          <h4 class="mb-0 fw-bold">
            <?= $catId ? htmlspecialchars($cats[array_search($catId, array_column($cats, 'id'))]['category_name'] ?? 'Products') : 'All Products' ?>
          </h4>
          <small class="text-muted"><?= $total ?> products found<?= $q ? " for \"$q\"" : '' ?></small>
        </div>
        <form method="GET" class="d-flex gap-2 align-items-center">
          <?php if ($catId): ?><input type="hidden" name="cat" value="<?= $catId ?>"><?php endif; ?>
          <?php if ($q): ?><input type="hidden" name="q" value="<?= htmlspecialchars($q) ?>"><?php endif; ?>
          <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="newest" <?= $sort === 'newest'    ? 'selected' : '' ?>>Newest</option>
            <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
            <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
            <option value="name" <?= $sort === 'name'      ? 'selected' : '' ?>>Name A–Z</option>
          </select>
        </form>
      </div>

      <?php if (!$products): ?>
        <div class="text-center py-5">
          <div style="font-size:4rem">🐠</div>
          <h5 class="mt-3">No products found</h5>
          <a href="catalog.php" class="btn btn-primary-custom mt-2">View All Products</a>
        </div>
      <?php else: ?>
        <div class="row g-4">
          <?php foreach ($products as $p): ?>
            <div class="col-6 col-md-4">
              <div class="product-card">
              <?php if (!empty($p['image'])): ?>
  <a href="product.php?id=<?= $p['id'] ?>">
    <img src="data:image/jpeg;base64,<?= base64_encode($p['image']) ?>"
         class="card-img-top"
         alt="<?= htmlspecialchars($p['product_name']) ?>" />
  </a>
<?php else: ?>
  <a href="product.php?id=<?= $p['id'] ?>">
    <div class="product-img-placeholder">🐟</div>
  </a>
<?php endif; ?>
                <div class="card-body">
                  <span class="badge-category mb-1 d-inline-block"><?= htmlspecialchars($p['category_name'] ?? '') ?></span>
                  <h6 class="card-title">
                    <a href="product.php?id=<?= $p['id'] ?>" class="text-decoration-none text-dark"><?= htmlspecialchars($p['product_name']) ?></a>
                  </h6>
                  <p class="small text-muted mb-2" style="overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical">
                    <?= htmlspecialchars($p['description']) ?></p>
                  <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="product-price">Rs <?= number_format($p['price'], 0, ',', '.') ?></div>
                    <small class="text-muted">Stock: <?= $p['stock_quantity'] ?></small>
                  </div>
                  <div class="d-flex gap-2">
                    <button class="btn-cart" onclick="addToCart(<?= $p['id'] ?>)"><i class="bi bi-cart-plus me-1"></i>Cart</button>
                    <a href="product.php?id=<?= $p['id'] ?>" class="btn-buy text-center text-decoration-none">Buy Now</a>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- PAGINATION -->
        <?php if ($pages > 1): ?>
          <nav class="mt-4">
            <ul class="pagination justify-content-center">
              <?php for ($i = 1; $i <= $pages; $i++): ?>
                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                  <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                </li>
              <?php endfor; ?>
            </ul>
          </nav>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
