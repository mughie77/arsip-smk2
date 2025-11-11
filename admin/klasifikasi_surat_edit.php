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

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit Klasifikasi Surat</h1>
</div>

<div class="card">
    <div class="card-body">
        <form id="editKlasifikasiForm" action="../core/klasifikasi_aksi.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <input type="hidden" name="id" value="<?php echo $klasifikasi['id']; ?>">
            <input type="hidden" name="action" value="edit">

            <div class="mb-3">
                <label for="kode" class="form-label">Kode Klasifikasi</label>
                <input type="text" class="form-control" id="kode" name="kode" value="<?php echo htmlspecialchars($klasifikasi['kode']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="jenis_surat" class="form-label">Jenis Surat</label>
                <input type="text" class="form-control" id="jenis_surat" name="jenis_surat" value="<?php echo htmlspecialchars($klasifikasi['jenis_surat']); ?>" required>
            </div>

            <div class="modal-footer">
                <a href="klasifikasi_surat.php" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<?php
require_once 'template_footer.php';
?>
