<?php
/**
 * Application Configuration
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('APP_NAME', 'Sistem POS & Inventori');
define('APP_VERSION', '1.0.0');

// Sesuaikan dengan struktur project kamu (pakai folder public)
define('BASE_URL', 'http://localhost/ardi-inventory/public');

date_default_timezone_set('Asia/Jakarta');

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Tambahkan ini di config/config.php
function requireAdmin() {
    if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
        setFlashMessage('danger', 'Akses ditolak. Hanya Admin yang diizinkan.');
        redirect(url('../public/pos/index.php')); // Atau ke halaman lain
    }
}

require_once __DIR__ . '/../config/database.php'; // <- Sesuaikan jika config di luar folder public

function redirect($url) {
    header('Location: ' . $url);
    exit();
}

function asset($path) {
    return BASE_URL . '/assets/' . ltrim($path, '/');
}

function url($path = '') {
    return BASE_URL . '/' . ltrim($path, '/');
}

function formatRupiah($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

function formatDate($date, $format = 'd/m/Y H:i') {
    return date($format, strtotime($date));
}

function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect(url('login.php'));
    }
}
