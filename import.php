<?php
// import.php
// Diperbarui untuk membuat akun pengguna default saat impor.

set_time_limit(300);
ini_set('memory_limit', '256M');

require 'db_config.php';

echo "<h1>Proses Impor Data dan Pembuatan Akun</h1>";

function parseDate($dateStr) {
    if (empty($dateStr) || $dateStr === '0000-00-00') return null;
    try {
        return (new DateTime($dateStr))->format('Y-m-d');
    } catch (Exception $e) {
        return null;
    }
}

function getLecturerInitial($name, $map) {
    if (empty($name)) return null;
    $normalizedName = strtolower(trim(preg_replace('/,.*$/', '', $name)));
    foreach ($map as $initial => $fullName) {
        if ($normalizedName == strtolower(trim(preg_replace('/,.*$/', '', $fullName)))) {
            return $initial;
        }
    }
    return strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $name), 0, 4));
}

try {
    $conn->query("SET FOREIGN_KEY_CHECKS=0");
    $conn->query("TRUNCATE TABLE `skripsi`");
    $conn->query("TRUNCATE TABLE `mahasiswa`");
    $conn->query("TRUNCATE TABLE `dosen`");
    $conn->query("TRUNCATE TABLE `users`");
    echo "<p>Semua tabel berhasil dikosongkan.</p>";

    $lecturerMap = [
        'ASR' => 'Asrul Abdullah, S.Kom., M.Cs', 'SCP' => 'Sucipto, M.Kom',
        'BRY' => 'Barry Ceasar Octariadi, S.Kom., M.Cs', 'SYF' => 'Syf Putri Agustini Alkadri, S.T., M.Kom',
        'ALD' => 'Alda Cendekia Siregar, S.Kom., M.Cs', 'WHD' => 'Rachmat Wahid Saleh Insani, S.Kom., M.Cs',
        'ISTI' => 'Istikoma, B.Sc., M.IT',
    ];
    $stmtDosen = $conn->prepare("INSERT INTO dosen (inisial, nama_lengkap) VALUES (?, ?)");
    $dosenIdMap = [];
    foreach ($lecturerMap as $initial => $fullName) {
        $stmtDosen->bind_param("ss", $initial, $fullName);
        $stmtDosen->execute();
        $dosenIdMap[$initial] = $conn->insert_id;
    }
    $stmtDosen->close();
    echo "<p>Data Dosen berhasil diimpor.</p>";

    echo "<p>Membuat akun pengguna default...</p>";
    $stmtUser = $conn->prepare("INSERT INTO users (username, password, role, dosen_id) VALUES (?, ?, ?, ?)");
    
    // Akun Admin
    $adminPass = password_hash('admin', PASSWORD_DEFAULT);
    $stmtUser->bind_param("sssi", $adminUsername, $adminPass, $adminRole, $adminDosenId);
    $adminUsername = 'admin'; $adminRole = 'admin'; $adminDosenId = null;
    $stmtUser->execute();
    echo "<p> - Akun Admin dibuat (user: admin, pass: admin)</p>";

    // Akun Kaprodi
    $kaprodiPass = password_hash('kaprodi', PASSWORD_DEFAULT);
    $stmtUser->bind_param("sssi", $kaprodiUsername, $kaprodiPass, $kaprodiRole, $kaprodiDosenId);
    $kaprodiUsername = 'kaprodi'; $kaprodiRole = 'kaprodi'; $kaprodiDosenId = null;
    $stmtUser->execute();
    echo "<p> - Akun Kaprodi dibuat (user: kaprodi, pass: kaprodi)</p>";

    // Akun Dosen
    $dosenPassDefault = password_hash('dosen123', PASSWORD_DEFAULT);
    $dosenRole = 'dosen';
    foreach($dosenIdMap as $initial => $id) {
        $dosenUsername = strtolower($initial);
        $stmtUser->bind_param("sssi", $dosenUsername, $dosenPassDefault, $dosenRole, $id);
        $stmtUser->execute();
        echo "<p> - Akun Dosen dibuat (user: ".strtolower($initial).", pass: dosen123)</p>";
    }
    $stmtUser->close();

    $files = ['Pembimbing_dan_penguji.csv', 'Sudah_Lulus.csv', 'Bimbingan_Belum_Lulus.csv'];
    $studentsData = [];
    foreach ($files as $file) {
        if (($handle = fopen($file, "r")) !== FALSE) {
            fgetcsv($handle);
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $nim = trim($data[4] ?? ''); if (empty($nim)) continue;
                if (!isset($studentsData[$nim])) {
                    $studentsData[$nim] = ['name' => trim($data[5] ?? ''), 'title' => trim($data[6] ?? ''), 'p1' => getLecturerInitial(trim($data[7] ?? ''), $lecturerMap), 'p2' => getLecturerInitial(trim($data[8] ?? ''), $lecturerMap), 'u1' => getLecturerInitial(trim($data[9] ?? ''), $lecturerMap), 'u2' => getLecturerInitial(trim($data[10] ?? ''), $lecturerMap), 'skDate' => parseDate(trim($data[1] ?? '')), 'passDate' => parseDate(trim($data[3] ?? '')), 'status' => 'Data Tidak Lengkap'];
                }
                if ($file === 'Sudah_Lulus.csv') {
                    $studentsData[$nim]['status'] = 'Lulus'; if (empty($studentsData[$nim]['passDate'])) { $studentsData[$nim]['passDate'] = parseDate(trim($data[3] ?? '')); }
                } elseif ($file === 'Bimbingan_Belum_Lulus.csv' && $studentsData[$nim]['status'] !== 'Lulus') {
                    $studentsData[$nim]['status'] = 'Belum Lulus';
                }
            }
            fclose($handle);
        }
    }
    echo "<p>Data CSV berhasil dibaca.</p>";

    $stmtMhs = $conn->prepare("INSERT INTO mahasiswa (nim, nama) VALUES (?, ?) ON DUPLICATE KEY UPDATE nama=VALUES(nama)");
    $stmtSkripsi = $conn->prepare("INSERT INTO skripsi (mahasiswa_nim, judul, status, tgl_sk_pembimbing, tgl_lulus, pembimbing_1_id, pembimbing_2_id, penguji_1_id, penguji_2_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($studentsData as $nim => $data) {
        $stmtMhs->bind_param("ss", $nim, $data['name']); $stmtMhs->execute();
        $p1_id = $dosenIdMap[$data['p1']] ?? null; $p2_id = $dosenIdMap[$data['p2']] ?? null;
        $u1_id = $dosenIdMap[$data['u1']] ?? null; $u2_id = $dosenIdMap[$data['u2']] ?? null;
        $stmtSkripsi->bind_param("sssssiiii", $nim, $data['title'], $data['status'], $data['skDate'], $data['passDate'], $p1_id, $p2_id, $u1_id, $u2_id);
        $stmtSkripsi->execute();
    }
    $stmtMhs->close(); $stmtSkripsi->close();

    $conn->query("SET FOREIGN_KEY_CHECKS=1");
    echo "<h2>Proses Impor Selesai!</h2>";
} catch (Exception $e) {
    $conn->query("SET FOREIGN_KEY_CHECKS=1");
    echo "<h2>Terjadi Kesalahan:</h2><p>" . $e->getMessage() . "</p>";
}
$conn->close();


