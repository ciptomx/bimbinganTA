<?php
// auth.php
// Menggunakan __DIR__ untuk memastikan jalur file selalu benar.
require __DIR__ . '/db_config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$action = $_GET['action'] ?? '';

if ($action === 'login') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $_SESSION['login_error'] = 'Username dan password harus diisi.';
        header('Location: login.php');
        exit;
    }

    global $mysqli;

    // Kueri sekarang menggabungkan tabel dosen untuk mendapatkan nama_lengkap
    $stmt = $mysqli->prepare("
        SELECT u.id, u.username, u.password as hash, u.role, u.dosen_id, d.nama_lengkap 
        FROM users u 
        LEFT JOIN dosen d ON u.dosen_id = d.id 
        WHERE u.username = ?
    ");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    $mysqli->close();

    if ($user && password_verify($password, $user['hash'])) {
        // Login berhasil
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['dosen_id'] = $user['dosen_id'];
        
        // Simpan nama_lengkap jika ada (untuk dosen), jika tidak, gunakan username
        $_SESSION['display_name'] = $user['nama_lengkap'] ?? $user['username'];

        header('Location: index.php');
        exit;
    } else {
        // Login gagal
        $_SESSION['login_error'] = 'Username atau password salah.';
        header('Location: login.php');
        exit;
    }

} elseif ($action === 'logout') {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
} else {
    header('Location: login.php');
    exit;
}

