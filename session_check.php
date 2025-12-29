<?php
// session_check.php
// Memulai sesi hanya jika belum ada.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Jika ID pengguna tidak ada di sesi, berarti belum login.
// Alihkan ke halaman login.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}