<?php
// error.php

// --- Configuration ---
$error_code = isset($_GET['code']) ? htmlspecialchars($_GET['code']) : 'Error';
$user_message = isset($_GET['message']) ? htmlspecialchars($_GET['message']) : null;

$error_details = [
    '403' => [
        'icon' => 'fa-shield-halved',
        'heading' => 'Akses Ditolak',
        'message' => 'Anda tidak memiliki izin untuk mengakses halaman ini. Silakan hubungi administrator jika Anda merasa ini adalah kesalahan.'
    ],
    '404' => [
        'icon' => 'fa-compass',
        'heading' => 'Halaman Tidak Ditemukan',
        'message' => 'Halaman yang Anda cari mungkin telah dipindahkan, dihapus, atau tidak pernah ada. Mari kami pandu Anda kembali.'
    ],
    '500' => [
        'icon' => 'fa-cogs',
        'heading' => 'Server Sedang Bermasalah',
        'message' => 'Terjadi kesalahan internal pada server kami. Tim kami telah diberitahu dan sedang menanganinya.'
    ],
    'File Not Found' => [
        'icon' => 'fa-file-circle-question',
        'heading' => 'Berkas Tidak Ditemukan',
        'message' => 'Berkas yang Anda minta tidak ada di server. Mungkin telah dihapus atau nama filenya berubah.'
    ],
    'default' => [
        'icon' => 'fa-triangle-exclamation',
        'heading' => 'Oops! Terjadi Masalah',
        'message' => 'Terjadi kesalahan yang tidak terduga. Silakan coba lagi nanti atau kembali ke beranda.'
    ]
];

$details = $error_details[$error_code] ?? $error_details['default'];
$icon_class = $details['icon'];
$heading = $details['heading'];
// Use the user-provided message if it exists, otherwise use the default one.
$message = $user_message ?? $details['message'];

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error <?php echo $error_code; ?> - Premium Archive</title>

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

    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-primary text-white min-h-screen flex items-center justify-center p-6 overflow-hidden">

    <!-- Background Decoration -->
    <div class="fixed inset-0 overflow-hidden -z-10">
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full h-full bg-accent/10 blur-[150px] rounded-full"></div>
    </div>

    <div class="max-w-2xl w-full text-center">
        <div class="bg-white/5 backdrop-blur-3xl border border-white/10 rounded-[3rem] p-12 md:p-20 shadow-2xl relative">
            <div class="absolute -top-12 left-1/2 -translate-x-1/2 w-24 h-24 bg-accent rounded-[2rem] flex items-center justify-center text-4xl shadow-2xl shadow-accent/40 rotate-12">
                <i class="fas <?php echo $icon_class; ?>"></i>
            </div>

            <h1 class="text-[8rem] md:text-[12rem] font-black tracking-tighter leading-none text-white/10 select-none">
                <?php echo $error_code; ?>
            </h1>

            <h2 class="text-3xl md:text-5xl font-black tracking-tight mb-6 -mt-8">
                <?php echo $heading; ?>
            </h2>

            <p class="text-slate-400 text-lg md:text-xl font-medium mb-12 max-w-md mx-auto italic leading-relaxed">
                "<?php echo $message; ?>"
            </p>

            <a href="/" class="inline-flex items-center gap-4 bg-white text-primary px-12 py-5 rounded-2xl font-black hover:scale-105 active:scale-95 transition-all shadow-xl">
                KEMBALI KE BERANDA <i class="fas fa-arrow-right text-sm"></i>
            </a>
        </div>

        <p class="mt-12 text-slate-500 text-xs font-bold uppercase tracking-[0.4em]">
            Premium Information Systems &copy; 2024
        </p>
    </div>

</body>
</html>
