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

// Logika Pagination
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
if (!in_array($limit, [10, 20, 100])) {
    $limit = 10; // Nilai default jika input tidak valid
}
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Query untuk menghitung total data
$count_query = "SELECT COUNT(*) as total FROM surat_masuk";
if (count($where_clauses) > 0) {
    $count_query .= " WHERE " . implode(' AND ', $where_clauses);
}
$count_result = mysqli_query($koneksi, $count_query);
$total_data = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_data / $limit);

// Query untuk mengambil data dengan limit dan offset
$query .= " ORDER BY tanggal_diterima DESC LIMIT $limit OFFSET $offset";
$result = mysqli_query($koneksi, $query);

if (!$result) {
    die("Query Error: " . mysqli_error($koneksi));
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Surat Masuk</h1>
</div>

<!-- Area Aksi dan Filter -->
<div class="row mb-4">
    <!-- Tombol Aksi -->
    <div class="col-md-6 mb-3">
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#suratMasukModal" id="btnTambah">
                <i class="fas fa-plus"></i> Tambah Data
            </button>
            <a href="../core/export_xlsx.php?jenis=surat_masuk&dari=<?php echo $dari_tanggal; ?>&sampai=<?php echo $sampai_tanggal; ?>&keyword=<?php echo urlencode($keyword); ?>" class="btn btn-success">
                <i class="fas fa-file-excel"></i> Ekspor XLSX
            </a>
            <a href="../core/export_zip.php?jenis=surat_masuk&dari=<?php echo $dari_tanggal; ?>&sampai=<?php echo $sampai_tanggal; ?>&keyword=<?php echo urlencode($keyword); ?>" class="btn btn-info text-white">
                <i class="fas fa-file-archive"></i> Unduh ZIP
            </a>
        </div>
    </div>
    <!-- Form Pencarian dan Filter -->
    <div class="col-md-6 mb-3">
        <form method="GET" action="surat_masuk.php">
            <div class="input-group">
                <input type="date" class="form-control" name="dari" value="<?php echo $dari_tanggal; ?>" title="Dari Tanggal">
                <input type="date" class="form-control" name="sampai" value="<?php echo $sampai_tanggal; ?>" title="Sampai Tanggal">
                <input type="text" class="form-control" name="keyword" placeholder="Cari..." value="<?php echo htmlspecialchars($keyword); ?>">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                <a href="surat_masuk.php" class="btn btn-secondary"><i class="fas fa-sync-alt"></i></a>
            </div>
        </form>
    </div>
</div>


<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-table"></i> Daftar Surat Masuk</span>
        <div>
            <form method="GET" action="surat_masuk.php" class="d-inline-block">
                <input type="hidden" name="dari" value="<?php echo $dari_tanggal; ?>">
                <input type="hidden" name="sampai" value="<?php echo $sampai_tanggal; ?>">
                <input type="hidden" name="keyword" value="<?php echo htmlspecialchars($keyword); ?>">
                <select name="limit" class="form-select form-select-sm d-inline-block" style="width: auto;" onchange="this.form.submit()">
                    <option value="10" <?php if ($limit == 10) echo 'selected'; ?>>10</option>
                    <option value="20" <?php if ($limit == 20) echo 'selected'; ?>>20</option>
                    <option value="100" <?php if ($limit == 100) echo 'selected'; ?>>100</option>
                </select>
            </form>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#suratMasukModal" id="btnTambah">
                <i class="fas fa-plus"></i> Tambah Data
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-sm">
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
                                    <?php if (!empty($row['nama_file_pdf'])) : ?>
                                        <a href="../uploads/surat_masuk/<?php echo htmlspecialchars($row['nama_file_pdf']); ?>" target="_blank" class="btn btn-outline-dark btn-sm">
                                            <i class="fas fa-eye"></i> Lihat
                                        </a>
                                    <?php else : ?>
                                        <span class="text-muted">No File</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="surat_masuk_edit.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="../core/surat_masuk_aksi.php" method="POST" style="display:inline-block;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
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

        <!-- Pagination -->
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <?php
                $query_params = http_build_query(array_filter(['limit' => $limit, 'dari' => $dari_tanggal, 'sampai' => $sampai_tanggal, 'keyword' => $keyword]));
                for ($i = 1; $i <= $total_pages; $i++) :
                ?>
                    <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                        <a class="page-link" href="surat_masuk.php?page=<?php echo $i; ?>&<?php echo $query_params; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>

        <div class="text-muted mt-3">
            Total data: <?php echo $total_data; ?>
        </div>
    </div>
</div>

<!-- Modal Tambah Surat Masuk -->
<div class="modal fade" id="suratMasukModal" tabindex="-1" aria-labelledby="suratMasukModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="suratMasukForm" action="../core/surat_masuk_aksi.php" method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="suratMasukModalLabel">Tambah Surat Masuk</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="action" value="add">

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
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Saat modal tambah data ditampilkan, reset form
        $('#suratMasukModal').on('shown.bs.modal', function() {
            $('#suratMasukForm')[0].reset();
        });
    });
</script>


<?php
require_once 'template_footer.php';
?>
