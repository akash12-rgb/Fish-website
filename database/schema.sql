-- ============================================================
--  Sunbis AgroFish - PostgreSQL Database Schema
-- ============================================================

CREATE DATABASE sunbis_agrofish;
\c sunbis_agrofish;

-- Extensions
CREATE EXTENSION IF NOT EXISTS "pgcrypto";

-- ─── CATEGORIES ─────────────────────────────────────────────
CREATE TABLE categories (
    id           SERIAL PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL,
    slug         VARCHAR(120) UNIQUE,
    created_at   TIMESTAMP DEFAULT NOW()
);

-- ─── USERS ──────────────────────────────────────────────────
CREATE TABLE users (
    id         SERIAL PRIMARY KEY,
    name       VARCHAR(120) NOT NULL,
    email      VARCHAR(150) UNIQUE NOT NULL,
    phone      VARCHAR(20),
    password   VARCHAR(255) NOT NULL,
    role       VARCHAR(20) DEFAULT 'customer',  -- customer | admin
    created_at TIMESTAMP DEFAULT NOW()
);

-- ─── PRODUCTS ───────────────────────────────────────────────
CREATE TABLE products (
    id               SERIAL PRIMARY KEY,
    product_name     VARCHAR(200) NOT NULL,
    slug             VARCHAR(220) UNIQUE,
    description      TEXT,
    price            NUMERIC(12,2) NOT NULL,
    stock_quantity   INT DEFAULT 0,
    category_id      INT REFERENCES categories(id) ON DELETE SET NULL,
    image            VARCHAR(300),
    is_featured      BOOLEAN DEFAULT FALSE,
    created_at       TIMESTAMP DEFAULT NOW()
);

-- ─── CART ───────────────────────────────────────────────────
CREATE TABLE cart (
    id         SERIAL PRIMARY KEY,
    user_id    INT REFERENCES users(id) ON DELETE CASCADE,
    product_id INT REFERENCES products(id) ON DELETE CASCADE,
    quantity   INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT NOW(),
    UNIQUE(user_id, product_id)
);

-- ─── ORDERS ─────────────────────────────────────────────────
CREATE TABLE orders (
    id               SERIAL PRIMARY KEY,
    user_id          INT REFERENCES users(id) ON DELETE SET NULL,
    total_amount     NUMERIC(14,2) NOT NULL,
    payment_status   VARCHAR(30) DEFAULT 'pending',  -- pending|success|failed
    order_status     VARCHAR(30) DEFAULT 'pending',  -- pending|processing|shipped|delivered|cancelled
    transaction_id   VARCHAR(100) UNIQUE,
    shipping_name    VARCHAR(150),
    shipping_phone   VARCHAR(20),
    shipping_address TEXT,
    shipping_city    VARCHAR(100),
    shipping_zip     VARCHAR(20),
    notes            TEXT,
    created_at       TIMESTAMP DEFAULT NOW()
);

-- ─── ORDER ITEMS ────────────────────────────────────────────
CREATE TABLE order_items (
    id         SERIAL PRIMARY KEY,
    order_id   INT REFERENCES orders(id) ON DELETE CASCADE,
    product_id INT REFERENCES products(id) ON DELETE SET NULL,
    quantity   INT NOT NULL,
    price      NUMERIC(12,2) NOT NULL
);

-- ─── PAYMENTS ───────────────────────────────────────────────
CREATE TABLE payments (
    id               SERIAL PRIMARY KEY,
    order_id         INT REFERENCES orders(id) ON DELETE CASCADE,
    payment_gateway  VARCHAR(60) DEFAULT 'ICICI_OrangePay',
    transaction_id   VARCHAR(100),
    amount           NUMERIC(14,2),
    payment_status   VARCHAR(30) DEFAULT 'pending',
    gateway_response TEXT,
    created_at       TIMESTAMP DEFAULT NOW()
);

-- ─── SEED DATA ──────────────────────────────────────────────
INSERT INTO categories (category_name, slug) VALUES
  ('Fresh Fish',       'fresh-fish'),
  ('Shrimp & Prawns',  'shrimp-prawns'),
  ('Agriculture',      'agriculture'),
  ('Aquaponics',       'aquaponics'),
  ('Processed Fish',   'processed-fish'),
  ('Seeds & Feeds',    'seeds-feeds');

-- Admin user  (password: Admin@1234  — bcrypt hash)
INSERT INTO users (name, email, phone, password, role) VALUES
  ('Admin Sunbis', 'admin@sunbisagrofish.com', '+62-812-0000-0000',
   '$2y$12$9K6MpHfQXbH.cB2lY8v5.OXONe8g.BsHzEiVFbVr.c8eX5H4jOFRy', 'admin');

INSERT INTO products (product_name, slug, description, price, stock_quantity, category_id, image, is_featured) VALUES
  ('Fresh Tilapia (Nila)', 'fresh-tilapia-nila',
   'Farm-raised Nile tilapia in clean, aerated ponds. Mild flavour, high protein. Sold per kg.',
   35000, 200, 1, 'tilapia.jpg', TRUE),

  ('Catfish (Lele)', 'catfish-lele',
   'Tender, nutrient-rich catfish raised in controlled environments. Soft texture, wide culinary use.',
   28000, 180, 1, 'catfish.jpg', TRUE),

  ('Freshwater Shrimp', 'freshwater-shrimp',
   'Plump, sweet-tasting freshwater shrimp harvested from pristine ponds. Per 500 g pack.',
   55000, 120, 2, 'shrimp.jpg', TRUE),

  ('Milkfish (Bandeng)', 'milkfish-bandeng',
   'Classic Indonesian table fish, rich taste. Raised in brackish ponds. Per kg.',
   42000, 150, 1, 'milkfish.jpg', TRUE),

  ('Organic Rice', 'organic-rice',
   'Paddy rice grown using integrated fish-rice farming. Rich soil, minimal pesticides. 5 kg bag.',
   75000, 300, 3, 'rice.jpg', FALSE),

  ('Aquaponic Kangkung', 'aquaponic-kangkung',
   'Water spinach grown through aquaponics — naturally fertilized by fish water. 250 g pack.',
   12000, 500, 4, 'kangkung.jpg', FALSE),

  ('Fish Pellet Feed (5 kg)', 'fish-pellet-feed',
   'High-protein floating pellets suitable for tilapia, catfish, and milkfish. 5 kg sack.',
   85000, 80, 6, 'pellet.jpg', FALSE),

  ('Smoked Fish (Bandeng Asap)', 'smoked-bandeng',
   'Traditional smoked milkfish, boneless, vacuum-packed. Ready to eat. Pack of 2.',
   65000, 90, 5, 'smoked.jpg', TRUE);

-- Indexes for performance
CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_cart_user        ON cart(user_id);
CREATE INDEX idx_orders_user      ON orders(user_id);
CREATE INDEX idx_order_items_order ON order_items(order_id);
CREATE INDEX idx_payments_order   ON payments(order_id);
