<?php
$pageTitle = 'Contact Us – Sunbis AgroFish';
require_once __DIR__ . '/includes/header.php';
$sent  = false;
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // In production: send email via PHPMailer or SMTP
  // For now we just mark as sent
  $name    = htmlspecialchars(trim($_POST['name']    ?? ''));
  $email   = htmlspecialchars(trim($_POST['email']   ?? ''));
  $subject = htmlspecialchars(trim($_POST['subject'] ?? ''));
  $message = htmlspecialchars(trim($_POST['message'] ?? ''));
  if ($name && $email && $message) {
    $sent = true;
    // mail('info@sunbisagrofish.com', $subject, "$name\n$email\n\n$message");
  } else {
    $error = 'Please fill all required fields.';
  }
}
?>
<meta name="app-url" content="<?= APP_URL ?>" />

<div class="container my-5">
  <div class="text-center mb-5">
    <p class="section-label">Reach Out</p>
    <h2 class="section-title">Contact Us</h2>
    <p class="text-muted">We'd love to hear from you — whether you're a buyer, partner, or have a question.</p>
  </div>

  <div class="row g-4 mb-5">
    <?php foreach (
      [
        ['bi-geo-alt-fill', 'Our Farm',    'Plot No. K-5,HIG-444,Kalinga Vihar,Bhubaneswar,Odisha,751019'],
        ['bi-telephone-fill', 'Phone / WhatsApp', '+91 9337227262'],
        ['bi-envelope-fill', 'Email',      'sunbisagri@gmail.com'],
        ['bi-clock-fill', 'Working Hours', 'Mon–Sat, 07:00 A.M – 17:00 P.M'],
      ] as $c
    ): ?>
      <div class="col-6 col-md-3">
        <div class="contact-card">
          <div class="contact-icon"><i class="bi <?= $c[0] ?>"></i></div>
          <h6 class="fw-bold"><?= $c[1] ?></h6>
          <p class="text-muted small mb-0"><?= $c[2] ?></p>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="row justify-content-center">
    <div class="col-lg-7">
      <?php if ($sent): ?>
        <div class="alert alert-success-custom rounded-3 text-center py-4">
          ✅ <strong>Message sent!</strong> We'll get back to you within 24 hours.
        </div>
      <?php else: ?>
        <?php if ($error): ?>
          <div class="alert alert-error-custom rounded-3 mb-3"><?= $error ?></div>
        <?php endif; ?>
        <div class="card border-0 shadow-sm rounded-4 p-4">
          <h5 class="fw-bold mb-3">Send us a Message</h5>
          <form method="POST">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-semibold">Your Name *</label>
                <input type="text" name="name" class="form-control" required />
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Email Address *</label>
                <input type="email" name="email" class="form-control" required />
              </div>
              <div class="col-12">
                <label class="form-label fw-semibold">Subject</label>
                <input type="text" name="subject" class="form-control" placeholder="e.g. Bulk order enquiry" />
              </div>
              <div class="col-12">
                <label class="form-label fw-semibold">Message *</label>
                <textarea name="message" class="form-control" rows="5" required placeholder="Tell us how we can help..."></textarea>
              </div>
              <div class="col-12">
                <button class="btn btn-primary-custom px-4 py-2">Send Message <i class="bi bi-send ms-1"></i></button>
              </div>
            </div>
          </form>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>