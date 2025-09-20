<?php
require_once 'template_header.php';
require_once '../config/koneksi.php';

// Logika Filter dan Pencarian
$dari_tanggal = isset($_GET['dari']) ? $_GET['dari'] : '';
$sampai_tanggal = isset($_GET['sampai']) ? $_GET['sampai'] : '';
$keyword = isset($_GET['keyword']) ? mysqli_real_escape_string($koneksi, $_GET['keyword']) : '';

$query = "SELECT * FROM surat_masuk";
$where_clauses = [];

if (!empty($dari_tanggal) && !empty($sampai_tanggal)) {
    $where_clauses[] = "tanggal_diterima BETWEEN '$dari_tanggal' AND '$sampai_tanggal'";
}

if (!empty($keyword)) {
    $where_clauses[] = "(nomor_arsip LIKE '%$keyword%' OR nomor_surat LIKE '%$keyword%' OR perihal LIKE '%$keyword%' OR asal_surat LIKE '%$keyword%')";
}

if (count($where_clauses) > 0) {
    $query .= " WHERE " . implode(' AND ', $where_clauses);
}

$query .= " ORDER BY tanggal_diterima DESC";

$result = mysqli_query($koneksi, $query);

if (!$result) {
    die("Query Error: " . mysqli_error($koneksi));
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Surat Masuk</h1>
</div>

<!-- Area Filter dan Ekspor -->
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-filter"></i> Filter & Ekspor
    </div>
    <div class="card-body">
        <form method="GET" action="surat_masuk.php" class="row g-3 align-items-center">
            <div class="col-md-3">
                <label for="dari" class="form-label">Dari Tanggal</label>
                <input type="date" class="form-control" id="dari" name="dari" value="<?php echo $dari_tanggal; ?>">
            </div>
            <div class="col-md-3">
                <label for="sampai" class="form-label">Sampai Tanggal</label>
                <input type="date" class="form-control" id="sampai" name="sampai" value="<?php echo $sampai_tanggal; ?>">
            </div>
            <div class="col-md-4">
                <label for="keyword" class="form-label">Kata Kunci</label>
                <input type="text" class="form-control" id="keyword" name="keyword" placeholder="Cari no arsip, no surat, perihal..." value="<?php echo htmlspecialchars($keyword); ?>">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">Cari</button>
                <a href="surat_masuk.php" class="btn btn-secondary">Reset</a>
            </div>
        </form>
        <hr>
        <div class="mt-3">
            <p class="fw-bold">Ekspor Data</p>
            <!-- Tombol ekspor akan memicu skrip ekspor dengan parameter filter dan pencarian yang sama -->
            <a href="../core/export_xlsx.php?jenis=surat_masuk&dari=<?php echo $dari_tanggal; ?>&sampai=<?php echo $sampai_tanggal; ?>&keyword=<?php echo urlencode($keyword); ?>" class="btn btn-success">
                <i class="fas fa-file-excel"></i> Download Daftar (XLSX)
            </a>
            <a href="../core/export_zip.php?jenis=surat_masuk&dari=<?php echo $dari_tanggal; ?>&sampai=<?php echo $sampai_tanggal; ?>&keyword=<?php echo urlencode($keyword); ?>" class="btn btn-info text-white">
                <i class="fas fa-file-archive"></i> Download Arsip (ZIP)
            </a>
        </div>
    </div>
</div>


<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-table"></i> Daftar Surat Masuk</span>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#suratMasukModal" id="btnTambah">
            <i class="fas fa-plus"></i> Tambah Data
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Nomor Arsip</th>
                        <th>Nomor Surat</th>
                        <th>Perihal</th>
                        <th>Asal Surat</th>
                        <th>Tanggal Diterima</th>
                        <th>Acc Kepada</th>
                        <th>Berkas</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0) : ?>
                        <?php $no = 1; ?>
                        <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars($row['nomor_arsip']); ?></td>
                                <td><?php echo htmlspecialchars($row['nomor_surat']); ?></td>
                                <td><?php echo htmlspecialchars($row['perihal']); ?></td>
                                <td><?php echo htmlspecialchars($row['asal_surat']); ?></td>
                                <td><?php echo date('d-m-Y', strtotime($row['tanggal_diterima'])); ?></td>
                                <td><?php echo htmlspecialchars($row['acc_kepada']); ?></td>
                                <td>
                                    <a href="../uploads/surat_masuk/<?php echo htmlspecialchars($row['nama_file_pdf']); ?>" target="_blank" class="btn btn-outline-dark btn-sm">
                                        <i class="fas fa-eye"></i> Lihat
                                    </a>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-warning btn-sm btn-edit" data-id="<?php echo $row['id']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="../core/surat_masuk_aksi.php?action=delete&id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="9" class="text-center">Tidak ada data yang ditemukan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah/Edit Surat Masuk -->
<div class="modal fade" id="suratMasukModal" tabindex="-1" aria-labelledby="suratMasukModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="suratMasukForm" action="../core/surat_masuk_aksi.php" method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="suratMasukModalLabel">Tambah Surat Masuk</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Hidden input untuk ID (untuk edit) dan action -->
                    <input type="hidden" name="id" id="id">
                    <input type="hidden" name="action" id="action" value="add">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nomor_surat" class="form-label">Nomor Surat</label>
                            <input type="text" class="form-control" id="nomor_surat" name="nomor_surat" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="asal_surat" class="form-label">Asal Surat</label>
                            <input type="text" class="form-control" id="asal_surat" name="asal_surat" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="perihal" class="form-label">Perihal</label>
                        <textarea class="form-control" id="perihal" name="perihal" rows="2" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tanggal_diterima" class="form-label">Tanggal Diterima</label>
                            <input type="date" class="form-control" id="tanggal_diterima" name="tanggal_diterima" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="acc_kepada" class="form-label">Diteruskan / Acc Kepada</label>
                            <input type="text" class="form-control" id="acc_kepada" name="acc_kepada">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="nama_file_pdf" class="form-label">Unggah Berkas (PDF, max 3MB)</label>
                        <input class="form-control" type="file" id="nama_file_pdf" name="nama_file_pdf" accept=".pdf">
                        <small id="fileHelp" class="form-text text-muted">Kosongkan jika tidak ingin mengubah berkas saat mengedit.</small>
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
    // Reset modal saat tombol 'Tambah Data' diklik
    $('#btnTambah').on('click', function() {
        $('#suratMasukModalLabel').text('Tambah Surat Masuk');
        $('#suratMasukForm')[0].reset();
        $('#action').val('add');
        $('#id').val('');
        $('#fileHelp').show();
    });

    // Handle klik tombol 'Edit'
    $('.btn-edit').on('click', function() {
        var id = $(this).data('id');

        // Ubah tampilan modal untuk mode edit
        $('#suratMasukModalLabel').text('Edit Surat Masuk');
        $('#action').val('edit');
        $('#id').val(id);
        $('#fileHelp').show();

        // Ambil data via AJAX untuk mengisi form
        $.ajax({
            url: '../core/surat_masuk_fetch.php',
            type: 'POST',
            data: { id: id },
            dataType: 'json',
            success: function(data) {
                if(data.status === 'success') {
                    $('#nomor_surat').val(data.data.nomor_surat);
                    $('#asal_surat').val(data.data.asal_surat);
                    $('#perihal').val(data.data.perihal);
                    $('#tanggal_diterima').val(data.data.tanggal_diterima);
                    $('#acc_kepada').val(data.data.acc_kepada);
                    // Tampilkan modal setelah data terisi
                    $('#suratMasukModal').modal('show');
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
