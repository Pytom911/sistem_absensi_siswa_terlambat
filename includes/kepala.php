<?php
// Kepala halaman: sidebar + topbar. Sediakan $judul_halaman, $sub_halaman, $menu_aktif sebelum include.
$judul_halaman = $judul_halaman ?? 'Aplikasi Absensi';
$sub_halaman = $sub_halaman ?? 'Absensi Keterlambatan';
$menu_aktif = $menu_aktif ?? '';
$nama_admin = $_SESSION['nama'] ?? 'Admin';
$inisial = mb_strtoupper(mb_substr((string)$nama_admin, 0, 1));
$tanggal_sekarang = ucwords(date('l, j F Y'));

$menu = [
    'beranda' => ['url' => 'index.php', 'label' => 'Dashboard', 'ikon' => 'bi-speedometer2'],
    'catat'    => ['url' => 'catat.php', 'label' => 'Pencatatan', 'ikon' => 'bi-pencil-square'],
    'siswa'    => ['url' => 'siswa.php', 'label' => 'Data Siswa', 'ikon' => 'bi-people'],
    'kelas'    => ['url' => 'kelas.php', 'label' => 'Data Kelas', 'ikon' => 'bi-building'],
    'riwayat'  => ['url' => 'riwayat.php', 'label' => 'Riwayat', 'ikon' => 'bi-clock-history'],
    'pengguna' => ['url' => 'pengguna.php', 'label' => 'Pengguna', 'ikon' => 'bi-person-gear'],
];
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($judul_halaman) ?> - SMK Negeri 1 Kraksaan</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700&family=IBM+Plex+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="assets/gaya.css" rel="stylesheet">
</head>
<body>
<div class="app-shell">
    <div class="tirai no-print" id="tirai" hidden></div>

    <aside class="sider no-print" id="sider">
        <div class="sider-identitas">
            <div class="sider-logo">SMK</div>
            <div>
                <div class="sider-nama">SMK Negeri 1 Kraksaan</div>
                <div class="sider-sub">Sistem Absensi Keterlambatan</div>
            </div>
        </div>
        <nav class="sider-menu" aria-label="Menu utama">
            <div class="sider-menu-label">Menu</div>
            <?php foreach ($menu as $kunci => $item): ?>
                <a href="<?= e($item['url']) ?>"
                   class="sider-item<?= $menu_aktif === $kunci ? ' aktif' : '' ?>"
                   <?= $menu_aktif === $kunci ? 'aria-current="page"' : '' ?>>
                    <i class="bi <?= e($item['ikon']) ?>"></i><?= e($item['label']) ?>
                </a>
            <?php endforeach; ?>
        </nav>
    </aside>

    <div class="konten">
        <header class="topbar no-print">
            <button class="topbar-tombol-sider" id="tombolSider" aria-label="Buka menu" aria-expanded="false">
                <i class="bi bi-list"></i>
            </button>
            <div class="topbar-judul-wrap">
                <div class="topbar-judul"><?= e($judul_halaman) ?></div>
                <div class="topbar-sub"><?= e($sub_halaman) ?></div>
            </div>
            <div class="topbar-kanan">
                <div class="jam-hidup" aria-hidden="true">
                    <span class="dot"></span>
                    <span id="jamAtas">--:--:--</span>
                </div>
                <div class="chip-admin">
                    <div class="chip-ava"><?= e($inisial) ?></div>
                    <div class="chip-teks">
                        <div class="chip-nama"><?= e($nama_admin) ?></div>
                        <a class="chip-keluar" href="keluar.php"><i class="bi bi-box-arrow-right"></i> Keluar</a>
                    </div>
                </div>
            </div>
        </header>

        <main class="utama">
            <?php tampilkan_flash(); ?>
