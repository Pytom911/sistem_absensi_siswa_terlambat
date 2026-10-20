<?php
// Riwayat keterlambatan dengan filter nama, NIS, kelas, dan tanggal
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/fungsi.php';

$cari = trim($_GET['cari'] ?? '');
$filter_kelas = (int)($_GET['id_kelas'] ?? 0);
$tanggal_mulai = $_GET['tanggal_mulai'] ?? '';
$tanggal_akhir = $_GET['tanggal_akhir'] ?? '';

$sql = 'SELECT a.*, s.nama AS nama_siswa, s.nis, k.tingkat, k.jurusan, k.bagian, p.nama AS nama_admin
        FROM absensi_terlambat a
        JOIN siswa s ON s.id = a.id_siswa
        JOIN kelas k ON k.id = s.id_kelas
        JOIN pengguna p ON p.id = a.id_pengguna
        WHERE 1 = 1';
$params = [];

if ($cari !== '') {
    $sql .= ' AND (s.nama LIKE ? OR s.nis LIKE ?)';
    $params[] = '%' . $cari . '%';
    $params[] = '%' . $cari . '%';
}
if ($filter_kelas > 0) {
    $sql .= ' AND s.id_kelas = ?';
    $params[] = $filter_kelas;
}
if ($tanggal_mulai !== '' && strtotime($tanggal_mulai)) {
    $sql .= ' AND DATE(a.waktu_terlambat) >= ?';
    $params[] = $tanggal_mulai;
}
if ($tanggal_akhir !== '' && strtotime($tanggal_akhir)) {
    $sql .= ' AND DATE(a.waktu_terlambat) <= ?';
    $params[] = $tanggal_akhir;
}
$sql .= ' ORDER BY a.waktu_terlambat DESC, a.id DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$riwayat = $stmt->fetchAll();

$daftar_kelas = $pdo->query('SELECT * FROM kelas
                             ORDER BY FIELD(tingkat,"X","XI","XII"), jurusan, bagian')->fetchAll();

$judul_halaman = 'Riwayat Keterlambatan';
$sub_halaman = 'Telusuri catatan keterlambatan dengan saringan';
$menu_aktif = 'riwayat';
require __DIR__ . '/includes/kepala.php';
?>

<!-- Filter -->
<div class="panel mb-4">
    <div class="panel-judul"><i class="bi bi-funnel"></i> Saring Data</div>
    <div class="panel-isi">
        <form method="get" class="baris" style="align-items:flex-end">
            <div class="k-30">
                <label class="label" for="cari">Nama / NIS</label>
                <input type="text" name="cari" id="cari" class="kontrol" placeholder="Cari nama atau NIS..." value="<?= e($cari) ?>">
            </div>
            <div class="k-25">
                <label class="label" for="id_kelas">Kelas</label>
                <select name="id_kelas" id="id_kelas" class="pilih">
                    <option value="0">Semua kelas</option>
                    <?php foreach ($daftar_kelas as $k): ?>
                        <option value="<?= (int)$k['id'] ?>" <?= $filter_kelas === (int)$k['id'] ? 'selected' : '' ?>>
                            <?= e(nama_kelas($k['tingkat'], $k['jurusan'], $k['bagian'])) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="k-25">
                <label class="label" for="tanggal_mulai">Dari Tanggal</label>
                <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="kontrol" value="<?= e($tanggal_mulai) ?>">
            </div>
            <div class="k-25">
                <label class="label" for="tanggal_akhir">Sampai Tanggal</label>
                <input type="date" name="tanggal_akhir" id="tanggal_akhir" class="kontrol" value="<?= e($tanggal_akhir) ?>">
            </div>
            <div style="flex:0 0 auto">
                <button type="submit" class="btn btn-inti"><i class="bi bi-search"></i> Terapkan</button>
            </div>
            <div style="flex:0 0 auto">
                <a href="riwayat.php" class="btn btn-garis">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Tabel riwayat -->
<div class="panel">
    <div class="panel-judul">
        <i class="bi bi-clock-history"></i> Daftar Keterlambatan
        <span class="badge badge-terlambat" style="margin-left:auto"><?= count($riwayat) ?> catatan</span>
    </div>

    <?php if (count($riwayat) === 0): ?>
        <div class="kosong">
            <i class="bi bi-journal-x"></i>
            <div class="kosong-judul">Tidak ada catatan yang cocok</div>
            <p>Ubah kata kunci atau rentang tanggal untuk melihat hasil lain.</p>
        </div>
    <?php else: ?>
        <div class="tabel-bungkus">
            <table class="tabel">
                <thead>
                    <tr>
                        <th style="width:56px">No</th>
                        <th>Siswa</th>
                        <th>Kelas</th>
                        <th>Waktu</th>
                        <th>Alasan</th>
                        <th>Keterangan</th>
                        <th>Pencatat</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($riwayat as $i => $r): ?>
                        <tr>
                            <td class="angka faint"><?= $i + 1 ?></td>
                            <td>
                                <div style="font-weight:600"><?= e($r['nama_siswa']) ?></div>
                                <div class="kecil faint angka"><?= e($r['nis']) ?></div>
                            </td>
                            <td><span class="badge badge-petrol"><?= e(nama_kelas($r['tingkat'], $r['jurusan'], $r['bagian'])) ?></span></td>
                            <td><span class="waktu-terlambat"><?= e(format_waktu($r['waktu_terlambat'])) ?></span></td>
                            <td class="redup"><?= e($r['alasan'] ?: '-') ?></td>
                            <td class="redup"><?= e($r['keterangan'] ?: '-') ?></td>
                            <td class="redup"><?= e($r['nama_admin']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/kaki.php'; ?>
