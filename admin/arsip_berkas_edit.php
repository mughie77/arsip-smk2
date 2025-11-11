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

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit Berkas Arsip</h1>
</div>

<div class="card">
    <div class="card-body">
        <form id="editBerkasForm" action="../core/arsip_berkas_aksi.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <input type="hidden" name="id" value="<?php echo $berkas['id']; ?>">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="file_path_existing" value="<?php echo htmlspecialchars($berkas['file_path']); ?>">

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="no_berkas" class="form-label">Nomor Berkas</label>
                    <input type="text" class="form-control" id="no_berkas" name="no_berkas" value="<?php echo htmlspecialchars($berkas['no_berkas']); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="nama_berkas" class="form-label">Nama Berkas</label>
                    <input type="text" class="form-control" id="nama_berkas" name="nama_berkas" value="<?php echo htmlspecialchars($berkas['nama_berkas']); ?>" required>
                </div>
            </div>

            <div class="mb-3">
                <label for="tanggal_berkas" class="form-label">Tanggal Berkas</label>
                <input type="date" class="form-control" id="tanggal_berkas" name="tanggal_berkas" value="<?php echo htmlspecialchars($berkas['tanggal_berkas']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="uraian" class="form-label">Uraian</label>
                <textarea class="form-control" id="uraian" name="uraian" rows="3" required><?php echo htmlspecialchars($berkas['uraian']); ?></textarea>
            </div>


            <div class="mb-3">
                <label for="nama_file_pdf" class="form-label">Unggah Berkas Baru (PDF, max 5MB)</label>
                <input class="form-control" type="file" id="nama_file_pdf" name="nama_file_pdf" accept=".pdf">
                <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah berkas.
                    <?php if (!empty($berkas['file_path'])) : ?>
                        Berkas saat ini: <a href="../uploads/berkas/<?php echo htmlspecialchars($berkas['file_path']); ?>" target="_blank">Lihat File</a>
                    <?php endif; ?>
                </small>
            </div>

            <div class="modal-footer">
                <a href="arsip_berkas.php" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<?php
require_once 'template_footer.php';
?>
