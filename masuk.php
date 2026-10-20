<?php
// Halaman masuk: verifikasi nama pengguna dan kata sandi
session_start();
require_once __DIR__ . '/includes/fungsi.php';

// Sudah login, langsung ke dashboard
if (isset($_SESSION['id_pengguna'])) {
    redirect('beranda.php');
}

$galat = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_pengguna = trim($_POST['nama_pengguna'] ?? '');
    $kata_sandi = $_POST['kata_sandi'] ?? '';

    if ($nama_pengguna === '' || $kata_sandi === '') {
        $galat = 'Nama pengguna dan kata sandi wajib diisi.';
    } else {
        require_once __DIR__ . '/config/koneksi.php';
        $stmt = $pdo->prepare('SELECT * FROM pengguna WHERE nama_pengguna = ? LIMIT 1');
        $stmt->execute([$nama_pengguna]);
        $akun = $stmt->fetch();

        if ($akun && password_verify($kata_sandi, $akun['kata_sandi'])) {
            session_regenerate_id(true);
            $_SESSION['id_pengguna'] = $akun['id'];
            $_SESSION['nama'] = $akun['nama'];
            $_SESSION['peran'] = $akun['peran'];
            redirect('beranda.php');
        }
        $galat = 'Nama pengguna atau kata sandi salah.';
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk - Absensi Keterlambatan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="assets/gaya.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-5 col-lg-4">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <i class="bi bi-clock-history display-5 text-primary"></i>
                        <h1 class="h5 mt-2 mb-0">Absensi Keterlambatan</h1>
                        <p class="text-muted small mb-0">Silakan masuk sebagai admin</p>
                    </div>

                    <?php if ($galat !== ''): ?>
                        <div class="alert alert-danger py-2"><?= e($galat) ?></div>
                    <?php endif; ?>

                    <form method="post" autocomplete="off">
                        <div class="mb-3">
                            <label for="nama_pengguna" class="form-label">Nama Pengguna</label>
                            <input type="text" name="nama_pengguna" id="nama_pengguna" class="form-control"
                                   value="<?= e($_POST['nama_pengguna'] ?? '') ?>" required autofocus>
                        </div>
                        <div class="mb-3">
                            <label for="kata_sandi" class="form-label">Kata Sandi</label>
                            <input type="password" name="kata_sandi" id="kata_sandi" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Masuk
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
