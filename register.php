<?php
// register.php
$pageTitle = 'Register – Sunbis AgroFish';
require_once __DIR__ . '/config/database.php';
$user = currentUser();
if ($user) { header('Location: ' . APP_URL . '/index.php'); exit; }

$error   = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $phone    = trim($_POST['phone']    ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm  = trim($_POST['confirm']  ?? '');

    if ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $db = getDB();
        $st = $db->prepare('SELECT id FROM users WHERE email=?');
        $st->execute([$email]);
        if ($st->fetch()) {
            $error = 'Email already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $db->prepare('INSERT INTO users (name,email,phone,password) VALUES (?,?,?,?)')->execute([$name,$email,$phone,$hash]);
            $success = 'Account created! <a href="login.php">Login here</a>.';
        }
    }
}
require_once __DIR__ . '/includes/header.php';
?>
<meta name="app-url" content="<?= APP_URL ?>"/>
<div class="container my-5 d-flex justify-content-center">
  <div class="card shadow-sm border-0 rounded-4 p-4" style="max-width:480px;width:100%">
    <div class="text-center mb-4">
      <span style="font-size:2.5rem">🌿</span>
      <h3 class="fw-bold mt-2">Create Account</h3>
    </div>
    <?php if ($error): ?>
      <div class="alert alert-error-custom rounded-3 mb-3"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="alert alert-success-custom rounded-3 mb-3"><?= $success ?></div>
    <?php endif; ?>
    <form method="POST">
      <div class="mb-3">
        <label class="form-label fw-semibold">Full Name</label>
        <input type="text" name="name" class="form-control" required/>
      </div>
      <div class="mb-3">
        <label class="form-label fw-semibold">Email Address</label>
        <input type="email" name="email" class="form-control" required/>
      </div>
      <div class="mb-3">
        <label class="form-label fw-semibold">Phone</label>
        <input type="tel" name="phone" class="form-control"/>
      </div>
      <div class="mb-3">
        <label class="form-label fw-semibold">Password</label>
        <input type="password" name="password" class="form-control" required minlength="6"/>
      </div>
      <div class="mb-4">
        <label class="form-label fw-semibold">Confirm Password</label>
        <input type="password" name="confirm" class="form-control" required/>
      </div>
      <button class="btn btn-primary-custom w-100 py-2">Create Account</button>
    </form>
    <p class="text-center text-muted small mt-3">
      Already have an account? <a href="login.php" style="color:var(--primary)">Sign in</a>
    </p>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
