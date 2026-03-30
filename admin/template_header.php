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

// Koneksi database wajib di-include di sini agar variabel $koneksi tersedia secara global di header
require_once '../config/koneksi.php';

// --- Perlindungan CSRF ---
// Buat token CSRF jika belum ada
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Mendapatkan path skrip saat ini untuk menandai menu aktif
$current_page = basename($_SERVER['SCRIPT_NAME']);

// Ambil data pengaturan untuk sidebar
$q_sidebar_pengaturan = mysqli_query($koneksi, "SELECT * FROM pengaturan LIMIT 1");
$d_sidebar_pengaturan = mysqli_fetch_assoc($q_sidebar_pengaturan);
$sidebar_nama_sekolah = $d_sidebar_pengaturan['nama_sekolah'] ?? 'Institusi Digital';
$sidebar_kode_sekolah = $d_sidebar_pengaturan['kode_sekolah'] ?? 'KODE-UNIK';
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
    <aside class="hidden lg:flex flex-col w-72 bg-slate-900 text-white shadow-2xl transition-all duration-300 ring-1 ring-white/10">
        <div class="p-6 flex flex-col gap-4 border-b border-white/5 bg-slate-950/50">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-500/20">
                    <i class="fas fa-archive text-xl text-white"></i>
                </div>
                <div class="flex flex-col">
                    <span class="text-xl font-black tracking-tighter italic leading-none">ARSIP<span class="text-blue-400">DIGITAL.</span></span>
                    <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest mt-1">Data Institusi</span>
                </div>
            </div>
            <div class="pl-1">
                <p class="text-xs font-bold text-white leading-tight mb-1"><?php echo htmlspecialchars($sidebar_nama_sekolah); ?></p>
                <span class="text-[9px] font-black text-blue-400 uppercase tracking-widest">ID: <?php echo htmlspecialchars($sidebar_kode_sekolah); ?></span>
            </div>
        </div>

        <nav class="flex-1 px-3 py-6 space-y-2">
            <?php
            $menus = [
                ['url' => './', 'icon' => 'tachometer-alt', 'label' => 'Dashboard', 'active' => $current_page == 'index.php', 'color' => 'blue-500'],
                ['url' => 'surat_masuk', 'icon' => 'envelope-open-text', 'label' => 'Surat Masuk', 'active' => $current_page == 'surat_masuk.php', 'color' => 'indigo-500'],
                ['url' => 'surat_keluar', 'icon' => 'paper-plane', 'label' => 'Surat Keluar', 'active' => $current_page == 'surat_keluar.php', 'color' => 'emerald-500'],
                ['url' => 'notulen', 'icon' => 'file-alt', 'label' => 'Daftar Notulen', 'active' => $current_page == 'notulen.php', 'color' => 'amber-500'],
                ['url' => 'arsip_berkas.php', 'icon' => 'archive', 'label' => 'Arsip Berkas', 'active' => $current_page == 'arsip_berkas.php', 'color' => 'purple-500'],
                ['url' => 'klasifikasi_surat.php', 'icon' => 'tags', 'label' => 'Klasifikasi Surat', 'active' => $current_page == 'klasifikasi_surat.php', 'color' => 'rose-500'],
            ];

            foreach ($menus as $menu):
                $activeClass = $menu['active'] ? 'bg-white/10 text-white border-l-4 border-blue-500 shadow-md' : 'text-slate-400 hover:bg-white/5 hover:text-white';
                $iconColor = 'bg-' . $menu['color'];
                $shadowClass = 'shadow-' . $menu['color'] . '/20';
                $activeIconClass = $menu['active'] ? 'scale-110 shadow-lg ' . $shadowClass : 'opacity-40 grayscale group-hover:opacity-100 group-hover:grayscale-0';
            ?>
                <a href="<?php echo $menu['url']; ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-300 font-bold group <?php echo $activeClass; ?>">
                    <div class="w-9 h-9 rounded-lg flex items-center justify-center transition-all duration-300 <?php echo $iconColor; ?> <?php echo $activeIconClass; ?> group-hover:scale-110">
                        <i class="fas fa-<?php echo $menu['icon']; ?> text-white text-base"></i>
                    </div>
                    <span class="tracking-widest uppercase text-[10px]"><?php echo $menu['label']; ?></span>
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

<div class="offcanvas offcanvas-start bg-slate-900 text-white w-72" tabindex="-1" id="mobileMenu" aria-labelledby="mobileMenuLabel">
    <div class="offcanvas-header border-b border-white/5 p-6 flex flex-col gap-4">
        <div class="flex items-center justify-between w-full">
            <h5 class="offcanvas-title text-xl font-black italic" id="mobileMenuLabel">ARSIP<span class="text-blue-400">DIGITAL.</span></h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
            <div class="bg-white/5 p-4 rounded-xl border border-white/10 w-full text-left">
                <p class="text-[10px] font-bold text-blue-400 uppercase tracking-widest mb-1">Institusi</p>
                <p class="text-xs font-bold text-white leading-tight mb-1"><?php echo htmlspecialchars($sidebar_nama_sekolah); ?></p>
                <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest">ID: <?php echo htmlspecialchars($sidebar_kode_sekolah); ?></span>
        </div>
    </div>
    <div class="offcanvas-body p-4">
        <nav class="space-y-2">
            <?php
            foreach ($menus as $menu):
                $activeClass = $menu['active'] ? 'bg-white/10 text-white border-l-4 border-blue-500' : 'text-slate-400 hover:bg-white/5 hover:text-white';
                $iconBg = 'bg-' . $menu['color'];
            ?>
                <a href="<?php echo $menu['url']; ?>" class="flex items-center gap-3 px-3 py-3 rounded-xl transition-all duration-300 font-bold <?php echo $activeClass; ?>">
                    <div class="w-9 h-9 rounded-lg flex items-center justify-center <?php echo $iconBg; ?>">
                        <i class="fas fa-<?php echo $menu['icon']; ?> text-white text-base"></i>
                    </div>
                    <span class="tracking-widest uppercase text-[9px]"><?php echo $menu['label']; ?></span>
                </a>
            <?php endforeach; ?>

            <div class="pt-6 mt-6 border-t border-white/5">
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
