<?php
// Dashboard: jumlah keterlambatan hari ini dan akses menu utama
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/fungsi.php';

$jumlah_hari_ini = jumlah_terlambat_hari_ini($pdo);

$judul_halaman = 'Dashboard';
$menu_aktif = 'beranda';
require __DIR__ . '/includes/kepala.php';
?>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card text-bg-primary kartu-menu">
            <div class="card-body">
                <div class="text-uppercase small opacity-75">Keterlambatan Hari Ini</div>
                <div class="jumlah-besar mt-2"><?= $jumlah_hari_ini ?></div>
                <div class="small opacity-75">catatan tercatat hari ini</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <a href="catat.php" class="text-decoration-none">
            <div class="card kartu-menu h-100">
                <div class="card-body">
                    <i class="bi bi-pencil-square fs-3 text-primary"></i>
                    <div class="fw-semibold mt-2">Pencatatan</div>
                    <div class="small text-muted">Catat keterlambatan siswa</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="riwayat.php" class="text-decoration-none">
            <div class="card kartu-menu h-100">
                <div class="card-body">
                    <i class="bi bi-clock-history fs-3 text-primary"></i>
                    <div class="fw-semibold mt-2">Riwayat</div>
                    <div class="small text-muted">Lihat riwayat keterlambatan</div>
                </div>
            </div>
        </a>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-4">
        <a href="siswa.php" class="text-decoration-none">
            <div class="card kartu-menu h-100">
                <div class="card-body">
                    <i class="bi bi-people fs-3 text-success"></i>
                    <div class="fw-semibold mt-2">Data Siswa</div>
                    <div class="small text-muted">Kelola data siswa dan kelasnya</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="kelas.php" class="text-decoration-none">
            <div class="card kartu-menu h-100">
                <div class="card-body">
                    <i class="bi bi-building fs-3 text-warning"></i>
                    <div class="fw-semibold mt-2">Data Kelas</div>
                    <div class="small text-muted">Kelola 36 kelas</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="pengguna.php" class="text-decoration-none">
            <div class="card kartu-menu h-100">
                <div class="card-body">
                    <i class="bi bi-person-gear fs-3 text-danger"></i>
                    <div class="fw-semibold mt-2">Pengguna</div>
                    <div class="small text-muted">Kelola akun admin</div>
                </div>
            </div>
        </a>
    </div>
</div>

<?php require __DIR__ . '/includes/kaki.php'; ?>
