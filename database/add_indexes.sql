-- Performance indexes for EffaFashion
USE effafashion;

-- Products
ALTER TABLE products
    ADD INDEX IF NOT EXISTS idx_active        (is_active),
    ADD INDEX IF NOT EXISTS idx_featured      (is_featured),
    ADD INDEX IF NOT EXISTS idx_category      (category_id),
    ADD INDEX IF NOT EXISTS idx_created       (created_at),
    ADD INDEX IF NOT EXISTS idx_price         (price),
    ADD INDEX IF NOT EXISTS idx_active_feat   (is_active, is_featured),
    ADD INDEX IF NOT EXISTS idx_active_cat    (is_active, category_id);

-- Orders
ALTER TABLE orders
    ADD INDEX IF NOT EXISTS idx_user_id       (user_id),
    ADD INDEX IF NOT EXISTS idx_status        (status),
    ADD INDEX IF NOT EXISTS idx_created       (created_at),
    ADD INDEX IF NOT EXISTS idx_user_status   (user_id, status);

-- Cart
ALTER TABLE cart
    ADD INDEX IF NOT EXISTS idx_user_id       (user_id),
    ADD INDEX IF NOT EXISTS idx_user_product  (user_id, product_id);

-- Wishlist
ALTER TABLE wishlist
    ADD INDEX IF NOT EXISTS idx_user_id       (user_id);

-- Reviews
ALTER TABLE reviews
    ADD INDEX IF NOT EXISTS idx_product       (product_id),
    ADD INDEX IF NOT EXISTS idx_approved      (product_id, is_approved);

-- Order items
ALTER TABLE order_items
    ADD INDEX IF NOT EXISTS idx_order_id      (order_id),
    ADD INDEX IF NOT EXISTS idx_product_id    (product_id);
