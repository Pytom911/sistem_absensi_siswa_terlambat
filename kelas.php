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
$sub_halaman = 'Kelola tingkat, jurusan, dan bagian kelas';
$menu_aktif = 'kelas';
require __DIR__ . '/includes/kepala.php';
?>

<!-- Form tambah / ubah -->
<div class="panel mb-4">
    <div class="panel-judul">
        <i class="bi bi-building-add"></i> <?= $kelas_ubah ? 'Ubah Kelas' : 'Tambah Kelas' ?>
    </div>
    <div class="panel-isi">
        <form method="post" class="baris" style="align-items:flex-end">
            <input type="hidden" name="aksi" value="<?= $kelas_ubah ? 'ubah' : 'tambah' ?>">
            <?php if ($kelas_ubah): ?>
                <input type="hidden" name="id" value="<?= (int)$kelas_ubah['id'] ?>">
            <?php endif; ?>
            <div class="k-25">
                <label class="label" for="tingkat">Tingkat <span class="wajib">*</span></label>
                <select name="tingkat" id="tingkat" class="pilih" required>
                    <?php foreach (['X', 'XI', 'XII'] as $t): ?>
                        <option value="<?= $t ?>" <?= ($kelas_ubah['tingkat'] ?? '') === $t ? 'selected' : '' ?>><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="k-40">
                <label class="label" for="jurusan">Jurusan <span class="wajib">*</span></label>
                <input type="text" name="jurusan" id="jurusan" class="kontrol" maxlength="30" required
                       list="daftar_jurusan" placeholder="Contoh: RPL, DKV, TKJ..."
                       value="<?= e($kelas_ubah['jurusan'] ?? '') ?>">
                <datalist id="daftar_jurusan">
                    <option value="RPL"></option>
                    <option value="DKV"></option>
                    <option value="TKJ"></option>
                    <option value="AKL"></option>
                    <option value="MPLB"></option>
                    <option value="Pemasaran"></option>
                </datalist>
            </div>
            <div class="k-25">
                <label class="label" for="bagian">Bagian <span class="wajib">*</span></label>
                <select name="bagian" id="bagian" class="pilih" required>
                    <?php foreach (['01', '02'] as $b): ?>
                        <option value="<?= $b ?>" <?= ($kelas_ubah['bagian'] ?? '') === $b ? 'selected' : '' ?>><?= $b ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="flex:0 0 auto">
                <button type="submit" class="btn btn-inti">
                    <i class="bi bi-save"></i> <?= $kelas_ubah ? 'Simpan' : 'Tambah' ?>
                </button>
            </div>
            <?php if ($kelas_ubah): ?>
                <div style="flex:0 0 auto">
                    <a href="kelas.php" class="btn btn-garis">Batal ubah</a>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Tabel kelas -->
<div class="panel">
    <div class="panel-judul">
        <i class="bi bi-table"></i> Daftar Kelas
        <span class="badge badge-garis"><?= count($daftar_kelas) ?> kelas</span>
    </div>

    <?php if (count($daftar_kelas) === 0): ?>
        <div class="kosong">
            <i class="bi bi-building"></i>
            <div class="kosong-judul">Belum ada data kelas</div>
            <p>Tambahkan kelas lewat formulir di atas.</p>
        </div>
    <?php else: ?>
        <div class="tabel-bungkus">
            <table class="tabel">
                <thead>
                    <tr>
                        <th style="width:56px">No</th>
                        <th>Tingkat</th>
                        <th>Jurusan</th>
                        <th>Bagian</th>
                        <th>Jumlah Siswa</th>
                        <th class="aksi">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($daftar_kelas as $i => $k): ?>
                        <tr>
                            <td class="angka faint"><?= $i + 1 ?></td>
                            <td><span class="badge badge-biru"><?= e($k['tingkat']) ?></span></td>
                            <td style="font-weight:600"><?= e($k['jurusan']) ?></td>
                            <td class="angka"><?= e($k['bagian']) ?></td>
                            <td class="angka"><?= (int)$k['jumlah_siswa'] ?> siswa</td>
                            <td class="aksi">
                                <a href="?aksi=ubah&id=<?= (int)$k['id'] ?>" class="btn btn-waspada btn-sm btn-ikon" title="Ubah">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="?aksi=hapus&id=<?= (int)$k['id'] ?>" class="btn btn-bahaya btn-sm btn-ikon" title="Hapus"
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

<?php require __DIR__ . '/includes/kaki.php'; ?>
