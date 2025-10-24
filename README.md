# Sistem POS & Inventori

Aplikasi web terintegrasi untuk **Pengelolaan Stok Barang (Inventori)** dan **Sistem Kasir (Point of Sale/POS)** yang dibangun dengan **PHP Native**, **Bootstrap 5**, dan **MySQL/PostgreSQL**.

## 🚀 Fitur Utama

### 1. 📊 Dashboard Real-time
- Statistik stok barang dan transaksi harian
- Grafik penjualan 7 hari terakhir (Chart.js)
- Peringatan otomatis untuk stok minimum
- Daftar transaksi penjualan terbaru

### 2. 📦 Kelola Barang (Master Items)
- **CRUD lengkap** untuk data barang
- SKU/Barcode, Nama, Kategori, Harga Beli, Harga Jual
- Tracking stok real-time dengan log pergerakan
- Alert otomatis saat stok menipis

### 3. 🏪 Kelola Supplier & Pelanggan
- CRUD data master supplier dan pelanggan
- Tracking riwayat transaksi per supplier/pelanggan
- Statistik pembelian dan penjualan

### 4. 📥 Transaksi Pembelian (Purchase)
- Form pembelian barang dari supplier
- **Auto-increment stok** setelah transaksi
- Generate nomor transaksi otomatis
- Operasi database **atomik** (rollback on error)

### 5. 💰 Sistem Kasir (POS) ⭐ FITUR UTAMA
- **Antarmuka cepat dan intuitif** untuk kasir
- Search barang by nama/SKU/barcode (real-time)
- Keranjang belanja interaktif
- Kalkulasi otomatis: Subtotal, Diskon (%), Total, Pembayaran, Kembalian
- **Pengurangan stok otomatis** dengan operasi atomik
- Multi metode pembayaran: Cash, Debit, Credit, QRIS
- Cetak struk transaksi

### 6. 📑 Laporan Lengkap
- **Laporan Stok:** Nilai stok, status ketersediaan
- **Laporan Penjualan:** Harian/Bulanan dengan filter tanggal
- **Laporan Laba Kotor:** Revenue, Cost, Profit, Margin %
- Export-ready format (siap cetak)

---

## 🛠️ Teknologi yang Digunakan

| Komponen | Teknologi |
|----------|-----------|
| **Backend** | PHP 8.2+ Native dengan Arsitektur MVC |
| **Database** | PostgreSQL (Replit) / MySQL 5.7+ (Production) |
| **Frontend** | HTML5, CSS3, JavaScript (Vanilla) |
| **UI Framework** | Bootstrap 5.3 |
| **Chart Library** | Chart.js 4.4 |
| **Icons** | Bootstrap Icons |
| **Security** | PDO Prepared Statements, Password Hashing (bcrypt) |

---

## 📁 Struktur Folder

```
├── config/                    # Konfigurasi aplikasi
│   ├── config.php             # Konfigurasi umum & helper functions
│   └── database.php           # Koneksi PDO (support MySQL & PostgreSQL)
├── models/                    # Model layer (Data Access)
│   ├── BaseModel.php          # Base model dengan CRUD
│   ├── Item.php               # Model barang (stok management)
│   ├── Category.php           # Model kategori
│   ├── Supplier.php           # Model supplier
│   ├── Customer.php           # Model pelanggan
│   ├── PurchaseTransaction.php  # Model transaksi pembelian
│   └── SaleTransaction.php      # Model transaksi penjualan (POS)
├── views/                     # View layer (Presentation)
│   └── layouts/               # Template layout
│       ├── header.php         # Header & navigation
│       └── footer.php         # Footer & scripts
├── public/                    # Document root (web accessible)
│   ├── index.php              # Entry point
│   ├── login.php              # Halaman login
│   ├── dashboard.php          # Dashboard utama
│   ├── items/                 # Modul kelola barang
│   ├── suppliers/             # Modul kelola supplier
│   ├── customers/             # Modul kelola pelanggan
│   ├── purchases/             # Modul transaksi pembelian
│   ├── pos/                   # Modul kasir (POS)
│   │   ├── index.php          # Antarmuka kasir
│   │   ├── process.php        # Proses transaksi (API)
│   │   └── receipt.php        # Cetak struk
│   └── reports/               # Modul laporan
│       ├── stock.php          # Laporan stok
│       ├── sales.php          # Laporan penjualan
│       └── profit.php         # Laporan laba
├── assets/                    # Static assets
│   ├── css/style.css          # Custom stylesheet
│   └── js/main.js             # Custom JavaScript
└── database/                  # Database schema
    ├── schema_mysql.sql       # Skema MySQL lengkap
    └── schema_postgresql.sql  # Skema PostgreSQL
```

---

## 🔑 Login Default

```
Username: admin
Password: admin123
```

**Catatan:** Segera ganti password default setelah login pertama kali!

---

## 💾 Database Schema

### Tabel Utama:

1. **users** - Data pengguna sistem
2. **categories** - Kategori barang
3. **suppliers** - Data supplier
4. **customers** - Data pelanggan
5. **items** - Master barang (INTI: harga & stok untuk POS)
6. **purchase_transactions** - Header transaksi pembelian
7. **purchase_details** - Detail item pembelian (MENAMBAH stok)
8. **sales_transactions** - Header transaksi penjualan
9. **sale_details** - Detail item penjualan (MENGURANGI stok)
10. **stock_movements** - Log audit pergerakan stok

### Relasi Foreign Keys:
- `items.category_id` → `categories.category_id`
- `purchase_details.item_id` → `items.item_id`
- `sale_details.item_id` → `items.item_id`
- `stock_movements.item_id` → `items.item_id`

---

## ⚙️ Instalasi & Setup

### Untuk Development di Replit (PostgreSQL)

Aplikasi sudah siap dijalankan! Database PostgreSQL otomatis terkonfigurasi.

1. Klik tombol **Run** atau buka URL Replit
2. Login dengan kredensial default
3. Mulai gunakan aplikasi

### Untuk Production dengan MySQL

#### 1. Persiapan Database

```bash
# Buat database
mysql -u root -p

# Di MySQL console:
CREATE DATABASE pos_inventory CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;

# Import schema
mysql -u root -p pos_inventory < database/schema_mysql.sql
```

#### 2. Konfigurasi Environment

Buat file `.env` atau set environment variables:

```bash
export DB_HOST=localhost
export DB_PORT=3306
export DB_NAME=pos_inventory
export DB_USER=root
export DB_PASS=your_password
```

#### 3. Setup Web Server

**Dengan Apache:**

```apache
<VirtualHost *:80>
    ServerName pos.example.com
    DocumentRoot /path/to/project/public
    
    <Directory /path/to/project/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

**Dengan PHP Built-in Server (Development only):**

```bash
cd /path/to/project
php -S localhost:8000 -t public
```

#### 4. Permissions

```bash
chmod -R 755 /path/to/project
chmod -R 777 /path/to/project/assets  # Jika ada upload file
```

---

## 🔐 Keamanan

### Implementasi Security:

✅ **Password Hashing** - Menggunakan `password_hash()` dengan bcrypt  
✅ **SQL Injection Prevention** - PDO Prepared Statements  
✅ **Session Management** - Session-based authentication  
✅ **Input Validation** - Server-side validation  
✅ **Transaction Atomicity** - Rollback on error  

### Rekomendasi Production:

- [ ] Ganti semua password default
- [ ] Enable HTTPS/SSL
- [ ] Set `display_errors = 0` di php.ini
- [ ] Implement CSRF protection
- [ ] Add rate limiting untuk login
- [ ] Regular database backup
- [ ] Implement role-based access control (RBAC)

---

## 🎯 Kode Penting: Transaksi POS Atomik

### File: `models/SaleTransaction.php` - Method `createSale()`

Ini adalah **INTI dari sistem POS** dengan pengurangan stok atomik:

```php
public function createSale($headerData, $itemsData) {
    try {
        // BEGIN TRANSACTION - Semua operasi menjadi satu kesatuan atomik
        $this->db->beginTransaction();
        
        // 1. Generate nomor transaksi otomatis
        $saleNumber = $this->generateSaleNumber();
        
        // 2. Insert header transaksi
        $saleId = $this->insert($headerData);
        
        // 3. Loop setiap item
        foreach ($itemsData as $item) {
            // Validasi stok tersedia
            $itemInfo = $itemModel->findById($item['item_id']);
            if ($itemInfo['stock_quantity'] < $item['quantity']) {
                throw new Exception("Stok tidak cukup");
            }
            
            // Insert detail transaksi
            $this->db->query("INSERT INTO sale_details ...");
            
            // KURANGI stok (quantity negatif)
            $itemModel->updateStock(
                $item['item_id'], 
                -$item['quantity'],  // Negatif = pengurangan
                'sale', 
                $saleId
            );
        }
        
        // COMMIT - Semua berhasil, simpan permanent
        $this->db->commit();
        return ['sale_id' => $saleId, 'sale_number' => $saleNumber];
        
    } catch (Exception $e) {
        // ROLLBACK - Ada error, batalkan SEMUA perubahan
        $this->db->rollback();
        throw $e;
    }
}
```

**Keunggulan:**
- ✅ Semua operasi dalam 1 transaksi atomik
- ✅ Jika 1 item error, semua dibatalkan (rollback)
- ✅ Tidak ada kemungkinan stok tidak sinkron
- ✅ Data konsisten 100%

---

## 🧪 Testing

### Test Manual Transaksi POS:

1. Login ke sistem
2. Buka menu **KASIR (POS)**
3. Tambahkan beberapa item ke keranjang
4. Set diskon (optional)
5. Masukkan uang pembayaran
6. Klik **PROSES PEMBAYARAN**
7. Verifikasi:
   - ✅ Transaksi tersimpan
   - ✅ Stok berkurang otomatis
   - ✅ Struk dapat dicetak
   - ✅ Log stock_movements tercatat

### Test Rollback (Error Handling):

Untuk test rollback, coba transaksi dengan stok tidak cukup:
- Sistem akan otomatis rollback
- Tidak ada perubahan stok
- Error message ditampilkan

---

## 📊 Sample Data

Database sudah dilengkapi dengan data sample:

- **Users:** admin, kasir1 (password: admin123)
- **Kategori:** 5 kategori (Makanan, Elektronik, Pakaian, Alat Tulis, Kesehatan)
- **Supplier:** 2 supplier dummy
- **Pelanggan:** 2 pelanggan (termasuk "Pelanggan Umum")
- **Barang:** 4 item contoh dengan stok

---

## 🔄 Workflow Backup & Maintenance

### Backup Database (MySQL):

```bash
# Backup
mysqldump -u root -p pos_inventory > backup_$(date +%Y%m%d).sql

# Restore
mysql -u root -p pos_inventory < backup_YYYYMMDD.sql
```

### Backup Database (PostgreSQL):

```bash
# Backup
pg_dump $DATABASE_URL > backup_$(date +%Y%m%d).sql

# Restore
psql $DATABASE_URL < backup_YYYYMMDD.sql
```

---

## 🚀 Deployment ke Production

### Checklist Deployment:

- [ ] Import schema database production
- [ ] Update konfigurasi database (.env)
- [ ] Set error reporting = 0
- [ ] Enable SSL/HTTPS
- [ ] Ganti password default
- [ ] Test semua fitur
- [ ] Setup backup otomatis
- [ ] Monitoring & logging
- [ ] Performance tuning (index, caching)

---

## 📝 API Endpoints

### POS Process API (`public/pos/process.php`)

**Method:** POST  
**Content-Type:** application/json

**Request Body:**
```json
{
  "customer_id": 1,
  "items": [
    {
      "item_id": 1,
      "quantity": 2,
      "selling_price": 3000
    }
  ],
  "subtotal": 6000,
  "discount_percent": 10,
  "discount_amount": 600,
  "total_amount": 5400,
  "payment_amount": 10000,
  "change_amount": 4600,
  "payment_method": "cash"
}
```

**Response:**
```json
{
  "success": true,
  "sale_id": 123,
  "sale_number": "SAL202410240001",
  "message": "Transaksi berhasil disimpan"
}
```

---

## 🆘 Troubleshooting

### Error: "There is already an active transaction"
**Solusi:** Issue ini sudah diperbaiki. `Item::updateStock()` tidak lagi memulai transaksi sendiri.

### Error: "Unknown column 'ILIKE'"
**Solusi:** Issue ini sudah diperbaiki. Query search sudah support MySQL dan PostgreSQL.

### Stok tidak berkurang setelah transaksi
**Cek:**
1. Apakah transaksi tersimpan di `sales_transactions`?
2. Apakah ada error di browser console?
3. Cek log `stock_movements` untuk audit trail

### Permission denied
**Solusi:** Set permissions yang tepat untuk folder aplikasi

---

## 📚 Dokumentasi Tambahan

- **replit.md** - Dokumentasi teknis lengkap
- **database/schema_mysql.sql** - Skema database MySQL dengan komentar
- **database/schema_postgresql.sql** - Skema database PostgreSQL

---

## 🔮 Pengembangan Selanjutnya

### Fitur yang Dapat Ditambahkan:

1. **Multi-User & RBAC**
   - Role: Admin, Manager, Kasir
   - Permission-based access control

2. **Advanced Reporting**
   - Dashboard analytics dengan grafik interaktif
   - Export ke Excel/PDF
   - Email laporan otomatis

3. **Inventory Management**
   - Stock opname/audit
   - Retur pembelian & penjualan
   - Stock transfer antar cabang
   - Prediksi kebutuhan stok (AI)

4. **POS Enhancement**
   - Barcode scanner integration
   - Thermal printer support
   - Split payment
   - Customer loyalty points

5. **Integration**
   - API untuk mobile app
   - Integrasi payment gateway
   - WhatsApp notification
   - Cloud backup otomatis

---

## 👥 Kontribusi

Aplikasi ini dibuat sebagai contoh implementasi sistem POS & Inventori yang profesional dengan PHP Native dan Bootstrap.

---

## 📄 Lisensi

Free to use for learning and commercial purposes.

---

## 📞 Support

Untuk pertanyaan dan bantuan, silakan buka issue atau hubungi developer.

---

**Built with ❤️ using PHP, Bootstrap & MySQL/PostgreSQL**
