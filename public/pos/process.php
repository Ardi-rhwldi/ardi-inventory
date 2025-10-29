<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/SaleTransaction.php';

// PENTING: Set header ke JSON di awal, agar output selalu JSON
header('Content-Type: application/json');

// Periksa Login. Jika gagal, hentikan eksekusi dan kirim JSON error.
// Kita harus menimpa default requireLogin agar tidak melakukan redirect HTML.
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Akses ditolak: Pengguna belum login.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['items']) || empty($input['items'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Data transaksi tidak valid atau keranjang kosong.']);
    exit;
}

try {
    $saleModel = new SaleTransaction();
    
    // Validasi data input
    $headerData = [
        'customer_id' => $input['customer_id'] ?: null,
        'subtotal' => (float)($input['subtotal'] ?? 0),
        'discount_percent' => (float)($input['discount_percent'] ?? 0),
        'discount_amount' => (float)($input['discount_amount'] ?? 0),
        'total_amount' => (float)($input['total_amount'] ?? 0),
        'payment_amount' => (float)($input['payment_amount'] ?? 0),
        'change_amount' => (float)($input['change_amount'] ?? 0),
        'payment_method' => $input['payment_method'] ?? 'cash',
        'notes' => $input['notes'] ?? null
    ];
    
    $itemsData = $input['items'];
    
    // KODE INTI: Memproses Transaksi
    $result = $saleModel->createSale($headerData, $itemsData);
    
    // Berhasil
    echo json_encode([
        'success' => true,
        'sale_id' => $result['sale_id'],
        'sale_number' => $result['sale_number'],
        'message' => 'Transaksi berhasil diproses.'
    ]);
    
} catch (\Exception $e) {
    // Tangani error database atau logic lainnya
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error Server: ' . $e->getMessage()]);
}

// Pastikan tidak ada kode lain di bawah ini
?>