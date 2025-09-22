<?php
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

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Pengaturan Data Sekolah</h1>
</div>

<?php
// Tampilkan pesan sukses atau error jika ada
if (isset($_GET['message'])) {
    $message = htmlspecialchars($_GET['message']);
    $status = isset($_GET['status']) && $_GET['status'] == 'success' ? 'success' : 'danger';
    echo "<div class='alert alert-$status'>$message</div>";
}
?>

<div class="card">
    <div class="card-header">
        <i class="fas fa-cog"></i> Form Pengaturan
    </div>
    <div class="card-body">
        <form action="../core/pengaturan_aksi.php" method="POST">
            <input type="hidden" name="action" value="update">

            <div class="mb-3">
                <label for="nama_sekolah" class="form-label">Nama Sekolah</label>
                <input type="text" class="form-control" id="nama_sekolah" name="nama_sekolah" value="<?php echo htmlspecialchars($pengaturan['nama_sekolah']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="kode_sekolah" class="form-label">Kode Sekolah</label>
                <input type="text" class="form-control" id="kode_sekolah" name="kode_sekolah" value="<?php echo htmlspecialchars($pengaturan['kode_sekolah']); ?>" required>
                <small class="form-text text-muted">Kode ini akan digunakan dalam pembuatan nomor arsip.</small>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Simpan Perubahan
            </button>
        </form>
    </div>
</div>

<?php
require_once 'template_footer.php';
?>
