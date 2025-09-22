<?php
require_once 'template_header.php';
require_once '../config/koneksi.php';

$query = "SELECT * FROM klasifikasi_surat ORDER BY kode ASC";
$result = mysqli_query($koneksi, $query);

if (!$result) {
    die("Query Error: " . mysqli_error($koneksi));
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manajemen Klasifikasi Surat</h1>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-list"></i> Daftar Klasifikasi</span>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#klasifikasiModal" id="btnTambah">
            <i class="fas fa-plus"></i> Tambah Data
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Kode Klasifikasi</th>
                        <th>Jenis Surat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0) : ?>
                        <?php $no = 1; ?>
                        <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars($row['kode']); ?></td>
                                <td><?php echo htmlspecialchars($row['jenis_surat']); ?></td>
                                <td>
                                    <button type="button" class="btn btn-warning btn-sm btn-edit" data-id="<?php echo $row['id']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="../core/klasifikasi_aksi.php?action=delete&id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="4" class="text-center">Tidak ada data klasifikasi.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah/Edit Klasifikasi -->
<div class="modal fade" id="klasifikasiModal" tabindex="-1" aria-labelledby="klasifikasiModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="klasifikasiForm" action="../core/klasifikasi_aksi.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="klasifikasiModalLabel">Tambah Klasifikasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="id">
                    <input type="hidden" name="action" id="action" value="add">

                    <div class="mb-3">
                        <label for="kode" class="form-label">Kode Klasifikasi</label>
                        <input type="text" class="form-control" id="kode" name="kode" required>
                    </div>
                    <div class="mb-3">
                        <label for="jenis_surat" class="form-label">Jenis Surat</label>
                        <input type="text" class="form-control" id="jenis_surat" name="jenis_surat" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary" id="btnSimpan">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Reset form saat modal tambah dibuka
    $('#btnTambah').on('click', function() {
        $('#klasifikasiModalLabel').text('Tambah Klasifikasi');
        $('#klasifikasiForm')[0].reset();
        $('#action').val('add');
        $('#id').val('');
    });

    // Isi form saat tombol edit diklik
    $('.btn-edit').on('click', function() {
        var id = $(this).data('id');

        $('#klasifikasiModalLabel').text('Edit Klasifikasi');
        $('#action').val('edit');
        $('#id').val(id);

        // AJAX request untuk mengambil data
        $.ajax({
            url: '../core/klasifikasi_fetch.php',
            type: 'POST',
            data: { id: id },
            dataType: 'json',
            success: function(data) {
                if(data.status === 'success') {
                    $('#kode').val(data.data.kode);
                    $('#jenis_surat').val(data.data.jenis_surat);
                    $('#klasifikasiModal').modal('show');
                } else {
                    alert('Gagal mengambil data: ' + data.message);
                }
            },
            error: function() {
                alert('Terjadi kesalahan. Tidak dapat mengambil data.');
            }
        });
    });
});
</script>

<?php
require_once 'template_footer.php';
?>
