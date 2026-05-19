-- EffaFashion Database Schema
-- Created for XAMPP / MySQL

CREATE DATABASE IF NOT EXISTS effafashion CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE effafashion;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(100),
    country VARCHAR(100) DEFAULT 'Nigeria',
    role ENUM('customer', 'admin') DEFAULT 'customer',
    profile_image VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Categories Table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    image VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products Table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    sale_price DECIMAL(10,2) DEFAULT NULL,
    stock INT DEFAULT 0,
    image VARCHAR(255),
    images TEXT COMMENT 'JSON array of additional images',
    sizes VARCHAR(255) COMMENT 'JSON array of available sizes',
    colors VARCHAR(255) COMMENT 'JSON array of available colors',
    is_featured TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Orders Table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_number VARCHAR(50) NOT NULL UNIQUE,
    total_amount DECIMAL(10,2) NOT NULL,
    shipping_amount DECIMAL(10,2) DEFAULT 0.00,
    discount_amount DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('pending','processing','shipped','delivered','cancelled','refunded') DEFAULT 'pending',
    payment_method VARCHAR(50) DEFAULT 'cash_on_delivery',
    payment_status ENUM('unpaid','paid','refunded') DEFAULT 'unpaid',
    shipping_name VARCHAR(100),
    shipping_email VARCHAR(150),
    shipping_phone VARCHAR(20),
    shipping_address TEXT,
    shipping_city VARCHAR(100),
    shipping_country VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Order Items Table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(200) NOT NULL,
    product_image VARCHAR(255),
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    size VARCHAR(20),
    color VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Cart Table
CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    size VARCHAR(20),
    color VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Wishlist Table
CREATE TABLE IF NOT EXISTS wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_wishlist (user_id, product_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Reviews Table
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    title VARCHAR(200),
    comment TEXT,
    is_approved TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Coupons Table
CREATE TABLE IF NOT EXISTS coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    discount_type ENUM('percentage','fixed') DEFAULT 'percentage',
    discount_value DECIMAL(10,2) NOT NULL,
    min_order DECIMAL(10,2) DEFAULT 0,
    max_uses INT DEFAULT NULL,
    used_count INT DEFAULT 0,
    expires_at DATE DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Newsletter Subscribers
CREATE TABLE IF NOT EXISTS newsletter (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL UNIQUE,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Contact Messages
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    subject VARCHAR(200),
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================
-- SEED DATA
-- =====================

-- Admin User (password: admin123)
INSERT INTO users (full_name, email, password, role) VALUES
('Admin EffaFashion', 'admin@effafashion.com', '$2y$12$IDEplQFZ6Zq.TKid56bOGuh4u/qWw.PLlfK2RUbFezc4KiWds.3Cm', 'admin');

-- Categories
INSERT INTO categories (name, slug, description) VALUES
('Women', 'women', 'Elegant women fashion collection'),
('Men', 'men', 'Premium men fashion collection'),
('Accessories', 'accessories', 'Luxury fashion accessories'),
('New Arrivals', 'new-arrivals', 'Latest fashion arrivals'),
('Sale', 'sale', 'Discounted fashion items');

-- Sample Products
INSERT INTO products (category_id, name, slug, description, price, sale_price, stock, image, sizes, colors, is_featured) VALUES
(1, 'Elegant Gold Dress', 'elegant-gold-dress', 'A stunning gold evening dress perfect for special occasions. Made with premium fabric.', 45000.00, 38000.00, 15, 'dress1.jpg', '["XS","S","M","L","XL"]', '["Gold","Black"]', 1),
(1, 'Black Luxury Gown', 'black-luxury-gown', 'Sophisticated black gown with gold accents. Perfect for formal events.', 65000.00, NULL, 8, 'gown1.jpg', '["S","M","L","XL","XXL"]', '["Black","Navy"]', 1),
(1, 'White Chiffon Blouse', 'white-chiffon-blouse', 'Lightweight white chiffon blouse with elegant draping.', 12000.00, 9500.00, 25, 'blouse1.jpg', '["XS","S","M","L"]', '["White","Cream"]', 0),
(2, 'Classic Black Suit', 'classic-black-suit', 'Premium tailored black suit for the modern gentleman.', 85000.00, NULL, 10, 'suit1.jpg', '["S","M","L","XL","XXL"]', '["Black","Charcoal"]', 1),
(2, 'Gold Trim Blazer', 'gold-trim-blazer', 'Stylish blazer with gold button accents. A statement piece.', 35000.00, 28000.00, 12, 'blazer1.jpg', '["S","M","L","XL"]', '["Black","Navy"]', 1),
(3, 'Gold Chain Necklace', 'gold-chain-necklace', '18K gold plated chain necklace. Timeless elegance.', 8500.00, NULL, 50, 'necklace1.jpg', NULL, '["Gold","Silver"]', 1),
(3, 'Leather Handbag', 'leather-handbag', 'Premium genuine leather handbag with gold hardware.', 25000.00, 20000.00, 20, 'bag1.jpg', NULL, '["Black","Brown","Tan"]', 1),
(4, 'Sequin Mini Dress', 'sequin-mini-dress', 'Glamorous sequin mini dress for nights out.', 28000.00, NULL, 18, 'dress2.jpg', '["XS","S","M","L"]', '["Gold","Silver","Black"]', 1);

-- Sample Coupon
INSERT INTO coupons (code, discount_type, discount_value, min_order) VALUES
('EFFA10', 'percentage', 10.00, 5000.00),
('WELCOME20', 'percentage', 20.00, 10000.00),
('SAVE5000', 'fixed', 5000.00, 30000.00);
