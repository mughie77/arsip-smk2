<?php
require_once 'template_header.php';
require_once '../config/koneksi.php';

$id = (int)($_GET['id'] ?? 0);
if ($id === 0) {
    $_SESSION['error_message'] = "ID surat tidak valid.";
    header("Location: surat_masuk.php");
    exit;
}

$stmt = $koneksi->prepare("SELECT * FROM surat_masuk WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$surat = $result->fetch_assoc();
$stmt->close();

if (!$surat) {
    $_SESSION['error_message'] = "Data surat tidak ditemukan.";
    header("Location: surat_masuk.php");
    exit;
}
?>

<div class="mb-8">
    <a href="surat_masuk.php" class="inline-flex items-center gap-2 text-slate-500 hover:text-primary font-bold text-sm transition-colors mb-4">
        <i class="fas fa-arrow-left"></i> KEMBALI KE DAFTAR
    </a>
    <h1 class="text-3xl font-black text-slate-900 tracking-tighter">Edit <span class="text-accent italic">Surat Masuk.</span></h1>
</div>

<div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
    <div class="p-8 md:p-12">
        <form id="editSuratMasukForm" action="../core/surat_masuk_aksi.php" method="POST" enctype="multipart/form-data" class="space-y-8">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <input type="hidden" name="id" value="<?php echo $surat['id']; ?>">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="nama_file_pdf_existing" value="<?php echo htmlspecialchars($surat['nama_file_pdf']); ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-2">
                    <label class="text-xs font-black text-slate-400 uppercase tracking-widest ml-1">Nomor Surat</label>
                    <input type="text" name="nomor_surat" value="<?php echo htmlspecialchars($surat['nomor_surat']); ?>" class="w-full bg-slate-50 border-2 border-slate-100 focus:border-accent focus:bg-white rounded-2xl px-6 py-4 font-bold text-slate-700 transition-all outline-none" required>
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-black text-slate-400 uppercase tracking-widest ml-1">Asal Surat</label>
                    <input type="text" name="asal_surat" value="<?php echo htmlspecialchars($surat['asal_surat']); ?>" class="w-full bg-slate-50 border-2 border-slate-100 focus:border-accent focus:bg-white rounded-2xl px-6 py-4 font-bold text-slate-700 transition-all outline-none" required>
                </div>
            </div>

            <div class="space-y-2">
                <label class="text-xs font-black text-slate-400 uppercase tracking-widest ml-1">Perihal</label>
                <textarea name="perihal" rows="3" class="w-full bg-slate-50 border-2 border-slate-100 focus:border-accent focus:bg-white rounded-2xl px-6 py-4 font-bold text-slate-700 transition-all outline-none" required><?php echo htmlspecialchars($surat['perihal']); ?></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-2">
                    <label class="text-xs font-black text-slate-400 uppercase tracking-widest ml-1">Tanggal Diterima</label>
                    <input type="date" name="tanggal_diterima" value="<?php echo htmlspecialchars($surat['tanggal_diterima']); ?>" class="w-full bg-slate-50 border-2 border-slate-100 focus:border-accent focus:bg-white rounded-2xl px-6 py-4 font-bold text-slate-700 transition-all outline-none" required>
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-black text-slate-400 uppercase tracking-widest ml-1">Diteruskan / Acc Kepada</label>
                    <input type="text" name="acc_kepada" value="<?php echo htmlspecialchars($surat['acc_kepada']); ?>" class="w-full bg-slate-50 border-2 border-slate-100 focus:border-accent focus:bg-white rounded-2xl px-6 py-4 font-bold text-slate-700 transition-all outline-none">
                </div>
            </div>

            <div class="space-y-4">
                <label class="text-xs font-black text-slate-400 uppercase tracking-widest ml-1 text-center block">Berkas Dokumen (PDF)</label>
                <div class="bg-slate-50 border-2 border-dashed border-slate-200 rounded-3xl p-8 text-center group hover:border-accent transition-colors">
                    <i class="fas fa-cloud-upload-alt text-4xl text-slate-300 group-hover:text-accent transition-colors mb-4 block"></i>
                    <input type="file" name="nama_file_pdf" accept=".pdf" class="hidden" id="fileUpload">
                    <label for="fileUpload" class="cursor-pointer">
                        <span class="bg-white border-2 border-slate-100 text-slate-600 px-6 py-2 rounded-xl font-bold text-sm shadow-sm hover:shadow-md transition-all">Pilih Berkas Baru</span>
                    </label>
                    <p class="mt-4 text-xs font-medium text-slate-400">PDF maksimal 3MB. Kosongkan jika tidak ingin mengubah berkas.</p>

                    <?php if (!empty($surat['nama_file_pdf'])) : ?>
                        <div class="mt-6 inline-flex items-center gap-3 bg-blue-50 text-blue-600 px-4 py-2 rounded-xl border border-blue-100">
                            <i class="fas fa-file-pdf"></i>
                            <span class="text-xs font-black uppercase tracking-tight">Berkas Saat Ini:</span>
                            <a href="../uploads/surat_masuk/<?php echo htmlspecialchars($surat['nama_file_pdf']); ?>" target="_blank" class="text-xs font-bold underline hover:text-blue-700">Lihat File</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="pt-10 border-t border-slate-100 flex flex-col md:flex-row gap-4">
                <button type="submit" class="flex-1 bg-accent hover:bg-accent/90 text-white py-5 rounded-2xl text-lg font-black shadow-2xl shadow-accent/30 transition-all active:scale-95">
                    SIMPAN PERUBAHAN
                </button>
                <a href="surat_masuk.php" class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-10 py-5 rounded-2xl text-lg font-bold transition-all text-center">
                    BATAL
                </a>
            </div>
        </form>
    </div>
</div>

<?php
require_once 'template_footer.php';
?>
