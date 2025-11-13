<?php
require_once 'template_header.php';
require_once '../config/koneksi.php';

$id = (int)($_GET['id'] ?? 0);
if ($id === 0) {
    $_SESSION['error_message'] = "ID surat tidak valid.";
    header("Location: surat_keluar.php");
    exit;
}

$stmt = $koneksi->prepare("SELECT * FROM surat_keluar WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$surat = $result->fetch_assoc();
$stmt->close();

if (!$surat) {
    $_SESSION['error_message'] = "Data surat tidak ditemukan.";
    header("Location: surat_keluar.php");
    exit;
}

$klasifikasi_result = mysqli_query($koneksi, "SELECT id, jenis_surat FROM klasifikasi_surat ORDER BY jenis_surat ASC");
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit Surat Keluar</h1>
</div>

<div class="card">
    <div class="card-body">
        <form id="editSuratKeluarForm" action="../core/surat_keluar_aksi.php" method="POST" enctype="multipart/form-data">
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
                    <label for="klasifikasi_id" class="form-label">Klasifikasi Surat</label>
                    <select class="form-control" id="klasifikasi_id" name="klasifikasi_id" required>
                        <option value="">-- Pilih Klasifikasi --</option>
                        <?php while ($klasifikasi = mysqli_fetch_assoc($klasifikasi_result)) : ?>
                            <option value="<?php echo $klasifikasi['id']; ?>" <?php echo ($surat['klasifikasi_id'] == $klasifikasi['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($klasifikasi['jenis_surat']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="tujuan_surat" class="form-label">Tujuan Surat</label>
                    <input type="text" class="form-control" id="tujuan_surat" name="tujuan_surat" value="<?php echo htmlspecialchars($surat['tujuan_surat']); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="tanggal_kirim" class="form-label">Tanggal Kirim</label>
                    <input type="date" class="form-control" id="tanggal_kirim" name="tanggal_kirim" value="<?php echo htmlspecialchars($surat['tanggal_kirim']); ?>" required>
                </div>
            </div>

            <div class="mb-3">
                <label for="perihal" class="form-label">Perihal</label>
                <textarea class="form-control" id="perihal" name="perihal" rows="3" required><?php echo htmlspecialchars($surat['perihal']); ?></textarea>
            </div>


            <div class="mb-3">
                <label for="nama_file_pdf" class="form-label">Unggah Berkas Baru (PDF, max 3MB)</label>
                <input class="form-control" type="file" id="nama_file_pdf" name="nama_file_pdf" accept=".pdf">
                <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah berkas.
                    <?php if (!empty($surat['nama_file_pdf'])) : ?>
                        Berkas saat ini: <a href="../uploads/surat_keluar/<?php echo htmlspecialchars($surat['nama_file_pdf']); ?>" target="_blank">Lihat File</a>
                    <?php endif; ?>
                </small>
            </div>

            <div class="modal-footer">
                <a href="surat_keluar.php" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<?php
require_once 'template_footer.php';
?>
