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
       "script-src 'self' 'unsafe-inline' https://code.jquery.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://cdn.tailwindcss.com; " .
       "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com; " .
       "font-src 'self' https://cdnjs.cloudflare.com https://fonts.gstatic.com; " .
       "img-src 'self' data: https://images.unsplash.com https://placehold.co;";
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
    <link rel="icon" href="../assets/img/favicon.png" type="image/png">
    <title>Dashboard Admin - Arsip Digital</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#0f172a',
                        secondary: '#1e293b',
                        accent: '#3b82f6',
                        premium: {
                            gold: '#fbbf24',
                            dark: '#020617'
                        }
                    }
                }
            }
        }
    </script>

    <!-- Bootstrap 5 CSS (kept for compatibility during transition) -->
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

<div class="flex min-h-screen bg-slate-50">
    <!-- Sidebar -->
    <aside class="hidden lg:flex flex-col w-72 bg-primary text-white shadow-2xl transition-all duration-300">
        <div class="p-8 flex items-center gap-3 border-b border-secondary">
            <div class="w-10 h-10 bg-accent rounded-xl flex items-center justify-center shadow-lg shadow-accent/50">
                <i class="fas fa-archive text-xl text-white"></i>
            </div>
            <span class="text-2xl font-bold tracking-tight">Arsip<span class="text-accent">Digital</span></span>
        </div>

        <nav class="flex-1 px-4 py-8 space-y-2">
            <?php
            $menus = [
                ['url' => './', 'icon' => 'tachometer-alt', 'label' => 'Dashboard', 'active' => $current_page == 'index.php'],
                ['url' => 'surat_masuk', 'icon' => 'envelope-open-text', 'label' => 'Surat Masuk', 'active' => $current_page == 'surat_masuk.php'],
                ['url' => 'surat_keluar', 'icon' => 'paper-plane', 'label' => 'Surat Keluar', 'active' => $current_page == 'surat_keluar.php'],
                ['url' => 'notulen', 'icon' => 'file-alt', 'label' => 'Daftar Notulen', 'active' => $current_page == 'notulen.php'],
                ['url' => 'arsip_berkas.php', 'icon' => 'archive', 'label' => 'Arsip Berkas', 'active' => $current_page == 'arsip_berkas.php'],
                ['url' => 'klasifikasi_surat.php', 'icon' => 'tags', 'label' => 'Klasifikasi Surat', 'active' => $current_page == 'klasifikasi_surat.php'],
            ];

            foreach ($menus as $menu):
                $activeClass = $menu['active'] ? 'bg-accent text-white shadow-lg shadow-accent/40' : 'text-slate-400 hover:bg-secondary hover:text-white';
            ?>
                <a href="<?php echo $menu['url']; ?>" class="flex items-center gap-4 px-4 py-3.5 rounded-xl transition-all duration-200 font-medium <?php echo $activeClass; ?>">
                    <i class="fas fa-<?php echo $menu['icon']; ?> w-6 text-center text-lg"></i>
                    <span><?php echo $menu['label']; ?></span>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="p-4 border-t border-secondary space-y-1">
            <a href="pengaturan.php" class="flex items-center gap-4 px-4 py-3 rounded-xl text-slate-400 hover:bg-secondary hover:text-white transition-all font-medium">
                <i class="fas fa-cog w-6 text-center"></i>
                <span>Pengaturan</span>
            </a>
            <a href="../logout" class="flex items-center gap-4 px-4 py-3 rounded-xl text-rose-400 hover:bg-rose-500/10 hover:text-rose-500 transition-all font-medium">
                <i class="fas fa-sign-out-alt w-6 text-center"></i>
                <span>Keluar</span>
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col min-w-0">
        <header class="bg-white border-b border-slate-200 px-8 py-4 flex items-center justify-between sticky top-0 z-10 shadow-sm">
            <button class="lg:hidden p-2 text-slate-600 hover:bg-slate-100 rounded-lg" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu">
                <i class="fas fa-bars text-xl"></i>
            </button>

            <div class="flex items-center gap-4 ml-auto">
                <div class="hidden md:flex flex-col items-end">
                    <span class="text-sm font-semibold text-slate-900 leading-tight"><?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                    <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">Administrator</span>
                </div>
                <div class="w-10 h-10 bg-slate-200 rounded-full flex items-center justify-center border-2 border-white shadow-sm ring-1 ring-slate-100">
                    <i class="fas fa-user text-slate-500"></i>
                </div>
            </div>
        </header>

        <div class="flex-1 p-4 md:p-8">
            <!-- Konten halaman akan dimulai di sini -->

<div class="offcanvas offcanvas-start bg-primary text-white" tabindex="-1" id="mobileMenu" aria-labelledby="mobileMenuLabel">
    <div class="offcanvas-header border-b border-secondary p-6">
        <h5 class="offcanvas-title text-xl font-bold" id="mobileMenuLabel">Arsip<span class="text-accent">Digital</span></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-4">
        <nav class="space-y-2">
            <?php
            foreach ($menus as $menu):
                $activeClass = $menu['active'] ? 'bg-accent text-white shadow-lg' : 'text-slate-400 hover:bg-secondary hover:text-white';
            ?>
                <a href="<?php echo $menu['url']; ?>" class="flex items-center gap-4 px-4 py-3 rounded-xl transition-all duration-200 font-medium <?php echo $activeClass; ?>">
                    <i class="fas fa-<?php echo $menu['icon']; ?> w-6 text-center text-lg"></i>
                    <span><?php echo $menu['label']; ?></span>
                </a>
            <?php endforeach; ?>

            <div class="pt-4 mt-4 border-t border-secondary">
                <a href="pengaturan.php" class="flex items-center gap-4 px-4 py-3 rounded-xl text-slate-400 hover:bg-secondary hover:text-white transition-all font-medium">
                    <i class="fas fa-cog w-6 text-center"></i>
                    <span>Pengaturan</span>
                </a>
                <a href="../logout" class="flex items-center gap-4 px-4 py-3 rounded-xl text-rose-400 hover:bg-rose-500/10 hover:text-rose-500 transition-all font-medium">
                    <i class="fas fa-sign-out-alt w-6 text-center"></i>
                    <span>Keluar</span>
                </a>
            </div>
        </nav>
    </div>
</div>
