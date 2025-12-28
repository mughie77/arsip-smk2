<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Arsip Digital Premium</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <!-- Header / Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-archive"></i>
                Arsip<strong>Digital</strong>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="lacak_arsip.php">Lacak Arsip</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-outline-premium ms-lg-3" href="login">
                            <i class="fas fa-sign-in-alt"></i> Login Admin
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Slider -->
    <header id="hero-slider" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#hero-slider" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
            <button type="button" data-bs-target="#hero-slider" data-bs-slide-to="1" aria-label="Slide 2"></button>
            <button type="button" data-bs-target="#hero-slider" data-bs-slide-to="2" aria-label="Slide 3"></button>
        </div>
        <div class="carousel-inner">
            <div class="carousel-item active" style="background-image: url('https://placehold.co/1920x1080/0A2647/FFFFFF?text=Manajemen+Arsip+Modern');">
                <div class="carousel-caption d-none d-md-block">
                    <h2>Manajemen Arsip yang Modern & Efisien</h2>
                    <p>Kelola semua surat dan notulensi Anda dalam satu platform yang aman dan terpusat.</p>
                </div>
            </div>
            <div class="carousel-item" style="background-image: url('https://placehold.co/1920x1080/144272/FFFFFF?text=Keamanan+Data+Terjamin');">
                <div class="carousel-caption d-none d-md-block">
                    <h2>Keamanan Data Terjamin</h2>
                    <p>Dengan sistem otentikasi dan hak akses, data arsip Anda selalu terlindungi.</p>
                </div>
            </div>
            <div class="carousel-item" style="background-image: url('https://placehold.co/1920x1080/000000/FFFFFF?text=Akses+Mudah+Kapan+Saja');">
                <div class="carousel-caption d-none d-md-block">
                    <h2>Akses Mudah Kapan Saja & Di Mana Saja</h2>
                    <p>Lacak dan temukan arsip penting Anda dengan cepat melalui fitur pencarian canggih.</p>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#hero-slider" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#hero-slider" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </header>

    <!-- Fitur Unggulan Section -->
    <section id="features" class="py-5">
        <div class="container">
            <div class="section-title">
                <h2>Fitur Unggulan</h2>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-box">
                        <div class="icon"><i class="fas fa-file-import"></i></div>
                        <h3>Digitalisasi Surat</h3>
                        <p>Ubah arsip fisik menjadi digital dengan mudah. Unggah dan kelola surat masuk dan keluar tanpa batas.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-box">
                        <div class="icon"><i class="fas fa-search"></i></div>
                        <h3>Pencarian Cepat</h3>
                        <p>Temukan dokumen dalam hitungan detik dengan fitur filter dan pencarian berdasarkan nomor arsip atau tanggal.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-box">
                        <div class="icon"><i class="fas fa-file-archive"></i></div>
                        <h3>Ekspor & Backup</h3>
                        <p>Unduh daftar arsip dalam format XLSX atau backup semua file fisik ke dalam satu file ZIP dengan sekali klik.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Tentang Sistem Section -->
    <section id="about" class="py-5 bg-light">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <img src="https://placehold.co/600x400/0A2647/FFFFFF?text=Tentang+Sistem" class="img-fluid rounded shadow" alt="Tentang Sistem">
                </div>
                <div class="col-md-6">
                    <h3>Tentang Sistem Arsip Digital</h3>
                    <p>Sistem Informasi Arsip Surat dan Notulensi Digital ini dirancang untuk memberikan solusi komprehensif bagi institusi dalam mengelola dokumen penting secara efisien, aman, dan terstruktur.</p>
                    <p>Dibangun dengan teknologi PHP Native dan Bootstrap 5, aplikasi ini menawarkan performa yang cepat dan antarmuka yang responsif, memastikan pengalaman pengguna yang premium di berbagai perangkat.</p>
                    <ul>
                        <li>Manajemen Surat Masuk, Surat Keluar, dan Notulensi.</li>
                        <li>Otentikasi admin yang aman.</li>
                        <li>Fitur pelacakan arsip untuk publik.</li>
                        <li>Ekspor data fleksibel (XLSX & ZIP).</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Sistem Arsip Digital Premium. Hak Cipta Dilindungi.</p>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" defer></script>

</body>
</html>
