<?php
// includes/header.php
require_once __DIR__ . '/../config/database.php';
$user = currentUser();
$db   = getDB();

// Cart count
$cartCount = 0;
if ($user) {
  $st = $db->prepare('SELECT COALESCE(SUM(quantity),0) FROM cart WHERE user_id = ?');
  $st->execute([$user['id']]);
  $cartCount = (int)$st->fetchColumn();
}

// Categories for nav
$cats = $db->query('SELECT * FROM categories ORDER BY category_name')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title><?= $pageTitle ?? APP_NAME ?></title>
  <meta name="description" content="<?= $pageDesc ?? 'Sunbis AgroFish – Premium Aquaculture & Agriculture Products' ?>" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
  <link href="<?= APP_URL ?>/public/css/style.css" rel="stylesheet" />
</head>

<body>

  <!-- TOP BAR -->
  <div class="topbar">
    <div class="container d-flex justify-content-between align-items-center">
      <span><i class="bi bi-telephone-fill me-1"></i> +91 9337227262
        &nbsp;|&nbsp;<i class="bi bi-envelope-fill me-1"></i>sunbisagri33@gmail.com</span>
      <div>
        <?php if ($user): ?>
          <span class="me-2">Hi, <?= htmlspecialchars($user['name']) ?></span>
          <?php if ($user['role'] === 'admin'): ?>
            <a href="<?= APP_URL ?>/admin/index.php" class="btn btn-sm btn-warning me-1">Admin</a>
          <?php endif; ?>
          <a href="<?= APP_URL ?>/logout.php" class="btn btn-sm btn-outline-light">Logout</a>
        <?php else: ?>
          <a href="<?= APP_URL ?>/login.php" class="btn btn-sm btn-outline-light me-1">Login</a>
          <a href="<?= APP_URL ?>/register.php" class="btn btn-sm btn-primary">Register</a>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- NAVBAR -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary-custom sticky-top shadow-sm">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center gap-2" href="<?= APP_URL ?>/index.php">
        <span class="brand-icon">🐟</span>
        <span class="brand-name">Sunbis <em>AgroFish</em></span>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navMain">
        <!-- Search -->
        <form class="d-flex mx-auto search-form" action="<?= APP_URL ?>/catalog.php" method="GET">
          <div class="input-group">
            <input type="text" name="q" class="form-control" placeholder="Search products..."
              value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" />
            <button class="btn btn-search" type="submit"><i class="bi bi-search"></i></button>
          </div>
        </form>
        <ul class="navbar-nav ms-auto align-items-center gap-1">
          <li class="nav-item"><a class="nav-link" href="<?= APP_URL ?>/index.php">Home</a></li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Shop</a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="<?= APP_URL ?>/catalog.php">All Products</a></li>
              <li>
                <hr class="dropdown-divider">
              </li>
              <?php foreach ($cats as $c): ?>
                <li><a class="dropdown-item" href="<?= APP_URL ?>/catalog.php?cat=<?= $c['id'] ?>">
                    <?= htmlspecialchars($c['category_name']) ?></a></li>
              <?php endforeach; ?>
            </ul>
          </li>
          <li class="nav-item"><a class="nav-link" href="<?= APP_URL ?>/contact.php">Contact</a></li>
          <li class="nav-item">
            <a class="nav-link cart-icon" href="<?= APP_URL ?>/cart.php">
              <i class="bi bi-cart3"></i>
              <span class="cart-badge"><?= $cartCount ?></span>
            </a>
          </li>
        </ul>
      </div>
    </div>
  </nav>
