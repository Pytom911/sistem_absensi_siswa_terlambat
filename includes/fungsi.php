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

// Tampilkan pesan flash (alert)
function tampilkan_flash()
{
    $peta = [
        'success' => ['alert-sukses', 'bi-check-circle-fill'],
        'danger'  => ['alert-bahaya', 'bi-exclamation-triangle-fill'],
        'warning' => ['alert-waspada', 'bi-exclamation-circle-fill'],
        'info'    => ['alert-info', 'bi-info-circle-fill'],
    ];
    foreach (ambil_flash() as $f) {
        [$kelas, $ikon] = $peta[$f['tipe']] ?? $peta['info'];
        echo '<div class="alert ' . $kelas . '" role="alert">'
            . '<i class="bi ' . $ikon . '"></i>'
            . '<span>' . e($f['teks']) . '</span>'
            . '<button type="button" class="alert-tutup" aria-label="Tutup">&times;</button>'
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
