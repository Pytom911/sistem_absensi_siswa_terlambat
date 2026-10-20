<?php
// Fungsi bantuan aplikasi

// Escape output agar aman dari XSS
function e($teks)
{
    return htmlspecialchars((string)$teks, ENT_QUOTES, 'UTF-8');
}

// Aliasan redirect lalu hentikan skrip
function redirect($url)
{
    header('Location: ' . $url);
    exit;
}

// Simpan pesan flash ke sesi
function pesan_flash($tipe, $teks)
{
    $_SESSION['flash'][] = ['tipe' => $tipe, 'teks' => $teks];
}

// Ambil lalu kosongkan semua pesan flash
function ambil_flash()
{
    $flash = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flash;
}

// Tampilkan pesan flash Bootstrap
function tampilkan_flash()
{
    foreach (ambil_flash() as $f) {
        $tipe = in_array($f['tipe'], ['success', 'danger', 'warning', 'info'], true) ? $f['tipe'] : 'info';
        echo '<div class="alert alert-' . $tipe . ' alert-dismissible fade show" role="alert">'
            . e($f['teks'])
            . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>'
            . '</div>';
    }
}

// Format waktu: 2026-10-20 07:31 -> 20 Okt 2026 07:31
function format_waktu($waktu)
{
    if (!$waktu) {
        return '-';
    }
    $bulan = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $ts = strtotime($waktu);
    return date('j', $ts) . ' ' . $bulan[(int)date('n', $ts)] . ' ' . date('Y H:i', $ts);
}

// Jumlah catatan keterlambatan hari ini
function jumlah_terlambat_hari_ini(PDO $pdo)
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM absensi_terlambat WHERE DATE(waktu_terlambat) = CURDATE()');
    $stmt->execute();
    return (int)$stmt->fetchColumn();
}

// Susun nama kelas dari 3 kolom
function nama_kelas($tingkat, $jurusan, $bagian)
{
    return trim($tingkat . ' ' . $jurusan . ' ' . $bagian);
}
