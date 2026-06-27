-- EffaFashion PostgreSQL Schema

CREATE TABLE IF NOT EXISTS users (
    id             SERIAL PRIMARY KEY,
    full_name      VARCHAR(100) NOT NULL,
    email          VARCHAR(150) NOT NULL UNIQUE,
    password       VARCHAR(255) NOT NULL,
    phone          VARCHAR(20),
    address        TEXT,
    city           VARCHAR(100),
    country        VARCHAR(100) DEFAULT 'Ethiopia',
    role           VARCHAR(20)  DEFAULT 'customer',
    profile_image  VARCHAR(255),
    is_active      BOOLEAN      DEFAULT true,
    created_at     TIMESTAMP    DEFAULT NOW(),
    updated_at     TIMESTAMP    DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS categories (
    id          SERIAL PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    slug        VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    image       VARCHAR(255),
    is_active   BOOLEAN   DEFAULT true,
    created_at  TIMESTAMP DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS products (
    id          SERIAL PRIMARY KEY,
    category_id INT          NOT NULL REFERENCES categories(id) ON DELETE CASCADE,
    name        VARCHAR(200) NOT NULL,
    slug        VARCHAR(200) NOT NULL UNIQUE,
    description TEXT,
    price       NUMERIC(10,2) NOT NULL,
    sale_price  NUMERIC(10,2),
    stock       INT           DEFAULT 0,
    image       VARCHAR(255),
    images      TEXT,
    sizes       TEXT,
    colors      TEXT,
    is_featured BOOLEAN   DEFAULT false,
    is_active   BOOLEAN   DEFAULT true,
    views       INT       DEFAULT 0,
    created_at  TIMESTAMP DEFAULT NOW(),
    updated_at  TIMESTAMP DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS orders (
    id               SERIAL PRIMARY KEY,
    user_id          INT          NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    order_number     VARCHAR(50)  NOT NULL UNIQUE,
    total_amount     NUMERIC(10,2) NOT NULL,
    shipping_amount  NUMERIC(10,2) DEFAULT 0,
    discount_amount  NUMERIC(10,2) DEFAULT 0,
    status           VARCHAR(20)  DEFAULT 'pending',
    payment_method   VARCHAR(50)  DEFAULT 'bank_transfer',
    payment_status   VARCHAR(30)  DEFAULT 'unpaid',
    payment_screenshot VARCHAR(255),
    shipping_name    VARCHAR(100),
    shipping_email   VARCHAR(150),
    shipping_phone   VARCHAR(20),
    shipping_address TEXT,
    shipping_city    VARCHAR(100),
    shipping_country VARCHAR(100),
    notes            TEXT,
    created_at       TIMESTAMP DEFAULT NOW(),
    updated_at       TIMESTAMP DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS order_items (
    id            SERIAL PRIMARY KEY,
    order_id      INT NOT NULL REFERENCES orders(id) ON DELETE CASCADE,
    product_id    INT NOT NULL REFERENCES products(id) ON DELETE CASCADE,
    product_name  VARCHAR(200) NOT NULL,
    product_image VARCHAR(255),
    quantity      INT          NOT NULL,
    price         NUMERIC(10,2) NOT NULL,
    size          VARCHAR(20),
    color         VARCHAR(50),
    created_at    TIMESTAMP DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS cart (
    id         SERIAL PRIMARY KEY,
    user_id    INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    product_id INT NOT NULL REFERENCES products(id) ON DELETE CASCADE,
    quantity   INT DEFAULT 1,
    size       VARCHAR(20),
    color      VARCHAR(50),
    created_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS wishlist (
    id         SERIAL PRIMARY KEY,
    user_id    INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    product_id INT NOT NULL REFERENCES products(id) ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT NOW(),
    UNIQUE(user_id, product_id)
);

CREATE TABLE IF NOT EXISTS reviews (
    id         SERIAL PRIMARY KEY,
    product_id INT NOT NULL REFERENCES products(id) ON DELETE CASCADE,
    user_id    INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    rating     SMALLINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    title      VARCHAR(200),
    comment    TEXT,
    is_approved BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS coupons (
    id             SERIAL PRIMARY KEY,
    code           VARCHAR(50) NOT NULL UNIQUE,
    discount_type  VARCHAR(20) DEFAULT 'percentage',
    discount_value NUMERIC(10,2) NOT NULL,
    min_order      NUMERIC(10,2) DEFAULT 0,
    max_uses       INT,
    used_count     INT       DEFAULT 0,
    expires_at     DATE,
    is_active      BOOLEAN   DEFAULT true,
    created_at     TIMESTAMP DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS newsletter (
    id         SERIAL PRIMARY KEY,
    email      VARCHAR(150) NOT NULL UNIQUE,
    is_active  BOOLEAN   DEFAULT true,
    created_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS contact_messages (
    id         SERIAL PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    email      VARCHAR(150) NOT NULL,
    subject    VARCHAR(200),
    message    TEXT NOT NULL,
    is_read    BOOLEAN   DEFAULT false,
    created_at TIMESTAMP DEFAULT NOW()
);

-- Seed admin (password: admin123)
INSERT INTO users (full_name, email, password, role)
VALUES ('Admin EffaFashion', 'admin@effafashion.com',
        '$2b$12$IDEplQFZ6Zq.TKid56bOGuh4u/qWw.PLlfK2RUbFezc4KiWds.3Cm', 'admin')
ON CONFLICT (email) DO NOTHING;

-- Seed categories
INSERT INTO categories (name, slug, description) VALUES
  ('Women',       'women',       'Elegant women fashion collection'),
  ('Men',         'men',         'Premium men fashion collection'),
  ('Accessories', 'accessories', 'Luxury fashion accessories'),
  ('New Arrivals','new-arrivals','Latest fashion arrivals'),
  ('Sale',        'sale',        'Discounted fashion items')
ON CONFLICT (slug) DO NOTHING;

-- Seed coupons
INSERT INTO coupons (code, discount_type, discount_value, min_order) VALUES
  ('EFFA10',   'percentage', 10.00, 5000.00),
  ('WELCOME20','percentage', 20.00, 10000.00),
  ('SAVE5000', 'fixed',      5000.00, 30000.00)
ON CONFLICT (code) DO NOTHING;
