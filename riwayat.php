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
$menu_aktif = 'riwayat';
require __DIR__ . '/includes/kepala.php';
?>

<!-- Filter -->
<div class="card mb-4">
    <div class="card-header fw-semibold"><i class="bi bi-funnel me-2"></i>Filter</div>
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Nama / NIS</label>
                <input type="text" name="cari" class="form-control" placeholder="Cari nama atau NIS..."
                       value="<?= e($cari) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Kelas</label>
                <select name="id_kelas" class="form-select">
                    <option value="0">Semua kelas</option>
                    <?php foreach ($daftar_kelas as $k): ?>
                        <option value="<?= (int)$k['id'] ?>" <?= $filter_kelas === (int)$k['id'] ? 'selected' : '' ?>>
                            <?= e(nama_kelas($k['tingkat'], $k['jurusan'], $k['bagian'])) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Dari Tanggal</label>
                <input type="date" name="tanggal_mulai" class="form-control" value="<?= e($tanggal_mulai) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Sampai Tanggal</label>
                <input type="date" name="tanggal_akhir" class="form-control" value="<?= e($tanggal_akhir) ?>">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i>Terapkan</button>
            </div>
            <div class="col-auto">
                <a href="riwayat.php" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Tabel riwayat -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="fw-semibold"><i class="bi bi-clock-history me-2"></i>Daftar Keterlambatan</span>
        <span class="badge text-bg-secondary"><?= count($riwayat) ?> catatan</span>
    </div>
    <div class="card-body">
        <?php if (count($riwayat) === 0): ?>
            <p class="text-muted mb-0">Tidak ada catatan keterlambatan yang cocok dengan filter.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Waktu Terlambat</th>
                            <th>Alasan</th>
                            <th>Keterangan</th>
                            <th>Admin Pencatat</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($riwayat as $i => $r): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= e($r['nis']) ?></td>
                                <td><?= e($r['nama_siswa']) ?></td>
                                <td><?= e(nama_kelas($r['tingkat'], $r['jurusan'], $r['bagian'])) ?></td>
                                <td><?= e(format_waktu($r['waktu_terlambat'])) ?></td>
                                <td><?= e($r['alasan'] ?? '-') ?></td>
                                <td><?= e($r['keterangan'] ?? '-') ?></td>
                                <td><?= e($r['nama_admin']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/includes/kaki.php'; ?>
