<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/PurchaseTransaction.php';

// Pastikan hanya POST request yang diizinkan
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Pastikan pengguna memiliki hak akses admin
requireAdmin();

$purchaseModel = new PurchaseTransaction();
$action = $_POST['action'] ?? '';

// --- LOGIKA UTAMA: CREATE PURCHASE ---
if ($action === 'create_purchase') {
    try {
        // 1. Ambil dan Validasi Data Header
        $headerData = [
            'purchase_number' => filter_input(INPUT_POST, 'purchase_number', FILTER_SANITIZE_STRING),
            'purchase_date' => filter_input(INPUT_POST, 'purchase_date', FILTER_SANITIZE_STRING),
            'supplier_id' => filter_input(INPUT_POST, 'supplier_id', FILTER_SANITIZE_NUMBER_INT),
            'notes' => filter_input(INPUT_POST, 'notes', FILTER_SANITIZE_STRING) ?? null,
            // total_amount dihitung ulang di model, tapi diambil untuk referensi
            'total_amount' => filter_input(INPUT_POST, 'total_amount', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
        ];

        // Validasi Kebutuhan Dasar
        if (empty($headerData['purchase_date']) || empty($headerData['supplier_id']) || empty($headerData['purchase_number'])) {
            setFlashMessage('error', 'Nomor, Tanggal Pembelian, dan Supplier wajib diisi.');
            header('Location: form.php');
            exit;
        }

        // 2. Ambil dan Validasi Data Item Detail
        $itemsData = [];
        $rawItems = $_POST['items'] ?? [];

        if (empty($rawItems)) {
            setFlashMessage('error', 'Transaksi pembelian harus memiliki minimal 1 item.');
            header('Location: form.php');
            exit;
        }

        foreach ($rawItems as $item) {
            $itemId = filter_var($item['item_id'], FILTER_VALIDATE_INT);
            $quantity = filter_var($item['quantity'], FILTER_VALIDATE_INT);
            $purchasePrice = filter_var($item['purchase_price'], FILTER_VALIDATE_FLOAT); 

            // Pastikan item valid dan kuantitas serta harga positif
            if ($itemId && $quantity > 0 && $purchasePrice >= 0) {
                $itemsData[] = [
                    'item_id' => $itemId,
                    'quantity' => $quantity,
                    'purchase_price' => $purchasePrice
                ];
            }
        }
        
        if (empty($itemsData)) {
            setFlashMessage('error', 'Tidak ada item yang valid untuk diproses. Pastikan semua item dipilih dan kuantitas > 0.');
            header('Location: form.php');
            exit;
        }

        // 3. Panggil method createPurchase yang menjalankan transaksi ATOMIK
        $purchaseId = $purchaseModel->createPurchase($headerData, $itemsData);

        // Sukses
        setFlashMessage('success', "Transaksi pembelian **#{$headerData['purchase_number']}** berhasil dicatat. Stok barang telah diperbarui.");
        header("Location: detail.php?id={$purchaseId}");
        
    } catch (Exception $e) {
        // Gagal (Rollback sudah dilakukan di model)
        setFlashMessage('error', 'Gagal mencatat transaksi: ' . $e->getMessage());
        
        // Simpan data POST ke session jika terjadi error agar user tidak mengulang input
        setFlashMessage('post_data', $_POST);
        
        header('Location: form.php');
        exit;
    }
} else {
    // Aksi tidak valid
    setFlashMessage('error', 'Aksi tidak valid.');
    header('Location: index.php');
}
exit;