<?php
require_once 'template_header.php';
require_once '../config/koneksi.php';

// Logika Filter dan Pencarian
$dari_tanggal = $_GET['dari'] ?? '';
$sampai_tanggal = $_GET['sampai'] ?? '';
$keyword = $_GET['keyword'] ?? '';

// --- Logika Pengurutan ---
$sort_columns = ['no_berkas', 'nama_berkas', 'tanggal_berkas'];
$is_user_sort = isset($_GET['sort']) && in_array($_GET['sort'], $sort_columns);

if ($is_user_sort) {
    $sort_by = $_GET['sort'];
    $sort_dir = isset($_GET['dir']) && in_array(strtoupper($_GET['dir']), ['ASC', 'DESC']) ? strtoupper($_GET['dir']) : 'DESC';
    $order_by_clause = "ORDER BY $sort_by $sort_dir";
} else {
    $sort_by = 'tanggal_berkas';
    $sort_dir = 'DESC';
    $order_by_clause = "ORDER BY tanggal_berkas DESC";
}

// Fungsi bantuan untuk membuat link header tabel
function sortable_header($title, $column, $current_sort, $current_dir) {
    $dir = ($current_sort == $column && $current_dir == 'ASC') ? 'DESC' : 'ASC';

    if ($current_sort == $column) {
        $icon = $current_dir == 'ASC' ? ' <i class="fas fa-sort-up"></i>' : ' <i class="fas fa-sort-down"></i>';
    } else {
        $icon = ' <i class="fas fa-sort"></i>';
    }

    $query_params = $_GET;
    $query_params['sort'] = $column;
    $query_params['dir'] = $dir;

    return '<a href="?' . http_build_query($query_params) . '">' . htmlspecialchars($title) . $icon . '</a>';
}
// --- Akhir Logika Pengurutan ---

// Array untuk menyimpan parameter dan tipe data untuk bind_param
$params = [];
$types = '';

// Query dasar
$query = "SELECT * FROM arsip_berkas";
$where_clauses = [];

if (!empty($dari_tanggal) && !empty($sampai_tanggal)) {
    $where_clauses[] = "tanggal_berkas BETWEEN ? AND ?";
    $types .= 'ss';
    array_push($params, $dari_tanggal, $sampai_tanggal);
}

if (!empty($keyword)) {
    $where_clauses[] = "(no_berkas LIKE ? OR nama_berkas LIKE ?)";
    $types .= 'ss';
    $keyword_param = "%" . $keyword . "%";
    array_push($params, $keyword_param, $keyword_param);
}

if (count($where_clauses) > 0) {
    $query .= " WHERE " . implode(' AND ', $where_clauses);
}

// Logika Pagination
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
if (!in_array($limit, [10, 20, 100])) $limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// --- Query untuk menghitung total data ---
$count_query = "SELECT COUNT(*) as total FROM arsip_berkas";
if (count($where_clauses) > 0) {
    $count_query .= " WHERE " . implode(' AND ', $where_clauses);
}
$stmt_count = $koneksi->prepare($count_query);
if ($stmt_count && count($params) > 0) {
    $stmt_count->bind_param($types, ...$params);
}
$stmt_count->execute();
$total_data = $stmt_count->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_data / $limit);
$stmt_count->close();


// --- Query untuk mengambil data dengan limit dan offset ---
$query .= " $order_by_clause LIMIT ? OFFSET ?";
$types .= 'ii';
array_push($params, $limit, $offset);

$stmt = $koneksi->prepare($query);
if ($stmt && count($params) > 0) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    die("Query Error: " . $stmt->error);
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Arsip Berkas</h1>
</div>

<!-- Area Aksi dan Filter -->
<div class="row mb-4">
    <!-- Tombol Aksi -->
    <div class="col-lg-6 col-12 mb-3">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#arsipBerkasModal" id="btnTambah">
            <i class="fas fa-plus"></i> Tambah Data
        </button>
    </div>
    <!-- Form Pencarian -->
    <div class="col-lg-6 col-12 mb-3">
        <form method="GET" action="arsip_berkas.php" class="input-group">
            <input type="text" class="form-control" id="keyword" name="keyword" placeholder="Cari no berkas, nama berkas..." value="<?php echo htmlspecialchars($keyword); ?>">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i>
            </button>
            <a href="arsip_berkas.php" class="btn btn-secondary">
                <i class="fas fa-sync-alt"></i>
            </a>
        </form>
    </div>
</div>


<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-table"></i> Daftar Arsip Berkas</span>
        <div>
            <form method="GET" action="arsip_berkas.php" class="d-inline-block">
                <input type="hidden" name="dari" value="<?php echo $dari_tanggal; ?>">
                <input type="hidden" name="sampai" value="<?php echo $sampai_tanggal; ?>">
                <input type="hidden" name="keyword" value="<?php echo htmlspecialchars($keyword); ?>">
                <select name="limit" class="form-select form-select-sm d-inline-block" style="width: auto;" onchange="this.form.submit()">
                    <option value="10" <?php if ($limit == 10) echo 'selected'; ?>>10</option>
                    <option value="20" <?php if ($limit == 20) echo 'selected'; ?>>20</option>
                    <option value="100" <?php if ($limit == 100) echo 'selected'; ?>>100</option>
                </select>
            </form>
            <button type="button" class="btn btn-primary btn-sm d-sm-none d-md-inline-block" data-bs-toggle="modal" data-bs-target="#arsipBerkasModal" id="btnTambah">
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
                        <th><?php echo sortable_header('Nomor Berkas', 'no_berkas', $sort_by, $sort_dir); ?></th>
                        <th><?php echo sortable_header('Nama Berkas', 'nama_berkas', $sort_by, $sort_dir); ?></th>
                        <th><?php echo sortable_header('Tanggal Berkas', 'tanggal_berkas', $sort_by, $sort_dir); ?></th>
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
                                <td data-label="Nomor Berkas"><?php echo htmlspecialchars($row['no_berkas']); ?></td>
                                <td data-label="Nama Berkas"><?php echo htmlspecialchars($row['nama_berkas']); ?></td>
                                <td data-label="Tanggal Berkas"><?php echo date('d-m-Y', strtotime($row['tanggal_berkas'])); ?></td>
                                <td data-label="Berkas">
                                    <?php if (!empty($row['file_path'])) : ?>
                                        <a href="../uploads/berkas/<?php echo htmlspecialchars($row['file_path']); ?>" target="_blank" class="btn btn-outline-dark btn-sm">
                                            <i class="fas fa-eye"></i> Lihat
                                        </a>
                                    <?php else : ?>
                                        <span class="text-muted">No File</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Aksi">
                                    <a href="arsip_berkas_edit.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="../core/arsip_berkas_aksi.php" method="POST" style="display:inline-block;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
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
                            <td colspan="6" class="text-center">Tidak ada data yang ditemukan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination-container">
            <ul class="pagination-amazon">
                <?php
                $query_params_base = array_filter([
                    'limit' => $limit,
                    'dari' => $dari_tanggal,
                    'sampai' => $sampai_tanggal,
                    'keyword' => $keyword,
                    'sort' => $is_user_sort ? $sort_by : null,
                    'dir' => $is_user_sort ? $sort_dir : null
                ]);

                // Tombol Sebelumnya
                $prev_disabled = ($page <= 1) ? 'disabled' : '';
                $prev_page = ($page > 1) ? $page - 1 : 1;
                $prev_params = array_merge($query_params_base, ['page' => $prev_page]);
                echo "<li class='page-item $prev_disabled'><a class='page-link' href='arsip_berkas.php?" . http_build_query($prev_params) . "'><i class='fas fa-chevron-left'></i></a></li>";

                // Logika Halaman Terpotong
                $range = 2;
                for ($i = 1; $i <= $total_pages; $i++) {
                    if ($i == 1 || $i == $total_pages || ($i >= $page - $range && $i <= $page + $range)) {
                        $active = ($i == $page) ? 'active' : '';
                        $page_params = array_merge($query_params_base, ['page' => $i]);
                        echo "<li class='page-item $active'><a class='page-link' href='arsip_berkas.php?" . http_build_query($page_params) . "'>$i</a></li>";
                    } elseif ($i == $page - $range - 1 || $i == $page + $range + 1) {
                        echo "<li class='page-item disabled'><span class='page-link'>...</span></li>";
                    }
                }

                // Tombol Selanjutnya
                $next_disabled = ($page >= $total_pages) ? 'disabled' : '';
                $next_page = ($page < $total_pages) ? $page + 1 : $total_pages;
                $next_params = array_merge($query_params_base, ['page' => $next_page]);
                echo "<li class='page-item $next_disabled'><a class='page-link' href='arsip_berkas.php?" . http_build_query($next_params) . "'><i class='fas fa-chevron-right'></i></a></li>";
                ?>
            </ul>
        </div>

        <div class="text-muted mt-3">
            Total data: <?php echo $total_data; ?>
        </div>
    </div>
</div>

<!-- Modal Tambah Arsip Berkas -->
<div class="modal fade" id="arsipBerkasModal" tabindex="-1" aria-labelledby="arsipBerkasModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="arsipBerkasForm" action="../core/arsip_berkas_aksi.php" method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="arsipBerkasModalLabel">Tambah Arsip Berkas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="action" value="add">

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
                        <label for="uraian" class="form-label">Uraian</label>
                        <textarea class="form-control" id="uraian" name="uraian" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="nama_file_pdf" class="form-label">Unggah Berkas (PDF, max 5MB)</label>
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
        // Reset modal saat tombol 'Tambah Data' diklik
        $('#arsipBerkasModal').on('shown.bs.modal', function() {
            $('#arsipBerkasForm')[0].reset();
        });
    });
</script>

<?php
require_once 'template_footer.php';
?>
