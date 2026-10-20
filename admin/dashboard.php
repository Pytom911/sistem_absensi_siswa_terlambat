<?php
require_once __DIR__ . '/../config/database.php';

$today = date('Y-m-d');

$totalSiswaStmt = $pdo->prepare('SELECT COUNT(*) AS total FROM siswa WHERE status = "Aktif"');
$totalSiswaStmt->execute();
$totalSiswa = $totalSiswaStmt->fetchColumn();

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
$persenKehadiran = $totalSiswa > 0 ? round(($stats['Hadir'] / $totalSiswa) * 100) : 0;

$latestStmt = $pdo->prepare('SELECT a.id_absensi, a.tanggal, s.nis, s.nama, a.status, a.jam_masuk, a.jam_keluar, a.keterangan FROM absensi a JOIN siswa s ON s.id_siswa = a.id_siswa ORDER BY a.created_at DESC LIMIT 6');
$latestStmt->execute();
$latestAbsensi = $latestStmt->fetchAll();

$summaryStmt = $pdo->prepare('SELECT a.status, COUNT(*) AS jumlah FROM absensi a JOIN siswa s ON s.id_siswa = a.id_siswa WHERE a.tanggal = ? GROUP BY a.status');
$summaryStmt->execute([$today]);
$summary = $summaryStmt->fetchAll();

$summaryMap = [];
foreach ($summary as $item) {
    $summaryMap[$item['status']] = $item['jumlah'];
}

function badgeClass($status) {
    return match ($status) {
        'Hadir' => 'badge-success',
        'Izin' => 'badge-warning',
        'Sakit' => 'badge-danger',
        'Terlambat' => 'badge-purple',
        default => 'badge-muted',
    };
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Absensi | XII RPL 1</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="app">
        <aside class="sidebar">
            <div class="logo">
                <div class="logo-icon">📘</div>
                <div>
                    <b>Absen Kelas</b>
                    <small>XII RPL 1</small>
                </div>
            </div>
            <nav>
                <a class="nav-item active" href="dashboard.php">Dashboard</a>
                <a class="nav-item" href="absensi.php">Absensi</a>
                <a class="nav-item" href="siswa.php">Daftar Siswa</a>
                <a class="nav-item" href="riwayat.php">Riwayat Absensi</a>
                <a class="nav-item" href="laporan.php">Laporan</a>
                <a class="nav-item" href="guru.php">Guru</a>
                <a class="nav-item logout-link" href="../index.php">Logout</a>
            </nav>
        </aside>
        <main class="main-content">
            <header class="topbar">
                <button id="menuToggle" class="menu-toggle">☰</button>
                <div class="page-title">Dashboard XII RPL 1</div>
                <div class="topbar-right">
                    <div class="user-info">
                        <span>Halo, Admin</span>
                        <div class="avatar">A</div>
                    </div>
                </div>
            </header>

            <section class="cards-grid">
                <article class="card card-blue">
                    <h3>Total Siswa</h3>
                    <p class="card-value"><?php echo htmlspecialchars($totalSiswa); ?></p>
                </article>
                <article class="card card-success">
                    <h3>Hadir Hari Ini</h3>
                    <p class="card-value"><?php echo htmlspecialchars($stats['Hadir']); ?></p>
                </article>
                <article class="card card-warning">
                    <h3>Izin Hari Ini</h3>
                    <p class="card-value"><?php echo htmlspecialchars($stats['Izin']); ?></p>
                </article>
                <article class="card card-danger">
                    <h3>Sakit Hari Ini</h3>
                    <p class="card-value"><?php echo htmlspecialchars($stats['Sakit']); ?></p>
                </article>
                <article class="card card-muted">
                    <h3>Alfa Hari Ini</h3>
                    <p class="card-value"><?php echo htmlspecialchars($stats['Alfa']); ?></p>
                </article>
                <article class="card card-purple">
                    <h3>Terlambat Hari Ini</h3>
                    <p class="card-value"><?php echo htmlspecialchars($stats['Terlambat']); ?></p>
                </article>
            </section>

            <section class="dashboard-section">
                <div class="section-header">
                    <div>
                        <h2>Ringkasan Absensi</h2>
                        <p><?php echo date('d F Y'); ?></p>
                    </div>
                </div>
                <div class="summary-cards">
                    <div class="summary-card">
                        <span>Persentase Kehadiran</span>
                        <strong><?php echo $persenKehadiran; ?>%</strong>
                    </div>
                    <div class="summary-card">
                        <span>Total Kehadiran Hari Ini</span>
                        <strong><?php echo htmlspecialchars($totalToday); ?></strong>
                    </div>
                    <div class="summary-card">
                        <span>Data Terbaru</span>
                        <strong><?php echo count($latestAbsensi); ?> entri</strong>
                    </div>
                </div>
                <div class="status-summary">
                    <?php foreach ($summaryMap as $status => $count): ?>
                        <div class="status-chip <?php echo badgeClass($status); ?>">
                            <span><?php echo htmlspecialchars($status); ?></span>
                            <strong><?php echo htmlspecialchars($count); ?></strong>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="dashboard-section">
                <div class="section-header">
                    <h2>Absensi Terbaru</h2>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>NIS</th>
                                <th>Nama</th>
                                <th>Status</th>
                                <th>Jam Masuk</th>
                                <th>Jam Keluar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($latestAbsensi as $index => $row): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                                    <td><?php echo htmlspecialchars($row['nis']); ?></td>
                                    <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                    <td><span class="badge <?php echo badgeClass($row['status']); ?>"><?php echo htmlspecialchars($row['status']); ?></span></td>
                                    <td><?php echo htmlspecialchars($row['jam_masuk'] ?: '-'); ?></td>
                                    <td><?php echo htmlspecialchars($row['jam_keluar'] ?: '-'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <script src="../assets/js/script.js"></script>
</body>
</html>
