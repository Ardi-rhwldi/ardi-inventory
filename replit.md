# Sistem POS & Inventori

Aplikasi web terintegrasi untuk Pengelolaan Stok Barang (Inventori) dan Sistem Kasir (Point of Sale/POS) menggunakan PHP, Bootstrap, dan MySQL/PostgreSQL.

## Teknologi
- **Backend:** PHP 8.3 Native dengan Arsitektur MVC
- **Database:** PostgreSQL (Replit) / MySQL (Production)
- **Frontend:** HTML5, CSS3, JavaScript, Bootstrap 5
- **Grafik:** Chart.js

## Struktur Folder

```
├── config/              # Konfigurasi aplikasi dan database
│   ├── config.php       # Konfigurasi umum aplikasi
│   └── database.php     # Koneksi database PDO
├── models/              # Model layer (database logic)
│   ├── BaseModel.php    # Base model dengan fungsi CRUD
│   ├── Item.php         # Model barang/produk
│   ├── Category.php     # Model kategori
│   ├── Supplier.php     # Model supplier
│   ├── Customer.php     # Model pelanggan
│   ├── PurchaseTransaction.php  # Model transaksi pembelian
│   └── SaleTransaction.php      # Model transaksi penjualan (POS)
├── controllers/         # Controller layer (business logic)
├── views/               # View layer (presentation)
│   └── layouts/         # Layout template
├── public/              # Public web root
│   ├── index.php        # Entry point
│   ├── login.php        # Halaman login
│   ├── dashboard.php    # Dashboard
│   ├── items/           # Modul kelola barang
│   ├── suppliers/       # Modul kelola supplier
│   ├── customers/       # Modul kelola pelanggan
│   ├── purchases/       # Modul transaksi pembelian
│   ├── pos/             # Modul kasir (POS)
│   └── reports/         # Modul laporan
├── assets/              # Assets statis
│   ├── css/             # Stylesheet
│   ├── js/              # JavaScript
│   └── images/          # Gambar
└── database/            # Database schema
    ├── schema_mysql.sql      # Skema MySQL lengkap
    └── schema_postgresql.sql # Skema PostgreSQL

```

## Fitur Utama

### 1. Dashboard
- Ringkasan stok barang real-time
- Grafik penjualan 7 hari terakhir
- Peringatan stok minimum
- Statistik penjualan harian

### 2. Kelola Barang (Items)
- CRUD lengkap untuk master barang
- SKU/Barcode, Nama, Kategori, Harga Beli, Harga Jual, Satuan, Stok
- Tracking stok otomatis
- Peringatan stok minimum

### 3. Kelola Supplier & Pelanggan
- CRUD data master supplier
- CRUD data master pelanggan
- Tracking riwayat transaksi

### 4. Transaksi Masuk (Pembelian)
- Formulir pembelian barang dari supplier
- Auto-increment stok barang secara atomik
- Generate nomor transaksi otomatis

### 5. Sistem Kasir (POS) ⭐
- Antarmuka cepat untuk kasir
- Search barang by nama/SKU/barcode
- Keranjang belanja interaktif
- Kalkulasi subtotal, diskon, total, pembayaran, kembalian
- **Pengurangan stok otomatis** setelah transaksi sukses
- **Operasi database atomik** (transaction safe)
- Cetak struk transaksi
- Multi metode pembayaran (Cash, Debit, Credit, QRIS)

### 6. Laporan
- Laporan Stok Saat Ini (dengan filter)
- Laporan Penjualan (Harian/Bulanan)
- Laporan Laba Kotor (Harga Jual - Harga Beli)
- Top selling items

## Login Default
- Username: `admin`
- Password: `admin123`

## Database Schema

Database menggunakan foreign keys untuk menjaga integritas data:
- `items` ← `purchase_details` (MENAMBAH stok)
- `items` ← `sale_details` (MENGURANGI stok)
- `stock_movements` (audit trail pergerakan stok)

## Kode Penting: Transaksi POS dengan Pengurangan Stok Atomik

File: `models/SaleTransaction.php` - Method `createSale()`

Proses:
1. **BEGIN TRANSACTION** - Semua operasi menjadi satu kesatuan
2. Generate nomor transaksi otomatis
3. Insert header transaksi
4. Loop setiap item:
   - Validasi stok tersedia
   - Insert detail transaksi
   - **KURANGI stok** via `Item::updateStock()` (quantity negatif)
   - Hitung profit (Harga Jual - Harga Beli)
5. Update total di header
6. **COMMIT** - Semua berhasil, simpan permanent
7. Jika ada error di step manapun, **ROLLBACK** otomatis

Contoh penggunaan:
```php
$saleModel = new SaleTransaction();
$result = $saleModel->createSale($headerData, $itemsData);
```

## Setup untuk Production dengan MySQL

1. Import skema database:
```bash
mysql -u root -p < database/schema_mysql.sql
```

2. Update konfigurasi di `config/database.php` atau set environment variables:
```bash
export DB_HOST=localhost
export DB_PORT=3306
export DB_NAME=pos_inventory
export DB_USER=root
export DB_PASS=your_password
```

3. Jalankan web server (Apache/Nginx dengan PHP-FPM)

## Keamanan
- Password di-hash menggunakan `password_hash()` (bcrypt)
- PDO Prepared Statements untuk mencegah SQL Injection
- Session management untuk autentikasi
- CSRF protection (dapat ditambahkan)

## Pengembangan Selanjutnya
- Multi-user dengan role-based access control
- Cetak invoice/struk thermal printer
- Manajemen retur barang
- Export laporan ke Excel/PDF
- Dashboard analytics lanjutan
- API untuk integrasi external system
