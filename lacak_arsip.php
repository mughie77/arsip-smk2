<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lacak Arsip - Premium Digital System</title>

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen">

    <!-- Header / Navbar -->
    <nav class="sticky top-0 w-full z-50 bg-white/80 backdrop-blur-md border-b border-slate-100">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="flex justify-between h-20 items-center">
                <a href="index.php" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 bg-primary rounded-xl flex items-center justify-center text-white shadow-lg group-hover:scale-110 transition-transform">
                        <i class="fas fa-archive text-lg"></i>
                    </div>
                    <span class="text-2xl font-black tracking-tighter text-primary italic">ARSIP<span class="text-accent">DIGITAL.</span></span>
                </a>

                <div class="hidden md:flex items-center gap-10">
                    <a href="index.php#features" class="text-sm font-bold text-slate-500 hover:text-primary transition-colors">Fitur</a>
                    <a href="lacak_arsip.php" class="text-sm font-bold text-accent">Lacak Arsip</a>
                    <a href="login" class="bg-primary text-white px-8 py-3 rounded-full text-sm font-bold shadow-xl shadow-primary/20 hover:scale-105 active:scale-95 transition-all">
                        Login Admin
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-6 py-16 lg:py-24">
        <div class="text-center mb-16">
            <h1 class="text-4xl lg:text-6xl font-black text-primary tracking-tighter mb-6">
                Lacak Status <span class="text-accent italic">Arsip Anda.</span>
            </h1>
            <p class="text-lg text-slate-500 font-medium">Masukkan Nomor Arsip atau Nomor Surat untuk melihat detail dokumen secara instan.</p>
        </div>

        <div class="bg-white rounded-[2.5rem] p-10 shadow-2xl shadow-slate-200/60 border border-slate-100">
            <form action="lacak_arsip.php" method="GET" class="relative">
                <div class="relative group">
                    <i class="fas fa-search absolute left-6 top-1/2 -translate-y-1/2 text-slate-400 text-xl group-focus-within:text-accent transition-colors"></i>
                    <input type="text" name="nomor_arsip" class="w-full bg-slate-50 border-2 border-slate-100 focus:border-accent focus:bg-white rounded-3xl pl-16 pr-44 py-6 text-lg font-bold transition-all outline-none" placeholder="Contoh: SM-20240520-001" required>
                    <button type="submit" class="absolute right-3 top-3 bottom-3 bg-accent hover:bg-accent/90 text-white px-10 rounded-2xl font-black shadow-xl shadow-accent/20 transition-all active:scale-95">
                        LACAK SEKARANG
                    </button>
                </div>
            </form>

            <div id="hasilLacak" class="mt-12 space-y-8">
                <?php
                require_once 'config/koneksi.php';

                if (isset($_GET['nomor_arsip']) && !empty($_GET['nomor_arsip'])) {
                    $nomor_arsip = mysqli_real_escape_string($koneksi, $_GET['nomor_arsip']);

                    $sql = "
                        (SELECT
                            id, nomor_arsip as no_identitas, nomor_surat, perihal, asal_surat AS pihak_terkait, tanggal_diterima AS tanggal, nama_file_pdf, 'Surat Masuk' as jenis, 'surat_masuk' as tipe_folder
                        FROM surat_masuk WHERE nomor_arsip = ? OR nomor_surat = ?)
                        UNION
                        (SELECT
                            id, kode_arsip as no_identitas, nomor_surat, perihal, tujuan_surat AS pihak_terkait, tanggal_kirim AS tanggal, nama_file_pdf, 'Surat Keluar' as jenis, 'surat_keluar' as tipe_folder
                        FROM surat_keluar WHERE kode_arsip = ? OR nomor_surat = ?)
                        UNION
                        (SELECT
                            id, no_berkas as no_identitas, nama_berkas as nomor_surat, '-' as perihal, '-' as pihak_terkait, tanggal_berkas as tanggal, nama_file_pdf, 'Arsip Berkas' as jenis, 'arsip_berkas' as tipe_folder
                        FROM arsip_berkas WHERE no_berkas = ?)
                    ";

                    if ($stmt = mysqli_prepare($koneksi, $sql)) {
                        mysqli_stmt_bind_param($stmt, "sssss", $nomor_arsip, $nomor_arsip, $nomor_arsip, $nomor_arsip, $nomor_arsip);
                        mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_get_result($stmt);

                        if (mysqli_num_rows($result) > 0) {
                            $data = mysqli_fetch_assoc($result);
                            $file_url = 'uploads/' . $data['tipe_folder'] . '/' . htmlspecialchars($data['nama_file_pdf']);
                            $pihak_terkait_label = ($data['jenis'] == 'Surat Masuk') ? 'Asal Surat' : 'Tujuan Surat';
                ?>
                            <div class="animate-in slide-in-from-bottom duration-500">
                                <div class="flex items-center gap-4 mb-8">
                                    <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center text-xl">
                                        <i class="fas fa-check-double"></i>
                                    </div>
                                    <div>
                                        <h2 class="text-2xl font-black text-primary tracking-tight">Arsip Ditemukan</h2>
                                        <p class="text-slate-500 font-medium">Detail informasi untuk identitas <b><?php echo htmlspecialchars($nomor_arsip); ?></b></p>
                                    </div>
                                </div>

                                <div class="bg-slate-50 rounded-3xl p-8 lg:p-10 border border-slate-100">
                                    <div class="grid md:grid-cols-2 gap-10">
                                        <div class="space-y-6">
                                            <div>
                                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">Perihal / Judul</label>
                                                <p class="text-xl font-bold text-primary"><?php echo htmlspecialchars($data['perihal']); ?></p>
                                            </div>
                                            <div class="grid grid-cols-2 gap-6">
                                                <div>
                                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Jenis</label>
                                                    <span class="inline-flex bg-accent/10 text-accent px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider"><?php echo htmlspecialchars($data['jenis']); ?></span>
                                                </div>
                                                <div>
                                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Tanggal</label>
                                                    <p class="text-sm font-bold text-slate-700"><?php echo date('d M Y', strtotime($data['tanggal'])); ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="space-y-6">
                                            <div>
                                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">Nomor Surat</label>
                                                <p class="text-sm font-bold text-slate-700"><?php echo htmlspecialchars($data['nomor_surat']); ?></p>
                                            </div>
                                            <div>
                                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2"><?php echo $pihak_terkait_label; ?></label>
                                                <p class="text-sm font-bold text-slate-700"><?php echo htmlspecialchars($data['pihak_terkait']); ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-10 pt-10 border-t border-slate-200">
                                        <a href="<?php echo $file_url; ?>" target="_blank" class="inline-flex items-center gap-4 bg-primary text-white px-8 py-4 rounded-2xl font-black shadow-xl shadow-primary/20 hover:scale-105 active:scale-95 transition-all">
                                            <i class="fas fa-file-pdf text-xl"></i> LIHAT DOKUMEN DIGITAL (PDF)
                                        </a>
                                    </div>
                                </div>
                            </div>
                <?php
                        } else {
                            echo '<div class="bg-rose-50 border-2 border-rose-100 p-8 rounded-3xl text-center animate-in zoom-in duration-300">
                                    <div class="w-16 h-16 bg-rose-100 text-rose-500 rounded-full flex items-center justify-center text-2xl mx-auto mb-6">
                                        <i class="fas fa-times-circle"></i>
                                    </div>
                                    <h3 class="text-xl font-bold text-rose-900 mb-2">Arsip Tidak Ditemukan</h3>
                                    <p class="text-rose-600/70 font-medium italic">Nomor identitas <b>' . htmlspecialchars($nomor_arsip) . '</b> tidak terdaftar di sistem kami.</p>
                                  </div>';
                        }
                        mysqli_stmt_close($stmt);
                    }
                    mysqli_close($koneksi);
                }
                ?>
            </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="py-12 border-t border-slate-100 mt-20">
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

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery (opsional, jika Anda ingin menambahkan interaktivitas) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

</body>
</html>
