<?php
require_once 'template_header.php';
require_once '../config/koneksi.php';

$id = (int)($_GET['id'] ?? 0);
if ($id === 0) {
    $_SESSION['error_message'] = "ID klasifikasi tidak valid.";
    header("Location: klasifikasi_surat.php");
    exit;
}

$stmt = $koneksi->prepare("SELECT * FROM klasifikasi_surat WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$klasifikasi = $result->fetch_assoc();
$stmt->close();

if (!$klasifikasi) {
    $_SESSION['error_message'] = "Data klasifikasi tidak ditemukan.";
    header("Location: klasifikasi_surat.php");
    exit;
}
?>

<div class="mb-8">
    <a href="klasifikasi_surat.php" class="inline-flex items-center gap-2 text-slate-500 hover:text-primary font-bold text-sm transition-colors mb-4">
        <i class="fas fa-arrow-left"></i> KEMBALI KE DAFTAR
    </a>
    <h1 class="text-3xl font-black text-slate-900 tracking-tighter">Edit <span class="text-indigo-500 italic">Klasifikasi Surat.</span></h1>
</div>

<div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden max-w-2xl">
    <div class="p-8 md:p-12">
        <form id="editKlasifikasiForm" action="../core/klasifikasi_aksi.php" method="POST" class="space-y-8">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <input type="hidden" name="id" value="<?php echo $klasifikasi['id']; ?>">
            <input type="hidden" name="action" value="edit">

            <div class="space-y-2">
                <label class="text-xs font-black text-slate-400 uppercase tracking-widest ml-1">Kode Klasifikasi</label>
                <input type="text" name="kode" value="<?php echo htmlspecialchars($klasifikasi['kode']); ?>" class="w-full bg-slate-50 border-2 border-slate-100 focus:border-accent focus:bg-white rounded-2xl px-6 py-4 font-bold text-slate-700 transition-all outline-none" required>
            </div>

            <div class="space-y-2">
                <label class="text-xs font-black text-slate-400 uppercase tracking-widest ml-1">Jenis Surat</label>
                <input type="text" name="jenis_surat" value="<?php echo htmlspecialchars($klasifikasi['jenis_surat']); ?>" class="w-full bg-slate-50 border-2 border-slate-100 focus:border-accent focus:bg-white rounded-2xl px-6 py-4 font-bold text-slate-700 transition-all outline-none" required>
            </div>

            <div class="pt-10 border-t border-slate-100 flex flex-col md:flex-row gap-4">
                <button type="submit" class="flex-1 bg-indigo-500 hover:bg-indigo-600 text-white py-5 rounded-2xl text-lg font-black shadow-2xl shadow-indigo-500/30 transition-all active:scale-95">
                    SIMPAN PERUBAHAN
                </button>
                <a href="klasifikasi_surat.php" class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-10 py-5 rounded-2xl text-lg font-bold transition-all text-center">
                    BATAL
                </a>
            </div>
        </form>
    </div>
</div>

<?php
require_once 'template_footer.php';
?>
