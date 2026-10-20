<?php
require_once __DIR__ . '/../config/database.php';

$search = trim($_GET['search'] ?? '');
$query = 'SELECT id_siswa, nis, nama, jenis_kelamin, alamat, no_hp, status FROM siswa';
$params = [];
if ($search !== '') {
    $query .= ' WHERE nis LIKE ? OR nama LIKE ?';
    $params[] = "%$search%";
    $params[] = "%$search%";
}
$query .= ' ORDER BY nama ASC';
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$students = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Siswa | XII RPL 1</title>
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
                <a class="nav-item active" href="siswa.php">Daftar Siswa</a>
                <a class="nav-item" href="riwayat.php">Riwayat Absensi</a>
                <a class="nav-item" href="laporan.php">Laporan</a>
                <a class="nav-item" href="guru.php">Guru</a>
                <a class="nav-item logout-link" href="../logout.php">Logout</a>
            </nav>
        </aside>
        <main class="main-content">
            <header class="topbar">
                <button id="menuToggle" class="menu-toggle">☰</button>
                <div class="page-title">Daftar Siswa</div>
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
                        <h2>Data Siswa XII RPL 1</h2>
                        <p>Kelola siswa aktif dan informasi dasar.</p>
                    </div>
                </div>
                <form class="filter-form" method="get" action="siswa.php">
                    <div class="filter-group">
                        <label>Cari Nama / NIS</label>
                        <input type="text" name="search" placeholder="Cari siswa" value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <button type="submit" class="secondary-btn">Cari</button>
                </form>
                <div class="table-actions">
                    <button id="addStudentBtn" class="primary-btn" type="button">Tambah Siswa</button>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>NIS</th>
                                <th>Nama Siswa</th>
                                <th>Jenis Kelamin</th>
                                <th>Alamat</th>
                                <th>No HP</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $index => $row): ?>
                                <tr data-id="<?php echo htmlspecialchars($row['id_siswa']); ?>" data-nis="<?php echo htmlspecialchars($row['nis']); ?>" data-nama="<?php echo htmlspecialchars($row['nama']); ?>" data-jenis_kelamin="<?php echo htmlspecialchars($row['jenis_kelamin']); ?>" data-alamat="<?php echo htmlspecialchars($row['alamat']); ?>" data-no_hp="<?php echo htmlspecialchars($row['no_hp']); ?>" data-status="<?php echo htmlspecialchars($row['status']); ?>">
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo htmlspecialchars($row['nis']); ?></td>
                                    <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                    <td><?php echo htmlspecialchars($row['jenis_kelamin']); ?></td>
                                    <td><?php echo htmlspecialchars($row['alamat']); ?></td>
                                    <td><?php echo htmlspecialchars($row['no_hp']); ?></td>
                                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                                    <td class="actions-cell">
                                        <button class="secondary-btn edit-student-btn" type="button">Edit</button>
                                        <button class="danger-btn delete-student-btn" type="button">Hapus</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <div id="studentModal" class="modal-overlay hidden">
        <div class="modal">
            <div class="modal-header">
                <h3 id="studentModalTitle">Tambah Siswa</h3>
                <button id="closeStudentModal" class="secondary-btn" type="button">Batal</button>
            </div>
            <form id="studentForm" class="modal-form">
                <input type="hidden" name="id_siswa" value="">
                <div class="form-grid">
                    <div class="form-group">
                        <label>NIS</label>
                        <input name="nis" type="text" placeholder="Masukkan NIS" required>
                    </div>
                    <div class="form-group">
                        <label>Nama</label>
                        <input name="nama" type="text" placeholder="Masukkan nama siswa" required>
                    </div>
                    <div class="form-group">
                        <label>Jenis Kelamin</label>
                        <select name="jenis_kelamin" required>
                            <option value="">Pilih jenis kelamin</option>
                            <option value="L">L</option>
                            <option value="P">P</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" required>
                            <option value="Aktif">Aktif</option>
                            <option value="Lulus">Lulus</option>
                        </select>
                    </div>
                    <div class="form-group form-full">
                        <label>Alamat</label>
                        <textarea name="alamat" rows="3" placeholder="Alamat siswa"></textarea>
                    </div>
                    <div class="form-group form-full">
                        <label>No HP</label>
                        <input name="no_hp" type="text" placeholder="Nomor HP siswa">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="primary-btn">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div id="toast" class="toast"></div>
    <script src="../assets/js/script.js"></script>
</body>
</html>
