<?php
require_once __DIR__ . '/../config/database.php';

$today = date('Y-m-d');
$statusOptions = ['Hadir', 'Izin', 'Sakit', 'Alfa', 'Terlambat'];

$dateFilter = $_GET['tanggal'] ?? $today;
$searchFilter = trim($_GET['search'] ?? '');
$statusFilter = $_GET['status'] ?? '';

$query = 'SELECT s.id_siswa, s.nis, s.nama, IFNULL(a.status, "") AS status, a.jam_masuk, a.jam_keluar, a.keterangan, a.id_absensi FROM siswa s LEFT JOIN absensi a ON a.id_siswa = s.id_siswa AND a.tanggal = ? WHERE s.status = "Aktif"';
$params = [$dateFilter];

if ($searchFilter !== '') {
    $query .= ' AND (s.nis LIKE ? OR s.nama LIKE ?)';
    $params[] = "%$searchFilter%";
    $params[] = "%$searchFilter%";
}

if ($statusFilter !== '' && in_array($statusFilter, $statusOptions, true)) {
    $query .= ' AND a.status = ?';
    $params[] = $statusFilter;
}

$query .= ' ORDER BY s.nama ASC';
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$students = $stmt->fetchAll();

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
    <title>Absensi | XII RPL 1</title>
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
                <a class="nav-item active" href="absensi.php">Absensi</a>
                <a class="nav-item" href="siswa.php">Daftar Siswa</a>
                <a class="nav-item" href="riwayat.php">Riwayat Absensi</a>
                <a class="nav-item" href="laporan.php">Laporan</a>
                <a class="nav-item" href="guru.php">Guru</a>
                <a class="nav-item logout-link" href="../logout.php">Logout</a>
            </nav>
        </aside>
        <main class="main-content">
            <header class="topbar">
                <button id="menuToggle" class="menu-toggle">☰</button>
                <div class="page-title">Absensi Kelas XII RPL 1</div>
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
                        <h2>Kelola kehadiran siswa</h2>
                        <p>Tanggal: <?php echo date('d/m/Y', strtotime($dateFilter)); ?> | Kelas: XII RPL 1</p>
                    </div>
                </div>

                <form class="filter-form" method="get" action="absensi.php">
                    <div class="filter-group">
                        <label>Tanggal</label>
                        <input type="date" name="tanggal" value="<?php echo htmlspecialchars($dateFilter); ?>">
                    </div>
                    <div class="filter-group">
                        <label>Cari Nama / NIS</label>
                        <input type="text" name="search" placeholder="Cari siswa" value="<?php echo htmlspecialchars($searchFilter); ?>">
                    </div>
                    <div class="filter-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="">Semua Status</option>
                            <?php foreach ($statusOptions as $option): ?>
                                <option value="<?php echo $option; ?>" <?php echo $statusFilter === $option ? 'selected' : ''; ?>><?php echo $option; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="secondary-btn">Tampilkan</button>
                </form>

                <div class="table-actions">
                    <button id="saveAllBtn" class="primary-btn">Simpan Semua</button>
                </div>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>NIS</th>
                                <th>Nama Siswa</th>
                                <th>Status</th>
                                <th>Jam Masuk</th>
                                <th>Jam Keluar</th>
                                <th>Keterangan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $index => $row): ?>
                                <tr data-id="<?php echo htmlspecialchars($row['id_siswa']); ?>">
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo htmlspecialchars($row['nis']); ?></td>
                                    <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                    <td>
                                        <select class="status-select" data-id="<?php echo htmlspecialchars($row['id_siswa']); ?>">
                                            <option value="">Pilih</option>
                                            <?php foreach ($statusOptions as $status): ?>
                                                <option value="<?php echo $status; ?>" <?php echo $row['status'] === $status ? 'selected' : ''; ?>><?php echo $status; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td><input type="time" class="jam-masuk" value="<?php echo htmlspecialchars($row['jam_masuk']); ?>" <?php echo in_array($row['status'], ['Izin', 'Sakit', 'Alfa'], true) ? '' : ''; ?>></td>
                                    <td><input type="time" class="jam-keluar" value="<?php echo htmlspecialchars($row['jam_keluar']); ?>"></td>
                                    <td><input type="text" class="keterangan" placeholder="Keterangan" value="<?php echo htmlspecialchars($row['keterangan']); ?>"></td>
                                    <td class="actions-cell">
                                        <button class="secondary-btn save-row-btn" data-id="<?php echo htmlspecialchars($row['id_siswa']); ?>">Simpan</button>
                                        <?php if ($row['id_absensi']): ?>
                                            <button class="secondary-btn leave-btn" data-id="<?php echo htmlspecialchars($row['id_absensi']); ?>">Jam Keluar</button>
                                            <button class="danger-btn delete-btn" data-id="<?php echo htmlspecialchars($row['id_absensi']); ?>">Hapus</button>
                                        <?php else: ?>
                                            <button class="secondary-btn save-row-btn" data-id="<?php echo htmlspecialchars($row['id_siswa']); ?>">Buat</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <div id="toast" class="toast"></div>
    <script src="../assets/js/script.js"></script>
</body>
</html>
