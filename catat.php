<?php
// Pencatatan keterlambatan: pilih kelas -> cari siswa -> isi formulir -> simpan
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/fungsi.php';

// Simpan catatan (POST-Redirect-GET)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'simpan') {
    $id_siswa = (int)($_POST['id_siswa'] ?? 0);
    $waktu = $_POST['waktu_terlambat'] ?? '';
    $alasan = trim($_POST['alasan'] ?? '');
    $keterangan = trim($_POST['keterangan'] ?? '');

    $ts = strtotime($waktu);
    if ($id_siswa <= 0) {
        pesan_flash('danger', 'Siswa tidak valid, silakan pilih ulang.');
    } elseif (!$ts) {
        pesan_flash('danger', 'Tanggal dan waktu keterlambatan tidak valid.');
    } else {
        $stmt = $pdo->prepare('INSERT INTO absensi_terlambat (id_siswa, id_pengguna, waktu_terlambat, alasan, keterangan)
                               VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([
            $id_siswa,
            $_SESSION['id_pengguna'],
            date('Y-m-d H:i:s', $ts),
            $alasan !== '' ? $alasan : null,
            $keterangan !== '' ? $keterangan : null,
        ]);
        pesan_flash('success', 'Keterlambatan berhasil dicatat.');
    }

    // Kembali ke daftar siswa kelas yang sama
    $kembali = 'catat.php?' . http_build_query(array_filter([
        'tingkat' => $_POST['tingkat'] ?? '',
        'jurusan' => $_POST['jurusan'] ?? '',
        'bagian' => $_POST['bagian'] ?? '',
        'cari' => $_POST['cari'] ?? '',
    ], fn($v) => $v !== ''));
    redirect($kembali);
}

// Pilihan dropdown dari data kelas aktual
$daftar_tingkat = $pdo->query("SELECT DISTINCT tingkat FROM kelas ORDER BY FIELD(tingkat,'X','XI','XII')")->fetchAll(PDO::FETCH_COLUMN);

$tingkat = $_GET['tingkat'] ?? '';
$jurusan = $_GET['jurusan'] ?? '';
$bagian = $_GET['bagian'] ?? '';
$cari = trim($_GET['cari'] ?? '');
$id_siswa_pilihan = (int)($_GET['pilih'] ?? 0);

$daftar_jurusan = [];
if ($tingkat !== '') {
    $stmt = $pdo->prepare('SELECT DISTINCT jurusan FROM kelas WHERE tingkat = ? ORDER BY jurusan');
    $stmt->execute([$tingkat]);
    $daftar_jurusan = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

$daftar_bagian = [];
if ($tingkat !== '' && $jurusan !== '') {
    $stmt = $pdo->prepare('SELECT DISTINCT bagian FROM kelas WHERE tingkat = ? AND jurusan = ? ORDER BY bagian');
    $stmt->execute([$tingkat, $jurusan]);
    $daftar_bagian = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Cari id kelas dari kombinasi pilihan
$id_kelas = 0;
$kelas_dipilih = '';
if ($tingkat !== '' && $jurusan !== '' && $bagian !== '') {
    $stmt = $pdo->prepare('SELECT id FROM kelas WHERE tingkat = ? AND jurusan = ? AND bagian = ?');
    $stmt->execute([$tingkat, $jurusan, $bagian]);
    $id_kelas = (int)$stmt->fetchColumn();
    $kelas_dipilih = nama_kelas($tingkat, $jurusan, $bagian);
}

// Daftar siswa kelas terpilih (+ pencarian nama/NIS)
$siswa_terpilih = null;
$daftar_siswa = [];
if ($id_kelas > 0) {
    $sql = 'SELECT * FROM siswa WHERE id_kelas = ?';
    $params = [$id_kelas];
    if ($cari !== '') {
        $sql .= ' AND (nama LIKE ? OR nis LIKE ?)';
        $params[] = '%' . $cari . '%';
        $params[] = '%' . $cari . '%';
    }
    $sql .= ' ORDER BY nama';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $daftar_siswa = $stmt->fetchAll();

    if ($id_siswa_pilihan > 0) {
        foreach ($daftar_siswa as $s) {
            if ((int)$s['id'] === $id_siswa_pilihan) {
                $siswa_terpilih = $s;
                break;
            }
        }
    }
}

// Waktu default otomatis sekarang
$waktu_default = date('Y-m-d\TH:i');

$judul_halaman = 'Pencatatan Keterlambatan';
$menu_aktif = 'catat';
require __DIR__ . '/includes/kepala.php';
?>

<!-- Langkah 1: pilih kelas -->
<div class="card mb-4">
    <div class="card-header fw-semibold"><i class="bi bi-building me-2"></i>1. Pilih Kelas</div>
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Tingkat</label>
                <select name="tingkat" class="form-select" onchange="this.form.submit()" required>
                    <option value="">-- pilih --</option>
                    <?php foreach ($daftar_tingkat as $t): ?>
                        <option value="<?= e($t) ?>" <?= $tingkat === $t ? 'selected' : '' ?>><?= e($t) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Jurusan</label>
                <select name="jurusan" class="form-select" onchange="this.form.submit()" required>
                    <option value="">-- pilih --</option>
                    <?php foreach ($daftar_jurusan as $j): ?>
                        <option value="<?= e($j) ?>" <?= $jurusan === $j ? 'selected' : '' ?>><?= e($j) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Bagian</label>
                <select name="bagian" class="form-select" onchange="this.form.submit()" required>
                    <option value="">-- pilih --</option>
                    <?php foreach ($daftar_bagian as $b): ?>
                        <option value="<?= e($b) ?>" <?= $bagian === $b ? 'selected' : '' ?>><?= e($b) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>
</div>

<?php if ($id_kelas > 0): ?>
<!-- Langkah 2: daftar siswa + pencarian -->
<div class="card mb-4">
    <div class="card-header fw-semibold">
        <i class="bi bi-people me-2"></i>2. Siswa Kelas <?= e($kelas_dipilih) ?>
    </div>
    <div class="card-body">
        <form method="get" class="row g-2 mb-3">
            <input type="hidden" name="tingkat" value="<?= e($tingkat) ?>">
            <input type="hidden" name="jurusan" value="<?= e($jurusan) ?>">
            <input type="hidden" name="bagian" value="<?= e($bagian) ?>">
            <div class="col-md-5">
                <input type="text" name="cari" class="form-control" placeholder="Cari nama atau NIS..."
                       value="<?= e($cari) ?>">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-outline-primary"><i class="bi bi-search me-1"></i>Cari</button>
            </div>
            <?php if ($cari !== ''): ?>
                <div class="col-auto">
                    <a href="?tingkat=<?= e($tingkat) ?>&jurusan=<?= e($jurusan) ?>&bagian=<?= e($bagian) ?>"
                       class="btn btn-outline-secondary">Reset</a>
                </div>
            <?php endif; ?>
        </form>

        <?php if (count($daftar_siswa) === 0): ?>
            <p class="text-muted mb-0">Tidak ada siswa yang cocok.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>NIS</th>
                            <th>Nama</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($daftar_siswa as $s): ?>
                            <tr>
                                <td><?= e($s['nis']) ?></td>
                                <td><?= e($s['nama']) ?></td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-primary"
                                       href="?tingkat=<?= e($tingkat) ?>&jurusan=<?= e($jurusan) ?>&bagian=<?= e($bagian) ?>&cari=<?= e($cari) ?>&pilih=<?= (int)$s['id'] ?>">
                                        Pilih
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($siswa_terpilih): ?>
<!-- Langkah 3: formulir pencatatan -->
<div class="card mb-4">
    <div class="card-header fw-semibold">
        <i class="bi bi-pencil-square me-2"></i>3. Catat Keterlambatan
        <span class="text-muted fw-normal">- <?= e($siswa_terpilih['nama']) ?> (<?= e($siswa_terpilih['nis']) ?>)</span>
    </div>
    <div class="card-body">
        <form method="post">
            <input type="hidden" name="aksi" value="simpan">
            <input type="hidden" name="id_siswa" value="<?= (int)$siswa_terpilih['id'] ?>">
            <input type="hidden" name="tingkat" value="<?= e($tingkat) ?>">
            <input type="hidden" name="jurusan" value="<?= e($jurusan) ?>">
            <input type="hidden" name="bagian" value="<?= e($bagian) ?>">
            <input type="hidden" name="cari" value="<?= e($cari) ?>">

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Waktu Terlambat <span class="text-danger">*</span></label>
                    <input type="datetime-local" name="waktu_terlambat" class="form-control"
                           value="<?= e($waktu_default) ?>" required>
                    <div class="form-text">Terisi otomatis dengan waktu sekarang, boleh diubah.</div>
                </div>
                <div class="col-md-8">
                    <label class="form-label">Alasan</label>
                    <input type="text" name="alasan" class="form-control" maxlength="255"
                           placeholder="Contoh: macet, ada keperluan keluarga">
                </div>
                <div class="col-12">
                    <label class="form-label">Keterangan</label>
                    <textarea name="keterangan" class="form-control" rows="2"
                              placeholder="Catatan tambahan (opsional)"></textarea>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary"
                            onclick="return confirm('Simpan catatan keterlambatan ini?')">
                        <i class="bi bi-save me-1"></i>Simpan Catatan
                    </button>
                    <a href="?tingkat=<?= e($tingkat) ?>&jurusan=<?= e($jurusan) ?>&bagian=<?= e($bagian) ?>&cari=<?= e($cari) ?>"
                       class="btn btn-outline-secondary">Batal</a>
                </div>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php else: ?>
<div class="alert alert-info">Pilih tingkat, jurusan, dan bagian terlebih dahulu untuk menampilkan daftar siswa.</div>
<?php endif; ?>

<?php require __DIR__ . '/includes/kaki.php'; ?>
