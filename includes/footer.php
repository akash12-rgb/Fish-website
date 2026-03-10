<?php // includes/footer.php 
?>
<footer class="site-footer mt-5">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-4">
        <div class="footer-brand mb-3">🐟 Sunbis <em>AgroFish</em></div>
        <p class="text-muted small">Premium aquaculture and agro-farming produce delivered fresh from our farms to your table. Sustainable. Natural. Trusted.</p>
        <div class="social-links mt-3">
          <a href="#"><i class="bi bi-facebook"></i></a>
          <a href="#"><i class="bi bi-instagram"></i></a>
          <a href="#"><i class="bi bi-whatsapp"></i></a>
          <a href="#"><i class="bi bi-youtube"></i></a>
        </div>
      </div>
      <div class="col-lg-2 col-6">
        <h6 class="footer-heading">Shop</h6>
        <ul class="footer-links">
          <li><a href="<?= APP_URL ?>/catalog.php">All Products</a></li>
          <li><a href="<?= APP_URL ?>/catalog.php?cat=1">Fresh Fish</a></li>
          <li><a href="<?= APP_URL ?>/catalog.php?cat=2">Shrimp</a></li>
          <li><a href="<?= APP_URL ?>/catalog.php?cat=3">Agriculture</a></li>
        </ul>
      </div>
      <div class="col-lg-2 col-6">
        <h6 class="footer-heading">Company</h6>
        <ul class="footer-links">
          <li><a href="<?= APP_URL ?>/index.php#about">About Us</a></li>
          <li><a href="<?= APP_URL ?>/contact.php">Contact</a></li>
          <li><a href="#">Privacy Policy</a></li>
          <li><a href="#">Terms of Service</a></li>
        </ul>
      </div>
      <div class="col-lg-4">
        <h6 class="footer-heading">Contact Us</h6>
        <ul class="footer-links">
          <li><i class="bi bi-geo-alt me-2"></i>Plot No. K-5,HIG-444,Kalinga Vihar,Bhubaneswar,Khurda,Odisha,751019</li>
          <li><i class="bi bi-telephone me-2"></i>+91 9337227262</li>
          <li><i class="bi bi-envelope me-2"></i>sunbisagri@gmail.com</li>
          <li><i class="bi bi-clock me-2"></i>Mon–Sat, 07:00 A.M –17:00 P.M</li>
        </ul>
      </div>
    </div>
    <hr class="footer-divider" />
    <p class="text-center text-muted small mb-0">
      © <?= date('Y') ?> Sunbis AgroFish. All rights reserved.
    </p>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= APP_URL ?>/public/js/app.js"></script>
</body>

</html>