<?php
// --- Pengaturan Security Headers ---
// Mencegah clickjacking
header("X-Frame-Options: DENY");
// Mencegah browser dari menebak tipe MIME
header("X-Content-Type-Options: nosniff");
// Mendorong penggunaan HTTPS
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
// Kebijakan Keamanan Konten (CSP) yang lebih ketat
$csp = "default-src 'self'; " .
       "script-src 'self' https://code.jquery.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; " .
       "style-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; " .
       "font-src 'self' https://cdnjs.cloudflare.com; " .
       "img-src 'self' data:;";
header("Content-Security-Policy: " . $csp);


// Memulai session dan memeriksa status login
require_once 'cek_sesi.php';

// --- Perlindungan CSRF ---
// Buat token CSRF jika belum ada
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Mendapatkan path skrip saat ini untuk menandai menu aktif
$current_page = basename($_SERVER['SCRIPT_NAME']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Arsip Digital</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/custom.css">

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
</head>
<body>

<div class="wrapper d-flex align-items-stretch">
    <!-- Sidebar -->
    <nav id="sidebar" class="sidebar">
        <div class="sidebar-header">
            <a href="./">
                <i class="fas fa-archive"></i> Arsip<strong>Digital</strong>
            </a>
        </div>
        <ul class="nav flex-column flex-grow-1">
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>" href="./">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'surat_masuk.php') ? 'active' : ''; ?>" href="surat_masuk">
                    <i class="fas fa-envelope-open-text"></i> Surat Masuk
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'surat_keluar.php') ? 'active' : ''; ?>" href="surat_keluar">
                    <i class="fas fa-paper-plane"></i> Surat Keluar
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'notulen.php') ? 'active' : ''; ?>" href="notulen">
                    <i class="fas fa-file-alt"></i> Daftar Notulen
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'arsip_berkas.php') ? 'active' : ''; ?>" href="arsip_berkas.php">
                    <i class="fas fa-archive"></i> Arsip Berkas
                </a>
            </li>
                    <li class="nav-item">
                        <a class="nav-link" href="klasifikasi_surat.php">
                            <i class="fas fa-tags"></i> Klasifikasi Surat
                        </a>
                    </li>
        </ul>
        <div class="mt-auto">
             <ul class="nav flex-column">
                 <li class="nav-item">
                    <a class="nav-link" href="pengaturan.php">
                        <i class="fas fa-cog"></i> Pengaturan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../logout">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
             </ul>
        </div>
    </nav>
    <!-- End Sidebar -->

    <!-- Main Content -->
    <div class="sidebar-overlay"></div>
    <div class="main-content">
        <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
            <div class="container-fluid">
                <button class="btn btn-outline-secondary" type="button" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="ms-auto">
                    <span class="navbar-text">
                        Selamat datang, <strong><?php echo htmlspecialchars($_SESSION['admin_username']); ?></strong>
                    </span>
                </div>
            </div>
        </nav>

        <div class="container-fluid">
            <!-- Konten halaman akan dimulai di sini -->
