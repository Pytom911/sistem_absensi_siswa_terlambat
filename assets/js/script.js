function showToast(message) {
    const toast = document.getElementById('toast');
    if (!toast) return;
    toast.textContent = message;
    toast.classList.add('show');
    clearTimeout(window.toastTimeout);
    window.toastTimeout = setTimeout(() => {
        toast.classList.remove('show');
    }, 3200);
}

const apiAbsensiUrl = window.location.pathname.includes('/api/') ? 'absensi.php' : '../api/absensi.php';

function getAbsensiData(tanggal) {
    return fetch(`${apiAbsensiUrl}?tanggal=${encodeURIComponent(tanggal)}`, {
        credentials: 'same-origin'
    })
        .then((res) => res.json())
        .catch(() => ({ success: false, message: 'Gagal memuat data absensi.' }));
}

function postAbsensi(data) {
    return fetch(apiAbsensiUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify(data),
    }).then((res) => res.json());
}

function putAbsensi(data) {
    return fetch(apiAbsensiUrl, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify(data),
    }).then((res) => res.json());
}

function deleteAbsensi(data) {
    return fetch(apiAbsensiUrl, {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify(data),
    }).then((res) => res.json());
}

function initAbsensiPage() {
    const saveAllBtn = document.getElementById('saveAllBtn');
    if (!saveAllBtn) return;

    const rows = Array.from(document.querySelectorAll('tbody tr'));

    rows.forEach((row) => {
        const statusSelect = row.querySelector('.status-select');
        const saveBtn = row.querySelector('.save-row-btn');
        const deleteBtn = row.querySelector('.delete-btn');
        const leaveBtn = row.querySelector('.leave-btn');
        const jamMasuk = row.querySelector('.jam-masuk');
        const jamKeluar = row.querySelector('.jam-keluar');

        if (statusSelect) {
            statusSelect.addEventListener('change', () => {
                if (['Hadir', 'Terlambat'].includes(statusSelect.value) && jamMasuk) {
                    jamMasuk.value = jamMasuk.value || new Date().toISOString().slice(11, 16);
                }
            });
        }

        if (saveBtn) {
            saveBtn.addEventListener('click', async () => {
                const idSiswa = row.dataset.id;
                const status = statusSelect.value;
                const data = {
                    id_siswa: idSiswa,
                    tanggal: document.querySelector('input[name="tanggal"]').value,
                    status,
                    jam_masuk: jamMasuk.value || null,
                    jam_keluar: jamKeluar.value || null,
                    keterangan: row.querySelector('.keterangan').value.trim(),
                };
                if (!status) {
                    showToast('Pilih status absensi terlebih dahulu.');
                    return;
                }
                const response = await postAbsensi(data);
                showToast(response.message);
                if (response.success) {
                    setTimeout(() => window.location.reload(), 800);
                }
            });
        }

        if (deleteBtn) {
            deleteBtn.addEventListener('click', async () => {
                const idAbsensi = deleteBtn.dataset.id;
                if (!confirm('Yakin ingin menghapus data absensi siswa ini?')) return;
                const response = await deleteAbsensi({ id_absensi: idAbsensi });
                showToast(response.message);
                if (response.success) {
                    setTimeout(() => window.location.reload(), 800);
                }
            });
        }

        if (leaveBtn) {
            leaveBtn.addEventListener('click', async () => {
                const idAbsensi = leaveBtn.dataset.id;
                if (!confirm('Set jam keluar sekarang?')) return;
                const now = new Date();
                const jamKeluarValue = now.toTimeString().slice(0, 8);
                const response = await putAbsensi({ id_absensi: idAbsensi, jam_keluar: jamKeluarValue });
                showToast(response.message);
                if (response.success) {
                    setTimeout(() => window.location.reload(), 800);
                }
            });
        }
    });

    saveAllBtn.addEventListener('click', async () => {
        const dateInput = document.querySelector('input[name="tanggal"]');
        const dateValue = dateInput.value || new Date().toISOString().slice(0, 10);
        const savePromises = rows.map((row) => {
            const statusSelect = row.querySelector('.status-select');
            const jamMasuk = row.querySelector('.jam-masuk');
            const jamKeluar = row.querySelector('.jam-keluar');
            const keterangan = row.querySelector('.keterangan');
            if (!statusSelect || !statusSelect.value) {
                return Promise.resolve({ success: false, message: 'Status belum dipilih untuk beberapa siswa.' });
            }
            const data = {
                id_siswa: row.dataset.id,
                tanggal: dateValue,
                status: statusSelect.value,
                jam_masuk: jamMasuk.value || null,
                jam_keluar: jamKeluar.value || null,
                keterangan: keterangan.value.trim(),
            };
            return postAbsensi(data);
        });

        const results = await Promise.all(savePromises);
        const failed = results.some((item) => !item.success);
        showToast(failed ? 'Beberapa data belum tersimpan. Periksa kembali.' : 'Absensi berhasil disimpan.');
        if (!failed) {
            setTimeout(() => window.location.reload(), 800);
        }
    });
}

function initMenuToggle() {
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.querySelector('.sidebar');
    if (!menuToggle || !sidebar) return;

    menuToggle.addEventListener('click', () => {
        sidebar.classList.toggle('collapsed');
    });
}

function initStudentPage() {
    const addBtn = document.getElementById('addStudentBtn');
    const modal = document.getElementById('studentModal');
    const closeModal = document.getElementById('closeStudentModal');
    const studentForm = document.getElementById('studentForm');
    const toast = document.getElementById('toast');

    if (!modal || !studentForm) return;

    const openModal = () => {
        modal.classList.remove('hidden');
    };

    const closeForm = () => {
        studentForm.reset();
        studentForm.querySelector('input[name="id_siswa"]').value = '';
        document.getElementById('studentModalTitle').textContent = 'Tambah Siswa';
        modal.classList.add('hidden');
    };

    const setFormValues = (row) => {
        const id = row.dataset.id || '';
        const nis = row.dataset.nis || '';
        const nama = row.dataset.nama || '';
        const jenisKelamin = row.dataset.jenis_kelamin || '';
        const alamat = row.dataset.alamat || '';
        const noHp = row.dataset.no_hp || '';
        const status = row.dataset.status || 'Aktif';

        studentForm.querySelector('input[name="id_siswa"]').value = id;
        studentForm.querySelector('input[name="nis"]').value = nis;
        studentForm.querySelector('input[name="nama"]').value = nama;
        studentForm.querySelector('select[name="jenis_kelamin"]').value = jenisKelamin;
        studentForm.querySelector('textarea[name="alamat"]').value = alamat;
        studentForm.querySelector('input[name="no_hp"]').value = noHp;
        studentForm.querySelector('select[name="status"]').value = status;
        document.getElementById('studentModalTitle').textContent = id ? 'Edit Siswa' : 'Tambah Siswa';
    };

    if (addBtn) {
        addBtn.addEventListener('click', () => {
            closeForm();
            openModal();
        });
    }

    if (closeModal) {
        closeModal.addEventListener('click', closeForm);
    }

    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeForm();
        }
    });

    document.querySelectorAll('.edit-student-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            const row = btn.closest('tr');
            setFormValues(row);
            openModal();
        });
    });

    document.querySelectorAll('.delete-student-btn').forEach(async (btn) => {
        btn.addEventListener('click', async () => {
            const row = btn.closest('tr');
            const id = row.dataset.id;
            if (!confirm('Yakin ingin menghapus siswa ini?')) return;
            const response = await fetch('../api/siswa.php', {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_siswa: id }),
            }).then((res) => res.json());
            showToast(response.message);
            if (response.success) {
                setTimeout(() => location.reload(), 800);
            }
        });
    });

    studentForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        const formData = new FormData(studentForm);
        const data = Object.fromEntries(formData.entries());
        const method = data.id_siswa ? 'PUT' : 'POST';
        if (!data.nis || !data.nama || !data.jenis_kelamin) {
            showToast('NIS, nama, dan jenis kelamin harus diisi.');
            return;
        }
        const response = await fetch('../api/siswa.php', {
            method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data),
        }).then((res) => res.json());
        showToast(response.message);
        if (response.success) {
            closeForm();
            setTimeout(() => location.reload(), 800);
        }
    });
}

function initPage() {
    initMenuToggle();
    initAbsensiPage();
    initStudentPage();
}

document.addEventListener('DOMContentLoaded', initPage);
