<!-- Konten halaman berakhir di sini -->
        </div>
    </div>
    <!-- End Main Content -->
</div>
<!-- Footer Universal -->
<footer class="footer mt-auto py-3 bg-light border-top">
    <div class="container-fluid text-center">
        <span class="text-muted">&copy; 2024 Sistem Arsip Digital Premium. Hak Cipta Dilindungi.</span>
    </div>
</footer>

<!-- JQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- Chart.js for graphs (can be deferred as it's likely not a dependency for inline scripts) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js" defer></script>

<!-- Custom Admin JS -->
<script>
$(document).ready(function() {
    // Fungsi untuk menampilkan atau menyembunyikan sidebar
    function toggleSidebar() {
        $('body').toggleClass('sidebar-toggled');
    }

    // Event listener untuk tombol toggle
    $('#sidebarToggle').on('click', function(e) {
        e.stopPropagation(); // Mencegah event bubbling
        toggleSidebar();
    });

    // Event listener untuk overlay (hanya aktif di mode mobile)
    $('.sidebar-overlay').on('click', function() {
        if ($(window).width() < 992) {
            toggleSidebar();
        }
    });

    // Cek lebar layar saat halaman dimuat
    // Jika layar desktop, sidebar tidak tertoggle secara default
    if ($(window).width() >= 992) {
        // Hapus class jika ada untuk memastikan state awal benar di desktop
        if ($('body').hasClass('sidebar-toggled')) {
            // Uncomment baris berikut jika ingin sidebar selalu terbuka di desktop saat reload
            // $('body').removeClass('sidebar-toggled');
        }
    }
});
</script>

</body>
</html>
