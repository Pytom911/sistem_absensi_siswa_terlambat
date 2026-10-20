<?php
// Dashboard: keterlambatan hari ini, rel waktu, dan akses cepat
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/fungsi.php';

$jumlah_hari_ini = jumlah_terlambat_hari_ini($pdo);

$jumlah_minggu_ini = (int)$pdo->query('SELECT COUNT(*) FROM absensi_terlambat
    WHERE YEARWEEK(waktu_terlambat, 1) = YEARWEEK(CURDATE(), 1)')->fetchColumn();

$jumlah_total = (int)$pdo->query('SELECT COUNT(*) FROM absensi_terlambat')->fetchColumn();

// Catatan hari ini (untuk rel waktu & daftar terbaru)
$hari_ini = $pdo->prepare('SELECT a.id, a.waktu_terlambat, a.alasan,
                                  s.nama AS nama_siswa, s.nis,
                                  k.tingkat, k.jurusan, k.bagian
                           FROM absensi_terlambat a
                           JOIN siswa s ON s.id = a.id_siswa
                           JOIN kelas k ON k.id = s.id_kelas
                           WHERE DATE(a.waktu_terlambat) = CURDATE()
                           ORDER BY a.waktu_terlambat DESC');
$hari_ini->execute();
$catatan_hari_ini = $hari_ini->fetchAll();

// Posisi jam pada rel 06:30 - 08:00 (menit ke-0 sampai ke-90)
$rel_mulai = 6 * 60 + 30;
$rel_durasi = 90;
$rel_jam_masuk = 7 * 60;

$titik_rel = [];
foreach (array_reverse($catatan_hari_ini) as $c) {
    $menit = (int)date('G', strtotime($c['waktu_terlambat'])) * 60 + (int)date('i', strtotime($c['waktu_terlambat']));
    $pct = (($menit - $rel_mulai) / $rel_durasi) * 100;
    if ($pct < 0 || $pct > 100) {
        continue;
    }
    $titik_rel[] = [
        'pct' => $pct,
        'label' => e($c['nama_siswa']) . ' &middot; ' . date('H:i', strtotime($c['waktu_terlambat'])),
    ];
}
$pct_jam_masuk = (($rel_jam_masuk - $rel_mulai) / $rel_durasi) * 100;
$isi_rel = count($titik_rel) > 0 ? max(array_column($titik_rel, 'pct')) : 0;

$judul_halaman = 'Dashboard';
$sub_halaman = 'Ringkasan keterlambatan & akses cepat';
$menu_aktif = 'beranda';
require __DIR__ . '/includes/kepala.php';
?>

<!-- Hero -->
<section class="hero mb-4">
    <div class="hero-tanggal"><?= e($tanggal_sekarang) ?></div>
    <div class="hero-angka"><?= $jumlah_hari_ini ?></div>
    <p class="hero-teks">keterlambatan tercatat hari ini. Batas masuk 07:00 — setiap catatan tercatat otomatis dengan waktu dan admin pelapor.</p>
    <a class="btn btn-terang" href="catat.php"><i class="bi bi-pencil-square"></i> Catat Keterlambatan</a>
</section>

<!-- Statistik -->
<div class="baris mb-4">
    <div class="k-33">
        <div class="stat stat-utama h-100">
            <span class="stat-label">Hari ini</span>
            <span class="stat-angka"><?= $jumlah_hari_ini ?></span>
        </div>
    </div>
    <div class="k-33">
        <div class="stat h-100">
            <span class="stat-label">Minggu berjalan</span>
            <span class="stat-angka"><?= $jumlah_minggu_ini ?></span>
        </div>
    </div>
    <div class="k-33">
        <div class="stat h-100">
            <span class="stat-label">Total tercatat</span>
            <span class="stat-angka"><?= $jumlah_total ?></span>
        </div>
    </div>
</div>

<!-- Rel waktu -->
<div class="panel mb-4">
    <div class="panel-judul"><i class="bi bi-clock"></i> Sebaran Keterlambatan Hari Ini</div>
    <?php if (count($titik_rel) === 0): ?>
        <div class="kosong">
            <i class="bi bi-check2-circle"></i>
            <div class="kosong-judul">Belum ada keterlambatan hari ini</div>
            <p>Semua siswa tercatat hadir tepat waktu.</p>
        </div>
    <?php else: ?>
        <div class="rel">
            <div class="rel-bingkai">
                <div class="rel-garis"></div>
                <div class="rel-isi" style="left:0;width:<?= $isi_rel ?>%"></div>

                <div class="rel-lonceng" style="left:<?= $pct_jam_masuk ?>%"></div>
                <div class="rel-lonceng-label" style="left:<?= $pct_jam_masuk ?>%">Masuk 07:00</div>

                <?php foreach ($titik_rel as $t): ?>
                    <div class="rel-titik" style="left:<?= $t['pct'] ?>%"
                         data-label="<?= $t['label'] ?>"
                         title="<?= $t['label'] ?>"></div>
                <?php endforeach; ?>

                <div class="rel-skala">
                    <span>06:30</span><span>07:00</span><span>07:30</span><span>08:00</span>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<div class="baris">
    <!-- Terbaru -->
    <div class="k-66">
        <div class="panel">
            <div class="panel-judul">
                <i class="bi bi-hourglass-split"></i> Keterlambatan Terbaru Hari Ini
            </div>
            <?php if (count($catatan_hari_ini) === 0): ?>
                <div class="kosong">
                    <i class="bi bi-journal-check"></i>
                    <div class="kosong-judul">Belum ada catatan</div>
                    <p>Catatan keterlambatan yang dibuat hari ini akan muncul di sini.</p>
                </div>
            <?php else: ?>
                <div class="tabel-bungkus">
                    <table class="tabel">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Kelas</th>
                                <th>Waktu</th>
                                <th>Alasan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($catatan_hari_ini as $c): ?>
                                <tr>
                                    <td>
                                        <div style="font-weight:600"><?= e($c['nama_siswa']) ?></div>
                                        <div class="kecil faint angka"><?= e($c['nis']) ?></div>
                                    </td>
                                    <td><span class="badge badge-biru"><?= e(nama_kelas($c['tingkat'], $c['jurusan'], $c['bagian'])) ?></span></td>
                                    <td><span class="waktu-terlambat"><?= e(date('H:i', strtotime($c['waktu_terlambat']))) ?></span></td>
                                    <td class="redup"><?= e($c['alasan'] ?: '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Akses cepat -->
    <div class="k-33">
        <div class="panel">
            <div class="panel-judul"><i class="bi bi-lightning"></i> Akses Cepat</div>
            <div class="panel-isi" style="display:flex;flex-direction:column;gap:12px">
                <a class="kartu-a" href="siswa.php">
                    <div style="display:flex;gap:12px;align-items:center">
                        <span class="ikon-wadah"><i class="bi bi-people"></i></span>
                        <span>
                            <span class="judul" style="display:block">Data Siswa</span>
                            <span class="desc">Kelola siswa & kelasnya</span>
                        </span>
                    </div>
                </a>
                <a class="kartu-a" href="riwayat.php">
                    <div style="display:flex;gap:12px;align-items:center">
                        <span class="ikon-wadah"><i class="bi bi-clock-history"></i></span>
                        <span>
                            <span class="judul" style="display:block">Riwayat</span>
                            <span class="desc">Telusuri & saring catatan</span>
                        </span>
                    </div>
                </a>
                <a class="kartu-a" href="kelas.php">
                    <div style="display:flex;gap:12px;align-items:center">
                        <span class="ikon-wadah"><i class="bi bi-building"></i></span>
                        <span>
                            <span class="judul" style="display:block">Data Kelas</span>
                            <span class="desc">36 kelas & jurusan</span>
                        </span>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/kaki.php'; ?>
