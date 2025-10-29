<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/PurchaseTransaction.php';
require_once __DIR__ . '/../../models/Supplier.php';
require_once __DIR__ . '/../../models/Item.php';

requireAdmin();

$purchaseModel = new PurchaseTransaction();
$supplierModel = new Supplier();
$itemModel = new Item(); // Digunakan untuk mencari/memilih item

// Tentukan path root relatif
$root_dir = __DIR__ . '/../../'; 

$pageTitle = 'Input Transaksi Pembelian Baru (IN)';

// Ambil data yang diperlukan
$suppliers = $supplierModel->getAllActiveSuppliers(); // Asumsi: Anda punya method ini
$purchaseNumber = $purchaseModel->generatePurchaseNumber(); // Generate nomor baru

// Ambil data item aktif untuk dropdown/autocomplete (Opsional)
$items = $itemModel->getAll(); // Asumsi: mengambil semua item untuk JS lookup

include $root_dir . 'views/layouts/header.php';
?>

<div class="container-fluid mt-4">

    <h2><i class="bi bi-cart-plus"></i> <?php echo $pageTitle; ?></h2>
    <form action="process.php" method="POST" id="purchaseForm">
        <input type="hidden" name="action" value="create_purchase">
        
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-secondary text-white">
                Informasi Utama Transaksi
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="purchase_number" class="form-label">Nomor Pembelian</label>
                        <input type="text" class="form-control" id="purchase_number" value="<?php echo htmlspecialchars($purchaseNumber); ?>" readonly>
                        <input type="hidden" name="purchase_number" value="<?php echo htmlspecialchars($purchaseNumber); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="purchase_date" class="form-label">Tanggal Pembelian <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="purchase_date" name="purchase_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="supplier_id" class="form-label">Pilih Supplier <span class="text-danger">*</span></label>
                        <select class="form-select" id="supplier_id" name="supplier_id" required>
                            <option value="">-- Pilih Supplier --</option>
                            <?php foreach ($suppliers as $supplier): ?>
                                <option value="<?php echo $supplier['supplier_id']; ?>">
                                    <?php echo htmlspecialchars($supplier['supplier_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="notes" class="form-label">Catatan (Opsional)</label>
                        <textarea class="form-control" id="notes" name="notes" rows="1"></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                Rincian Barang Masuk
                <button type="button" class="btn btn-sm btn-success" id="addItemBtn">
                    <i class="bi bi-plus"></i> Tambah Item
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="itemsTable">
                        <thead>
                            <tr>
                                <th style="width: 30%;">Barang (Nama/SKU)</th>
                                <th style="width: 15%;" class="text-center">Kuantitas</th>
                                <th style="width: 25%;" class="text-end">Harga Beli (@)</th>
                                <th style="width: 20%;" class="text-end">Subtotal</th>
                                <th style="width: 10%;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="itemsTableBody">
                            <tr id="emptyRow">
                                <td colspan="5" class="text-center text-muted">Klik "Tambah Item" untuk memulai input.</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end">**TOTAL KESELURUHAN**</td>
                                <td class="text-end"><strong id="grandTotalDisplay">Rp 0</strong></td>
                                <td></td>
                            </tr>
                            <input type="hidden" name="total_amount" id="totalAmountInput" value="0">
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between mb-4">
            <a href="index.php" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary" id="savePurchaseBtn" disabled><i class="bi bi-save"></i> Simpan Transaksi Pembelian</button>
        </div>
        
    </form>
</div>

<script>
    // Data Item dari PHP untuk Lookup
    const itemData = <?php echo json_encode($items); ?>; 
    
    // Logika JS untuk Tambah Baris, Perhitungan Subtotal, dan Grand Total harus ditambahkan di sini
    // ... (Anda perlu menulis kode JavaScript di sini) ...

    document.addEventListener('DOMContentLoaded', function() {
        const itemsTableBody = document.getElementById('itemsTableBody');
        const addItemBtn = document.getElementById('addItemBtn');
        const grandTotalDisplay = document.getElementById('grandTotalDisplay');
        const totalAmountInput = document.getElementById('totalAmountInput');
        const savePurchaseBtn = document.getElementById('savePurchaseBtn');
        let itemIndex = 0;

        // Fungsi untuk menambahkan baris item baru
        addItemBtn.addEventListener('click', function() {
            document.getElementById('emptyRow').style.display = 'none';

            const newRow = document.createElement('tr');
            newRow.setAttribute('data-index', itemIndex);
            newRow.innerHTML = `
                <td>
                    <select name="items[${itemIndex}][item_id]" class="form-select item-select" required>
                        <option value="">-- Pilih Barang --</option>
                        ${itemData.map(item => `<option value="${item.item_id}" data-price="${item.purchase_price}">${item.sku} - ${item.item_name}</option>`).join('')}
                    </select>
                </td>
                <td>
                    <input type="number" name="items[${itemIndex}][quantity]" class="form-control quantity-input text-center" min="1" value="1" required>
                </td>
                <td>
                    <input type="text" name="items[${itemIndex}][purchase_price]" class="form-control price-input text-end" value="0" required>
                </td>
                <td class="text-end subtotal-display">Rp 0</td>
                <input type="hidden" name="items[${itemIndex}][subtotal]" class="subtotal-input" value="0">
                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm remove-item-btn"><i class="bi bi-x-lg"></i></button>
                </td>
            `;
            itemsTableBody.appendChild(newRow);
            itemIndex++;
            updateTotals();
        });

        // Event listener untuk menghapus baris item
        itemsTableBody.addEventListener('click', function(e) {
            if (e.target.closest('.remove-item-btn')) {
                e.target.closest('tr').remove();
                updateTotals();
                if (itemsTableBody.children.length === 1 && itemsTableBody.children[0].id === 'emptyRow') {
                    document.getElementById('emptyRow').style.display = 'table-row';
                }
            }
        });

        // Event listener untuk perubahan Kuantitas dan Harga
        itemsTableBody.addEventListener('change', function(e) {
            if (e.target.classList.contains('quantity-input') || e.target.classList.contains('price-input') || e.target.classList.contains('item-select')) {
                updateTotals();
            }
        });
        
        // Event listener untuk input harga beli
        itemsTableBody.addEventListener('input', function(e) {
            if (e.target.classList.contains('price-input')) {
                // Hapus semua kecuali digit dan titik
                let value = e.target.value.replace(/[^0-9.]/g, '');
                e.target.value = value;
                updateTotals();
            }
        });
        
        // Event listener saat memilih item, otomatis isi harga beli
        itemsTableBody.addEventListener('change', function(e) {
            if (e.target.classList.contains('item-select')) {
                const selectedOption = e.target.options[e.target.selectedIndex];
                const price = selectedOption.getAttribute('data-price') || '0';
                
                const row = e.target.closest('tr');
                const priceInput = row.querySelector('.price-input');
                
                priceInput.value = price;
                updateTotals();
            }
        });

        // Fungsi utama untuk menghitung Subtotal dan Grand Total
        function updateTotals() {
            let grandTotal = 0;
            let rowCount = 0;

            itemsTableBody.querySelectorAll('tr[data-index]').forEach(row => {
                rowCount++;
                
                // Ambil nilai Kuantitas dan Harga
                const quantityElement = row.querySelector('.quantity-input');
                const priceElement = row.querySelector('.price-input');
                
                const quantity = parseInt(quantityElement.value) || 0;
                const price = parseFloat(priceElement.value) || 0;
                
                // Hitung Subtotal
                const subtotal = quantity * price;
                grandTotal += subtotal;

                // Tampilkan Subtotal
                row.querySelector('.subtotal-display').textContent = formatRupiah(subtotal);
                row.querySelector('.subtotal-input').value = subtotal;
            });
            
            // Tampilkan Grand Total
            grandTotalDisplay.textContent = formatRupiah(grandTotal);
            totalAmountInput.value = grandTotal;
            
            // Kontrol tombol simpan
            savePurchaseBtn.disabled = grandTotal <= 0 || rowCount === 0;
        }

        // Helper function (Format Rupiah)
        function formatRupiah(angka) {
            let reverse = angka.toString().split('').reverse().join('');
            let ribuan = reverse.match(/\d{1,3}/g);
            let result = ribuan.join('.').split('').reverse().join('');
            return 'Rp ' + result;
        }

        // Panggil updateTotals saat inisialisasi untuk memastikan nilai awal benar
        updateTotals();
    });
</script>

<?php 
include $root_dir . 'views/layouts/footer.php'; 
?>