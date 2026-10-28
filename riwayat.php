<?php
// Riwayat keterlambatan: filter, ubah, dan hapus catatan
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/fungsi.php';

$cari = trim($_GET['cari'] ?? '');
$filter_kelas = (int) ($_GET['id_kelas'] ?? 0);
$tanggal_mulai = $_GET['tanggal_mulai'] ?? '';
$tanggal_akhir = $_GET['tanggal_akhir'] ?? '';

// Susun kueri string filter aktif agar bisa dibawa saat pindah halaman / redirect
function filter_riwayat(array $filter): string
{
    $bersih = array_filter([
        'cari' => $filter['cari'] ?? '',
        'id_kelas' => (int) ($filter['id_kelas'] ?? 0),
        'tanggal_mulai' => $filter['tanggal_mulai'] ?? '',
        'tanggal_akhir' => $filter['tanggal_akhir'] ?? '',
    ], fn($v) => $v !== '' && $v !== 0);
    return http_build_query($bersih);
}

$param_filter = [
    'cari' => $cari,
    'id_kelas' => $filter_kelas,
    'tanggal_mulai' => $tanggal_mulai,
    'tanggal_akhir' => $tanggal_akhir,
];
$kembali = 'riwayat.php' . (filter_riwayat($param_filter) !== '' ? '?' . filter_riwayat($param_filter) : '');

// Hapus catatan (GET, dengan konfirmasi di tombol)
if (($_GET['aksi'] ?? '') === 'hapus') {
    $id = (int) ($_GET['id'] ?? 0);
    if ($id > 0) {
        try {
            $stmt = $pdo->prepare('DELETE FROM absensi_terlambat WHERE id = ?');
            $stmt->execute([$id]);
            pesan_flash('success', 'Catatan keterlambatan berhasil dihapus.');
        } catch (PDOException $e) {
            pesan_flash('danger', 'Catatan gagal dihapus.');
        }
    }
    redirect($kembali);
}

// Simpan hasil edit (POST-Redirect-GET)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'ubah') {
    $id = (int) ($_POST['id'] ?? 0);
    $id_siswa = (int) ($_POST['id_siswa'] ?? 0);
    $waktu = $_POST['waktu_terlambat'] ?? '';
    $alasan = mb_substr(trim($_POST['alasan'] ?? ''), 0, 255);
    $keterangan = mb_substr(trim($_POST['keterangan'] ?? ''), 0, 2000);

    $ts = strtotime($waktu);
    if ($id <= 0 || $id_siswa <= 0) {
        pesan_flash('danger', 'Data tidak valid, silakan pilih ulang.');
    } elseif (!$ts) {
        pesan_flash('danger', 'Tanggal dan waktu keterlambatan tidak valid.');
    } else {
        $waktu_sql = date('Y-m-d H:i:s', $ts);

        // Cegah duplikat: siswa sama + waktu sama, kecuali record ini sendiri
        $cek = $pdo->prepare('SELECT a.id, s.nama FROM absensi_terlambat a
                              JOIN siswa s ON s.id = a.id_siswa
                              WHERE a.id_siswa = ? AND a.waktu_terlambat = ? AND a.id <> ? LIMIT 1');
        $cek->execute([$id_siswa, $waktu_sql, $id]);
        $dup = $cek->fetch();

        if ($dup) {
            pesan_flash('warning', 'Siswa "' . $dup['nama'] . '" sudah punya catatan lain pada '
                . format_waktu($waktu_sql) . '. Pilih waktu berbeda.');
        } else {
            $stmt = $pdo->prepare('UPDATE absensi_terlambat
                                   SET id_siswa = ?, waktu_terlambat = ?, alasan = ?, keterangan = ?
                                   WHERE id = ?');
            $stmt->execute([
                $id_siswa,
                $waktu_sql,
                $alasan !== '' ? $alasan : null,
                $keterangan !== '' ? $keterangan : null,
                $id,
            ]);
            pesan_flash('success', 'Catatan keterlambatan berhasil diperbarui.');
        }
    }
    redirect($kembali);
}

// Mode edit: ambil record + daftar siswa untuk select
$record_ubah = null;
if (($_GET['aksi'] ?? '') === 'ubah') {
    $id = (int) ($_GET['id'] ?? 0);
    $stmt = $pdo->prepare('SELECT a.*, s.nama AS nama_siswa, s.nis
                           FROM absensi_terlambat a
                           JOIN siswa s ON s.id = a.id_siswa
                           WHERE a.id = ?');
    $stmt->execute([$id]);
    $record_ubah = $stmt->fetch() ?: null;
    if (!$record_ubah) {
        pesan_flash('danger', 'Catatan tidak ditemukan.');
        redirect($kembali);
    }
}

$daftar_siswa_edit = [];
if ($record_ubah) {
    $daftar_siswa_edit = $pdo->query('SELECT s.id, s.nama, s.nis, k.tingkat, k.jurusan, k.bagian
                                      FROM siswa s
                                      JOIN kelas k ON k.id = s.id_kelas
                                      ORDER BY FIELD(k.tingkat,"X","XI","XII"), k.jurusan, k.bagian, s.nama')->fetchAll();
}

$riwayat = daftar_riwayat($pdo, $param_filter);

$daftar_kelas = $pdo->query('SELECT * FROM kelas
                             ORDER BY FIELD(tingkat,"X","XI","XII"), jurusan, bagian')->fetchAll();

$judul_halaman = 'Riwayat Keterlambatan';
$sub_halaman = 'Telusuri catatan keterlambatan dengan saringan';
$menu_aktif = 'riwayat';
require __DIR__ . '/includes/kepala.php';
?>

<?php if ($record_ubah): ?>
    <!-- Form edit catatan -->
    <div class="panel mb-4">
        <div class="panel-judul">
            <i class="bi bi-pencil-square"></i> Ubah Catatan
            <span class="redup" style="font-weight:400">&mdash; <?= e($record_ubah['nama_siswa']) ?>
                (<?= e($record_ubah['nis']) ?>)</span>
        </div>
        <div class="panel-isi">
            <form method="post">
                <input type="hidden" name="aksi" value="ubah">
                <input type="hidden" name="id" value="<?= (int) $record_ubah['id'] ?>">
                <input type="hidden" name="cari" value="<?= e($cari) ?>">
                <input type="hidden" name="id_kelas" value="<?= $filter_kelas ?>">
                <input type="hidden" name="tanggal_mulai" value="<?= e($tanggal_mulai) ?>">
                <input type="hidden" name="tanggal_akhir" value="<?= e($tanggal_akhir) ?>">

                <div class="baris">
                    <div class="k-33">
                        <label class="label" for="edit_siswa">Siswa <span class="wajib">*</span></label>
                        <select name="id_siswa" id="edit_siswa" class="pilih" required>
                            <?php foreach ($daftar_siswa_edit as $s): ?>
                                <option value="<?= (int) $s['id'] ?>" <?= (int) $record_ubah['id_siswa'] === (int) $s['id'] ? 'selected' : '' ?>>
                                    <?= e(nama_kelas($s['tingkat'], $s['jurusan'], $s['bagian'])) ?> - <?= e($s['nama']) ?>
                                    (<?= e($s['nis']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="k-33">
                        <label class="label" for="edit_waktu">Waktu Terlambat <span class="wajib">*</span></label>
                        <input type="datetime-local" name="waktu_terlambat" id="edit_waktu" class="kontrol" required
                            value="<?= e(date('Y-m-d\TH:i', strtotime($record_ubah['waktu_terlambat']))) ?>">
                    </div>
                    <div class="k-33">
                        <label class="label" for="edit_alasan">Alasan</label>
                        <input type="text" name="alasan" id="edit_alasan" class="kontrol" maxlength="255"
                            placeholder="Contoh: macet" value="<?= e($record_ubah['alasan']) ?>">
                    </div>
                    <div class="k-100">
                        <label class="label" for="edit_keterangan">Keterangan</label>
                        <textarea name="keterangan" id="edit_keterangan" class="area" maxlength="2000"
                            placeholder="Catatan tambahan (opsional)"><?= e($record_ubah['keterangan']) ?></textarea>
                    </div>
                    <div class="k-100">
                        <button type="submit" class="btn btn-inti"
                            onclick="return confirm('Simpan perubahan catatan ini?')">
                            <i class="bi bi-save"></i> Simpan Perubahan
                        </button>
                        <a href="<?= e($kembali) ?>" class="btn btn-garis">Batal</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<!-- Filter -->
<div class="panel mb-4">
    <div class="panel-judul"><i class="bi bi-funnel"></i> Saring Data</div>
    <div class="panel-isi">
        <form method="get" class="baris" style="align-items:flex-end">
            <div class="k-30">
                <label class="label" for="cari">Nama / NIS</label>
                <input type="text" name="cari" id="cari" class="kontrol" maxlength="100"
                    placeholder="Cari nama atau NIS..." value="<?= e($cari) ?>">
            </div>
            <div class="k-25">
                <label class="label" for="id_kelas">Kelas</label>
                <select name="id_kelas" id="id_kelas" class="pilih">
                    <option value="0">Semua kelas</option>
                    <?php foreach ($daftar_kelas as $k): ?>
                        <option value="<?= (int) $k['id'] ?>" <?= $filter_kelas === (int) $k['id'] ? 'selected' : '' ?>>
                            <?= e(nama_kelas($k['tingkat'], $k['jurusan'], $k['bagian'])) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="k-25">
                <label class="label" for="tanggal_mulai">Dari Tanggal</label>
                <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="kontrol"
                    value="<?= e($tanggal_mulai) ?>">
            </div>
            <div class="k-25">
                <label class="label" for="tanggal_akhir">Sampai Tanggal</label>
                <input type="date" name="tanggal_akhir" id="tanggal_akhir" class="kontrol"
                    value="<?= e($tanggal_akhir) ?>">
            </div>
            <div style="flex:0 0 auto">
                <button type="submit" class="btn btn-inti"><i class="bi bi-search"></i> Terapkan</button>
            </div>
            <div style="flex:0 0 auto">
                <a href="laporan.php<?= filter_riwayat($param_filter) !== '' ? '?' . e(filter_riwayat($param_filter)) : '' ?>"
                    class="btn btn-waspada" target="_blank" rel="noopener">
                    <i class="bi bi-file-earmark-pdf"></i> Export PDF
                </a>
            </div>
            <div style="flex:0 0 auto">
                <a href="riwayat.php" class="btn btn-garis">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Tabel riwayat -->
<div class="panel">
    <div class="panel-judul">
        <i class="bi bi-clock-history"></i> Daftar Keterlambatan
        <span class="badge badge-terlambat" style="margin-left:auto"><?= count($riwayat) ?> catatan</span>
    </div>

    <?php if (count($riwayat) === 0): ?>
        <div class="kosong">
            <i class="bi bi-journal-x"></i>
            <div class="kosong-judul">Tidak ada catatan yang cocok</div>
            <p>Ubah kata kunci atau rentang tanggal untuk melihat hasil lain.</p>
        </div>
    <?php else: ?>
        <div class="tabel-bungkus">
            <table class="tabel">
                <thead>
                    <tr>
                        <th style="width:56px">No</th>
                        <th>Siswa</th>
                        <th>Kelas</th>
                        <th>Waktu</th>
                        <th>Alasan</th>
                        <th>Keterangan</th>
                        <th>Pencatat</th>
                        <th class="aksi">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($riwayat as $i => $r): ?>
                        <tr>
                            <td class="angka faint"><?= $i + 1 ?></td>
                            <td>
                                <div style="font-weight:600"><?= e($r['nama_siswa']) ?></div>
                                <div class="kecil faint angka"><?= e($r['nis']) ?></div>
                            </td>
                            <td><span
                                    class="badge badge-biru"><?= e(nama_kelas($r['tingkat'], $r['jurusan'], $r['bagian'])) ?></span>
                            </td>
                            <td><span class="waktu-terlambat"><?= e(format_waktu($r['waktu_terlambat'])) ?></span></td>
                            <td class="redup"><?= e($r['alasan'] ?: '-') ?></td>
                            <td class="redup"><?= e($r['keterangan'] ?: '-') ?></td>
                            <td class="redup"><?= e($r['nama_admin']) ?></td>
                            <td class="aksi">
                                <a href="?<?= e(filter_riwayat($param_filter) !== '' ? filter_riwayat($param_filter) . '&' : '') ?>aksi=ubah&id=<?= (int) $r['id'] ?>"
                                    class="btn btn-waspada btn-sm btn-ikon" title="Ubah">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="?<?= e(filter_riwayat($param_filter) !== '' ? filter_riwayat($param_filter) . '&' : '') ?>aksi=hapus&id=<?= (int) $r['id'] ?>"
                                    class="btn btn-bahaya btn-sm btn-ikon" title="Hapus"
                                    onclick="return confirm('Hapus catatan keterlambatan <?= e($r['nama_siswa']) ?> pada <?= e(format_waktu($r['waktu_terlambat'])) ?>?')">
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