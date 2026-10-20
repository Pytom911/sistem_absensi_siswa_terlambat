        </main>

        <footer class="utama no-print" style="padding-top:0;padding-bottom:24px">
            <p class="kecil faint">&copy; <?= date('Y') ?> SMK Negeri 1 Kraksaan &middot; Sistem Absensi Keterlambatan</p>
        </footer>
    </div>
</div>

<script>
// Jam hidup
(function () {
    var el = document.getElementById('jamAtas');
    if (!el) return;
    function tik() {
        el.textContent = new Date().toLocaleTimeString('id-ID', { hour12: false });
    }
    tik();
    setInterval(tik, 1000);
})();

// Sidebar mobile
(function () {
    var sider = document.getElementById('sider');
    var tirai = document.getElementById('tirai');
    var tombol = document.getElementById('tombolSider');
    if (!sider || !tirai || !tombol) return;

    function buka(status) {
        sider.classList.toggle('buka', status);
        tirai.classList.toggle('buka', status);
        tirai.hidden = !status;
        tombol.setAttribute('aria-expanded', String(status));
    }

    tombol.addEventListener('click', function () {
        buka(!sider.classList.contains('buka'));
    });
    tirai.addEventListener('click', function () { buka(false); });
    document.addEventListener('keydown', function (ev) {
        if (ev.key === 'Escape') buka(false);
    });
})();

// Tutup alert flash
document.querySelectorAll('.alert-tutup').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var alert = btn.closest('.alert');
        if (alert) alert.remove();
    });
});
</script>
</body>
</html>
