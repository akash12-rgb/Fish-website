// ============================================================
//  Sunbis AgroFish – app.js
// ============================================================

const APP_URL = document.querySelector('meta[name="app-url"]')?.content ?? '';

// ── TOAST ────────────────────────────────────────────────────
function showToast(msg, type = 'success') {
  let c = document.getElementById('toast-container');
  if (!c) {
    c = document.createElement('div');
    c.id = 'toast-container';
    document.body.appendChild(c);
  }
  const t = document.createElement('div');
  t.className = 'toast-msg';
  t.style.borderLeftColor = type === 'error' ? '#dc3545' : '#2bbfa0';
  t.innerHTML = (type === 'error' ? '❌ ' : '✅ ') + msg;
  c.appendChild(t);
  setTimeout(() => t.remove(), 3500);
}

// ── CART ─────────────────────────────────────────────────────
async function addToCart(productId, qty = 1) {
  try {
    const res  = await fetch(`${APP_URL}/api/cart.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'add', product_id: productId, quantity: qty }),
    });
    const data = await res.json();
    if (data.success) {
      showToast('Added to cart!');
      updateCartBadge(data.cart_count);
    } else {
      showToast(data.message || 'Error adding to cart', 'error');
    }
  } catch (e) {
    showToast('Network error. Please try again.', 'error');
  }
}

function updateCartBadge(count) {
  document.querySelectorAll('.cart-badge').forEach(b => b.textContent = count);
}

// ── QTY CONTROLS ─────────────────────────────────────────────
document.addEventListener('click', (e) => {
  if (e.target.classList.contains('qty-minus')) {
    const inp = e.target.closest('.qty-control').querySelector('input');
    if (+inp.value > 1) inp.value = +inp.value - 1;
    inp.dispatchEvent(new Event('change'));
  }
  if (e.target.classList.contains('qty-plus')) {
    const inp = e.target.closest('.qty-control').querySelector('input');
    inp.value = +inp.value + 1;
    inp.dispatchEvent(new Event('change'));
  }
});

// ── CART PAGE – Update qty / Remove ─────────────────────────
async function updateCartQty(cartId, qty) {
  const res  = await fetch(`${APP_URL}/api/cart.php`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'update', cart_id: cartId, quantity: qty }),
  });
  const data = await res.json();
  if (data.success) location.reload();
}

async function removeFromCart(cartId) {
  const res  = await fetch(`${APP_URL}/api/cart.php`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'remove', cart_id: cartId }),
  });
  const data = await res.json();
  if (data.success) location.reload();
}

// ── ADMIN: IMAGE PREVIEW ──────────────────────────────────────
const imgInput = document.getElementById('product_image');
if (imgInput) {
  imgInput.addEventListener('change', function () {
    const preview = document.getElementById('img-preview');
    if (!preview) return;
    const file = this.files[0];
    if (file) {
      preview.src = URL.createObjectURL(file);
      preview.style.display = 'block';
    }
  });
}

// ── ADMIN: Confirm delete ────────────────────────────────────
document.addEventListener('click', (e) => {
  if (e.target.classList.contains('confirm-delete')) {
    if (!confirm('Are you sure you want to delete this item?')) {
      e.preventDefault();
    }
  }
});
