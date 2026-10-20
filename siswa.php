<?php
// Data siswa: tambah, ubah, hapus, dan filter kelas
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/fungsi.php';

$aksi = $_POST['aksi'] ?? $_GET['aksi'] ?? '';

// Tambah siswa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $aksi === 'tambah') {
    $nis = trim($_POST['nis'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $id_kelas = (int)($_POST['id_kelas'] ?? 0);

    if ($nis === '' || $nama === '' || $id_kelas <= 0) {
        pesan_flash('danger', 'NIS, nama, dan kelas wajib diisi.');
    } else {
        try {
            $stmt = $pdo->prepare('INSERT INTO siswa (nis, nama, id_kelas) VALUES (?, ?, ?)');
            $stmt->execute([$nis, $nama, $id_kelas]);
            pesan_flash('success', 'Data siswa berhasil ditambahkan.');
        } catch (PDOException $e) {
            pesan_flash('danger', 'Gagal menyimpan. Periksa kembali, NIS mungkin sudah terpakai.');
        }
    }
    redirect('siswa.php');
}

// Ubah siswa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $aksi === 'ubah') {
    $id = (int)($_POST['id'] ?? 0);
    $nis = trim($_POST['nis'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $id_kelas = (int)($_POST['id_kelas'] ?? 0);

    if ($id <= 0 || $nis === '' || $nama === '' || $id_kelas <= 0) {
        pesan_flash('danger', 'Data tidak lengkap.');
    } else {
        try {
            $stmt = $pdo->prepare('UPDATE siswa SET nis = ?, nama = ?, id_kelas = ? WHERE id = ?');
            $stmt->execute([$nis, $nama, $id_kelas, $id]);
            pesan_flash('success', 'Data siswa berhasil diperbarui.');
        } catch (PDOException $e) {
            pesan_flash('danger', 'Gagal memperbarui. NIS mungkin sudah dipakai siswa lain.');
        }
    }
    redirect('siswa.php');
}

// Hapus siswa (ditolak bila punya riwayat keterlambatan)
if ($aksi === 'hapus') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id > 0) {
        try {
            $stmt = $pdo->prepare('DELETE FROM siswa WHERE id = ?');
            $stmt->execute([$id]);
            pesan_flash('success', 'Data siswa berhasil dihapus.');
        } catch (PDOException $e) {
            pesan_flash('danger', 'Siswa tidak dapat dihapus karena masih memiliki riwayat keterlambatan.');
        }
    }
    redirect('siswa.php');
}

// Data untuk tabel & filter
$daftar_kelas = $pdo->query('SELECT k.*, COUNT(s.id) AS jumlah_siswa
                             FROM kelas k
                             LEFT JOIN siswa s ON s.id_kelas = k.id
                             GROUP BY k.id
                             ORDER BY FIELD(k.tingkat,"X","XI","XII"), k.jurusan, k.bagian')->fetchAll();

$filter_kelas = (int)($_GET['id_kelas'] ?? 0);

$sql = 'SELECT s.*, k.tingkat, k.jurusan, k.bagian
        FROM siswa s
        JOIN kelas k ON k.id = s.id_kelas';
$params = [];
if ($filter_kelas > 0) {
    $sql .= ' WHERE s.id_kelas = ?';
    $params[] = $filter_kelas;
}
$sql .= ' ORDER BY k.tingkat, k.jurusan, k.bagian, s.nama';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$daftar_siswa = $stmt->fetchAll();

// Mode ubah: ambil data siswa
$siswa_ubah = null;
if ($aksi === 'ubah' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = (int)($_GET['id'] ?? 0);
    $stmt = $pdo->prepare('SELECT * FROM siswa WHERE id = ?');
    $stmt->execute([$id]);
    $siswa_ubah = $stmt->fetch();
}

$judul_halaman = 'Data Siswa';
$sub_halaman = 'Kelola identitas siswa dan kelasnya';
$menu_aktif = 'siswa';
require __DIR__ . '/includes/kepala.php';
?>

<!-- Form tambah / ubah -->
<div class="panel mb-4">
    <div class="panel-judul">
        <i class="bi bi-person-plus"></i> <?= $siswa_ubah ? 'Ubah Siswa' : 'Tambah Siswa' ?>
    </div>
    <div class="panel-isi">
        <form method="post" class="baris" style="align-items:flex-end">
            <input type="hidden" name="aksi" value="<?= $siswa_ubah ? 'ubah' : 'tambah' ?>">
            <?php if ($siswa_ubah): ?>
                <input type="hidden" name="id" value="<?= (int)$siswa_ubah['id'] ?>">
            <?php endif; ?>
            <div class="k-25">
                <label class="label" for="nis">NIS <span class="wajib">*</span></label>
                <input type="text" name="nis" id="nis" class="kontrol" maxlength="30" required
                       value="<?= e($siswa_ubah['nis'] ?? '') ?>">
            </div>
            <div class="k-40">
                <label class="label" for="nama">Nama Lengkap <span class="wajib">*</span></label>
                <input type="text" name="nama" id="nama" class="kontrol" maxlength="100" required
                       value="<?= e($siswa_ubah['nama'] ?? '') ?>">
            </div>
            <div class="k-25">
                <label class="label" for="id_kelas">Kelas <span class="wajib">*</span></label>
                <select name="id_kelas" id="id_kelas" class="pilih" required>
                    <option value="">-- pilih --</option>
                    <?php foreach ($daftar_kelas as $k): ?>
                        <option value="<?= (int)$k['id'] ?>"
                            <?= (int)($siswa_ubah['id_kelas'] ?? 0) === (int)$k['id'] ? 'selected' : '' ?>>
                            <?= e(nama_kelas($k['tingkat'], $k['jurusan'], $k['bagian'])) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="flex:0 0 auto">
                <button type="submit" class="btn btn-inti">
                    <i class="bi bi-save"></i> <?= $siswa_ubah ? 'Simpan' : 'Tambah' ?>
                </button>
            </div>
            <?php if ($siswa_ubah): ?>
                <div style="flex:0 0 auto">
                    <a href="siswa.php" class="btn btn-garis">Batal ubah</a>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Filter + tabel -->
<div class="panel">
    <div class="panel-judul">
        <i class="bi bi-table"></i> Daftar Siswa
        <span class="badge badge-garis"><?= count($daftar_siswa) ?> siswa</span>
        <form method="get" style="margin-left:auto;min-width:220px">
            <select name="id_kelas" class="pilih" onchange="this.form.submit()" aria-label="Saring per kelas">
                <option value="0">Semua kelas</option>
                <?php foreach ($daftar_kelas as $k): ?>
                    <option value="<?= (int)$k['id'] ?>" <?= $filter_kelas === (int)$k['id'] ? 'selected' : '' ?>>
                        <?= e(nama_kelas($k['tingkat'], $k['jurusan'], $k['bagian'])) ?> (<?= (int)$k['jumlah_siswa'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <?php if (count($daftar_siswa) === 0): ?>
        <div class="kosong">
            <i class="bi bi-people"></i>
            <div class="kosong-judul">Belum ada data siswa</div>
            <p>Tambahkan siswa lewat formulir di atas, atau ubah saringan kelas.</p>
        </div>
    <?php else: ?>
        <div class="tabel-bungkus">
            <table class="tabel">
                <thead>
                    <tr>
                        <th>NIS</th>
                        <th>Nama</th>
                        <th>Kelas</th>
                        <th class="aksi">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($daftar_siswa as $s): ?>
                        <tr>
                            <td class="angka"><?= e($s['nis']) ?></td>
                            <td style="font-weight:600"><?= e($s['nama']) ?></td>
                            <td><span class="badge badge-biru"><?= e(nama_kelas($s['tingkat'], $s['jurusan'], $s['bagian'])) ?></span></td>
                            <td class="aksi">
                                <a href="?aksi=ubah&id=<?= (int)$s['id'] ?>" class="btn btn-waspada btn-sm btn-ikon" title="Ubah">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="?aksi=hapus&id=<?= (int)$s['id'] ?>" class="btn btn-bahaya btn-sm btn-ikon" title="Hapus"
                                   onclick="return confirm('Hapus siswa <?= e($s['nama']) ?>?')">
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
