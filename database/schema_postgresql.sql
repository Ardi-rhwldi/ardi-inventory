-- ============================================
-- SKEMA DATABASE PostgreSQL untuk Sistem POS & Inventori
-- (Adaptasi dari MySQL untuk Replit environment)
-- ============================================

-- Drop existing tables if needed
DROP TABLE IF EXISTS stock_movements CASCADE;
DROP TABLE IF EXISTS sale_details CASCADE;
DROP TABLE IF EXISTS sales_transactions CASCADE;
DROP TABLE IF EXISTS purchase_details CASCADE;
DROP TABLE IF EXISTS purchase_transactions CASCADE;
DROP TABLE IF EXISTS items CASCADE;
DROP TABLE IF EXISTS customers CASCADE;
DROP TABLE IF EXISTS suppliers CASCADE;
DROP TABLE IF EXISTS categories CASCADE;
DROP TABLE IF EXISTS users CASCADE;

-- ============================================
-- TABEL: users
-- ============================================
CREATE TABLE users (
    user_id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    role VARCHAR(20) DEFAULT 'kasir' CHECK (role IN ('admin', 'kasir', 'manajer')),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_role ON users(role);

-- ============================================
-- TABEL: categories
-- ============================================
CREATE TABLE categories (
    category_id SERIAL PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_categories_name ON categories(category_name);

-- ============================================
-- TABEL: suppliers
-- ============================================
CREATE TABLE suppliers (
    supplier_id SERIAL PRIMARY KEY,
    supplier_name VARCHAR(150) NOT NULL,
    contact_person VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    city VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_suppliers_name ON suppliers(supplier_name);

-- ============================================
-- TABEL: customers
-- ============================================
CREATE TABLE customers (
    customer_id SERIAL PRIMARY KEY,
    customer_name VARCHAR(150) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    city VARCHAR(100),
    points INTEGER DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_customers_name ON customers(customer_name);
CREATE INDEX idx_customers_phone ON customers(phone);

-- ============================================
-- TABEL: items
-- ============================================
CREATE TABLE items (
    item_id SERIAL PRIMARY KEY,
    sku VARCHAR(50) UNIQUE NOT NULL,
    barcode VARCHAR(100) UNIQUE,
    item_name VARCHAR(200) NOT NULL,
    category_id INTEGER REFERENCES categories(category_id) ON DELETE SET NULL,
    unit VARCHAR(20) DEFAULT 'pcs',
    purchase_price DECIMAL(15,2) DEFAULT 0,
    selling_price DECIMAL(15,2) NOT NULL,
    stock_quantity INTEGER DEFAULT 0,
    min_stock INTEGER DEFAULT 10,
    description TEXT,
    image_url VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_items_sku ON items(sku);
CREATE INDEX idx_items_barcode ON items(barcode);
CREATE INDEX idx_items_name ON items(item_name);
CREATE INDEX idx_items_category ON items(category_id);
CREATE INDEX idx_items_stock ON items(stock_quantity);

-- ============================================
-- TABEL: purchase_transactions
-- ============================================
CREATE TABLE purchase_transactions (
    purchase_id SERIAL PRIMARY KEY,
    purchase_number VARCHAR(50) UNIQUE NOT NULL,
    supplier_id INTEGER REFERENCES suppliers(supplier_id) ON DELETE SET NULL,
    purchase_date DATE NOT NULL,
    total_amount DECIMAL(15,2) DEFAULT 0,
    notes TEXT,
    created_by INTEGER REFERENCES users(user_id) ON DELETE SET NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_purchase_number ON purchase_transactions(purchase_number);
CREATE INDEX idx_purchase_date ON purchase_transactions(purchase_date);
CREATE INDEX idx_purchase_supplier ON purchase_transactions(supplier_id);

-- ============================================
-- TABEL: purchase_details
-- ============================================
CREATE TABLE purchase_details (
    detail_id SERIAL PRIMARY KEY,
    purchase_id INTEGER NOT NULL REFERENCES purchase_transactions(purchase_id) ON DELETE CASCADE,
    item_id INTEGER NOT NULL REFERENCES items(item_id) ON DELETE RESTRICT,
    quantity INTEGER NOT NULL,
    purchase_price DECIMAL(15,2) NOT NULL,
    subtotal DECIMAL(15,2) NOT NULL
);

CREATE INDEX idx_purchase_details_purchase ON purchase_details(purchase_id);
CREATE INDEX idx_purchase_details_item ON purchase_details(item_id);

-- ============================================
-- TABEL: sales_transactions
-- ============================================
CREATE TABLE sales_transactions (
    sale_id SERIAL PRIMARY KEY,
    sale_number VARCHAR(50) UNIQUE NOT NULL,
    customer_id INTEGER REFERENCES customers(customer_id) ON DELETE SET NULL,
    sale_date TIMESTAMP NOT NULL,
    subtotal DECIMAL(15,2) DEFAULT 0,
    discount_percent DECIMAL(5,2) DEFAULT 0,
    discount_amount DECIMAL(15,2) DEFAULT 0,
    tax_percent DECIMAL(5,2) DEFAULT 0,
    tax_amount DECIMAL(15,2) DEFAULT 0,
    total_amount DECIMAL(15,2) NOT NULL,
    payment_amount DECIMAL(15,2) NOT NULL,
    change_amount DECIMAL(15,2) DEFAULT 0,
    payment_method VARCHAR(20) DEFAULT 'cash' CHECK (payment_method IN ('cash', 'debit', 'credit', 'qris')),
    notes TEXT,
    created_by INTEGER REFERENCES users(user_id) ON DELETE SET NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_sale_number ON sales_transactions(sale_number);
CREATE INDEX idx_sale_date ON sales_transactions(sale_date);
CREATE INDEX idx_sale_customer ON sales_transactions(customer_id);
CREATE INDEX idx_sale_created_by ON sales_transactions(created_by);

-- ============================================
-- TABEL: sale_details
-- ============================================
CREATE TABLE sale_details (
    detail_id SERIAL PRIMARY KEY,
    sale_id INTEGER NOT NULL REFERENCES sales_transactions(sale_id) ON DELETE CASCADE,
    item_id INTEGER NOT NULL REFERENCES items(item_id) ON DELETE RESTRICT,
    quantity INTEGER NOT NULL,
    selling_price DECIMAL(15,2) NOT NULL,
    purchase_price DECIMAL(15,2) DEFAULT 0,
    subtotal DECIMAL(15,2) NOT NULL,
    profit DECIMAL(15,2) DEFAULT 0
);

CREATE INDEX idx_sale_details_sale ON sale_details(sale_id);
CREATE INDEX idx_sale_details_item ON sale_details(item_id);

-- ============================================
-- TABEL: stock_movements
-- ============================================
CREATE TABLE stock_movements (
    movement_id SERIAL PRIMARY KEY,
    item_id INTEGER NOT NULL REFERENCES items(item_id) ON DELETE CASCADE,
    movement_type VARCHAR(20) NOT NULL CHECK (movement_type IN ('purchase', 'sale', 'adjustment', 'return')),
    reference_id INTEGER,
    quantity_before INTEGER NOT NULL,
    quantity_change INTEGER NOT NULL,
    quantity_after INTEGER NOT NULL,
    notes TEXT,
    created_by INTEGER REFERENCES users(user_id) ON DELETE SET NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_stock_movements_item ON stock_movements(item_id);
CREATE INDEX idx_stock_movements_type ON stock_movements(movement_type);
CREATE INDEX idx_stock_movements_created ON stock_movements(created_at);

-- ============================================
-- INSERT DATA AWAL
-- ============================================

-- User default (password: admin123)
INSERT INTO users (username, password, full_name, email, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@pos.com', 'admin'),
('kasir1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Kasir Satu', 'kasir1@pos.com', 'kasir');

-- Kategori default
INSERT INTO categories (category_name, description) VALUES
('Makanan', 'Produk makanan dan minuman'),
('Elektronik', 'Perangkat elektronik dan aksesoris'),
('Pakaian', 'Pakaian dan aksesoris fashion'),
('Alat Tulis', 'Perlengkapan kantor dan sekolah'),
('Kesehatan', 'Produk kesehatan dan kecantikan');

-- Supplier default
INSERT INTO suppliers (supplier_name, contact_person, phone, address, city) VALUES
('PT Maju Jaya', 'Budi Santoso', '081234567890', 'Jl. Sudirman No. 123', 'Jakarta'),
('CV Sukses Sejahtera', 'Ani Wijaya', '081234567891', 'Jl. Thamrin No. 45', 'Bandung');

-- Customer default
INSERT INTO customers (customer_name, phone, address, city) VALUES
('Pelanggan Umum', '-', '-', '-'),
('Toko Berkah', '081234567892', 'Jl. Merdeka No. 78', 'Surabaya');

-- Item contoh
INSERT INTO items (sku, barcode, item_name, category_id, unit, purchase_price, selling_price, stock_quantity, min_stock) VALUES
('ITM-001', '8991234567890', 'Indomie Goreng', 1, 'pcs', 2500, 3000, 100, 20),
('ITM-002', '8991234567891', 'Aqua 600ml', 1, 'btl', 3000, 4000, 50, 15),
('ITM-003', '8991234567892', 'Pulpen Standard', 4, 'pcs', 1500, 2500, 200, 30),
('ITM-004', '8991234567893', 'Buku Tulis 58 Lembar', 4, 'pcs', 3500, 5000, 75, 25);

-- ============================================
-- VIEWS untuk Laporan
-- ============================================

CREATE OR REPLACE VIEW v_stock_report AS
SELECT 
    i.item_id,
    i.sku,
    i.barcode,
    i.item_name,
    c.category_name,
    i.unit,
    i.stock_quantity,
    i.min_stock,
    i.purchase_price,
    i.selling_price,
    (i.selling_price - i.purchase_price) as profit_per_unit,
    (i.stock_quantity * i.purchase_price) as stock_value,
    CASE 
        WHEN i.stock_quantity <= i.min_stock THEN 'Low Stock'
        WHEN i.stock_quantity = 0 THEN 'Out of Stock'
        ELSE 'Available'
    END as stock_status
FROM items i
LEFT JOIN categories c ON i.category_id = c.category_id
WHERE i.is_active = TRUE;

CREATE OR REPLACE VIEW v_daily_sales AS
SELECT 
    DATE(s.sale_date) as sale_date,
    COUNT(DISTINCT s.sale_id) as total_transactions,
    SUM(sd.quantity) as total_items_sold,
    SUM(s.subtotal) as gross_sales,
    SUM(s.discount_amount) as total_discount,
    SUM(s.total_amount) as net_sales,
    SUM(sd.profit) as total_profit
FROM sales_transactions s
INNER JOIN sale_details sd ON s.sale_id = sd.sale_id
GROUP BY DATE(s.sale_date);

-- ============================================
-- FUNCTIONS untuk generate nomor transaksi
-- ============================================

CREATE OR REPLACE FUNCTION generate_sale_number() 
RETURNS VARCHAR AS $$
DECLARE
    last_number INTEGER;
    today VARCHAR(8);
    new_number VARCHAR(50);
BEGIN
    today := TO_CHAR(CURRENT_DATE, 'YYYYMMDD');
    
    SELECT COALESCE(MAX(CAST(SUBSTRING(sale_number FROM 10) AS INTEGER)), 0) INTO last_number
    FROM sales_transactions
    WHERE sale_number LIKE 'SAL' || today || '%';
    
    new_number := 'SAL' || today || LPAD((last_number + 1)::TEXT, 4, '0');
    
    RETURN new_number;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION generate_purchase_number() 
RETURNS VARCHAR AS $$
DECLARE
    last_number INTEGER;
    today VARCHAR(8);
    new_number VARCHAR(50);
BEGIN
    today := TO_CHAR(CURRENT_DATE, 'YYYYMMDD');
    
    SELECT COALESCE(MAX(CAST(SUBSTRING(purchase_number FROM 10) AS INTEGER)), 0) INTO last_number
    FROM purchase_transactions
    WHERE purchase_number LIKE 'PUR' || today || '%';
    
    new_number := 'PUR' || today || LPAD((last_number + 1)::TEXT, 4, '0');
    
    RETURN new_number;
END;
$$ LANGUAGE plpgsql;
