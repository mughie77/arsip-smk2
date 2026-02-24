<?php
require_once 'template_header.php';
require_once '../config/koneksi.php';

// Logika Filter dan Pencarian
$dari_tanggal = isset($_GET['dari']) ? $_GET['dari'] : '';
$sampai_tanggal = isset($_GET['sampai']) ? $_GET['sampai'] : '';
$keyword = isset($_GET['keyword']) ? mysqli_real_escape_string($koneksi, $_GET['keyword']) : '';

$query = "SELECT * FROM notulen";
$where_clauses = [];

if (!empty($dari_tanggal) && !empty($sampai_tanggal)) {
    $where_clauses[] = "tanggal BETWEEN '$dari_tanggal' AND '$sampai_tanggal'";
}

if (!empty($keyword)) {
    $where_clauses[] = "kegiatan LIKE '%$keyword%'";
}

if (count($where_clauses) > 0) {
    $query .= " WHERE " . implode(' AND ', $where_clauses);
}

// Logika Pagination
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
if (!in_array($limit, [10, 20, 30, 40, 50])) {
    $limit = 10; // Nilai default jika input tidak valid
}
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Query untuk menghitung total data
$count_query = "SELECT COUNT(*) as total FROM notulen";
if (count($where_clauses) > 0) {
    $count_query .= " WHERE " . implode(' AND ', $where_clauses);
}
$count_result = mysqli_query($koneksi, $count_query);
$total_data = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_data / $limit);

// Query untuk mengambil data dengan limit dan offset
$query .= " ORDER BY tanggal DESC LIMIT $limit OFFSET $offset";
$result = mysqli_query($koneksi, $query);

if (!$result) {
    die("Query Error: " . mysqli_error($koneksi));
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Daftar Notulen</h1>
</div>

<!-- Area Aksi dan Filter -->
<div class="row mb-4">
    <!-- Tombol Aksi -->
    <div class="col-lg-6 col-12 mb-3">
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#notulenModal" id="btnTambah">
                <i class="fas fa-plus"></i> Tambah Data
            </button>
            <a href="../core/export_xlsx.php?jenis=notulen&dari=<?php echo $dari_tanggal; ?>&sampai=<?php echo $sampai_tanggal; ?>&keyword=<?php echo urlencode($keyword); ?>" class="btn btn-success">
                <i class="fas fa-file-excel"></i> Ekspor XLSX
            </a>
            <a href="../core/export_zip.php?jenis=notulen&dari=<?php echo $dari_tanggal; ?>&sampai=<?php echo $sampai_tanggal; ?>&keyword=<?php echo urlencode($keyword); ?>" class="btn btn-info text-white">
                <i class="fas fa-file-archive"></i> Unduh ZIP
            </a>
        </div>
    </div>
    <!-- Form Pencarian -->
    <div class="col-lg-6 col-12 mb-3">
        <form method="GET" action="notulen.php" class="input-group">
            <input type="text" class="form-control" id="keyword" name="keyword" placeholder="Cari nama kegiatan..." value="<?php echo htmlspecialchars($keyword); ?>">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i>
            </button>
            <a href="notulen.php" class="btn btn-secondary">
                <i class="fas fa-sync-alt"></i>
            </a>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-table"></i> Daftar Notulen Rapat/Kegiatan</span>
        <button type="button" class="btn btn-primary btn-sm d-sm-none d-md-inline-block" data-bs-toggle="modal" data-bs-target="#notulenModal" id="btnTambah">
            <i class="fas fa-plus"></i> Tambah Data
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-sm">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Kegiatan</th>
                        <th>Berkas</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0) : ?>
                        <?php $no = 1; ?>
                        <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                            <tr>
                                <td data-label="No"><?php echo $no++; ?></td>
                                <td data-label="Tanggal"><?php echo date('d-m-Y', strtotime($row['tanggal'])); ?></td>
                                <td data-label="Kegiatan"><?php echo htmlspecialchars($row['kegiatan']); ?></td>
                                <td data-label="Berkas">
                                    <?php if (!empty($row['nama_file'])) : ?>
                                        <a href="../uploads/notulen/<?php echo htmlspecialchars($row['nama_file']); ?>" target="_blank" class="btn btn-outline-dark btn-sm">
                                            <i class="fas fa-eye"></i> Lihat
                                        </a>
                                    <?php else : ?>
                                        <span class="text-muted">No File</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Aksi">
                                    <a href="notulen_edit.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="../core/notulen_aksi.php" method="POST" style="display:inline-block;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
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
                            <td colspan="5" class="text-center">Tidak ada data yang ditemukan.</td>
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

                // Tombol Sebelumnya
                $prev_disabled = ($page <= 1) ? 'disabled' : '';
                echo "<li class='page-item $prev_disabled'><a class='page-link' href='notulen.php?page=" . ($page - 1) . "&$query_params'><i class='fas fa-chevron-left'></i></a></li>";

                // Angka Halaman
                $adjacents = 1;
                if ($total_pages <= 7) {
                    for ($i = 1; $i <= $total_pages; $i++) {
                        $active = ($i == $page) ? 'active' : '';
                        echo "<li class='page-item $active'><a class='page-link' href='notulen.php?page=$i&$query_params'>$i</a></li>";
                    }
                } else {
                    if ($page <= 4) {
                        for ($i = 1; $i <= 5; $i++) {
                            $active = ($i == $page) ? 'active' : '';
                            echo "<li class='page-item $active'><a class='page-link' href='notulen.php?page=$i&$query_params'>$i</a></li>";
                        }
                        echo "<li class='page-item disabled'><span class='page-link'>...</span></li>";
                        echo "<li class='page-item'><a class='page-link' href='notulen.php?page=$total_pages&$query_params'>$total_pages</a></li>";
                    } elseif ($page > 4 && $page < $total_pages - 3) {
                        echo "<li class='page-item'><a class='page-link' href='notulen.php?page=1&$query_params'>1</a></li>";
                        echo "<li class='page-item disabled'><span class='page-link'>...</span></li>";
                        for ($i = $page - $adjacents; $i <= $page + $adjacents; $i++) {
                            $active = ($i == $page) ? 'active' : '';
                            echo "<li class='page-item $active'><a class='page-link' href='notulen.php?page=$i&$query_params'>$i</a></li>";
                        }
                        echo "<li class='page-item disabled'><span class='page-link'>...</span></li>";
                        echo "<li class='page-item'><a class='page-link' href='notulen.php?page=$total_pages&$query_params'>$total_pages</a></li>";
                    } else {
                        echo "<li class='page-item'><a class='page-link' href='notulen.php?page=1&$query_params'>1</a></li>";
                        echo "<li class='page-item disabled'><span class='page-link'>...</span></li>";
                        for ($i = $total_pages - 4; $i <= $total_pages; $i++) {
                            $active = ($i == $page) ? 'active' : '';
                            echo "<li class='page-item $active'><a class='page-link' href='notulen.php?page=$i&$query_params'>$i</a></li>";
                        }
                    }
                }

                // Tombol Berikutnya
                $next_disabled = ($page >= $total_pages) ? 'disabled' : '';
                echo "<li class='page-item $next_disabled'><a class='page-link' href='notulen.php?page=" . ($page + 1) . "&$query_params'><i class='fas fa-chevron-right'></i></a></li>";
                ?>
            </ul>
        </nav>

        <div class="pagination-info-container">
            <div>
                <?php
                $start_data = ($total_data > 0) ? ($offset + 1) : 0;
                $end_data = min($page * $limit, $total_data);
                echo "Menampilkan $start_data - $end_data dari $total_data";
                ?>
            </div>
            <div>
                <form method="GET" action="notulen.php" class="d-inline-block">
                    <input type="hidden" name="dari" value="<?php echo $dari_tanggal; ?>">
                    <input type="hidden" name="sampai" value="<?php echo $sampai_tanggal; ?>">
                    <input type="hidden" name="keyword" value="<?php echo htmlspecialchars($keyword); ?>">
                    <select name="limit" class="pagination-limit-select" onchange="this.form.submit()">
                        <?php foreach ([10, 20, 30, 40, 50] as $opt) : ?>
                            <option value="<?php echo $opt; ?>" <?php if ($limit == $opt) echo 'selected'; ?>><?php echo $opt; ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Notulen -->
<div class="modal fade" id="notulenModal" tabindex="-1" aria-labelledby="notulenModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="notulenForm" action="../core/notulen_aksi.php" method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="notulenModalLabel">Tambah Notulen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="action" value="add">

                    <div class="mb-3">
                        <label for="kegiatan" class="form-label">Nama Kegiatan</label>
                        <input type="text" class="form-control" id="kegiatan" name="kegiatan" required>
                    </div>
                    <div class="mb-3">
                        <label for="tanggal" class="form-label">Tanggal Kegiatan</label>
                        <input type="date" class="form-control" id="tanggal" name="tanggal" required>
                    </div>
                    <div class="mb-3">
                        <label for="nama_file" class="form-label">Unggah Berkas (PDF, DOC, DOCX)</label>
                        <input class="form-control" type="file" id="nama_file" name="nama_file" accept=".pdf,.doc,.docx">
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
        // Reset form saat modal tambah dibuka
        $('#notulenModal').on('shown.bs.modal', function() {
            $('#notulenForm')[0].reset();
        });
    });
</script>

<?php
require_once 'template_footer.php';
?>
