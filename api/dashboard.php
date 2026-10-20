<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

try {
    $today = date('Y-m-d');
    $totalSiswaStmt = $pdo->prepare('SELECT COUNT(*) AS total FROM siswa WHERE status = "Aktif"');
    $totalSiswaStmt->execute();
    $totalSiswa = (int) $totalSiswaStmt->fetchColumn();

    $statusCountsStmt = $pdo->prepare('SELECT status, COUNT(*) AS jumlah FROM absensi WHERE tanggal = ? GROUP BY status');
    $statusCountsStmt->execute([$today]);
    $statusCounts = $statusCountsStmt->fetchAll();

    $stats = [
        'Hadir' => 0,
        'Izin' => 0,
        'Sakit' => 0,
        'Alfa' => 0,
        'Terlambat' => 0,
    ];
    foreach ($statusCounts as $row) {
        $stats[$row['status']] = (int) $row['jumlah'];
    }

    $totalToday = array_sum($stats);
    $persentase = $totalSiswa > 0 ? round(($stats['Hadir'] / $totalSiswa) * 100) : 0;

    echo json_encode([
        'success' => true,
        'message' => 'Dashboard data berhasil diambil.',
        'data' => [
            'total_siswa' => $totalSiswa,
            'status' => $stats,
            'persentase' => $persentase,
            'total_hari_ini' => $totalToday,
        ],
    ]);
} catch (Exception $e) {
    error_log('API dashboard error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Gagal mengambil data dashboard.']);
}
