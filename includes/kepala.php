<?php
// Kepala halaman: navbar + sidebar. Sediakan $judul_halaman dan $menu_aktif sebelum include.
$judul_halaman = $judul_halaman ?? 'Aplikasi Absensi';
$menu_aktif = $menu_aktif ?? '';
$nama_admin = $_SESSION['nama'] ?? 'Admin';

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
    <title><?= e($judul_halaman) ?> - Absensi Keterlambatan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="assets/gaya.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary no-print">
    <div class="container-fluid">
        <a class="navbar-brand fw-semibold" href="index.php">
            <i class="bi bi-clock-history me-1"></i>Absensi Keterlambatan
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="menuNav">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item me-lg-3 text-white-50 small">
                    <i class="bi bi-person-circle me-1"></i><?= e($nama_admin) ?>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="keluar.php"><i class="bi bi-box-arrow-right me-1"></i>Keluar</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse py-3 no-print">
            <div class="list-group list-group-flush">
                <?php foreach ($menu as $kunci => $item): ?>
                    <a href="<?= e($item['url']) ?>"
                       class="list-group-item list-group-item-action border-0 <?= $menu_aktif === $kunci ? 'active' : '' ?>">
                        <i class="bi <?= e($item['ikon']) ?> me-2"></i><?= e($item['label']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </nav>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-3 py-4">
            <h1 class="h4 mb-4"><?= e($judul_halaman) ?></h1>
            <?php tampilkan_flash(); ?>
