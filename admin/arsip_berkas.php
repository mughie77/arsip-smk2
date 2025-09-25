<?php
require_once 'template_header.php';
require_once '../config/koneksi.php';

// Logika Filter dan Pencarian
$dari_tanggal = isset($_GET['dari']) ? $_GET['dari'] : '';
$sampai_tanggal = isset($_GET['sampai']) ? $_GET['sampai'] : '';
$keyword = isset($_GET['keyword']) ? mysqli_real_escape_string($koneksi, $_GET['keyword']) : '';

$query = "SELECT * FROM arsip_berkas";
$where_clauses = [];

if (!empty($dari_tanggal) && !empty($sampai_tanggal)) {
    $where_clauses[] = "tanggal_berkas BETWEEN '$dari_tanggal' AND '$sampai_tanggal'";
}

if (!empty($keyword)) {
    $where_clauses[] = "(no_berkas LIKE '%$keyword%' OR nama_berkas LIKE '%$keyword%')";
}

if (count($where_clauses) > 0) {
    $query .= " WHERE " . implode(' AND ', $where_clauses);
}

$limit = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Query untuk menghitung total data
$count_query = "SELECT COUNT(*) as total FROM arsip_berkas";
if (count($where_clauses) > 0) {
    $count_query .= " WHERE " . implode(' AND ', $where_clauses);
}
$count_result = mysqli_query($koneksi, $count_query);
$total_data = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_data / $limit);

// Query untuk mengambil data dengan limit dan offset
if (count($where_clauses) > 0) {
    $query .= " WHERE " . implode(' AND ', $where_clauses);
}
$query .= " ORDER BY tanggal_berkas DESC LIMIT $limit OFFSET $offset";
$result = mysqli_query($koneksi, $query);

if (!$result) {
    die("Query Error: " . mysqli_error($koneksi));
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Arsip Berkas</h1>
</div>

<!-- Area Filter -->
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-filter"></i> Filter
    </div>
    <div class="card-body">
        <form method="GET" action="arsip_berkas.php" class="row g-3 align-items-center">
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
                <input type="text" class="form-control" id="keyword" name="keyword" placeholder="Cari no berkas, nama berkas..." value="<?php echo htmlspecialchars($keyword); ?>">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">Cari</button>
                <a href="arsip_berkas.php" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>


<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-table"></i> Daftar Arsip Berkas</span>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#arsipBerkasModal" id="btnTambah">
            <i class="fas fa-plus"></i> Tambah Data
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Nomor Berkas</th>
                        <th>Nama Berkas</th>
                        <th>Tanggal Berkas</th>
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
                                <td><?php echo htmlspecialchars($row['no_berkas']); ?></td>
                                <td><?php echo htmlspecialchars($row['nama_berkas']); ?></td>
                                <td><?php echo date('d-m-Y', strtotime($row['tanggal_berkas'])); ?></td>
                                <td>
                                    <?php if (!empty($row['nama_file_pdf'])) : ?>
                                        <a href="../uploads/arsip_berkas/<?php echo htmlspecialchars($row['nama_file_pdf']); ?>" target="_blank" class="btn btn-outline-dark btn-sm">
                                            <i class="fas fa-eye"></i> Lihat
                                        </a>
                                    <?php else : ?>
                                        <span class="text-muted">No File</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-warning btn-sm btn-edit" data-id="<?php echo $row['id']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="../core/arsip_berkas_aksi.php?action=delete&id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="6" class="text-center">Tidak ada data yang ditemukan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <?php
                $query_params = http_build_query(array_filter(['dari' => $dari_tanggal, 'sampai' => $sampai_tanggal, 'keyword' => $keyword]));
                for ($i = 1; $i <= $total_pages; $i++) :
                ?>
                    <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                        <a class="page-link" href="arsip_berkas.php?page=<?php echo $i; ?>&<?php echo $query_params; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
</div>

<!-- Modal Tambah/Edit Arsip Berkas -->
<div class="modal fade" id="arsipBerkasModal" tabindex="-1" aria-labelledby="arsipBerkasModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="arsipBerkasForm" action="../core/arsip_berkas_aksi.php" method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="arsipBerkasModalLabel">Tambah Arsip Berkas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Hidden input untuk ID (untuk edit) dan action -->
                    <input type="hidden" name="id" id="id">
                    <input type="hidden" name="action" id="action" value="add">

                    <div class="mb-3">
                        <label for="no_berkas" class="form-label">Nomor Berkas</label>
                        <input type="text" class="form-control" id="no_berkas" name="no_berkas" required>
                    </div>
                    <div class="mb-3">
                        <label for="nama_berkas" class="form-label">Nama Berkas</label>
                        <input type="text" class="form-control" id="nama_berkas" name="nama_berkas" required>
                    </div>
                    <div class="mb-3">
                        <label for="tanggal_berkas" class="form-label">Tanggal Berkas</label>
                        <input type="date" class="form-control" id="tanggal_berkas" name="tanggal_berkas" required>
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
        $('#arsipBerkasModalLabel').text('Tambah Arsip Berkas');
        $('#arsipBerkasForm')[0].reset();
        $('#action').val('add');
        $('#id').val('');
        $('#fileHelp').show();
    });

    // Handle klik tombol 'Edit'
    $('.btn-edit').on('click', function() {
        var id = $(this).data('id');

        // Ubah tampilan modal untuk mode edit
        $('#arsipBerkasModalLabel').text('Edit Arsip Berkas');
        $('#action').val('edit');
        $('#id').val(id);
        $('#fileHelp').show();

        // Ambil data via AJAX untuk mengisi form
        $.ajax({
            url: '../core/arsip_berkas_fetch.php',
            type: 'POST',
            data: { id: id },
            dataType: 'json',
            success: function(data) {
                if(data.status === 'success') {
                    $('#no_berkas').val(data.data.no_berkas);
                    $('#nama_berkas').val(data.data.nama_berkas);
                    $('#tanggal_berkas').val(data.data.tanggal_berkas);
                    // Tampilkan modal setelah data terisi
                    $('#arsipBerkasModal').modal('show');
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