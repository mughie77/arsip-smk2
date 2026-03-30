<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premium Digital Archive - Modern Archival System</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#0f172a',
                        accent: '#3b82f6',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-white text-slate-900 font-sans selection:bg-accent selection:text-white">

    <!-- Header / Navbar -->
    <nav class="fixed top-0 w-full z-50 bg-white/80 backdrop-blur-md border-b border-slate-100">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="flex justify-between h-20 items-center">
                <div class="flex items-center gap-3 group cursor-pointer">
                    <div class="w-10 h-10 bg-primary rounded-xl flex items-center justify-center text-white shadow-lg group-hover:scale-110 transition-transform">
                        <i class="fas fa-archive text-lg"></i>
                    </div>
                    <span class="text-2xl font-black tracking-tighter text-primary italic">ARSIP<span class="text-accent">DIGITAL.</span></span>
                </div>

                <div class="hidden md:flex items-center gap-10">
                    <a href="#features" class="text-sm font-bold text-slate-500 hover:text-primary transition-colors">Fitur</a>
                    <a href="#about" class="text-sm font-bold text-slate-500 hover:text-primary transition-colors">Tentang</a>
                    <a href="lacak_arsip.php" class="text-sm font-bold text-slate-500 hover:text-primary transition-colors">Lacak Arsip</a>
                    <a href="login" class="bg-primary text-white px-8 py-3 rounded-full text-sm font-bold shadow-xl shadow-primary/20 hover:scale-105 active:scale-95 transition-all">
                        Login Admin <i class="fas fa-arrow-right ml-2 text-[10px]"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="pt-32 pb-20 lg:pt-48 lg:pb-32 overflow-hidden">
        <div class="max-w-7xl mx-auto px-6 lg:px-8 relative text-center">
            <div class="absolute -top-24 left-1/2 -translate-x-1/2 w-96 h-96 bg-accent/10 blur-[120px] rounded-full -z-10"></div>

            <span class="inline-flex bg-accent/5 text-accent px-4 py-2 rounded-full text-xs font-black tracking-widest uppercase mb-6 border border-accent/10">
                Sistem Informasi Arsip v2.0
            </span>

            <h1 class="text-5xl lg:text-8xl font-black text-primary tracking-tighter mb-8 leading-[0.9]">
                Kelola Arsip Anda <br> <span class="text-transparent bg-clip-text bg-gradient-to-r from-accent to-indigo-600 italic">Lebih Modern.</span>
            </h1>

            <p class="max-w-2xl mx-auto text-lg lg:text-xl text-slate-500 font-medium mb-12 leading-relaxed">
                Transformasikan manajemen dokumen institusi Anda dengan platform digital yang cepat, aman, dan sangat mudah digunakan.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="login" class="w-full sm:w-auto bg-accent text-white px-10 py-5 rounded-2xl text-lg font-bold shadow-2xl shadow-accent/40 hover:scale-105 active:scale-95 transition-all">
                    Mulai Sekarang
                </a>
                <a href="lacak_arsip.php" class="w-full sm:w-auto bg-slate-50 text-slate-600 px-10 py-5 rounded-2xl text-lg font-bold hover:bg-slate-100 transition-all">
                    Cek Status Arsip
                </a>
            </div>

            <div class="mt-20 lg:mt-32 relative">
                <div class="absolute inset-0 bg-gradient-to-t from-white via-transparent to-transparent z-10 h-full"></div>
                <img src="https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=2426&q=80" class="w-full rounded-[3rem] shadow-2xl border border-slate-100 hover:scale-[1.01] transition-transform duration-700" alt="Dashboard Preview">
            </div>
        </div>
    </section>

    <!-- Fitur Section -->
    <section id="features" class="py-24 bg-slate-50">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="text-center mb-20">
                <h2 class="text-4xl font-black text-primary tracking-tight mb-4">Fitur Unggulan</h2>
                <p class="text-slate-500 font-medium">Teknologi mutakhir untuk kebutuhan administrasi masa depan.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white p-10 rounded-[2.5rem] shadow-sm border border-slate-100 hover:shadow-2xl transition-all duration-300 group">
                    <div class="w-16 h-16 bg-blue-50 text-blue-500 rounded-2xl flex items-center justify-center text-2xl mb-8 group-hover:scale-110 transition-transform">
                        <i class="fas fa-file-import"></i>
                    </div>
                    <h3 class="text-xl font-bold text-primary mb-4">Digitalisasi Instan</h3>
                    <p class="text-slate-500 font-medium leading-relaxed italic">Ubah arsip fisik menjadi format digital dengan enkripsi keamanan tinggi.</p>
                </div>
                <div class="bg-white p-10 rounded-[2.5rem] shadow-sm border border-slate-100 hover:shadow-2xl transition-all duration-300 group">
                    <div class="w-16 h-16 bg-emerald-50 text-emerald-500 rounded-2xl flex items-center justify-center text-2xl mb-8 group-hover:scale-110 transition-transform">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3 class="text-xl font-bold text-primary mb-4">Pencarian AI</h3>
                    <p class="text-slate-500 font-medium leading-relaxed italic">Temukan dokumen dalam sepersekian detik dengan filter pencarian cerdas.</p>
                </div>
                <div class="bg-white p-10 rounded-[2.5rem] shadow-sm border border-slate-100 hover:shadow-2xl transition-all duration-300 group">
                    <div class="w-16 h-16 bg-indigo-50 text-indigo-500 rounded-2xl flex items-center justify-center text-2xl mb-8 group-hover:scale-110 transition-transform">
                        <i class="fas fa-file-archive"></i>
                    </div>
                    <h3 class="text-xl font-bold text-primary mb-4">Eksport Fleksibel</h3>
                    <p class="text-slate-500 font-medium leading-relaxed italic">Download laporan format XLSX atau backup seluruh berkas dalam file ZIP.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-12 border-t border-slate-100">
        <div class="max-w-7xl mx-auto px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center text-white text-xs">
                    <i class="fas fa-archive"></i>
                </div>
                <span class="font-bold text-primary italic">ARSIP<span class="text-accent">DIGITAL.</span></span>
            </div>
            <p class="text-slate-400 text-sm font-medium">&copy; 2024 Premium System. Crafted for excellence.</p>
        </div>
    </footer>

</body>
</html>
