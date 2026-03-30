<?php
session_start();
require_once 'template_header.php';
require_once '../config/koneksi.php';

// Ambil data pengaturan saat ini
$query = "SELECT * FROM pengaturan WHERE id = 1"; // Asumsikan hanya ada satu baris pengaturan
$result = mysqli_query($koneksi, $query);
$pengaturan = mysqli_fetch_assoc($result);

if (!$pengaturan) {
    // Jika belum ada data, buat data default untuk menghindari error
    $pengaturan = ['nama_sekolah' => 'Belum Diatur', 'kode_sekolah' => 'Belum Diatur'];
}
?>

<div class="mb-8">
    <h1 class="text-3xl font-black text-slate-900 tracking-tighter">Sistem <span class="text-accent italic">Pengaturan.</span></h1>
    <p class="text-slate-500 mt-1 font-medium text-lg">Konfigurasi identitas institusi dan parameter sistem.</p>
</div>

<?php if (isset($_SESSION['success_message'])): ?>
    <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 mb-8 rounded-r-2xl animate-in slide-in-from-top duration-300">
        <div class="flex items-center">
            <i class="fas fa-check-circle text-emerald-500 text-lg mr-3"></i>
            <span class="text-emerald-700 font-bold"><?php echo $_SESSION['success_message']; ?></span>
        </div>
    </div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden max-w-4xl">
    <div class="bg-slate-50/50 px-10 py-6 border-b border-slate-100 flex items-center gap-3">
        <div class="w-10 h-10 bg-white rounded-xl shadow-sm flex items-center justify-center text-slate-400">
            <i class="fas fa-school"></i>
        </div>
        <h3 class="font-black text-primary uppercase tracking-widest text-sm">Identitas Institusi</h3>
    </div>
    <div class="p-10 md:p-12">
        <form action="../core/pengaturan_aksi.php" method="POST" class="space-y-8">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <input type="hidden" name="action" value="update">

            <div class="space-y-2">
                <label class="text-xs font-black text-slate-400 uppercase tracking-widest ml-1">Nama Institusi / Sekolah</label>
                <div class="relative group">
                    <i class="fas fa-university absolute left-6 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-accent transition-colors"></i>
                    <input type="text" name="nama_sekolah" value="<?php echo htmlspecialchars($pengaturan['nama_sekolah']); ?>" class="w-full bg-slate-50 border-2 border-slate-100 focus:border-accent focus:bg-white rounded-2xl pl-16 pr-6 py-4 font-bold text-slate-700 transition-all outline-none" placeholder="Masukkan nama sekolah..." required>
                </div>
            </div>

            <div class="space-y-2">
                <label class="text-xs font-black text-slate-400 uppercase tracking-widest ml-1">Kode Unik Institusi</label>
                <div class="relative group">
                    <i class="fas fa-fingerprint absolute left-6 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-accent transition-colors"></i>
                    <input type="text" name="kode_sekolah" value="<?php echo htmlspecialchars($pengaturan['kode_sekolah']); ?>" class="w-full bg-slate-50 border-2 border-slate-100 focus:border-accent focus:bg-white rounded-2xl pl-16 pr-6 py-4 font-bold text-slate-700 transition-all outline-none" placeholder="Contoh: KODE01" required>
                </div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-2 ml-1 italic">* Kode ini digunakan sebagai prefix otomatis untuk pembuatan Nomor Arsip.</p>
            </div>

            <div class="pt-8 border-t border-slate-100">
                <button type="submit" class="inline-flex items-center gap-4 bg-primary hover:bg-primary/90 text-white px-10 py-5 rounded-2xl font-black shadow-2xl shadow-primary/20 transition-all active:scale-95">
                    <i class="fas fa-save text-xl text-accent"></i> SIMPAN PERUBAHAN
                </button>
            </div>
        </form>
    </div>
</div>

<?php
require_once 'template_footer.php';
?>
