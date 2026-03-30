<?php
require_once 'template_header.php';
require_once '../config/koneksi.php';

$id = (int)($_GET['id'] ?? 0);
if ($id === 0) {
    $_SESSION['error_message'] = "ID notulen tidak valid.";
    header("Location: notulen.php");
    exit;
}

$stmt = $koneksi->prepare("SELECT * FROM notulen WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$notulen = $result->fetch_assoc();
$stmt->close();

if (!$notulen) {
    $_SESSION['error_message'] = "Data notulen tidak ditemukan.";
    header("Location: notulen.php");
    exit;
}
?>

<div class="mb-8">
    <a href="notulen.php" class="inline-flex items-center gap-2 text-slate-500 hover:text-primary font-bold text-sm transition-colors mb-4">
        <i class="fas fa-arrow-left"></i> KEMBALI KE DAFTAR
    </a>
    <h1 class="text-3xl font-black text-slate-900 tracking-tighter">Edit <span class="text-amber-500 italic">Notulen Rapat.</span></h1>
</div>

<div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden max-w-4xl">
    <div class="p-8 md:p-12">
        <form id="editNotulenForm" action="../core/notulen_aksi.php" method="POST" enctype="multipart/form-data" class="space-y-8">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <input type="hidden" name="id" value="<?php echo $notulen['id']; ?>">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="nama_file_existing" value="<?php echo htmlspecialchars($notulen['nama_file']); ?>">

            <div class="space-y-2">
                <label class="text-xs font-black text-slate-400 uppercase tracking-widest ml-1">Nama Kegiatan</label>
                <input type="text" name="kegiatan" value="<?php echo htmlspecialchars($notulen['kegiatan']); ?>" class="w-full bg-slate-50 border-2 border-slate-100 focus:border-accent focus:bg-white rounded-2xl px-6 py-4 font-bold text-slate-700 transition-all outline-none" required>
            </div>

            <div class="space-y-2">
                <label class="text-xs font-black text-slate-400 uppercase tracking-widest ml-1">Tanggal Kegiatan</label>
                <input type="date" name="tanggal" value="<?php echo htmlspecialchars($notulen['tanggal']); ?>" class="w-full bg-slate-50 border-2 border-slate-100 focus:border-accent focus:bg-white rounded-2xl px-6 py-4 font-bold text-slate-700 transition-all outline-none" required>
            </div>

            <div class="space-y-4">
                <label class="text-xs font-black text-slate-400 uppercase tracking-widest ml-1 text-center block">Unggah Berkas Baru</label>
                <div class="bg-slate-50 border-2 border-dashed border-slate-200 rounded-3xl p-8 text-center group hover:border-amber-500 transition-colors">
                    <i class="fas fa-file-alt text-4xl text-slate-300 group-hover:text-amber-500 transition-colors mb-4 block"></i>
                    <input type="file" name="nama_file" accept=".pdf,.doc,.docx" class="hidden" id="fileUpload">
                    <label for="fileUpload" class="cursor-pointer">
                        <span class="bg-white border-2 border-slate-100 text-slate-600 px-6 py-2 rounded-xl font-bold text-sm shadow-sm hover:shadow-md transition-all">Pilih Berkas Baru</span>
                    </label>
                    <p class="mt-4 text-xs font-medium text-slate-400">PDF, DOC, atau DOCX maksimal 3MB. Kosongkan jika tidak ingin mengubah.</p>

                    <?php if (!empty($notulen['nama_file'])) : ?>
                        <div class="mt-6 inline-flex items-center gap-3 bg-amber-50 text-amber-600 px-4 py-2 rounded-xl border border-amber-100">
                            <i class="fas fa-file-alt"></i>
                            <span class="text-xs font-black uppercase tracking-tight">Berkas Saat Ini:</span>
                            <a href="../uploads/notulen/<?php echo htmlspecialchars($notulen['nama_file']); ?>" target="_blank" class="text-xs font-bold underline hover:text-amber-700">Lihat File</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="pt-10 border-t border-slate-100 flex flex-col md:flex-row gap-4">
                <button type="submit" class="flex-1 bg-amber-500 hover:bg-amber-600 text-white py-5 rounded-2xl text-lg font-black shadow-2xl shadow-amber-500/30 transition-all active:scale-95">
                    SIMPAN PERUBAHAN
                </button>
                <a href="notulen.php" class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-10 py-5 rounded-2xl text-lg font-bold transition-all text-center">
                    BATAL
                </a>
            </div>
        </form>
    </div>
</div>

<?php
require_once 'template_footer.php';
?>
