<?php
// C:\xampp 1\htdocs\ardi-inventory\public\suppliers\action.php

// Sertakan file konfigurasi dan model
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/Supplier.php'; 

// Minta user untuk login
requireLogin(); 

$supplierModel = new Supplier();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_supplier'])) {
    
    // 1. Ambil dan validasi data
    $supplierId = $_POST['supplier_id'] ?? null;
    $supplierName = trim($_POST['supplier_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if (empty($supplierName) || empty($phone)) {
        $_SESSION['error_message'] = "Nama Supplier dan Telepon wajib diisi.";
        header('Location: ' . ($supplierId ? "form.php?id=$supplierId" : "form.php"));
        exit;
    }

    // 2. Siapkan data untuk disimpan
    $data = [
        'supplier_name'    => $supplierName,
        'contact_person'   => trim($_POST['contact_person'] ?? ''),
        'phone'            => $phone,
        'email'            => trim($_POST['email'] ?? ''),
        'address'          => trim($_POST['address'] ?? ''),
        'city'             => trim($_POST['city'] ?? ''),
    ];

    if ($supplierId) {
        // Mode EDIT: Tambahkan kolom is_active dan updated_at
        $data['is_active'] = isset($_POST['is_active']) ? 1 : 0;
        $data['updated_at'] = date('Y-m-d H:i:s');
        $success = $supplierModel->update($supplierId, $data);
        $message = "Supplier **" . htmlspecialchars($supplierName) . "** berhasil diperbarui.";
    } else {
        // Mode TAMBAH BARU: Tambahkan created_at dan is_active default
        $data['is_active'] = 1; // Default aktif
        $data['created_at'] = date('Y-m-d H:i:s');
        $success = $supplierModel->insert($data);
        $message = "Supplier **" . htmlspecialchars($supplierName) . "** berhasil ditambahkan.";
    }
    
    // 3. Beri feedback dan redirect
    if ($success) {
        $_SESSION['success_message'] = $message;
    } else {
        $_SESSION['error_message'] = "Gagal menyimpan data supplier. Silakan coba lagi.";
    }

    header('Location: index.php');
    exit;

} else {
    // Jika diakses langsung tanpa POST, redirect
    header('Location: index.php');
    exit;
}