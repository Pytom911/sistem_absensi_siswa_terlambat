<?php
require_once __DIR__ . '/../config/database.php';

$fromDate = $_GET['from_date'] ?? '';
$toDate = $_GET['to_date'] ?? '';
$search = trim($_GET['search'] ?? '');
$status = $_GET['status'] ?? '';

$query = 'SELECT a.id_absensi, a.tanggal, s.nis, s.nama, a.status, a.jam_masuk, a.jam_keluar, a.keterangan, u.username AS dibuat_oleh FROM absensi a JOIN siswa s ON s.id_siswa = a.id_siswa LEFT JOIN users u ON u.id_user = a.dibuat_oleh WHERE 1=1';
$params = [];

if ($fromDate !== '') {
    $query .= ' AND a.tanggal >= ?';
    $params[] = $fromDate;
}
if ($toDate !== '') {
    $query .= ' AND a.tanggal <= ?';
    $params[] = $toDate;
}
if ($search !== '') {
    $query .= ' AND (s.nis LIKE ? OR s.nama LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($status !== '') {
    $query .= ' AND a.status = ?';
    $params[] = $status;
}
$query .= ' ORDER BY a.tanggal DESC, s.nama ASC';
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$records = $stmt->fetchAll();
$statusOptions = ['Hadir', 'Izin', 'Sakit', 'Alfa', 'Terlambat'];

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
    <title>Riwayat Absensi | XII RPL 1</title>
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
                <a class="nav-item active" href="riwayat.php">Riwayat Absensi</a>
                <a class="nav-item" href="laporan.php">Laporan</a>
                <a class="nav-item" href="guru.php">Guru</a>
                <a class="nav-item logout-link" href="../logout.php">Logout</a>
            </nav>
        </aside>
        <main class="main-content">
            <header class="topbar">
                <button id="menuToggle" class="menu-toggle">☰</button>
                <div class="page-title">Riwayat Absensi</div>
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
                        <h2>Riwayat Absensi Siswa</h2>
                        <p>Filter data siswa berdasarkan rentang, status, dan nama.</p>
                    </div>
                </div>
                <form class="filter-form" method="get" action="riwayat.php">
                    <div class="filter-group">
                        <label>Tanggal Awal</label>
                        <input type="date" name="from_date" value="<?php echo htmlspecialchars($fromDate); ?>">
                    </div>
                    <div class="filter-group">
                        <label>Tanggal Akhir</label>
                        <input type="date" name="to_date" value="<?php echo htmlspecialchars($toDate); ?>">
                    </div>
                    <div class="filter-group">
                        <label>Cari Nama / NIS</label>
                        <input type="text" name="search" placeholder="Cari siswa" value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="filter-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="">Semua Status</option>
                            <?php foreach ($statusOptions as $option): ?>
                                <option value="<?php echo $option; ?>" <?php echo $status === $option ? 'selected' : ''; ?>><?php echo $option; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="secondary-btn">Terapkan</button>
                </form>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>NIS</th>
                                <th>Nama Siswa</th>
                                <th>Status</th>
                                <th>Jam Masuk</th>
                                <th>Jam Keluar</th>
                                <th>Keterangan</th>
                                <th>Dibuat Oleh</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($records as $index => $row): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                                    <td><?php echo htmlspecialchars($row['nis']); ?></td>
                                    <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                    <td><span class="badge <?php echo badgeClass($row['status']); ?>"><?php echo htmlspecialchars($row['status']); ?></span></td>
                                    <td><?php echo htmlspecialchars($row['jam_masuk'] ?: '-'); ?></td>
                                    <td><?php echo htmlspecialchars($row['jam_keluar'] ?: '-'); ?></td>
                                    <td><?php echo htmlspecialchars($row['keterangan'] ?: '-'); ?></td>
                                    <td><?php echo htmlspecialchars($row['dibuat_oleh'] ?: '-'); ?></td>
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
