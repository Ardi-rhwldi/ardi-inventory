<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/SaleTransaction.php';

header('Content-Type: application/json');

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['items']) || empty($input['items'])) {
    echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
    exit;
}

try {
    $saleModel = new SaleTransaction();
    
    $headerData = [
        'customer_id' => $input['customer_id'] ?: null,
        'subtotal' => $input['subtotal'],
        'discount_percent' => $input['discount_percent'] ?? 0,
        'discount_amount' => $input['discount_amount'] ?? 0,
        'total_amount' => $input['total_amount'],
        'payment_amount' => $input['payment_amount'],
        'change_amount' => $input['change_amount'],
        'payment_method' => $input['payment_method'] ?? 'cash',
        'notes' => $input['notes'] ?? null
    ];
    
    $itemsData = $input['items'];
    
    $result = $saleModel->createSale($headerData, $itemsData);
    
    echo json_encode([
        'success' => true,
        'sale_id' => $result['sale_id'],
        'sale_number' => $result['sale_number'],
        'message' => 'Transaksi berhasil disimpan'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
