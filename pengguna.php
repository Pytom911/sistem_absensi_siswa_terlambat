<?php
// Pengguna: kelola akun admin (tambah, ubah, hapus)
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/fungsi.php';

$aksi = $_POST['aksi'] ?? $_GET['aksi'] ?? '';

// Tambah admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $aksi === 'tambah') {
    $nama = trim($_POST['nama'] ?? '');
    $nama_pengguna = trim($_POST['nama_pengguna'] ?? '');
    $kata_sandi = $_POST['kata_sandi'] ?? '';

    if ($nama === '' || $nama_pengguna === '' || $kata_sandi === '') {
        pesan_flash('danger', 'Semua kolom wajib diisi.');
    } elseif (strlen($kata_sandi) < 6) {
        pesan_flash('danger', 'Kata sandi minimal 6 karakter.');
    } else {
        try {
            $stmt = $pdo->prepare('INSERT INTO pengguna (nama, nama_pengguna, kata_sandi, peran)
                                   VALUES (?, ?, ?, "admin")');
            $stmt->execute([$nama, $nama_pengguna, $kata_sandi]);
            pesan_flash('success', 'Akun admin berhasil ditambahkan.');
        } catch (PDOException $e) {
            pesan_flash('danger', 'Gagal menyimpan. Nama pengguna mungkin sudah terpakai.');
        }
    }
    redirect('pengguna.php');
}

// Ubah admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $aksi === 'ubah') {
    $id = (int)($_POST['id'] ?? 0);
    $nama = trim($_POST['nama'] ?? '');
    $nama_pengguna = trim($_POST['nama_pengguna'] ?? '');
    $kata_sandi = $_POST['kata_sandi'] ?? '';

    if ($id <= 0 || $nama === '' || $nama_pengguna === '') {
        pesan_flash('danger', 'Nama dan nama pengguna wajib diisi.');
    } elseif ($kata_sandi !== '' && strlen($kata_sandi) < 6) {
        pesan_flash('danger', 'Kata sandi minimal 6 karakter.');
    } else {
        try {
            if ($kata_sandi !== '') {
                $stmt = $pdo->prepare('UPDATE pengguna SET nama = ?, nama_pengguna = ?, kata_sandi = ? WHERE id = ?');
                $stmt->execute([$nama, $nama_pengguna, $kata_sandi, $id]);
            } else {
                $stmt = $pdo->prepare('UPDATE pengguna SET nama = ?, nama_pengguna = ? WHERE id = ?');
                $stmt->execute([$nama, $nama_pengguna, $id]);
            }
            pesan_flash('success', 'Akun admin berhasil diperbarui.');
        } catch (PDOException $e) {
            pesan_flash('danger', 'Gagal memperbarui. Nama pengguna mungkin sudah dipakai akun lain.');
        }
    }
    redirect('pengguna.php');
}

// Hapus admin (tidak boleh menghapus akun sendiri)
if ($aksi === 'hapus') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id === (int)$_SESSION['id_pengguna']) {
        pesan_flash('danger', 'Anda tidak dapat menghapus akun yang sedang digunakan.');
    } elseif ($id > 0) {
        try {
            $stmt = $pdo->prepare('DELETE FROM pengguna WHERE id = ?');
            $stmt->execute([$id]);
            pesan_flash('success', 'Akun admin berhasil dihapus.');
        } catch (PDOException $e) {
            pesan_flash('danger', 'Akun tidak dapat dihapus karena masih memiliki catatan keterlambatan.');
        }
    }
    redirect('pengguna.php');
}

$daftar_pengguna = $pdo->query('SELECT p.*, COUNT(a.id) AS jumlah_catatan
                                FROM pengguna p
                                LEFT JOIN absensi_terlambat a ON a.id_pengguna = p.id
                                GROUP BY p.id
                                ORDER BY p.nama')->fetchAll();

// Mode ubah: ambil data pengguna
$pengguna_ubah = null;
if ($aksi === 'ubah' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = (int)($_GET['id'] ?? 0);
    $stmt = $pdo->prepare('SELECT * FROM pengguna WHERE id = ?');
    $stmt->execute([$id]);
    $pengguna_ubah = $stmt->fetch();
}

$judul_halaman = 'Kelola Pengguna';
$sub_halaman = 'Akun admin yang berhak mencatat';
$menu_aktif = 'pengguna';
require __DIR__ . '/includes/kepala.php';
?>

<!-- Form tambah / ubah -->
<div class="panel mb-4">
    <div class="panel-judul">
        <i class="bi bi-person-plus"></i> <?= $pengguna_ubah ? 'Ubah Akun' : 'Tambah Akun Admin' ?>
    </div>
    <div class="panel-isi">
        <form method="post" class="baris" style="align-items:flex-end">
            <input type="hidden" name="aksi" value="<?= $pengguna_ubah ? 'ubah' : 'tambah' ?>">
            <?php if ($pengguna_ubah): ?>
                <input type="hidden" name="id" value="<?= (int)$pengguna_ubah['id'] ?>">
            <?php endif; ?>
            <div class="k-30">
                <label class="label" for="nama">Nama Lengkap <span class="wajib">*</span></label>
                <input type="text" name="nama" id="nama" class="kontrol" maxlength="100" required
                       value="<?= e($pengguna_ubah['nama'] ?? '') ?>">
            </div>
            <div class="k-30">
                <label class="label" for="nama_pengguna">Nama Pengguna <span class="wajib">*</span></label>
                <input type="text" name="nama_pengguna" id="nama_pengguna" class="kontrol" maxlength="50" required
                       value="<?= e($pengguna_ubah['nama_pengguna'] ?? '') ?>">
            </div>
            <div class="k-30">
                <label class="label" for="kata_sandi">Kata Sandi
                    <?php if ($pengguna_ubah): ?><span class="redup" style="font-weight:400">(kosongkan bila tetap)</span>
                    <?php else: ?><span class="wajib">*</span><?php endif; ?>
                </label>
                <input type="password" name="kata_sandi" id="kata_sandi" class="kontrol" minlength="6"
                       placeholder="minimal 6 karakter" <?= $pengguna_ubah ? '' : 'required' ?>>
            </div>
            <div style="flex:0 0 auto">
                <button type="submit" class="btn btn-inti">
                    <i class="bi bi-save"></i> <?= $pengguna_ubah ? 'Simpan' : 'Tambah' ?>
                </button>
            </div>
            <?php if ($pengguna_ubah): ?>
                <div style="flex:0 0 auto">
                    <a href="pengguna.php" class="btn btn-garis">Batal ubah</a>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Tabel pengguna -->
<div class="panel">
    <div class="panel-judul">
        <i class="bi bi-table"></i> Daftar Akun Admin
        <span class="badge badge-garis"><?= count($daftar_pengguna) ?> akun</span>
    </div>
    <div class="tabel-bungkus">
        <table class="tabel">
            <thead>
                <tr>
                    <th style="width:56px">No</th>
                    <th>Nama</th>
                    <th>Nama Pengguna</th>
                    <th>Peran</th>
                    <th>Jumlah Catatan</th>
                    <th>Dibuat</th>
                    <th class="aksi">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($daftar_pengguna as $i => $p): ?>
                    <tr>
                        <td class="angka faint"><?= $i + 1 ?></td>
                        <td style="font-weight:600"><?= e($p['nama']) ?></td>
                        <td class="angka"><?= e($p['nama_pengguna']) ?></td>
                        <td><span class="badge badge-petrol"><?= e($p['peran']) ?></span></td>
                        <td class="angka"><?= (int)$p['jumlah_catatan'] ?> catatan</td>
                        <td class="redup"><?= e(format_waktu($p['dibuat_pada'])) ?></td>
                        <td class="aksi">
                            <?php if ((int)$p['id'] === (int)$_SESSION['id_pengguna']): ?>
                                <span class="badge badge-sukses">akun aktif</span>
                            <?php else: ?>
                                <a href="?aksi=ubah&id=<?= (int)$p['id'] ?>" class="btn btn-waspada btn-sm btn-ikon" title="Ubah">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="?aksi=hapus&id=<?= (int)$p['id'] ?>" class="btn btn-bahaya btn-sm btn-ikon" title="Hapus"
                                   onclick="return confirm('Hapus akun <?= e($p['nama_pengguna']) ?>?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/includes/kaki.php'; ?>
