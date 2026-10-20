<?php
// Halaman masuk: verifikasi nama pengguna dan kata sandi
session_start();
require_once __DIR__ . '/includes/fungsi.php';

// Sudah login, langsung ke dashboard
if (isset($_SESSION['id_pengguna'])) {
    redirect('index.php');
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

        if ($akun && $kata_sandi === $akun['kata_sandi']) {
            session_regenerate_id(true);
            $_SESSION['id_pengguna'] = $akun['id'];
            $_SESSION['nama'] = $akun['nama'];
            $_SESSION['peran'] = $akun['peran'];
            redirect('index.php');
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
    <title>Masuk - SMK Negeri 1 Kraksaan</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700&family=IBM+Plex+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="assets/gaya.css" rel="stylesheet">
</head>
<body>
<div class="masuk-shell">
    <!-- Panel kiri: identitas + jam -->
    <section class="masuk-panel no-print">
        <div class="masuk-merek">
            <div class="masuk-logo">
                <img src="assets/logo_smkn.png" alt="Logo SMK Negeri 1 Kraksaan">
            </div>
            <div>
                <div class="masuk-nama">SMK Negeri 1 Kraksaan</div>
                <div class="masuk-tag">Sistem Absensi Keterlambatan</div>
            </div>
        </div>

        <div>
            <div class="masuk-jam-label">Waktu Sekarang</div>
            <div class="masuk-jam" id="jamBesar">--:--</div>
            <div class="masuk-tag mt-2">Batas masuk 07:00 &middot; Tepat waktu adalah awal belajar.</div>
        </div>

        <div class="masuk-kaki">&copy; <?= date('Y') ?> SMK Negeri 1 Kraksaan</div>
    </section>

    <!-- Panel kanan: formulir -->
    <section class="masuk-form-area">
        <div class="masuk-form-wadah">
            <h1 class="masuk-judul mb-2">Masuk</h1>
            <p class="redup mb-4">Silakan masuk menggunakan akun admin.</p>

            <?php if ($galat !== ''): ?>
                <div class="alert alert-bahaya" role="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span><?= e($galat) ?></span>
                </div>
            <?php endif; ?>

            <form method="post" autocomplete="off">
                <div class="mb-3">
                    <label for="nama_pengguna" class="label">Nama Pengguna</label>
                    <input type="text" name="nama_pengguna" id="nama_pengguna" class="kontrol"
                           placeholder="nama pengguna" value="<?= e($_POST['nama_pengguna'] ?? '') ?>" required autofocus>
                </div>
                <div class="mb-4">
                    <label for="kata_sandi" class="label">Kata Sandi</label>
                    <input type="password" name="kata_sandi" id="kata_sandi" class="kontrol"
                           placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn btn-inti btn-blok">
                    <i class="bi bi-box-arrow-in-right"></i> Masuk
                </button>
            </form>
        </div>
    </section>
</div>

<script>
(function () {
    var el = document.getElementById('jamBesar');
    if (!el) return;
    function tik() {
        el.textContent = new Date().toLocaleTimeString('id-ID', {
            hour: '2-digit', minute: '2-digit', hour12: false
        });
    }
    tik();
    setInterval(tik, 1000);
})();
</script>
</body>
</html>
