<?php
// Pastikan file config dimuat pertama kali
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/Customer.php';

// Pastikan ID tersedia dan request diizinkan
if ($_SERVER['REQUEST_METHOD'] !== 'GET' || !isset($_GET['id']) || !is_numeric($_GET['id'])) {
    setFlashMessage('error', 'Permintaan tidak valid.');
    header('Location: index.php');
    exit;
}

requireAdmin();

$customerModel = new Customer();
$customer_id = $_GET['id'];

try {
    // Kita gunakan update untuk soft delete (mengubah is_active menjadi 0)
    $result = $customerModel->update($customer_id, ['is_active' => 0, 'updated_at' => date('Y-m-d H:i:s')]);
    
    if ($result) {
        setFlashMessage('success', 'Pelanggan berhasil dinonaktifkan.');
    } else {
        // Ini akan tertangkap jika ID tidak ditemukan atau update gagal
        setFlashMessage('error', 'Gagal menonaktifkan pelanggan. ID mungkin tidak ditemukan.');
    }
    
} catch (Exception $e) {
    setFlashMessage('error', 'Terjadi kesalahan database saat menghapus: ' . $e->getMessage());
}

header('Location: index.php');
exit;