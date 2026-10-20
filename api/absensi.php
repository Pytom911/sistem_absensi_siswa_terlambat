<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

function respond($success, $message, $data = []) {
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
    exit;
}

function validateStatus($status) {
    $valid = ['Hadir', 'Izin', 'Sakit', 'Alfa', 'Terlambat'];
    return in_array($status, $valid, true);
}

try {
    if ($method === 'GET') {
        $tanggal = $_GET['tanggal'] ?? date('Y-m-d');
        $query = 'SELECT a.id_absensi, a.id_siswa, s.nis, s.nama, a.tanggal, a.status, a.jam_masuk, a.jam_keluar, a.keterangan, a.dibuat_oleh FROM absensi a JOIN siswa s ON s.id_siswa = a.id_siswa WHERE a.tanggal = ? ORDER BY s.nama ASC';
        $stmt = $pdo->prepare($query);
        $stmt->execute([$tanggal]);
        $data = $stmt->fetchAll();
        respond(true, 'Data absensi berhasil diambil.', $data);
    }

    if ($method === 'POST') {
        $idSiswa = (int) ($input['id_siswa'] ?? 0);
        $tanggal = $input['tanggal'] ?? date('Y-m-d');
        $status = $input['status'] ?? '';
        $jamMasuk = $input['jam_masuk'] ?? null;
        $jamKeluar = $input['jam_keluar'] ?? null;
        $keterangan = trim($input['keterangan'] ?? '');

        if (!$idSiswa || !validateStatus($status)) {
            respond(false, 'Data tidak valid. Pastikan siswa dan status terisi.');
        }

        if (in_array($status, ['Hadir', 'Terlambat'], true) && !$jamMasuk) {
            $jamMasuk = date('H:i:s');
        }
        if (in_array($status, ['Izin', 'Sakit', 'Alfa'], true)) {
            $jamMasuk = null;
        }

        $checkStmt = $pdo->prepare('SELECT id_absensi FROM absensi WHERE id_siswa = ? AND tanggal = ? LIMIT 1');
        $checkStmt->execute([$idSiswa, $tanggal]);
        $existing = $checkStmt->fetch();

        $createdBy = null;
        if ($existing) {
            $updateStmt = $pdo->prepare('UPDATE absensi SET status = ?, jam_masuk = ?, jam_keluar = ?, keterangan = ?, dibuat_oleh = ? WHERE id_absensi = ?');
            $updateStmt->execute([$status, $jamMasuk, $jamKeluar, $keterangan ?: null, $createdBy, $existing['id_absensi']]);
            respond(true, 'Absensi berhasil diperbarui.', ['id_absensi' => $existing['id_absensi']]);
        }

        $insertStmt = $pdo->prepare('INSERT INTO absensi (id_siswa, tanggal, status, jam_masuk, jam_keluar, keterangan, dibuat_oleh) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $insertStmt->execute([$idSiswa, $tanggal, $status, $jamMasuk, $jamKeluar, $keterangan ?: null, $createdBy]);
        respond(true, 'Absensi berhasil disimpan.', ['id_absensi' => $pdo->lastInsertId()]);
    }

    if ($method === 'PUT') {
        $idAbsensi = (int) ($input['id_absensi'] ?? 0);
        if (!$idAbsensi) {
            respond(false, 'ID absensi tidak valid.');
        }
        $status = $input['status'] ?? '';
        $jamMasuk = $input['jam_masuk'] ?? null;
        $jamKeluar = $input['jam_keluar'] ?? null;
        $keterangan = trim($input['keterangan'] ?? '');

        if ($status !== '' && !validateStatus($status)) {
            respond(false, 'Status absensi tidak valid.');
        }

        $queryParts = [];
        $params = [];
        if ($status !== '') {
            $queryParts[] = 'status = ?';
            $params[] = $status;
        }
        $queryParts[] = 'jam_masuk = ?';
        $params[] = $jamMasuk ?: null;
        $queryParts[] = 'jam_keluar = ?';
        $params[] = $jamKeluar ?: null;
        $queryParts[] = 'keterangan = ?';
        $params[] = $keterangan ?: null;
        $createdBy = null;
        $queryParts[] = 'dibuat_oleh = ?';
        $params[] = $createdBy;
        $params[] = $idAbsensi;

        $updateStmt = $pdo->prepare('UPDATE absensi SET ' . implode(', ', $queryParts) . ' WHERE id_absensi = ?');
        $updateStmt->execute($params);
        respond(true, 'Absensi berhasil diperbarui.');
    }

    if ($method === 'DELETE') {
        $idAbsensi = (int) ($input['id_absensi'] ?? 0);
        if (!$idAbsensi) {
            respond(false, 'ID absensi tidak valid.');
        }

        $deleteStmt = $pdo->prepare('DELETE FROM absensi WHERE id_absensi = ?');
        $deleteStmt->execute([$idAbsensi]);
        respond(true, 'Absensi berhasil dihapus.');
    }

    respond(false, 'Metode tidak didukung.');
} catch (Exception $e) {
    error_log('API absensi error: ' . $e->getMessage());
    respond(false, 'Terjadi kesalahan pada server. Silakan coba lagi.');
}
