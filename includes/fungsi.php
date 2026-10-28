<?php
// Fungsi bantuan aplikasi

// Escape output agar aman dari XSS
function e($teks)
{
    return htmlspecialchars((string) $teks, ENT_QUOTES, 'UTF-8');
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
        'danger' => ['alert-bahaya', 'bi-exclamation-triangle-fill'],
        'warning' => ['alert-waspada', 'bi-exclamation-circle-fill'],
        'info' => ['alert-info', 'bi-info-circle-fill'],
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
    return date('j', $ts) . ' ' . $bulan[(int) date('n', $ts)] . ' ' . date('Y H:i', $ts);
}

// Ambil daftar riwayat keterlambatan sesuai filter (dipakai halaman riwayat & laporan PDF)
function daftar_riwayat(PDO $pdo, array $filter): array
{
    $cari = trim((string) ($filter['cari'] ?? ''));
    $id_kelas = (int) ($filter['id_kelas'] ?? 0);
    $tanggal_mulai = (string) ($filter['tanggal_mulai'] ?? '');
    $tanggal_akhir = (string) ($filter['tanggal_akhir'] ?? '');

    $sql = 'SELECT a.*, s.nama AS nama_siswa, s.nis, k.tingkat, k.jurusan, k.bagian, p.nama AS nama_admin
            FROM absensi_terlambat a
            JOIN siswa s ON s.id = a.id_siswa
            JOIN kelas k ON k.id = s.id_kelas
            JOIN pengguna p ON p.id = a.id_pengguna
            WHERE 1 = 1';
    $params = [];

    if ($cari !== '') {
        $sql .= ' AND (s.nama LIKE ? OR s.nis LIKE ?)';
        $params[] = '%' . $cari . '%';
        $params[] = '%' . $cari . '%';
    }
    if ($id_kelas > 0) {
        $sql .= ' AND s.id_kelas = ?';
        $params[] = $id_kelas;
    }
    if ($tanggal_mulai !== '' && strtotime($tanggal_mulai)) {
        $sql .= ' AND DATE(a.waktu_terlambat) >= ?';
        $params[] = $tanggal_mulai;
    }
    if ($tanggal_akhir !== '' && strtotime($tanggal_akhir)) {
        $sql .= ' AND DATE(a.waktu_terlambat) <= ?';
        $params[] = $tanggal_akhir;
    }
    $sql .= ' ORDER BY a.waktu_terlambat DESC, a.id DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// Jumlah catatan keterlambatan hari ini
function jumlah_terlambat_hari_ini(PDO $pdo)
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM absensi_terlambat WHERE DATE(waktu_terlambat) = CURDATE()');
    $stmt->execute();
    return (int) $stmt->fetchColumn();
}

// Susun nama kelas dari 3 kolom
function nama_kelas($tingkat, $jurusan, $bagian)
{
    return trim($tingkat . ' ' . $jurusan . ' ' . $bagian);
}

// Apakah exception karena pelanggaran unique key (duplikat)?
function duplikat(PDOException $e)
{
    return ($e->errorInfo[0] ?? '') === '23000' && (int) ($e->errorInfo[1] ?? 0) === 1062;
}

// Cari data lain yang sudah memakai nilai unik tertentu.
// $kecuali = id yang dikecualikan (saat mode ubah), null saat tambah.
// Mengembalikan baris pertama yang bentrok, atau null bila bebas.
function nilai_terpakai(PDO $pdo, $tabel, $kolom, $nilai, ?int $kecuali = null)
{
    $sql = "SELECT * FROM {$tabel} WHERE {$kolom} = ? LIMIT 1";
    $params = [$nilai];
    if ($kecuali !== null) {
        $sql = "SELECT * FROM {$tabel} WHERE {$kolom} = ? AND id <> ? LIMIT 1";
        $params[] = $kecuali;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $baris = $stmt->fetch();
    return $baris ?: null;
}

// Cari kelas lain dengan kombinasi tingkat+jurusan+bagian sama (unique key gabungan).
// $kecuali = id kelas yang dikecualikan saat mode ubah.
function kelas_terpakai(PDO $pdo, $tingkat, $jurusan, $bagian, ?int $kecuali = null)
{
    $sql = 'SELECT * FROM kelas WHERE tingkat = ? AND jurusan = ? AND bagian = ?';
    $params = [$tingkat, $jurusan, $bagian];
    if ($kecuali !== null) {
        $sql .= ' AND id <> ?';
        $params[] = $kecuali;
    }
    $stmt = $pdo->prepare($sql . ' LIMIT 1');
    $stmt->execute($params);
    $baris = $stmt->fetch();
    return $baris ?: null;
}
