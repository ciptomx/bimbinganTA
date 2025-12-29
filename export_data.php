<?php
// export_data.php
// Menggunakan __DIR__ untuk memastikan path selalu benar
require __DIR__ . '/session_check.php';
require __DIR__ . '/db_config.php';
require __DIR__ . '/fpdf/fpdf.php'; 

// ... (Sisa kode sama persis dengan versi sebelumnya) ...
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'kaprodi') {
    die('Akses ditolak.');
}

$format = $_GET['format'] ?? 'csv';
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$lecturer_initial = $_GET['lecturer'] ?? ''; // Ini adalah inisial

$query = "
    SELECT
        m.nim, m.nama AS nama_mahasiswa, s.judul, s.status, s.jumlah_pengambilan,
        DATE_FORMAT(s.tgl_sk_pembimbing, '%d-%m-%Y') as tgl_sk,
        DATE_FORMAT(s.tgl_lulus, '%d-%m-%Y') as tgl_lulus,
        d1.nama_lengkap AS pembimbing_1, d2.nama_lengkap AS pembimbing_2,
        d3.nama_lengkap AS penguji_1, d4.nama_lengkap AS penguji_2
    FROM skripsi s
    JOIN mahasiswa m ON s.mahasiswa_nim = m.nim
    LEFT JOIN dosen d1 ON s.pembimbing_1_id = d1.id
    LEFT JOIN dosen d2 ON s.pembimbing_2_id = d2.id
    LEFT JOIN dosen d3 ON s.penguji_1_id = d3.id
    LEFT JOIN dosen d4 ON s.penguji_2_id = d4.id
";

$whereClauses = []; $params = []; $types = '';

if (!empty($search)) {
    $searchTerm = '%' . $search . '%';
    $whereClauses[] = "(m.nim LIKE ? OR m.nama LIKE ?)";
    array_push($params, $searchTerm, $searchTerm); $types .= 'ss';
}
if (!empty($status)) {
    $whereClauses[] = "s.status = ?"; $params[] = $status; $types .= 's';
}
if (!empty($lecturer_initial)) {
    $dosen_id_query = $mysqli->prepare("SELECT id FROM dosen WHERE inisial = ?");
    $dosen_id_query->bind_param('s', $lecturer_initial);
    $dosen_id_query->execute();
    $dosen_id_result = $dosen_id_query->get_result()->fetch_assoc();
    if ($dosen_id_result) {
        $dosen_id = $dosen_id_result['id'];
        $whereClauses[] = "(s.pembimbing_1_id = ? OR s.pembimbing_2_id = ? OR s.penguji_1_id = ? OR s.penguji_2_id = ?)";
        array_push($params, $dosen_id, $dosen_id, $dosen_id, $dosen_id); $types .= 'iiii';
    }
}
if (!empty($whereClauses)) { $query .= " WHERE " . implode(" AND ", $whereClauses); }
$query .= " ORDER BY m.nim ASC";
$stmt = $mysqli->prepare($query);
if (!empty($types)) { $stmt->bind_param($types, ...$params); }
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$mysqli->close();

$filename = "laporan-tugas-akhir-" . date('Ymd') . "." . $format;
$headers = ['NIM', 'Nama Mahasiswa', 'Judul Tugas Akhir', 'Status', 'Tgl SK', 'Tgl Lulus', 'Pembimbing 1', 'Pembimbing 2', 'Penguji 1', 'Penguji 2', 'Pengambilan Ke'];

switch ($format) {
    case 'csv':
        header('Content-Type: text/csv'); header('Content-Disposition: attachment; filename="' . $filename . '"');
        $output = fopen('php://output', 'w'); fputcsv($output, $headers);
        foreach ($data as $row) { fputcsv($output, [$row['nim'], $row['nama_mahasiswa'], $row['judul'], $row['status'], $row['tgl_sk'], $row['tgl_lulus'], $row['pembimbing_1'], $row['pembimbing_2'], $row['penguji_1'], $row['penguji_2'], $row['jumlah_pengambilan']]); }
        fclose($output);
        break;
    case 'xls':
        header('Content-Type: application/vnd.ms-excel'); header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo '<table border="1"><tr><th>' . implode('</th><th>', $headers) . '</th></tr>';
        foreach ($data as $row) { echo '<tr><td>' . htmlspecialchars($row['nim']) . '</td><td>' . htmlspecialchars($row['nama_mahasiswa']) . '</td><td>' . htmlspecialchars($row['judul']) . '</td><td>' . htmlspecialchars($row['status']) . '</td><td>' . htmlspecialchars($row['tgl_sk']) . '</td><td>' . htmlspecialchars($row['tgl_lulus']) . '</td><td>' . htmlspecialchars($row['pembimbing_1']) . '</td><td>' . htmlspecialchars($row['pembimbing_2']) . '</td><td>' . htmlspecialchars($row['penguji_1']) . '</td><td>' . htmlspecialchars($row['penguji_2']) . '</td><td>' . htmlspecialchars($row['jumlah_pengambilan']) . '</td></tr>'; }
        echo '</table>';
        break;
    case 'pdf':
        class PDF extends FPDF {
            function Header() { $this->SetFont('Arial', 'B', 14); $this->Cell(0, 10, 'Laporan Data Tugas Akhir', 0, 1, 'C'); $this->SetFont('Arial', '', 10); $this->Cell(0, 5, 'Dibuat pada: ' . date('d F Y'), 0, 1, 'C'); $this->Ln(10); }
            function Footer() { $this->SetY(-15); $this->SetFont('Arial', 'I', 8); $this->Cell(0, 10, 'Halaman ' . $this->PageNo() . '/{nb}', 0, 0, 'C'); }
        }
        $pdf = new PDF('L', 'mm', 'A4'); $pdf->AliasNbPages(); $pdf->AddPage(); $pdf->SetFont('Arial', 'B', 8);
        $colWidths = [20, 40, 75, 18, 18, 18, 30, 30, 8]; $pdfHeaders = ['NIM', 'Nama Mahasiswa', 'Judul', 'Status', 'Tgl SK', 'Tgl Lulus', 'Pembimbing 1', 'Pembimbing 2', 'Ke-'];
        for ($i = 0; $i < count($pdfHeaders); $i++) { $pdf->Cell($colWidths[$i], 7, $pdfHeaders[$i], 1, 0, 'C'); }
        $pdf->Ln();
        $pdf->SetFont('Arial', '', 7);
        foreach ($data as $row) { $pdf->Cell($colWidths[0], 6, $row['nim'], 1); $pdf->Cell($colWidths[1], 6, $row['nama_mahasiswa'], 1); $pdf->Cell($colWidths[2], 6, substr($row['judul'], 0, 50), 1); $pdf->Cell($colWidths[3], 6, $row['status'], 1); $pdf->Cell($colWidths[4], 6, $row['tgl_sk'], 1); $pdf->Cell($colWidths[5], 6, $row['tgl_lulus'], 1); $pdf->Cell($colWidths[6], 6, $row['pembimbing_1'], 1); $pdf->Cell($colWidths[7], 6, $row['pembimbing_2'], 1); $pdf->Cell($colWidths[8], 6, $row['jumlah_pengambilan'], 1, 0, 'C'); $pdf->Ln(); }
        $pdf->Output('D', $filename);
        break;
}
exit;