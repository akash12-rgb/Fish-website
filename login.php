<?php
// login.php
$pageTitle = 'Login – Sunbis AgroFish';
require_once __DIR__ . '/config/database.php';
$user = currentUser();
if ($user) {
  header('Location: ' . APP_URL . '/index.php');
  exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email    = trim($_POST['email']    ?? '');
  $password = trim($_POST['password'] ?? '');

  $db       = getDB();
  $st       = $db->prepare('SELECT * FROM users WHERE email=?');

  $st->execute([$email]);
  $u = $st->fetch();
  if ($u && password_verify($password, $u['password'])) {
    $_SESSION['user'] = ['id' => $u['id'], 'name' => $u['name'], 'email' => $u['email'], 'role' => $u['role']];
    header('Location: ' . ($_GET['redirect'] ?? APP_URL . '/admin/index.php'));
    exit;
  } else {
    $error = 'Invalid email or password.';
  }
}
require_once __DIR__ . '/includes/header.php';
?>
<meta name="app-url" content="<?= APP_URL ?>" />
<div class="container my-5 d-flex justify-content-center">
  <div class="card shadow-sm border-0 rounded-4 p-4" style="max-width:440px;width:100%">
    <div class="text-center mb-4">
      <span style="font-size:2.5rem">🐟</span>
      <h3 class="fw-bold mt-2">Welcome Back</h3>
      <p class="text-muted small">Sign in to your Sunbis AgroFish account</p>
    </div>
    <?php if ($error): ?>
      <div class="alert alert-error-custom rounded-3 mb-3"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST">
      <div class="mb-3">
        <label class="form-label fw-semibold">Email Address</label>
        <input type="email" name="email" class="form-control" required placeholder="you@email.com" />
      </div>
      <div class="mb-4">
        <label class="form-label fw-semibold">Password</label>
        <input type="password" name="password" class="form-control" required placeholder="" />
      </div>
      <button class="btn btn-primary-custom w-100 py-2">Sign In</button>
    </form>
    <p class="text-center text-muted small mt-3">
      Don't have an account? <a href="register.php" style="color:var(--primary)">Register here</a>
    </p>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>