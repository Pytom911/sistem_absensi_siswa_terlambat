<?php
require_once __DIR__ . '/../config/database.php';

$stmt = $pdo->query('SELECT id_guru, nip, nama_guru, no_hp, email, created_at FROM guru ORDER BY nama_guru ASC');
$gurus = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guru | XII RPL 1</title>
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
                <a class="nav-item" href="laporan.php">Laporan</a>
                <a class="nav-item active" href="guru.php">Guru</a>
                <a class="nav-item logout-link" href="../logout.php">Logout</a>
            </nav>
        </aside>
        <main class="main-content">
            <header class="topbar">
                <button id="menuToggle" class="menu-toggle">☰</button>
                <div class="page-title">Data Guru</div>
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
                        <h2>Daftar Guru</h2>
                        <p>Informasi guru yang terdaftar pada sistem.</p>
                    </div>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>NIP</th>
                                <th>Nama Guru</th>
                                <th>No HP</th>
                                <th>Email</th>
                                <th>Dibuat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($gurus as $index => $guru): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo htmlspecialchars($guru['nip']); ?></td>
                                    <td><?php echo htmlspecialchars($guru['nama_guru']); ?></td>
                                    <td><?php echo htmlspecialchars($guru['no_hp']); ?></td>
                                    <td><?php echo htmlspecialchars($guru['email']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($guru['created_at'])); ?></td>
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
