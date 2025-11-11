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

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit Notulen</h1>
</div>

<div class="card">
    <div class="card-body">
        <form id="editNotulenForm" action="../core/notulen_aksi.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <input type="hidden" name="id" value="<?php echo $notulen['id']; ?>">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="nama_file_existing" value="<?php echo htmlspecialchars($notulen['nama_file']); ?>">

            <div class="mb-3">
                <label for="kegiatan" class="form-label">Kegiatan</label>
                <input type="text" class="form-control" id="kegiatan" name="kegiatan" value="<?php echo htmlspecialchars($notulen['kegiatan']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="tanggal" class="form-label">Tanggal</label>
                <input type="date" class="form-control" id="tanggal" name="tanggal" value="<?php echo htmlspecialchars($notulen['tanggal']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="nama_file" class="form-label">Unggah Berkas Baru (PDF/DOC/DOCX, max 3MB)</label>
                <input class="form-control" type="file" id="nama_file" name="nama_file" accept=".pdf,.doc,.docx">
                <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah berkas.
                    <?php if (!empty($notulen['nama_file'])) : ?>
                        Berkas saat ini: <a href="../uploads/notulen/<?php echo htmlspecialchars($notulen['nama_file']); ?>" target="_blank">Lihat File</a>
                    <?php endif; ?>
                </small>
            </div>


            <div class="modal-footer">
                <a href="notulen.php" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<?php
require_once 'template_footer.php';
?>
