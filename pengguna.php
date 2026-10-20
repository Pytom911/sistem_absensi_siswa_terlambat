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
$menu_aktif = 'pengguna';
require __DIR__ . '/includes/kepala.php';
?>

<!-- Form tambah / ubah -->
<div class="card mb-4">
    <div class="card-header fw-semibold">
        <i class="bi bi-person-plus me-2"></i><?= $pengguna_ubah ? 'Ubah Akun' : 'Tambah Akun Admin' ?>
    </div>
    <div class="card-body">
        <form method="post" class="row g-2 align-items-end">
            <input type="hidden" name="aksi" value="<?= $pengguna_ubah ? 'ubah' : 'tambah' ?>">
            <?php if ($pengguna_ubah): ?>
                <input type="hidden" name="id" value="<?= (int)$pengguna_ubah['id'] ?>">
            <?php endif; ?>
            <div class="col-md-3">
                <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                <input type="text" name="nama" class="form-control" maxlength="100" required
                       value="<?= e($pengguna_ubah['nama'] ?? '') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Nama Pengguna <span class="text-danger">*</span></label>
                <input type="text" name="nama_pengguna" class="form-control" maxlength="50" required
                       value="<?= e($pengguna_ubah['nama_pengguna'] ?? '') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Kata Sandi <?= $pengguna_ubah ? '(kosongkan bila tetap)' : '<span class="text-danger">*</span>' ?></label>
                <input type="password" name="kata_sandi" class="form-control" minlength="6"
                       <?= $pengguna_ubah ? '' : 'required' ?>>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-save me-1"></i><?= $pengguna_ubah ? 'Simpan' : 'Tambah' ?>
                </button>
            </div>
            <?php if ($pengguna_ubah): ?>
                <div class="col-12">
                    <a href="pengguna.php" class="btn btn-outline-secondary btn-sm">Batal ubah</a>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Tabel pengguna -->
<div class="card">
    <div class="card-header fw-semibold">
        <i class="bi bi-table me-2"></i>Daftar Akun Admin (<?= count($daftar_pengguna) ?>)
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Nama Pengguna</th>
                        <th>Peran</th>
                        <th>Jumlah Catatan</th>
                        <th>Dibuat Pada</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($daftar_pengguna as $i => $p): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= e($p['nama']) ?></td>
                            <td><?= e($p['nama_pengguna']) ?></td>
                            <td><span class="badge text-bg-primary"><?= e($p['peran']) ?></span></td>
                            <td><?= (int)$p['jumlah_catatan'] ?></td>
                            <td><?= e(format_waktu($p['dibuat_pada'])) ?></td>
                            <td class="text-end">
                                <?php if ((int)$p['id'] === (int)$_SESSION['id_pengguna']): ?>
                                    <span class="badge text-bg-light">akun aktif</span>
                                <?php else: ?>
                                    <a href="?aksi=ubah&id=<?= (int)$p['id'] ?>" class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="?aksi=hapus&id=<?= (int)$p['id'] ?>" class="btn btn-sm btn-danger"
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
</div>

<?php require __DIR__ . '/includes/kaki.php'; ?>
