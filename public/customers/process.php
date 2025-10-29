<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/Customer.php';

// Pastikan hanya POST request yang diizinkan
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

requireAdmin();

$customerModel = new Customer();
$action = $_POST['action'] ?? '';
$data = [];
$errors = [];

// 1. Ambil & Sanitize Data
$customer_id = filter_input(INPUT_POST, 'customer_id', FILTER_SANITIZE_NUMBER_INT);
$data['customer_name'] = filter_input(INPUT_POST, 'customer_name', FILTER_SANITIZE_STRING);
$data['phone'] = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
$data['email'] = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$data['address'] = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
$data['city'] = filter_input(INPUT_POST, 'city', FILTER_SANITIZE_STRING);

// 2. Validasi Data
if (empty($data['customer_name'])) {
    $errors['customer_name'] = 'Nama pelanggan wajib diisi.';
}
if (empty($data['phone'])) {
    $errors['phone'] = 'Nomor telepon wajib diisi.';
}

if (!empty($errors)) {
    // Simpan data POST dan errors ke session dan kembali ke form
    setFlashMessage('errors', $errors);
    setFlashMessage('post_data', $data);
    
    $redirectUrl = ($action === 'update' && $customer_id) ? "form.php?id={$customer_id}" : "form.php";
    header("Location: {$redirectUrl}");
    exit;
}

// 3. Proses Aksi
try {
    if ($action === 'create') {
        // CREATE
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        $data['is_active'] = 1; 
        
        $customerModel->insert($data);
        setFlashMessage('success', 'Data pelanggan berhasil ditambahkan.');
        
    } elseif ($action === 'update' && $customer_id) {
        // UPDATE
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        // Hapus customer_id dari data sebelum update
        unset($data['customer_id']); 
        
        $customerModel->update($customer_id, $data);
        setFlashMessage('success', 'Data pelanggan berhasil diperbarui.');
        
    } else {
        setFlashMessage('error', 'Aksi tidak valid.');
    }
} catch (Exception $e) {
    setFlashMessage('error', 'Terjadi kesalahan database: ' . $e->getMessage());
}

header('Location: index.php');
exit;