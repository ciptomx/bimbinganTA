<?php
// api.php
header('Content-Type: application/json');
set_error_handler(function($severity, $message, $file, $line) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => "Server Error: $message in $file on line $line"]);
    exit;
});

require __DIR__ . '/session_check.php';
require __DIR__ . '/db_config.php';

global $mysqli;

try {
    // --- Ambil Parameter & Informasi Sesi ---
    $role = $_SESSION['role'];
    $dosen_id_session = $_SESSION['dosen_id'] ?? null;
    $search = $_GET['search'] ?? '';
    $status = $_GET['status'] ?? '';
    $lecturer_id_filter = $_GET['lecturer'] ?? '';
    $sortColumn = $_GET['sort'] ?? 'nim'; // Default sort column
    $sortDirection = $_GET['direction'] ?? 'asc'; // Default sort direction

    // --- Validasi Keamanan untuk Sorting ---
    $allowedSortColumns = ['nim', 'name', 'status', 'jumlah_pengambilan'];
    if (!in_array($sortColumn, $allowedSortColumns)) {
        $sortColumn = 'nim'; // Reset ke default jika tidak valid
    }
    $sortDirection = strtolower($sortDirection) === 'desc' ? 'DESC' : 'ASC';

    // --- Bangun Query SQL Dinamis ---
    $baseQuery = "
        SELECT
            m.nim, m.nama as name, s.judul as title, s.status, s.jumlah_pengambilan,
            p1.inisial as p1, p2.inisial as p2,
            u1.inisial as u1, u2.inisial as u2,
            DATEDIFF(s.tgl_lulus, s.tgl_sk_pembimbing) / 365.25 as durasi_lulus
        FROM mahasiswa m
        JOIN skripsi s ON m.nim = s.mahasiswa_nim
        LEFT JOIN dosen p1 ON s.pembimbing_1_id = p1.id
        LEFT JOIN dosen p2 ON s.pembimbing_2_id = p2.id
        LEFT JOIN dosen u1 ON s.penguji_1_id = u1.id
        LEFT JOIN dosen u2 ON s.penguji_2_id = u2.id
    ";

    $whereClauses = []; $params = []; $types = '';

    if ($role === 'dosen' && $dosen_id_session) {
        $whereClauses[] = "(s.pembimbing_1_id = ? OR s.pembimbing_2_id = ?)";
        array_push($params, $dosen_id_session, $dosen_id_session);
        $types .= 'ii';
    }
    if (!empty($search)) {
        $searchTerm = '%' . $search . '%';
        $whereClauses[] = "(m.nim LIKE ? OR m.nama LIKE ?)";
        array_push($params, $searchTerm, $searchTerm); $types .= 'ss';
    }
    if (!empty($status)) {
        $whereClauses[] = "s.status = ?"; $params[] = $status; $types .= 's';
    }
    if (!empty($lecturer_id_filter) && ($role === 'admin' || $role === 'kaprodi')) {
        $whereClauses[] = "(s.pembimbing_1_id = ? OR s.pembimbing_2_id = ?)";
        array_push($params, $lecturer_id_filter, $lecturer_id_filter); $types .= 'ii';
    }

    if (!empty($whereClauses)) { $baseQuery .= " WHERE " . implode(" AND ", $whereClauses); }
    $baseQuery .= " ORDER BY $sortColumn $sortDirection";
    
    $stmt = $mysqli->prepare($baseQuery);
    if (!$stmt) { throw new Exception("Query preparation failed: " . $mysqli->error); }
    if (!empty($types)) { $stmt->bind_param($types, ...$params); }
    $stmt->execute();
    $result = $stmt->get_result();
    $students = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    // --- Ambil Statistik & Insight (Hanya untuk Admin/Kaprodi) ---
    $stats = [];
    $insights = [];
    if ($role === 'admin' || $role === 'kaprodi') {
        $stats_result = $mysqli->query("SELECT (SELECT COUNT(*) FROM mahasiswa) as totalMahasiswa, (SELECT COUNT(*) FROM skripsi WHERE status='Lulus') as sudahLulus, (SELECT COUNT(*) FROM skripsi WHERE status='Belum Lulus') as belumLulus, (SELECT COUNT(*) FROM dosen) as totalDosen");
        $stats = $stats_result->fetch_assoc();
        
        // Insight Distribusi Bimbingan
        $distribusi_result = $mysqli->query("SELECT d.nama_lengkap as nama, COUNT(s.mahasiswa_nim) as jumlah FROM dosen d LEFT JOIN skripsi s ON d.id = s.pembimbing_1_id OR d.id = s.pembimbing_2_id GROUP BY d.id ORDER BY jumlah DESC");
        $insights['distribusi'] = $distribusi_result->fetch_all(MYSQLI_ASSOC);

        // Insight Rata-rata Kelulusan
        $avg_lulus_result = $mysqli->query("SELECT AVG(DATEDIFF(tgl_lulus, tgl_sk_pembimbing)) / 365.25 as avg_duration FROM skripsi WHERE status = 'Lulus' AND tgl_lulus IS NOT NULL AND tgl_sk_pembimbing IS NOT NULL");
        $insights['avg_lulus_tahun'] = $avg_lulus_result->fetch_assoc()['avg_duration'];

        // Insight Mahasiswa Terlama
        $terlama_result = $mysqli->query("SELECT m.nama, DATEDIFF(CURDATE(), s.tgl_sk_pembimbing) / 365.25 as durasi FROM skripsi s JOIN mahasiswa m ON s.mahasiswa_nim = m.nim WHERE s.status = 'Belum Lulus' ORDER BY durasi DESC LIMIT 1");
        $insights['terlama'] = $terlama_result->fetch_assoc();

    } else if ($role === 'dosen') {
        $stmt_stats = $mysqli->prepare("SELECT COUNT(*) as totalMahasiswa, SUM(CASE WHEN status = 'Lulus' THEN 1 ELSE 0 END) as sudahLulus, SUM(CASE WHEN status = 'Belum Lulus' THEN 1 ELSE 0 END) as belumLulus FROM skripsi WHERE pembimbing_1_id = ? OR pembimbing_2_id = ?");
        $stmt_stats->bind_param("ii", $dosen_id_session, $dosen_id_session);
        $stmt_stats->execute();
        $stats = $stmt_stats->get_result()->fetch_assoc();
        $stmt_stats->close();
    }
    
    // Ambil Daftar Dosen
    $lecturers_result = $mysqli->query("SELECT id, inisial, nama_lengkap FROM dosen ORDER BY nama_lengkap ASC");
    $lecturers = $lecturers_result->fetch_all(MYSQLI_ASSOC);
    
    echo json_encode([
        'success' => true, 'students' => $students, 'stats' => $stats, 'lecturers' => $lecturers, 'insights' => $insights
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An unhandled exception occurred: ' . $e->getMessage()]);
} finally {
    if (isset($mysqli) && $mysqli instanceof mysqli) {
        $mysqli->close();
    }
}

