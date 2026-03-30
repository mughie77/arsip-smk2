<?php
require_once 'template_header.php';
require_once '../config/koneksi.php';

$id = (int)($_GET['id'] ?? 0);
if ($id === 0) {
    $_SESSION['error_message'] = "ID berkas tidak valid.";
    header("Location: arsip_berkas.php");
    exit;
}

$stmt = $koneksi->prepare("SELECT * FROM arsip_berkas WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$berkas = $result->fetch_assoc();
$stmt->close();

if (!$berkas) {
    $_SESSION['error_message'] = "Data berkas tidak ditemukan.";
    header("Location: arsip_berkas.php");
    exit;
}
?>

<div class="mb-8">
    <a href="arsip_berkas.php" class="inline-flex items-center gap-2 text-slate-500 hover:text-primary font-bold text-sm transition-colors mb-4">
        <i class="fas fa-arrow-left"></i> KEMBALI KE DAFTAR
    </a>
    <h1 class="text-3xl font-black text-slate-900 tracking-tighter">Edit <span class="text-purple-500 italic">Berkas Arsip.</span></h1>
</div>

<div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
    <div class="p-8 md:p-12">
        <form id="editBerkasForm" action="../core/arsip_berkas_aksi.php" method="POST" enctype="multipart/form-data" class="space-y-8">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <input type="hidden" name="id" value="<?php echo $berkas['id']; ?>">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="file_path_existing" value="<?php echo htmlspecialchars($berkas['file_path']); ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-2">
                    <label class="text-xs font-black text-slate-400 uppercase tracking-widest ml-1">Nomor Berkas</label>
                    <input type="text" name="no_berkas" value="<?php echo htmlspecialchars($berkas['no_berkas']); ?>" class="w-full bg-slate-50 border-2 border-slate-100 focus:border-accent focus:bg-white rounded-2xl px-6 py-4 font-bold text-slate-700 transition-all outline-none" required>
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-black text-slate-400 uppercase tracking-widest ml-1">Nama Berkas</label>
                    <input type="text" name="nama_berkas" value="<?php echo htmlspecialchars($berkas['nama_berkas']); ?>" class="w-full bg-slate-50 border-2 border-slate-100 focus:border-accent focus:bg-white rounded-2xl px-6 py-4 font-bold text-slate-700 transition-all outline-none" required>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-2">
                    <label class="text-xs font-black text-slate-400 uppercase tracking-widest ml-1">Tanggal Berkas</label>
                    <input type="date" name="tanggal_berkas" value="<?php echo htmlspecialchars($berkas['tanggal_berkas']); ?>" class="w-full bg-slate-50 border-2 border-slate-100 focus:border-accent focus:bg-white rounded-2xl px-6 py-4 font-bold text-slate-700 transition-all outline-none" required>
                </div>
            </div>

            <div class="space-y-2">
                <label class="text-xs font-black text-slate-400 uppercase tracking-widest ml-1">Uraian / Deskripsi</label>
                <textarea name="uraian" rows="4" class="w-full bg-slate-50 border-2 border-slate-100 focus:border-accent focus:bg-white rounded-2xl px-6 py-4 font-bold text-slate-700 transition-all outline-none" required><?php echo htmlspecialchars($berkas['uraian']); ?></textarea>
            </div>

            <div class="space-y-4">
                <label class="text-xs font-black text-slate-400 uppercase tracking-widest ml-1 text-center block">Berkas Digital (PDF)</label>
                <div class="bg-slate-50 border-2 border-dashed border-slate-200 rounded-3xl p-8 text-center group hover:border-purple-500 transition-colors">
                    <i class="fas fa-file-pdf text-4xl text-slate-300 group-hover:text-purple-500 transition-colors mb-4 block"></i>
                    <input type="file" name="nama_file_pdf" accept=".pdf" class="hidden" id="fileUpload">
                    <label for="fileUpload" class="cursor-pointer">
                        <span class="bg-white border-2 border-slate-100 text-slate-600 px-6 py-2 rounded-xl font-bold text-sm shadow-sm hover:shadow-md transition-all">Pilih Berkas Baru</span>
                    </label>
                    <p class="mt-4 text-xs font-medium text-slate-400">PDF maksimal 5MB. Kosongkan jika tidak ingin mengubah.</p>

                    <?php if (!empty($berkas['file_path'])) : ?>
                        <div class="mt-6 inline-flex items-center gap-3 bg-purple-50 text-purple-600 px-4 py-2 rounded-xl border border-purple-100">
                            <i class="fas fa-file-pdf"></i>
                            <span class="text-xs font-black uppercase tracking-tight">Berkas Saat Ini:</span>
                            <a href="../uploads/berkas/<?php echo htmlspecialchars($berkas['file_path']); ?>" target="_blank" class="text-xs font-bold underline hover:text-purple-700">Lihat File</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="pt-10 border-t border-slate-100 flex flex-col md:flex-row gap-4">
                <button type="submit" class="flex-1 bg-purple-600 hover:bg-purple-700 text-white py-5 rounded-2xl text-lg font-black shadow-2xl shadow-purple-500/30 transition-all active:scale-95">
                    SIMPAN PERUBAHAN
                </button>
                <a href="arsip_berkas.php" class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-10 py-5 rounded-2xl text-lg font-bold transition-all text-center">
                    BATAL
                </a>
            </div>
        </form>
    </div>
</div>

<?php
require_once 'template_footer.php';
?>
