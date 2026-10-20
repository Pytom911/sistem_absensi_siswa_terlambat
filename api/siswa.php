<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

function respond($success, $message, $data = []) {
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
    exit;
}

try {
    if ($method === 'GET') {
        $stmt = $pdo->query('SELECT id_siswa, nis, nama, jenis_kelamin, alamat, no_hp, status FROM siswa ORDER BY nama ASC');
        $data = $stmt->fetchAll();
        respond(true, 'Data siswa berhasil diambil.', $data);
    }

    if ($method === 'POST') {
        $nis = trim($input['nis'] ?? '');
        $nama = trim($input['nama'] ?? '');
        $jenisKelamin = $input['jenis_kelamin'] ?? '';
        $alamat = trim($input['alamat'] ?? '');
        $noHp = trim($input['no_hp'] ?? '');
        $status = $input['status'] ?? 'Aktif';

        if ($nis === '' || $nama === '' || !in_array($jenisKelamin, ['L', 'P'], true)) {
            respond(false, 'Data siswa tidak valid.');
        }

        $stmt = $pdo->prepare('INSERT INTO siswa (nis, nama, jenis_kelamin, alamat, no_hp, status) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$nis, $nama, $jenisKelamin, $alamat ?: null, $noHp ?: null, $status]);
        respond(true, 'Data siswa berhasil ditambahkan.', ['id_siswa' => $pdo->lastInsertId()]);
    }

    if ($method === 'PUT') {
        $idSiswa = (int) ($input['id_siswa'] ?? 0);
        $nis = trim($input['nis'] ?? '');
        $nama = trim($input['nama'] ?? '');
        $jenisKelamin = $input['jenis_kelamin'] ?? '';
        $alamat = trim($input['alamat'] ?? '');
        $noHp = trim($input['no_hp'] ?? '');
        $status = $input['status'] ?? 'Aktif';

        if (!$idSiswa || $nis === '' || $nama === '' || !in_array($jenisKelamin, ['L', 'P'], true)) {
            respond(false, 'Data siswa tidak valid.');
        }

        $stmt = $pdo->prepare('UPDATE siswa SET nis = ?, nama = ?, jenis_kelamin = ?, alamat = ?, no_hp = ?, status = ? WHERE id_siswa = ?');
        $stmt->execute([$nis, $nama, $jenisKelamin, $alamat ?: null, $noHp ?: null, $status, $idSiswa]);
        respond(true, 'Data siswa berhasil diperbarui.');
    }

    if ($method === 'DELETE') {
        $idSiswa = (int) ($input['id_siswa'] ?? 0);
        if (!$idSiswa) {
            respond(false, 'ID siswa tidak valid.');
        }
        $stmt = $pdo->prepare('DELETE FROM siswa WHERE id_siswa = ?');
        $stmt->execute([$idSiswa]);
        respond(true, 'Data siswa berhasil dihapus.');
    }

    respond(false, 'Metode tidak didukung.');
} catch (Exception $e) {
    error_log('API siswa error: ' . $e->getMessage());
    respond(false, 'Terjadi kesalahan pada server. Silakan coba lagi.');
}
