# 🐟 Sunbis AgroFish – Full E-Commerce Website

Complete e-commerce solution for aquaculture and agriculture products.

---

## 📁 Project Structure

```
sunbis/
├── admin/
│   ├── index.php          ← Dashboard
│   ├── products.php       ← Add/Edit/Delete products + image upload
│   ├── categories.php     ← Manage categories
│   ├── orders.php         ← View & update orders
│   └── customers.php      ← View customers
│
├── api/
│   └── cart.php           ← Cart REST API (add/update/remove/get)
│
├── config/
│   ├── database.php       ← PostgreSQL connection + helpers
│   └── payment.php        ← ICICI Orange Pay configuration
│
├── database/
│   └── schema.sql         ← Complete PostgreSQL schema + seed data
│
├── includes/
│   ├── header.php         ← Navbar, top-bar, Bootstrap
│   └── footer.php         ← Footer + Bootstrap JS
│
├── payments/
│   ├── payment_request.php  ← Creates order → redirects to Orange Pay
│   ├── payment_success.php  ← Handles success callback
│   └── payment_failure.php  ← Handles failure/cancel callback
│
├── public/
│   ├── css/style.css      ← All custom CSS (Bootstrap + custom)
│   ├── js/app.js          ← Cart AJAX, toasts, qty controls
│   └── uploads/products/  ← Product images (writable!)
│
├── index.php              ← Home page
├── catalog.php            ← Product catalog + filters + search
├── product.php            ← Product detail page
├── cart.php               ← Shopping cart
├── checkout.php           ← Checkout form
├── orders.php             ← Customer order history
├── login.php              ← Login page
├── register.php           ← Registration page
├── logout.php             ← Session destroy
├── contact.php            ← Contact page
└── .htaccess              ← Apache rules + security
```

---

## ⚙️ Requirements

| Software    | Version     |
|-------------|-------------|
| PHP         | 8.0+        |
| PostgreSQL  | 13+         |
| Apache      | 2.4+ (with mod_rewrite) |
| PHP Extensions | pdo, pdo_pgsql |

---

## 🚀 Setup Instructions

### Step 1 – Clone / Copy Project
Place the `sunbis/` folder in your web root:
```
/var/www/html/sunbis/        (Linux)
C:\xampp\htdocs\sunbis\      (XAMPP Windows)
```

### Step 2 – Create PostgreSQL Database
```bash
psql -U postgres
```
```sql
\i /path/to/sunbis/database/schema.sql
```
Or run the SQL file via pgAdmin.

### Step 3 – Configure Database Connection
Edit `config/database.php`:
```php
define('DB_HOST',     'localhost');
define('DB_PORT',     '5432');
define('DB_NAME',     'sunbis_agrofish');
define('DB_USER',     'postgres');
define('DB_PASSWORD', 'YOUR_PASSWORD');
define('APP_URL',     'http://localhost/sunbis');
```

### Step 4 – Set Upload Permissions
```bash
chmod 775 public/uploads/products/
chown www-data:www-data public/uploads/products/
```

### Step 5 – Configure ICICI Orange Pay
Edit `config/payment.php`:
```php
define('ORANGEPAY_MERCHANT_ID',  'YOUR_MERCHANT_ID');
define('ORANGEPAY_MERCHANT_KEY', 'YOUR_SECRET_KEY');
define('ORANGEPAY_SANDBOX', true);   // false for production
```

Register at: https://orangepay.icicibank.com/merchant

### Step 6 – Enable Apache mod_rewrite
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

In your Apache virtualhost/config, ensure:
```
AllowOverride All
```

### Step 7 – Access the Website
- **Frontend:** http://localhost/sunbis/
- **Admin Panel:** http://localhost/sunbis/admin/

### Default Admin Credentials
| Field    | Value                         |
|----------|-------------------------------|
| Email    | admin@sunbisagrofish.com      |
| Password | Admin@1234                    |

> **Change the admin password immediately** in the database after first login.

---

## 💳 ICICI Orange Pay Integration Flow

```
Customer clicks "Buy Now" or "Checkout"
    ↓
checkout.php  →  Collects shipping details
    ↓
payments/payment_request.php
    →  Creates order in PostgreSQL (status: pending)
    →  Generates SHA256 checksum
    →  Auto-submits HTML form to Orange Pay gateway
    ↓
ICICI Orange Pay processes payment
    ↓
    ├── SUCCESS  →  payment_success.php
    │       → Verifies checksum
    │       → Updates orders.payment_status = 'success'
    │       → Updates orders.order_status = 'processing'
    │       → Updates payments table
    │       → Shows success page
    │
    └── FAILURE  →  payment_failure.php
            → Updates orders.payment_status = 'failed'
            → Updates orders.order_status = 'cancelled'
            → Shows error page with retry option
```

### Checksum Verification Formula
```
SHA256( merchant_id | order_id | amount | currency | merchant_key )
```

---

## 🔒 Security Features

- **CSRF tokens** on all forms
- **Password hashing** with PHP `password_hash()` (bcrypt)
- **PDO prepared statements** – SQL injection prevention
- **Admin role check** on every admin page
- **Login required** for cart, checkout, orders
- **.htaccess** blocks access to `config/` and `includes/`
- **File type validation** for image uploads

---

## 📱 Responsive Design

- Bootstrap 5.3 grid system
- Mobile-first CSS
- Hamburger navigation on mobile
- Responsive admin panel

---

## 🎨 Tech Stack

| Layer     | Technology                          |
|-----------|-------------------------------------|
| Frontend  | HTML5, CSS3, Bootstrap 5.3, Vanilla JS |
| Backend   | PHP 8.0+ (procedural REST-style)    |
| Database  | PostgreSQL 13+                      |
| Payment   | ICICI Bank Orange Pay Gateway       |
| Icons     | Bootstrap Icons 1.11                |
| Fonts     | Google Fonts (Playfair Display + Inter) |

---

## 🌿 Color Theme

| Name     | Hex       |
|----------|-----------|
| Primary  | `#1a7a6e` |
| Secondary| `#2bbfa0` |
| Accent   | `#f0b429` |
| Deep     | `#0a1628` |
| Cream    | `#fdf6ec` |

---

## 📞 Support

**Sunbis AgroFish**  
📍 Jl. Tambak Raya No. 12, Jawa Tengah, Indonesia  
📞 +62 812-3456-7890  
✉️ info@sunbisagrofish.com
