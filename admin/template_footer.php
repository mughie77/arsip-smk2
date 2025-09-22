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


<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Chart.js for graphs (can be deferred as it's likely not a dependency for inline scripts) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js" defer></script>

<!-- Custom Admin JS -->
<script>
$(document).ready(function() {
    // Logika untuk toggle sidebar
    $('#sidebarToggle').on('click', function() {
        $('.sidebar').toggleClass('toggled');
        $('.main-content').toggleClass('toggled');
    });
});
</script>

</body>
</html>
