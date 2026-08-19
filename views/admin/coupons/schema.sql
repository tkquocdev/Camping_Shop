-- ============================================================================
-- CAMPING SHOP DATABASE SCHEMA - PostgreSQL 12+
-- ============================================================================
-- Import Command (Linux/Mac):
--   psql -U postgres -d camping_shop -f schema.sql
--
-- Import Command (Windows):
--   1. Open PgAdmin 4
--   2. Right-click database → Query Tool
--   3. Copy-paste entire content of this file
--   4. Execute (F5 or Run)
--
-- Or via command line:
--   psql -U postgres -h localhost -d camping_shop < schema.sql
-- ============================================================================

BEGIN;

-- ============================================================================
-- DROP OLD TABLES (RESET DATABASE)
-- ============================================================================
DROP TABLE IF EXISTS cart_items CASCADE;
DROP TABLE IF EXISTS notification_reads CASCADE;
DROP TABLE IF EXISTS notifications CASCADE;
DROP TABLE IF EXISTS customer_care_logs CASCADE;
DROP TABLE IF EXISTS point_history CASCADE;
DROP TABLE IF EXISTS loyalty_rewards CASCADE;
DROP TABLE IF EXISTS lucky_history CASCADE;
DROP TABLE IF EXISTS lucky_prizes CASCADE;
DROP TABLE IF EXISTS news CASCADE;
DROP TABLE IF EXISTS password_resets CASCADE;
DROP TABLE IF EXISTS reviews CASCADE;
DROP TABLE IF EXISTS order_items CASCADE;
DROP TABLE IF EXISTS orders CASCADE;
DROP TABLE IF EXISTS flash_sale_items CASCADE;
DROP TABLE IF EXISTS flash_sales CASCADE;
DROP TABLE IF EXISTS stock_issue_details CASCADE;
DROP TABLE IF EXISTS stock_issues CASCADE;
DROP TABLE IF EXISTS stock_import_items CASCADE;
DROP TABLE IF EXISTS stock_imports CASCADE;
DROP TABLE IF EXISTS user_addresses CASCADE;
DROP TABLE IF EXISTS suppliers CASCADE;
DROP TABLE IF EXISTS coupons CASCADE;
DROP TABLE IF EXISTS products CASCADE;
DROP TABLE IF EXISTS categories CASCADE;
DROP TABLE IF EXISTS settings CASCADE;
DROP TABLE IF EXISTS users CASCADE;

-- ============================================================================
-- 1. USERS TABLE - Người dùng & Admin
-- ============================================================================
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(100) UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    phone VARCHAR(20),
    address TEXT,
    role VARCHAR(20) DEFAULT 'customer' CHECK (role IN ('admin', 'staff', 'customer')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    avatar VARCHAR(255),
    status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'banned', 'inactive')),
    points INTEGER DEFAULT 0,
    google_id VARCHAR(255)
);

CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_role ON users(role);

-- ============================================================================
-- 2. CATEGORIES TABLE - Danh mục sản phẩm
-- ============================================================================
CREATE TABLE categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================================
-- 3. PRODUCTS TABLE - Sản phẩm
-- ============================================================================
CREATE TABLE products (
    id SERIAL PRIMARY KEY,
    category_id INTEGER REFERENCES categories(id) ON DELETE SET NULL,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price NUMERIC(10, 2) NOT NULL CHECK (price >= 0),
    stock INTEGER DEFAULT 0 CHECK (stock >= 0),
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_products_name ON products(name);

-- ============================================================================
-- 4. COUPONS TABLE - Mã giảm giá
-- ============================================================================
CREATE TABLE coupons (
    id SERIAL PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(255),
    discount_type VARCHAR(20) NOT NULL CHECK (discount_type IN ('fixed', 'amount')),
    discount_value NUMERIC(10, 2) NOT NULL,
    min_order_value NUMERIC(10, 2) DEFAULT 0,
    quantity INTEGER DEFAULT 0,
    start_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expiration_date TIMESTAMP,
    status INTEGER DEFAULT 1,
    is_private INTEGER DEFAULT 0,
    points_required INTEGER DEFAULT 0,
    user_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_coupons_code ON coupons(code);
CREATE INDEX idx_coupons_status ON coupons(status);

-- Fix lỗi không tạo được mã giảm (%)
ALTER TABLE coupons DROP CONSTRAINT IF EXISTS coupons_discount_type_check;
UPDATE coupons SET discount_type = 'amount' WHERE discount_type = 'percent';
ALTER TABLE coupons ADD CONSTRAINT coupons_discount_type_check CHECK (discount_type IN ('fixed', 'amount'));

-- ============================================================================
-- 5. ORDERS TABLE - Đơn hàng
-- ============================================================================
CREATE TABLE orders (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    total_amount NUMERIC(10, 2) NOT NULL,
    status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending', 'processing', 'shipped', 'delivered', 'completed', 'cancelled', 'returned')),
    shipping_address TEXT NOT NULL,
    phone VARCHAR(20) NOT NULL,
    note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    payment_method VARCHAR(50) DEFAULT 'cod' CHECK (payment_method IN ('cod', 'banking', 'momo')),
    discount_amount NUMERIC(10, 2) DEFAULT 0,
    shipping_fee NUMERIC(10, 2) DEFAULT 0,
    coupon_code VARCHAR(50),
    return_reason VARCHAR(255),
    return_description TEXT,
    is_rewarded SMALLINT DEFAULT 0,
    completed_at TIMESTAMP
);

CREATE INDEX idx_orders_user_id ON orders(user_id);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_orders_created_at ON orders(created_at);

-- Drop the old constraint
ALTER TABLE orders DROP CONSTRAINT orders_status_check;

-- Add the updated constraint that includes 'completed'
ALTER TABLE orders ADD CONSTRAINT orders_status_check CHECK (status IN ('pending', 'processing', 'shipped', 'delivered', 'completed', 'cancelled', 'returned'));
-- ============================================================================
-- 6. ORDER_ITEMS TABLE - Chi tiết đơn hàng
-- ============================================================================
CREATE TABLE order_items (
    id SERIAL PRIMARY KEY,
    order_id INTEGER NOT NULL REFERENCES orders(id) ON DELETE CASCADE,
    product_id INTEGER REFERENCES products(id) ON DELETE SET NULL,
    quantity INTEGER NOT NULL,
    price NUMERIC(10, 2) NOT NULL
);

CREATE INDEX idx_order_items_order_id ON order_items(order_id);
CREATE INDEX idx_order_items_product_id ON order_items(product_id);

-- ============================================================================
-- 7. CART_ITEMS TABLE - Giỏ hàng
-- ============================================================================
CREATE TABLE cart_items (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    product_id INTEGER NOT NULL REFERENCES products(id) ON DELETE CASCADE,
    quantity INTEGER DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT unique_user_product UNIQUE (user_id, product_id)
);

CREATE INDEX idx_cart_user ON cart_items(user_id);

-- ============================================================================
-- 8. REVIEWS TABLE - Đánh giá sản phẩm
-- ============================================================================
CREATE TABLE reviews (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    product_id INTEGER REFERENCES products(id) ON DELETE CASCADE,
    rating INTEGER CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewer_name VARCHAR(255),
    reviewer_avatar VARCHAR(255)
);

CREATE INDEX idx_reviews_product_id ON reviews(product_id);
CREATE INDEX idx_reviews_user_id ON reviews(user_id);

-- ============================================================================
-- 9. FLASH_SALES TABLE - Flash Sale
-- ============================================================================
CREATE TABLE flash_sales (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    start_time TIMESTAMP NOT NULL,
    end_time TIMESTAMP NOT NULL,
    status INTEGER DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP
);

-- ============================================================================
-- 10. FLASH_SALE_ITEMS TABLE - Chi tiết Flash Sale
-- ============================================================================
CREATE TABLE flash_sale_items (
    id SERIAL PRIMARY KEY,
    flash_sale_id INTEGER REFERENCES flash_sales(id) ON DELETE CASCADE,
    product_id INTEGER REFERENCES products(id) ON DELETE CASCADE,
    sale_price NUMERIC(10, 2) NOT NULL,
    quantity INTEGER NOT NULL DEFAULT 10,
    sold INTEGER DEFAULT 0
);

CREATE INDEX idx_flash_sale_items_sale ON flash_sale_items(flash_sale_id);
CREATE INDEX idx_flash_sale_items_product ON flash_sale_items(product_id);

-- ============================================================================
-- 11. SUPPLIERS TABLE - Nhà cung cấp
-- ============================================================================
CREATE TABLE suppliers (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================================
-- 12. STOCK_IMPORTS TABLE - Nhập kho
-- ============================================================================
CREATE TABLE stock_imports (
    id SERIAL PRIMARY KEY,
    supplier_id INTEGER REFERENCES suppliers(id) ON DELETE NO ACTION,
    user_id INTEGER REFERENCES users(id) ON DELETE NO ACTION,
    total_amount NUMERIC(15, 2) DEFAULT 0,
    note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================================
-- 13. STOCK_IMPORT_ITEMS TABLE - Chi tiết nhập kho
-- ============================================================================
CREATE TABLE stock_import_items (
    id SERIAL PRIMARY KEY,
    import_id INTEGER REFERENCES stock_imports(id) ON DELETE CASCADE,
    product_id INTEGER REFERENCES products(id) ON DELETE NO ACTION,
    quantity INTEGER NOT NULL,
    import_price NUMERIC(15, 2) NOT NULL,
    total NUMERIC(15, 2) NOT NULL
);

-- ============================================================================
-- 14. STOCK_ISSUES TABLE - Xuất kho
-- ============================================================================
CREATE TABLE stock_issues (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE NO ACTION,
    note TEXT,
    total_amount NUMERIC(15, 2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================================
-- 15. STOCK_ISSUE_DETAILS TABLE - Chi tiết xuất kho
-- ============================================================================
CREATE TABLE stock_issue_details (
    id SERIAL PRIMARY KEY,
    issue_id INTEGER NOT NULL REFERENCES stock_issues(id) ON DELETE CASCADE,
    product_id INTEGER NOT NULL REFERENCES products(id) ON DELETE NO ACTION,
    quantity INTEGER NOT NULL,
    price NUMERIC(15, 2) DEFAULT 0,
    total NUMERIC(15, 2) GENERATED ALWAYS AS (quantity::numeric * price) STORED
);

-- ============================================================================
-- 16. NEWS TABLE - Tin tức
-- ============================================================================
CREATE TABLE news (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    summary TEXT,
    content TEXT,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================================
-- 17. NOTIFICATIONS TABLE - Thông báo
-- ============================================================================
CREATE TABLE notifications (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    message TEXT,
    link VARCHAR(255) DEFAULT '#',
    type VARCHAR(50) DEFAULT 'system',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_notifications_user_id ON notifications(user_id);

-- ============================================================================
-- 18. NOTIFICATION_READS TABLE - Đã đọc thông báo
-- ============================================================================
CREATE TABLE notification_reads (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    notification_id INTEGER NOT NULL REFERENCES notifications(id) ON DELETE CASCADE,
    read_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT unique_user_notification UNIQUE (user_id, notification_id)
);

CREATE INDEX idx_reads_user_id ON notification_reads(user_id);

-- ============================================================================
-- 19. LUCKY_PRIZES TABLE - Giải thưởng vòng quay
-- ============================================================================
CREATE TABLE lucky_prizes (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    coupon_code VARCHAR(50),
    percent INTEGER DEFAULT 0,
    color VARCHAR(20) DEFAULT '#ffffff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================================
-- 20. LUCKY_HISTORY TABLE - Lịch sử vòng quay
-- ============================================================================
CREATE TABLE lucky_history (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    prize_id INTEGER NOT NULL REFERENCES lucky_prizes(id) ON DELETE NO ACTION,
    prize_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================================
-- 21. LOYALTY_REWARDS TABLE - Phần thưởng thành viên thân thiết
-- ============================================================================
CREATE TABLE loyalty_rewards (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    points_required INTEGER NOT NULL,
    voucher_value NUMERIC(10, 2) NOT NULL,
    status INTEGER DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================================
-- 22. POINT_HISTORY TABLE - Lịch sử điểm
-- ============================================================================
CREATE TABLE point_history (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    amount INTEGER NOT NULL,
    type VARCHAR(50),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================================
-- 23. PASSWORD_RESETS TABLE - Reset mật khẩu
-- ============================================================================
CREATE TABLE password_resets (
    email VARCHAR(100) NOT NULL,
    token VARCHAR(10) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================================
-- 24. USER_ADDRESSES TABLE - Địa chỉ người dùng
-- ============================================================================
CREATE TABLE user_addresses (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    recipient_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT NOT NULL,
    is_default BOOLEAN DEFAULT false,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_user_addresses_user_id ON user_addresses(user_id);

-- ============================================================================
-- 25. CUSTOMER_CARE_LOGS TABLE - Log hỗ trợ khách hàng
-- ============================================================================
CREATE TABLE customer_care_logs (
    id SERIAL PRIMARY KEY,
    customer_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    staff_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    name VARCHAR(255),
    email VARCHAR(100),
    phone VARCHAR(20),
    interaction_type VARCHAR(50) DEFAULT 'Tuvan',
    content TEXT,
    status VARCHAR(50) DEFAULT 'Pending',
    type VARCHAR(50) DEFAULT 'System',
    ticket_id VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP
);

-- ============================================================================
-- 26. SETTINGS TABLE - Cấu hình hệ thống
-- ============================================================================
CREATE TABLE settings (
    key VARCHAR(50) PRIMARY KEY,
    value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================================
-- COMMIT TRANSACTION
-- ============================================================================
COMMIT;

INSERT INTO coupons (code, name, discount_type, discount_value, min_order_value, expiration_date, quantity, is_private) VALUES 
('SALE50K', 'Giảm 50k đơn từ 200k', 'fixed', 50000, 200000, '2025-12-31 23:59:59', 100, 0),
('CHAOHE', 'Chào hè giảm 10%', 'percent', 10, 0, '2025-12-31 23:59:59', 100, 0),
('LUCKY10', 'Voucher may mắn 10K', 'fixed', 10000, 0, '2030-12-31 23:59:59', 1000, 1),
('LUCKY20', 'Voucher may mắn 20K', 'fixed', 20000, 0, '2030-12-31 23:59:59', 1000, 1),
('FREESHIP', 'Freeship may mắn', 'fixed', 30000, 150000, '2030-12-31 23:59:59', 500, 1),
('BIGSALE50', 'Giải đặc biệt giảm 50%', 'percent', 50, 0, '2030-12-31 23:59:59', 10, 1);
-- Suppliers
INSERT INTO suppliers (name, phone, email, address) VALUES 
('TKQuocDEV Outdoor Gear', '0868285284', 'contact@tkquocdev.com', 'Tòa nhà TKQuoc, Q.1, TP.HCM'),
('Kho Tổng Logistics', '0988888888', 'logistics@tkquocdev.vn', 'Khu công nghiệp, TP.Cần Thơ');

-- Lucky Prizes
INSERT INTO lucky_prizes (name, coupon_code, percent, color) VALUES 
('Voucher 10K', 'LUCKY10', 40, '#FFD700'),  
('Voucher 20K', 'LUCKY20', 20, '#FF6B6B'),  
('Chúc may mắn', NULL, 30, '#A9A9A9'),      
('Freeship', 'FREESHIP', 9, '#4ECDC4'),     
('Giảm 50%', 'BIGSALE50', 1, '#FF0000');    

-- Loyalty Rewards
INSERT INTO loyalty_rewards (name, points_required, voucher_value) VALUES 
('Voucher 50.000đ', 50, 50000),
('Voucher 100.000đ', 90, 100000);
