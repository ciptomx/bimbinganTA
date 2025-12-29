<?php
// db_config.php
// Harap sesuaikan detail koneksi ini dengan pengaturan server database Anda.

define('DB_HOST', 'localhost');
define('DB_USER', 'u767676268_bimbingan');
define('DB_PASS', 'CiP300383#');
define('DB_NAME', 'u767676268_bimbingan');

// Matikan pelaporan galat default untuk penanganan manual
mysqli_report(MYSQLI_REPORT_OFF);

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Pemeriksaan koneksi yang sangat penting
if ($mysqli->connect_error) {
    // Hentikan skrip dan berikan pesan yang jelas jika koneksi gagal
    http_response_code(500);
    die("Koneksi Database GAGAL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error);
}

$mysqli->set_charset("utf8mb4");