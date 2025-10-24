-- ============================================
-- SKEMA DATABASE MySQL untuk Sistem POS & Inventori
-- ============================================

-- Hapus database jika sudah ada (hati-hati di production!)
-- DROP DATABASE IF EXISTS pos_inventory;

-- Buat database
CREATE DATABASE IF NOT EXISTS pos_inventory 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE pos_inventory;

-- ============================================
-- TABEL: users
-- Menyimpan data pengguna sistem
-- ============================================
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    role ENUM('admin', 'kasir', 'manajer') DEFAULT 'kasir',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_role (role)
) ENGINE=InnoDB;

-- ============================================
-- TABEL: categories
-- Kategori barang
-- ============================================
CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category_name (category_name)
) ENGINE=InnoDB;

-- ============================================
-- TABEL: suppliers
-- Data supplier/pemasok barang
-- ============================================
CREATE TABLE suppliers (
    supplier_id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_name VARCHAR(150) NOT NULL,
    contact_person VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    city VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_supplier_name (supplier_name)
) ENGINE=InnoDB;

-- ============================================
-- TABEL: customers
-- Data pelanggan
-- ============================================
CREATE TABLE customers (
    customer_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(150) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    city VARCHAR(100),
    points INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_customer_name (customer_name),
    INDEX idx_phone (phone)
) ENGINE=InnoDB;

-- ============================================
-- TABEL: items
-- Master data barang/produk
-- INTI SISTEM: Harga dan stok digunakan oleh POS
-- ============================================
CREATE TABLE items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    sku VARCHAR(50) UNIQUE NOT NULL,
    barcode VARCHAR(100) UNIQUE,
    item_name VARCHAR(200) NOT NULL,
    category_id INT,
    unit VARCHAR(20) DEFAULT 'pcs',
    purchase_price DECIMAL(15,2) DEFAULT 0,
    selling_price DECIMAL(15,2) NOT NULL,
    stock_quantity INT DEFAULT 0,
    min_stock INT DEFAULT 10,
    description TEXT,
    image_url VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE SET NULL,
    INDEX idx_sku (sku),
    INDEX idx_barcode (barcode),
    INDEX idx_item_name (item_name),
    INDEX idx_category (category_id),
    INDEX idx_stock (stock_quantity)
) ENGINE=InnoDB;

-- ============================================
-- TABEL: purchase_transactions
-- Header transaksi pembelian/barang masuk
-- ============================================
CREATE TABLE purchase_transactions (
    purchase_id INT AUTO_INCREMENT PRIMARY KEY,
    purchase_number VARCHAR(50) UNIQUE NOT NULL,
    supplier_id INT,
    purchase_date DATE NOT NULL,
    total_amount DECIMAL(15,2) DEFAULT 0,
    notes TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_purchase_number (purchase_number),
    INDEX idx_purchase_date (purchase_date),
    INDEX idx_supplier (supplier_id)
) ENGINE=InnoDB;

-- ============================================
-- TABEL: purchase_details
-- Detail item dalam transaksi pembelian
-- MENAMBAH stok barang
-- ============================================
CREATE TABLE purchase_details (
    detail_id INT AUTO_INCREMENT PRIMARY KEY,
    purchase_id INT NOT NULL,
    item_id INT NOT NULL,
    quantity INT NOT NULL,
    purchase_price DECIMAL(15,2) NOT NULL,
    subtotal DECIMAL(15,2) NOT NULL,
    FOREIGN KEY (purchase_id) REFERENCES purchase_transactions(purchase_id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES items(item_id) ON DELETE RESTRICT,
    INDEX idx_purchase (purchase_id),
    INDEX idx_item (item_id)
) ENGINE=InnoDB;

-- ============================================
-- TABEL: sales_transactions
-- Header transaksi penjualan/POS
-- ============================================
CREATE TABLE sales_transactions (
    sale_id INT AUTO_INCREMENT PRIMARY KEY,
    sale_number VARCHAR(50) UNIQUE NOT NULL,
    customer_id INT,
    sale_date DATETIME NOT NULL,
    subtotal DECIMAL(15,2) DEFAULT 0,
    discount_percent DECIMAL(5,2) DEFAULT 0,
    discount_amount DECIMAL(15,2) DEFAULT 0,
    tax_percent DECIMAL(5,2) DEFAULT 0,
    tax_amount DECIMAL(15,2) DEFAULT 0,
    total_amount DECIMAL(15,2) NOT NULL,
    payment_amount DECIMAL(15,2) NOT NULL,
    change_amount DECIMAL(15,2) DEFAULT 0,
    payment_method ENUM('cash', 'debit', 'credit', 'qris') DEFAULT 'cash',
    notes TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_sale_number (sale_number),
    INDEX idx_sale_date (sale_date),
    INDEX idx_customer (customer_id),
    INDEX idx_created_by (created_by)
) ENGINE=InnoDB;

-- ============================================
-- TABEL: sale_details
-- Detail item dalam transaksi penjualan
-- MENGURANGI stok barang (otomatis via trigger/code)
-- ============================================
CREATE TABLE sale_details (
    detail_id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT NOT NULL,
    item_id INT NOT NULL,
    quantity INT NOT NULL,
    selling_price DECIMAL(15,2) NOT NULL,
    purchase_price DECIMAL(15,2) DEFAULT 0,
    subtotal DECIMAL(15,2) NOT NULL,
    profit DECIMAL(15,2) DEFAULT 0,
    FOREIGN KEY (sale_id) REFERENCES sales_transactions(sale_id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES items(item_id) ON DELETE RESTRICT,
    INDEX idx_sale (sale_id),
    INDEX idx_item (item_id)
) ENGINE=InnoDB;

-- ============================================
-- TABEL: stock_movements
-- Log pergerakan stok (opsional untuk audit trail)
-- ============================================
CREATE TABLE stock_movements (
    movement_id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    movement_type ENUM('purchase', 'sale', 'adjustment', 'return') NOT NULL,
    reference_id INT,
    quantity_before INT NOT NULL,
    quantity_change INT NOT NULL,
    quantity_after INT NOT NULL,
    notes TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES items(item_id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_item (item_id),
    INDEX idx_movement_type (movement_type),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB;

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

-- View: Stock dengan informasi kategori
CREATE VIEW v_stock_report AS
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

-- View: Laporan penjualan harian
CREATE VIEW v_daily_sales AS
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
-- STORED PROCEDURES (Opsional)
-- ============================================

DELIMITER //

-- Procedure untuk generate nomor transaksi penjualan
CREATE PROCEDURE generate_sale_number(OUT new_number VARCHAR(50))
BEGIN
    DECLARE last_number INT;
    DECLARE today VARCHAR(8);
    
    SET today = DATE_FORMAT(CURDATE(), '%Y%m%d');
    
    SELECT IFNULL(MAX(CAST(SUBSTRING(sale_number, 10) AS UNSIGNED)), 0) INTO last_number
    FROM sales_transactions
    WHERE sale_number LIKE CONCAT('SAL', today, '%');
    
    SET new_number = CONCAT('SAL', today, LPAD(last_number + 1, 4, '0'));
END //

-- Procedure untuk generate nomor transaksi pembelian
CREATE PROCEDURE generate_purchase_number(OUT new_number VARCHAR(50))
BEGIN
    DECLARE last_number INT;
    DECLARE today VARCHAR(8);
    
    SET today = DATE_FORMAT(CURDATE(), '%Y%m%d');
    
    SELECT IFNULL(MAX(CAST(SUBSTRING(purchase_number, 10) AS UNSIGNED)), 0) INTO last_number
    FROM purchase_transactions
    WHERE purchase_number LIKE CONCAT('PUR', today, '%');
    
    SET new_number = CONCAT('PUR', today, LPAD(last_number + 1, 4, '0'));
END //

DELIMITER ;
