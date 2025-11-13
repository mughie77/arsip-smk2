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

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit Surat Masuk</h1>
</div>

<div class="card">
    <div class="card-body">
        <form id="editSuratMasukForm" action="../core/surat_masuk_aksi.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <input type="hidden" name="id" value="<?php echo $surat['id']; ?>">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="nama_file_pdf_existing" value="<?php echo htmlspecialchars($surat['nama_file_pdf']); ?>">

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nomor_surat" class="form-label">Nomor Surat</label>
                    <input type="text" class="form-control" id="nomor_surat" name="nomor_surat" value="<?php echo htmlspecialchars($surat['nomor_surat']); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="asal_surat" class="form-label">Asal Surat</label>
                    <input type="text" class="form-control" id="asal_surat" name="asal_surat" value="<?php echo htmlspecialchars($surat['asal_surat']); ?>" required>
                </div>
            </div>
            <div class="mb-3">
                <label for="perihal" class="form-label">Perihal</label>
                <textarea class="form-control" id="perihal" name="perihal" rows="2" required><?php echo htmlspecialchars($surat['perihal']); ?></textarea>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="tanggal_diterima" class="form-label">Tanggal Diterima</label>
                    <input type="date" class="form-control" id="tanggal_diterima" name="tanggal_diterima" value="<?php echo htmlspecialchars($surat['tanggal_diterima']); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="acc_kepada" class="form-label">Diteruskan / Acc Kepada</label>
                    <input type="text" class="form-control" id="acc_kepada" name="acc_kepada" value="<?php echo htmlspecialchars($surat['acc_kepada']); ?>">
                </div>
            </div>
            <div class="mb-3">
                <label for="nama_file_pdf" class="form-label">Unggah Berkas Baru (PDF, max 3MB)</label>
                <input class="form-control" type="file" id="nama_file_pdf" name="nama_file_pdf" accept=".pdf">
                <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah berkas.
                    <?php if (!empty($surat['nama_file_pdf'])) : ?>
                        Berkas saat ini: <a href="../uploads/surat_masuk/<?php echo htmlspecialchars($surat['nama_file_pdf']); ?>" target="_blank">Lihat File</a>
                    <?php endif; ?>
                </small>
            </div>

            <div class="modal-footer">
                <a href="surat_masuk.php" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<?php
require_once 'template_footer.php';
?>
