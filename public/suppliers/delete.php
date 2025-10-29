<?php
// C:\xampp 1\htdocs\ardi-inventory\public\suppliers\delete.php

// Sertakan file konfigurasi dan model
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/Supplier.php'; 

// Minta user untuk login
requireLogin(); 

$supplierModel = new Supplier();
$supplierId = $_GET['id'] ?? null;

if ($supplierId) {
    try {
        // Disarankan: Menonaktifkan (Soft Delete) daripada menghapus permanen
        $data = [
            'is_active' => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $success = $supplierModel->update($supplierId, $data);
        
        if ($success) {
            $_SESSION['success_message'] = "Supplier berhasil dinonaktifkan (Soft Deleted).";
        } else {
            $_SESSION['error_message'] = "Gagal menonaktifkan supplier.";
        }
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Terjadi kesalahan saat memproses: " . $e->getMessage();
    }
} else {
    $_SESSION['error_message'] = "ID Supplier tidak valid untuk dihapus.";
}

header('Location: index.php');
exit;