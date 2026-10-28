<?php
// Laporan PDF keterlambatan: mengikuti filter aktif di riwayat.php, diunduh via dompdf
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/fungsi.php';
require_once __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Filter sama dengan halaman riwayat
$param_filter = [
    'cari' => trim($_GET['cari'] ?? ''),
    'id_kelas' => (int) ($_GET['id_kelas'] ?? 0),
    'tanggal_mulai' => $_GET['tanggal_mulai'] ?? '',
    'tanggal_akhir' => $_GET['tanggal_akhir'] ?? '',
];

$riwayat = daftar_riwayat($pdo, $param_filter);

// Nama kelas terpilih (untuk keterangan laporan)
$kelas_label = 'Semua kelas';
if ($param_filter['id_kelas'] > 0) {
    $stmt = $pdo->prepare('SELECT tingkat, jurusan, bagian FROM kelas WHERE id = ?');
    $stmt->execute([$param_filter['id_kelas']]);
    if ($k = $stmt->fetch()) {
        $kelas_label = nama_kelas($k['tingkat'], $k['jurusan'], $k['bagian']);
    }
}

// Periode tampil di laporan
if ($param_filter['tanggal_mulai'] !== '' && $param_filter['tanggal_akhir'] !== '') {
    $periode = format_waktu($param_filter['tanggal_mulai'] . ' 00:00:00')
        . ' &ndash; ' . format_waktu($param_filter['tanggal_akhir'] . ' 00:00:00');
} elseif ($param_filter['tanggal_mulai'] !== '') {
    $periode = 'Sejak ' . format_waktu($param_filter['tanggal_mulai'] . ' 00:00:00');
} elseif ($param_filter['tanggal_akhir'] !== '') {
    $periode = 'Sampai ' . format_waktu($param_filter['tanggal_akhir'] . ' 00:00:00');
} else {
    $periode = 'Seluruh periode';
}

// Logo sekolah disematkan sebagai data URI agar aman untuk dompdf
$logo_path = __DIR__ . '/assets/logo_smkn.png';
$logo_html = '';
if (is_file($logo_path)) {
    $logo_html = '<img src="data:image/png;base64,' . base64_encode(file_get_contents($logo_path)) . '" alt="Logo">';
}

// Baris tabel
$baris_html = '';
foreach ($riwayat as $i => $r) {
    $waktu = strtotime($r['waktu_terlambat']);
    $baris_html .= '<tr>'
        . '<td class="angka">' . ($i + 1) . '</td>'
        . '<td class="angka">' . e($r['nis']) . '</td>'
        . '<td>' . e($r['nama_siswa']) . '</td>'
        . '<td>' . e(nama_kelas($r['tingkat'], $r['jurusan'], $r['bagian'])) . '</td>'
        . '<td class="angka">' . e(date('d/m/Y', $waktu)) . '</td>'
        . '<td class="angka">' . e(date('H:i', $waktu)) . '</td>'
        . '<td>' . e($r['alasan'] ?: '-') . '</td>'
        . '<td>' . e($r['nama_admin']) . '</td>'
        . '</tr>';
}
if ($baris_html === '') {
    $baris_html = '<tr><td colspan="8" class="kosong">Tidak ada catatan keterlambatan untuk filter ini.</td></tr>';
}

$tempat_tanggal = 'Kraksaan, ' . format_waktu(date('Y-m-d 00:00:00'));
$jumlah = count($riwayat);
$cari_tampil = $param_filter['cari'] !== '' ? '"' . e($param_filter['cari']) . '"' : 'Semua';

$html = <<<HTML
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<style>
    @page { size: A4 landscape; margin: 1.5cm 1.5cm 2cm 1.5cm; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; color: #111; }
    .kop { width: 100%; border-bottom: 3px double #111; padding-bottom: 8px; margin-bottom: 14px; }
    .kop table { width: 100%; border: 0; }
    .kop td { border: 0; vertical-align: middle; }
    .logo { width: 78px; }
    .logo img { width: 78px; height: 108px; }
    .sekolah { text-align: center; }
    .sekolah h1 { font-size: 15pt; margin: 0; letter-spacing: 1px; }
    .sekolah h2 { font-size: 11pt; font-weight: normal; margin: 2px 0; }
    .sekolah p { font-size: 9pt; margin: 1px 0; color: #333; }
    .judul { text-align: center; margin: 4px 0 14px; }
    .judul h3 { font-size: 12pt; margin: 0; text-decoration: underline; }
    .judul p { font-size: 10pt; margin: 2px 0 0; }
    .meta { margin-bottom: 10px; }
    .meta table { width: auto; border: 0; }
    .meta td { border: 0; padding: 1px 6px 1px 0; vertical-align: top; }
    .meta .label { width: 90px; color: #333; }
    table.data { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
    table.data th, table.data td { border: 1px solid #555; padding: 4px 6px; }
    table.data th { background: #e9ecef; font-size: 9pt; }
    table.data tbody tr:nth-child(even) td { background: #f7f9fb; }
    .angka { text-align: center; }
    .kosong { text-align: center; color: #555; padding: 12px; }
    .tanda-tangan { width: 100%; margin-top: 18px; border: 0; }
    .tanda-tangan td { border: 0; vertical-align: top; }
    .tanda-tangan .kanan { text-align: right; width: 42%; }
    .ttd-nama { margin-top: 52px; font-weight: bold; text-decoration: underline; }
</style>
</head>
<body>

<div class="kop">
    <table>
        <tr>
            <td class="logo">{$logo_html}</td>
            <td class="sekolah">
                <h1>SMK NEGERI 1 KRAKSAAN</h1>
                <p>Jalan Veteran No. 18, Kraksaan, Probolinggo, Jawa Timur 67282</p>
                <p>Telp. (0335) 841177 &middot; Email: smkn1kraksaan@yahoo.co.id</p>
            </td>
            <td class="logo"></td>
        </tr>
    </table>
</div>

<div class="judul">
    <h3>LAPORAN KETERLAMBATAN SISWA</h3>
    <p>Periode: {$periode}</p>
</div>

<div class="meta">
    <table>
        <tr><td class="label">Kelas</td><td>:</td><td>{$kelas_label}</td></tr>
        <tr><td class="label">Pencarian</td><td>:</td><td>{$cari_tampil}</td></tr>
        <tr><td class="label">Jumlah Data</td><td>:</td><td>{$jumlah} catatan</td></tr>
    </table>
</div>

<table class="data">
    <thead>
        <tr>
            <th style="width:5%">No</th>
            <th style="width:10%">NIS</th>
            <th style="width:20%">Nama Siswa</th>
            <th style="width:14%">Kelas</th>
            <th style="width:10%">Tanggal</th>
            <th style="width:8%">Jam</th>
            <th style="width:18%">Alasan</th>
            <th style="width:15%">Pencatat</th>
        </tr>
    </thead>
    <tbody>{$baris_html}</tbody>
</table>

<table class="tanda-tangan">
    <tr>
        <td>{$tempat_tanggal}</td>
        <td class="kanan">
            Wakil Kepala Sekolah Bidang Kurikulum
            <div class="ttd-nama">..................................</div>
            <div>NIP. ......................................</div>
        </td>
    </tr>
</table>

</body>
</html>
HTML;

// Render PDF
$opsi = new Options();
$opsi->set('isRemoteEnabled', false);
$opsi->set('isHtml5ParserEnabled', true);
$dompdf = new Dompdf($opsi);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

$dompdf->stream(
    'laporan-keterlambatan-' . date('Y-m-d') . '.pdf',
    ['Attachment' => true]
);
exit;
