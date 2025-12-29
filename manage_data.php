<?php
// manage_data.php
header('Content-Type: application/json');
// Menggunakan __DIR__ untuk memastikan path selalu benar
require __DIR__ . '/session_check.php';
require __DIR__ . '/db_config.php';

// ... (Sisa kode sama persis dengan versi sebelumnya) ...
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'kaprodi') {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak.']);
    exit;
}

$action = $_GET['action'] ?? '';
$input = json_decode(file_get_contents('php://input'), true);

try {
    switch ($action) {
        case 'get_skripsi':
            $nim = $_GET['nim'] ?? '';
            if (empty($nim)) { throw new Exception('NIM tidak boleh kosong.'); }
            $stmt = $mysqli->prepare("SELECT m.nim as mahasiswa_nim, m.nama, s.* FROM skripsi s JOIN mahasiswa m ON s.mahasiswa_nim = m.nim WHERE m.nim = ?");
            $stmt->bind_param("s", $nim);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($data = $result->fetch_assoc()) {
                echo json_encode(['success' => true, 'data' => $data]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Data tidak ditemukan.']);
            }
            $stmt->close();
            break;

        case 'add_skripsi':
            $mysqli->begin_transaction();
            // Cek apakah mahasiswa sudah ada
            $stmt = $mysqli->prepare("SELECT nim FROM mahasiswa WHERE nim = ?");
            $stmt->bind_param("s", $input['nim']);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 0) {
                // Jika tidak ada, tambahkan mahasiswa baru
                $stmt_mhs = $mysqli->prepare("INSERT INTO mahasiswa (nim, nama) VALUES (?, ?)");
                $stmt_mhs->bind_param("ss", $input['nim'], $input['nama']);
                $stmt_mhs->execute();
                $stmt_mhs->close();
            }
            // Tambahkan data skripsi
            $stmt_skripsi = $mysqli->prepare("INSERT INTO skripsi (mahasiswa_nim, judul, status, pembimbing_1_id, pembimbing_2_id, penguji_1_id, penguji_2_id, tgl_sk_pembimbing, tgl_lulus, jumlah_pengambilan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt_skripsi->bind_param("sssiissssi", $input['nim'], $input['judul'], $input['status'], $input['pembimbing_1_id'], $input['pembimbing_2_id'], $input['penguji_1_id'], $input['penguji_2_id'], $input['tgl_sk_pembimbing'], $input['tgl_lulus'], $input['jumlah_pengambilan']);
            $stmt_skripsi->execute();
            $mysqli->commit();
            echo json_encode(['success' => true, 'message' => 'Data berhasil ditambahkan.']);
            break;

        case 'update_skripsi':
            $mysqli->begin_transaction();
            // Update data mahasiswa
            $stmt_mhs = $mysqli->prepare("UPDATE mahasiswa SET nama = ? WHERE nim = ?");
            $stmt_mhs->bind_param("ss", $input['nama'], $input['nim']);
            $stmt_mhs->execute();
            // Update data skripsi
            $stmt_skripsi = $mysqli->prepare("UPDATE skripsi SET judul = ?, status = ?, pembimbing_1_id = ?, pembimbing_2_id = ?, penguji_1_id = ?, penguji_2_id = ?, tgl_sk_pembimbing = ?, tgl_lulus = ?, jumlah_pengambilan = ? WHERE mahasiswa_nim = ?");
            $stmt_skripsi->bind_param("ssiiisssis", $input['judul'], $input['status'], $input['pembimbing_1_id'], $input['pembimbing_2_id'], $input['penguji_1_id'], $input['penguji_2_id'], $input['tgl_sk_pembimbing'], $input['tgl_lulus'], $input['jumlah_pengambilan'], $input['nim']);
            $stmt_skripsi->execute();
            $mysqli->commit();
            echo json_encode(['success' => true, 'message' => 'Data berhasil diperbarui.']);
            break;

        case 'delete_skripsi':
             // Hapus data skripsi terlebih dahulu karena ada foreign key
            $stmt = $mysqli->prepare("DELETE FROM skripsi WHERE mahasiswa_nim = ?");
            $stmt->bind_param("s", $input['nim']);
            $stmt->execute();
             // Kemudian hapus data mahasiswa
            $stmt_mhs = $mysqli->prepare("DELETE FROM mahasiswa WHERE nim = ?");
            $stmt_mhs->bind_param("s", $input['nim']);
            $stmt_mhs->execute();
            if ($stmt_mhs->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Data berhasil dihapus.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal menghapus data atau data tidak ditemukan.']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Aksi tidak valid.']);
            break;
    }
} catch (Exception $e) {
    if ($mysqli->in_transaction) {
        $mysqli->rollback();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
} finally {
    if (isset($mysqli) && $mysqli instanceof mysqli) {
        $mysqli->close();
    }
}