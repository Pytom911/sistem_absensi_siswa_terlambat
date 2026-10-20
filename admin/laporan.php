<?php
require_once __DIR__ . '/../config/database.php';

$month = $_GET['month'] ?? date('Y-m');
$fromDate = $_GET['from_date'] ?? '';
$toDate = $_GET['to_date'] ?? '';

$start = $fromDate ?: ($month ? $month . '-01' : date('Y-m-01'));
$end = $toDate ?: date('Y-m-t', strtotime($start));

$reportStmt = $pdo->prepare(
    'SELECT s.id_siswa, s.nis, s.nama,
        SUM(a.status = "Hadir") AS hadir,
        SUM(a.status = "Izin") AS izin,
        SUM(a.status = "Sakit") AS sakit,
        SUM(a.status = "Alfa") AS alfa,
        SUM(a.status = "Terlambat") AS terlambat,
        COUNT(a.id_absensi) AS total_pertemuan
     FROM siswa s
     LEFT JOIN absensi a ON a.id_siswa = s.id_siswa AND a.tanggal BETWEEN ? AND ?
     WHERE s.status = "Aktif"
     GROUP BY s.id_siswa
     ORDER BY s.nama ASC'
);
$reportStmt->execute([$start, $end]);
$reportData = $reportStmt->fetchAll();

function attendancePercent($hadir, $total) {
    return $total > 0 ? round(($hadir / $total) * 100) : 0;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Absensi | XII RPL 1</title>
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
                <a class="nav-item" href="dashboard.php">Dashboard</a>
                <a class="nav-item" href="absensi.php">Absensi</a>
                <a class="nav-item" href="siswa.php">Daftar Siswa</a>
                <a class="nav-item" href="riwayat.php">Riwayat Absensi</a>
                <a class="nav-item active" href="laporan.php">Laporan</a>
                <a class="nav-item" href="guru.php">Guru</a>
                <a class="nav-item logout-link" href="../logout.php">Logout</a>
            </nav>
        </aside>
        <main class="main-content">
            <header class="topbar">
                <button id="menuToggle" class="menu-toggle">☰</button>
                <div class="page-title">Laporan Absensi</div>
                <div class="topbar-right">
                    <div class="user-info">
                        <span>Admin</span>
                        <div class="avatar">A</div>
                    </div>
                </div>
            </header>

            <section class="page-section">
                <div class="section-header">
                    <div>
                        <h2>Rekap Absensi</h2>
                        <p>Filter laporan berdasarkan bulan atau rentang tanggal.</p>
                    </div>
                    <div class="report-actions">
                        <button onclick="window.print()" class="secondary-btn">Print</button>
                    </div>
                </div>
                <form class="filter-form" method="get" action="laporan.php">
                    <div class="filter-group">
                        <label>Bulan</label>
                        <input type="month" name="month" value="<?php echo htmlspecialchars($month); ?>">
                    </div>
                    <div class="filter-group">
                        <label>Dari Tanggal</label>
                        <input type="date" name="from_date" value="<?php echo htmlspecialchars($fromDate); ?>">
                    </div>
                    <div class="filter-group">
                        <label>Sampai Tanggal</label>
                        <input type="date" name="to_date" value="<?php echo htmlspecialchars($toDate); ?>">
                    </div>
                    <button type="submit" class="secondary-btn">Terapkan</button>
                </form>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Siswa</th>
                                <th>Total Pertemuan</th>
                                <th>Hadir</th>
                                <th>Izin</th>
                                <th>Sakit</th>
                                <th>Alfa</th>
                                <th>Terlambat</th>
                                <th>% Kehadiran</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reportData as $index => $row): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                    <td><?php echo htmlspecialchars($row['total_pertemuan']); ?></td>
                                    <td><?php echo htmlspecialchars($row['hadir']); ?></td>
                                    <td><?php echo htmlspecialchars($row['izin']); ?></td>
                                    <td><?php echo htmlspecialchars($row['sakit']); ?></td>
                                    <td><?php echo htmlspecialchars($row['alfa']); ?></td>
                                    <td><?php echo htmlspecialchars($row['terlambat']); ?></td>
                                    <td><?php echo attendancePercent($row['hadir'], $row['total_pertemuan']); ?>%</td>
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
