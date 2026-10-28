<?php
// Pencatatan keterlambatan: pilih kelas -> cari siswa -> isi formulir -> simpan
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/fungsi.php';

// Simpan catatan (POST-Redirect-GET)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'simpan') {
    $id_siswa = (int) ($_POST['id_siswa'] ?? 0);
    $waktu = $_POST['waktu_terlambat'] ?? '';
    $alasan = mb_substr(trim($_POST['alasan'] ?? ''), 0, 255);
    $keterangan = mb_substr(trim($_POST['keterangan'] ?? ''), 0, 2000);

    $ts = strtotime($waktu);
    if ($id_siswa <= 0) {
        pesan_flash('danger', 'Siswa tidak valid, silakan pilih ulang.');
    } elseif (!$ts) {
        pesan_flash('danger', 'Tanggal dan waktu keterlambatan tidak valid.');
    } else {
        // Cegah duplikat: siswa sama + waktu sama
        $cek = $pdo->prepare('SELECT a.id, s.nama FROM absensi_terlambat a
                              JOIN siswa s ON s.id = a.id_siswa
                              WHERE a.id_siswa = ? AND a.waktu_terlambat = ? LIMIT 1');
        $cek->execute([$id_siswa, date('Y-m-d H:i:s', $ts)]);
        $dup = $cek->fetch();

        if ($dup) {
            pesan_flash('warning', 'Siswa "' . $dup['nama'] . '" sudah punya catatan pada '
                . format_waktu(date('Y-m-d H:i:s', $ts))
                . '. Periksa riwayat sebelum mencatat ulang.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO absensi_terlambat (id_siswa, id_pengguna, waktu_terlambat, alasan, keterangan)
                                   VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([
                $id_siswa,
                $_SESSION['id_pengguna'],
                date('Y-m-d H:i:s', $ts),
                $alasan !== '' ? $alasan : null,
                $keterangan !== '' ? $keterangan : null,
            ]);
            pesan_flash('success', 'Keterlambatan berhasil dicatat.');
        }
    }

    $kembali = 'catat.php?' . http_build_query(array_filter([
        'tingkat' => $_POST['tingkat'] ?? '',
        'jurusan' => $_POST['jurusan'] ?? '',
        'bagian' => $_POST['bagian'] ?? '',
        'cari' => $_POST['cari'] ?? '',
    ], fn($v) => $v !== ''));
    redirect($kembali);
}

// Pilihan dropdown dari data kelas aktual
$daftar_tingkat = $pdo->query("SELECT DISTINCT tingkat FROM kelas ORDER BY FIELD(tingkat,'X','XI','XII')")->fetchAll(PDO::FETCH_COLUMN);

$tingkat = $_GET['tingkat'] ?? '';
$jurusan = $_GET['jurusan'] ?? '';
$bagian = $_GET['bagian'] ?? '';
$cari = trim($_GET['cari'] ?? '');
$id_siswa_pilihan = (int) ($_GET['pilih'] ?? 0);

$daftar_jurusan = [];
if ($tingkat !== '') {
    $stmt = $pdo->prepare('SELECT DISTINCT jurusan FROM kelas WHERE tingkat = ? ORDER BY jurusan');
    $stmt->execute([$tingkat]);
    $daftar_jurusan = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

$daftar_bagian = [];
if ($tingkat !== '' && $jurusan !== '') {
    $stmt = $pdo->prepare('SELECT DISTINCT bagian FROM kelas WHERE tingkat = ? AND jurusan = ? ORDER BY bagian');
    $stmt->execute([$tingkat, $jurusan]);
    $daftar_bagian = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Cari id kelas dari kombinasi pilihan
$id_kelas = 0;
$kelas_dipilih = '';
if ($tingkat !== '' && $jurusan !== '' && $bagian !== '') {
    $stmt = $pdo->prepare('SELECT id FROM kelas WHERE tingkat = ? AND jurusan = ? AND bagian = ?');
    $stmt->execute([$tingkat, $jurusan, $bagian]);
    $id_kelas = (int) $stmt->fetchColumn();
    $kelas_dipilih = nama_kelas($tingkat, $jurusan, $bagian);
}

// Daftar siswa kelas terpilih (+ pencarian nama/NIS)
$siswa_terpilih = null;
$daftar_siswa = [];
if ($id_kelas > 0) {
    $sql = 'SELECT * FROM siswa WHERE id_kelas = ?';
    $params = [$id_kelas];
    if ($cari !== '') {
        $sql .= ' AND (nama LIKE ? OR nis LIKE ?)';
        $params[] = '%' . $cari . '%';
        $params[] = '%' . $cari . '%';
    }
    $sql .= ' ORDER BY nama';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $daftar_siswa = $stmt->fetchAll();

    if ($id_siswa_pilihan > 0) {
        foreach ($daftar_siswa as $s) {
            if ((int) $s['id'] === $id_siswa_pilihan) {
                $siswa_terpilih = $s;
                break;
            }
        }
    }
}

// Waktu default otomatis sekarang
$waktu_default = date('Y-m-d\TH:i');

$judul_halaman = 'Pencatatan Keterlambatan';
$sub_halaman = 'Pilih kelas, cari siswa, lalu catat keterlambatan';
$menu_aktif = 'catat';
require __DIR__ . '/includes/kepala.php';
?>

<!-- Langkah -->
<div class="baris mb-4 langkah no-print" style="align-items:center;gap:10px">
    <span class="langkah-bilik<?= $id_kelas <= 0 ? ' aktif' : '' ?>"><span class="no">1</span> Pilih Kelas</span>
    <span class="langkah-pemisah"></span>
    <span class="langkah-bilik<?= $id_kelas > 0 && !$siswa_terpilih ? ' aktif' : '' ?>"><span class="no">2</span> Pilih
        Siswa</span>
    <span class="langkah-pemisah"></span>
    <span class="langkah-bilik<?= $siswa_terpilih ? ' aktif' : '' ?>"><span class="no">3</span> Isi &amp; Simpan</span>
</div>

<!-- Langkah 1: pilih kelas -->
<div class="panel mb-4">
    <div class="panel-judul"><i class="bi bi-building"></i> 1. Pilih Kelas</div>
    <div class="panel-isi">
        <form method="get" class="baris">
            <div class="k-25">
                <label class="label" for="tingkat">Tingkat</label>
                <select name="tingkat" id="tingkat" class="pilih" onchange="this.form.submit()" required>
                    <option value="">-- pilih --</option>
                    <?php foreach ($daftar_tingkat as $t): ?>
                        <option value="<?= e($t) ?>" <?= $tingkat === $t ? 'selected' : '' ?>><?= e($t) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="k-25">
                <label class="label" for="jurusan">Jurusan</label>
                <select name="jurusan" id="jurusan" class="pilih" onchange="this.form.submit()" required>
                    <option value="">-- pilih --</option>
                    <?php foreach ($daftar_jurusan as $j): ?>
                        <option value="<?= e($j) ?>" <?= $jurusan === $j ? 'selected' : '' ?>><?= e($j) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="k-25">
                <label class="label" for="bagian">Bagian</label>
                <select name="bagian" id="bagian" class="pilih" onchange="this.form.submit()" required>
                    <option value="">-- pilih --</option>
                    <?php foreach ($daftar_bagian as $b): ?>
                        <option value="<?= e($b) ?>" <?= $bagian === $b ? 'selected' : '' ?>><?= e($b) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>
</div>

<?php if ($id_kelas > 0): ?>
    <!-- Langkah 2: daftar siswa + pencarian -->
    <div class="panel mb-4">
        <div class="panel-judul">
            <i class="bi bi-people"></i> 2. Siswa Kelas <?= e($kelas_dipilih) ?>
            <span class="badge badge-biru" style="margin-left:auto"><?= count($daftar_siswa) ?> siswa</span>
        </div>
        <div class="panel-isi">
            <form method="get" class="baris mb-4" style="align-items:flex-end">
                <input type="hidden" name="tingkat" value="<?= e($tingkat) ?>">
                <input type="hidden" name="jurusan" value="<?= e($jurusan) ?>">
                <input type="hidden" name="bagian" value="<?= e($bagian) ?>">
                <div class="k-40">
                    <label class="label" for="cari">Cari siswa</label>
                    <input type="text" name="cari" id="cari" class="kontrol" maxlength="100" placeholder="Nama atau NIS..."
                        value="<?= e($cari) ?>">
                </div>
                <div class="k-25" style="flex:0 0 auto">
                    <button type="submit" class="btn btn-inti"><i class="bi bi-search"></i> Cari</button>
                </div>
                <?php if ($cari !== ''): ?>
                    <div style="flex:0 0 auto">
                        <a href="?tingkat=<?= e($tingkat) ?>&jurusan=<?= e($jurusan) ?>&bagian=<?= e($bagian) ?>"
                            class="btn btn-garis">Reset</a>
                    </div>
                <?php endif; ?>
            </form>

            <?php if (count($daftar_siswa) === 0): ?>
                <div class="kosong">
                    <i class="bi bi-person-x"></i>
                    <div class="kosong-judul">Tidak ada siswa yang cocok</div>
                    <p>Ubah kata kunci pencarian atau pastikan kelas sudah berisi siswa.</p>
                </div>
            <?php else: ?>
                <div class="tabel-bungkus">
                    <table class="tabel">
                        <thead>
                            <tr>
                                <th>NIS</th>
                                <th>Nama</th>
                                <th class="aksi">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($daftar_siswa as $s): ?>
                                <tr>
                                    <td class="angka"><?= e($s['nis']) ?></td>
                                    <td style="font-weight:600"><?= e($s['nama']) ?></td>
                                    <td class="aksi">
                                        <a class="btn btn-inti btn-sm"
                                            href="?tingkat=<?= e($tingkat) ?>&jurusan=<?= e($jurusan) ?>&bagian=<?= e($bagian) ?>&cari=<?= e($cari) ?>&pilih=<?= (int) $s['id'] ?>">
                                            Pilih
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($siswa_terpilih): ?>
        <!-- Langkah 3: formulir pencatatan -->
        <div class="panel mb-4">
            <div class="panel-judul">
                <i class="bi bi-pencil-square"></i> 3. Catat Keterlambatan
                <span class="redup" style="font-weight:400">&mdash; <?= e($siswa_terpilih['nama']) ?>
                    (<?= e($siswa_terpilih['nis']) ?>)</span>
            </div>
            <div class="panel-isi">
                <form method="post">
                    <input type="hidden" name="aksi" value="simpan">
                    <input type="hidden" name="id_siswa" value="<?= (int) $siswa_terpilih['id'] ?>">
                    <input type="hidden" name="tingkat" value="<?= e($tingkat) ?>">
                    <input type="hidden" name="jurusan" value="<?= e($jurusan) ?>">
                    <input type="hidden" name="bagian" value="<?= e($bagian) ?>">
                    <input type="hidden" name="cari" value="<?= e($cari) ?>">

                    <div class="baris">
                        <div class="k-30">
                            <label class="label" for="waktu_terlambat">Waktu Terlambat <span class="wajib">*</span></label>
                            <input type="datetime-local" name="waktu_terlambat" id="waktu_terlambat" class="kontrol"
                                value="<?= e($waktu_default) ?>" required>
                            <div class="bantuan">Terisi otomatis waktu sekarang, boleh diubah.</div>
                        </div>
                        <div class="k-70 tumbuh">
                            <label class="label" for="alasan">Alasan</label>
                            <input type="text" name="alasan" id="alasan" class="kontrol" maxlength="255"
                                placeholder="Contoh: macet, ada keperluan keluarga">
                        </div>
                        <div class="k-100">
                            <label class="label" for="keterangan">Keterangan</label>
                            <textarea name="keterangan" id="keterangan" class="area" maxlength="2000"
                                placeholder="Catatan tambahan (opsional)"></textarea>
                        </div>
                        <div class="k-100">
                            <button type="submit" class="btn btn-inti"
                                onclick="return confirm('Simpan catatan keterlambatan ini?')">
                                <i class="bi bi-save"></i> Simpan Catatan
                            </button>
                            <a href="?tingkat=<?= e($tingkat) ?>&jurusan=<?= e($jurusan) ?>&bagian=<?= e($bagian) ?>&cari=<?= e($cari) ?>"
                                class="btn btn-garis">Batal</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

<?php else: ?>
    <div class="panel">
        <div class="kosong">
            <i class="bi bi-list-check"></i>
            <div class="kosong-judul">Mulai dengan memilih kelas</div>
            <p>Pilih tingkat, jurusan, dan bagian untuk menampilkan daftar siswa.</p>
        </div>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/kaki.php'; ?>