<?php
// Data kelas: tambah, ubah, hapus 36 kelas
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/fungsi.php';

$aksi = $_POST['aksi'] ?? $_GET['aksi'] ?? '';

// Tambah kelas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $aksi === 'tambah') {
    $tingkat = $_POST['tingkat'] ?? '';
    $jurusan = trim($_POST['jurusan'] ?? '');
    $bagian = $_POST['bagian'] ?? '';

    if (!in_array($tingkat, ['X', 'XI', 'XII'], true) || $jurusan === '' || !in_array($bagian, ['01', '02'], true)) {
        pesan_flash('danger', 'Tingkat, jurusan, dan bagian wajib diisi dengan benar.');
    } else {
        try {
            $stmt = $pdo->prepare('INSERT INTO kelas (tingkat, jurusan, bagian) VALUES (?, ?, ?)');
            $stmt->execute([$tingkat, $jurusan, $bagian]);
            pesan_flash('success', 'Kelas berhasil ditambahkan.');
        } catch (PDOException $e) {
            pesan_flash('danger', 'Gagal menyimpan. Kombinasi tingkat, jurusan, dan bagian mungkin sudah ada.');
        }
    }
    redirect('kelas.php');
}

// Ubah kelas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $aksi === 'ubah') {
    $id = (int)($_POST['id'] ?? 0);
    $tingkat = $_POST['tingkat'] ?? '';
    $jurusan = trim($_POST['jurusan'] ?? '');
    $bagian = $_POST['bagian'] ?? '';

    if ($id <= 0 || !in_array($tingkat, ['X', 'XI', 'XII'], true) || $jurusan === '' || !in_array($bagian, ['01', '02'], true)) {
        pesan_flash('danger', 'Data tidak valid.');
    } else {
        try {
            $stmt = $pdo->prepare('UPDATE kelas SET tingkat = ?, jurusan = ?, bagian = ? WHERE id = ?');
            $stmt->execute([$tingkat, $jurusan, $bagian, $id]);
            pesan_flash('success', 'Kelas berhasil diperbarui.');
        } catch (PDOException $e) {
            pesan_flash('danger', 'Gagal memperbarui. Kombinasi tersebut mungkin sudah dipakai kelas lain.');
        }
    }
    redirect('kelas.php');
}

// Hapus kelas (ditolak bila masih ada siswa)
if ($aksi === 'hapus') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id > 0) {
        try {
            $stmt = $pdo->prepare('DELETE FROM kelas WHERE id = ?');
            $stmt->execute([$id]);
            pesan_flash('success', 'Kelas berhasil dihapus.');
        } catch (PDOException $e) {
            pesan_flash('danger', 'Kelas tidak dapat dihapus karena masih memiliki siswa.');
        }
    }
    redirect('kelas.php');
}

$daftar_kelas = $pdo->query('SELECT k.*, COUNT(s.id) AS jumlah_siswa
                             FROM kelas k
                             LEFT JOIN siswa s ON s.id_kelas = k.id
                             GROUP BY k.id
                             ORDER BY FIELD(k.tingkat,"X","XI","XII"), k.jurusan, k.bagian')->fetchAll();

// Mode ubah: ambil data kelas
$kelas_ubah = null;
if ($aksi === 'ubah' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = (int)($_GET['id'] ?? 0);
    $stmt = $pdo->prepare('SELECT * FROM kelas WHERE id = ?');
    $stmt->execute([$id]);
    $kelas_ubah = $stmt->fetch();
}

$judul_halaman = 'Data Kelas';
$menu_aktif = 'kelas';
require __DIR__ . '/includes/kepala.php';
?>

<!-- Form tambah / ubah -->
<div class="card mb-4">
    <div class="card-header fw-semibold">
        <i class="bi bi-building-add me-2"></i><?= $kelas_ubah ? 'Ubah Kelas' : 'Tambah Kelas' ?>
    </div>
    <div class="card-body">
        <form method="post" class="row g-2 align-items-end">
            <input type="hidden" name="aksi" value="<?= $kelas_ubah ? 'ubah' : 'tambah' ?>">
            <?php if ($kelas_ubah): ?>
                <input type="hidden" name="id" value="<?= (int)$kelas_ubah['id'] ?>">
            <?php endif; ?>
            <div class="col-md-3">
                <label class="form-label">Tingkat <span class="text-danger">*</span></label>
                <select name="tingkat" class="form-select" required>
                    <?php foreach (['X', 'XI', 'XII'] as $t): ?>
                        <option value="<?= $t ?>" <?= ($kelas_ubah['tingkat'] ?? '') === $t ? 'selected' : '' ?>>
                            <?= $t ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Jurusan <span class="text-danger">*</span></label>
                <input type="text" name="jurusan" class="form-control" maxlength="30" required
                       placeholder="Contoh: RPL, TKJ, DKV..."
                       value="<?= e($kelas_ubah['jurusan'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Bagian <span class="text-danger">*</span></label>
                <select name="bagian" class="form-select" required>
                    <?php foreach (['01', '02'] as $b): ?>
                        <option value="<?= $b ?>" <?= ($kelas_ubah['bagian'] ?? '') === $b ? 'selected' : '' ?>>
                            <?= $b ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-save me-1"></i><?= $kelas_ubah ? 'Simpan' : 'Tambah' ?>
                </button>
            </div>
            <?php if ($kelas_ubah): ?>
                <div class="col-12">
                    <a href="kelas.php" class="btn btn-outline-secondary btn-sm">Batal ubah</a>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Tabel kelas -->
<div class="card">
    <div class="card-header fw-semibold">
        <i class="bi bi-table me-2"></i>Daftar Kelas (<?= count($daftar_kelas) ?>)
    </div>
    <div class="card-body">
        <?php if (count($daftar_kelas) === 0): ?>
            <p class="text-muted mb-0">Belum ada data kelas.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tingkat</th>
                            <th>Jurusan</th>
                            <th>Bagian</th>
                            <th>Jumlah Siswa</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($daftar_kelas as $i => $k): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= e($k['tingkat']) ?></td>
                                <td><?= e($k['jurusan']) ?></td>
                                <td><?= e($k['bagian']) ?></td>
                                <td><?= (int)$k['jumlah_siswa'] ?></td>
                                <td class="text-end">
                                    <a href="?aksi=ubah&id=<?= (int)$k['id'] ?>" class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="?aksi=hapus&id=<?= (int)$k['id'] ?>" class="btn btn-sm btn-danger"
                                       onclick="return confirm('Hapus kelas <?= e(nama_kelas($k['tingkat'], $k['jurusan'], $k['bagian'])) ?>?')">
                                        <i class="bi bi-trash"></i>
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

<?php require __DIR__ . '/includes/kaki.php'; ?>
