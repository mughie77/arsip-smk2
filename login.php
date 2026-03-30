<?php
session_start();
// Jika sudah login, redirect ke dashboard admin
if (isset($_SESSION['admin_id'])) {
    header("Location: admin/index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Premium Archive</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#0f172a',
                        accent: '#3b82f6',
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-950 font-sans selection:bg-accent selection:text-white min-h-screen flex items-center justify-center p-6">

    <!-- Background Decoration -->
    <div class="fixed inset-0 overflow-hidden -z-10">
        <div class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] bg-accent/20 blur-[120px] rounded-full"></div>
        <div class="absolute -bottom-[10%] -right-[10%] w-[40%] h-[40%] bg-indigo-500/10 blur-[120px] rounded-full"></div>
    </div>

    <div class="w-full max-w-[480px] animate-in fade-in zoom-in duration-700">
        <div class="bg-white/5 backdrop-blur-2xl border border-white/10 rounded-[2.5rem] p-10 md:p-12 shadow-2xl">
            <div class="text-center mb-10">
                <div class="w-20 h-20 bg-accent rounded-3xl flex items-center justify-center text-white text-3xl shadow-2xl shadow-accent/40 mx-auto mb-6 rotate-3">
                    <i class="fas fa-archive"></i>
                </div>
                <h1 class="text-3xl font-black text-white tracking-tighter mb-2">Portal <span class="text-accent italic">Admin.</span></h1>
                <p class="text-slate-400 font-medium">Silakan masuk untuk mengelola arsip digital.</p>
            </div>

            <?php if (isset($_GET['error'])): ?>
                <div class="bg-rose-500/10 border border-rose-500/20 text-rose-400 p-4 rounded-2xl mb-8 flex items-center gap-3 animate-bounce">
                    <i class="fas fa-exclamation-circle text-lg"></i>
                    <span class="text-sm font-bold uppercase tracking-tight"><?php echo htmlspecialchars($_GET['error']); ?></span>
                </div>
            <?php endif; ?>

            <form action="core/login_aksi.php" method="POST" class="space-y-6">
                <div class="space-y-2">
                    <label class="text-xs font-black text-slate-400 uppercase tracking-widest ml-1">Username</label>
                    <div class="relative group">
                        <i class="fas fa-user absolute left-5 top-1/2 -translate-y-1/2 text-slate-500 group-focus-within:text-accent transition-colors"></i>
                        <input type="text" name="username" class="w-full bg-white/5 border border-white/10 focus:border-accent focus:ring-4 focus:ring-accent/10 rounded-2xl pl-14 pr-6 py-4 text-white font-bold transition-all outline-none" placeholder="Masukkan username..." required>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-xs font-black text-slate-400 uppercase tracking-widest ml-1">Password</label>
                    <div class="relative group">
                        <i class="fas fa-lock absolute left-5 top-1/2 -translate-y-1/2 text-slate-500 group-focus-within:text-accent transition-colors"></i>
                        <input type="password" name="password" class="w-full bg-white/5 border border-white/10 focus:border-accent focus:ring-4 focus:ring-accent/10 rounded-2xl pl-14 pr-6 py-4 text-white font-bold transition-all outline-none" placeholder="••••••••" required>
                    </div>
                </div>

                <button type="submit" class="w-full bg-accent hover:bg-accent/90 text-white py-5 rounded-2xl text-lg font-black shadow-2xl shadow-accent/30 transition-all hover:scale-[1.02] active:scale-95 flex items-center justify-center gap-3">
                    LOGIN SEKARANG <i class="fas fa-arrow-right text-sm"></i>
                </button>
            </form>

            <div class="mt-10 text-center">
                <a href="./" class="text-slate-500 hover:text-white font-bold text-sm transition-colors flex items-center justify-center gap-2">
                    <i class="fas fa-chevron-left text-[10px]"></i> KEMBALI KE BERANDA
                </a>
            </div>
        </div>

        <p class="mt-8 text-center text-slate-600 text-xs font-bold uppercase tracking-[0.3em]">
            &copy; 2024 Premium Archive System
        </p>
    </div>

</body>
</html>
