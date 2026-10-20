<?php
// Penjaga halaman terproteksi: wajib login
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_pengguna'])) {
    header('Location: masuk.php');
    exit;
}
