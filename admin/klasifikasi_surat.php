<?php
require_once 'template_header.php';
require_once '../config/koneksi.php';

// Pagination and Search Logic
// Logika Pagination
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
if (!in_array($limit, [10, 20, 100])) {
    $limit = 10; // Nilai default jika input tidak valid
}
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$keyword = $_GET['keyword'] ?? '';

// --- Logika Pengurutan ---
$sort_columns = ['kode', 'jenis_surat'];
$is_user_sort = isset($_GET['sort']) && in_array($_GET['sort'], $sort_columns);

if ($is_user_sort) {
    $sort_by = $_GET['sort'];
    $sort_dir = isset($_GET['dir']) && in_array(strtoupper($_GET['dir']), ['ASC', 'DESC']) ? strtoupper($_GET['dir']) : 'ASC';
    $order_by_clause = "ORDER BY $sort_by $sort_dir";
} else {
    $sort_by = 'kode';
    $sort_dir = 'ASC';
    $order_by_clause = "ORDER BY kode ASC";
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

$where_clauses = [];
if (!empty($keyword)) {
    $where_clauses[] = "(kode LIKE ? OR jenis_surat LIKE ?)";
    $types .= 'ss';
    $keyword_param = "%" . $keyword . "%";
    array_push($params, $keyword_param, $keyword_param);
}

// --- Query untuk menghitung total data ---
$count_query = "SELECT COUNT(*) as total FROM klasifikasi_surat";
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
$query = "SELECT * FROM klasifikasi_surat";
if (count($where_clauses) > 0) {
    $query .= " WHERE " . implode(' AND ', $where_clauses);
}
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
    <h1 class="h2">Manajemen Klasifikasi Surat</h1>
</div>

<?php
if (isset($_SESSION['success_message'])) {
    echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
    unset($_SESSION['error_message']);
}
?>

<div class="row">
    <div class="col-lg-8 col-12 mb-3">
        <div class="card mb-4">
            <div class="card-header"><i class="fas fa-search"></i> Pencarian</div>
            <div class="card-body">
                <form method="GET" action="klasifikasi_surat.php">
                    <div class="input-group">
                        <input type="text" class="form-control" id="keyword" name="keyword" placeholder="Cari berdasarkan Kode atau Jenis Surat..." value="<?php echo htmlspecialchars($keyword); ?>">
                        <button type="submit" class="btn btn-primary">Cari</button>
                        <a href="klasifikasi_surat.php" class="btn btn-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-12 mb-3">
        <div class="card mb-4">
            <div class="card-header"><i class="fas fa-plus-circle"></i> Aksi</div>
            <div class="card-body">
                <div class="d-grid">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#klasifikasiModal" id="btnTambah">
                        <i class="fas fa-plus"></i> Tambah Data Klasifikasi
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-list"></i> Daftar Klasifikasi (Total: <?php echo $total_data; ?>)</span>
        <div>
            <form method="GET" action="klasifikasi_surat.php" class="d-inline-block">
                <input type="hidden" name="keyword" value="<?php echo htmlspecialchars($keyword); ?>">
                <select name="limit" class="form-select form-select-sm d-inline-block" style="width: auto;" onchange="this.form.submit()">
                    <option value="10" <?php if ($limit == 10) echo 'selected'; ?>>10</option>
                    <option value="20" <?php if ($limit == 20) echo 'selected'; ?>>20</option>
                    <option value="100" <?php if ($limit == 100) echo 'selected'; ?>>100</option>
                </select>
            </form>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-sm">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th><?php echo sortable_header('Kode Klasifikasi', 'kode', $sort_by, $sort_dir); ?></th>
                        <th><?php echo sortable_header('Jenis Surat', 'jenis_surat', $sort_by, $sort_dir); ?></th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0) : ?>
                        <?php $no = $offset + 1; ?>
                        <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                            <tr>
                                <td data-label="No"><?php echo $no++; ?></td>
                                <td data-label="Kode Klasifikasi"><?php echo htmlspecialchars($row['kode']); ?></td>
                                <td data-label="Jenis Surat"><?php echo htmlspecialchars($row['jenis_surat']); ?></td>
                                <td data-label="Aksi">
                                    <a href="klasifikasi_surat_edit.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="../core/klasifikasi_aksi.php" method="POST" style="display:inline-block;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
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
                            <td colspan="4" class="text-center">Tidak ada data klasifikasi.</td>
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
                    'keyword' => $keyword,
                    'sort' => $is_user_sort ? $sort_by : null,
                    'dir' => $is_user_sort ? $sort_dir : null
                ]);

                // Tombol Sebelumnya
                $prev_disabled = ($page <= 1) ? 'disabled' : '';
                $prev_page = ($page > 1) ? $page - 1 : 1;
                $prev_params = array_merge($query_params_base, ['page' => $prev_page]);
                echo "<li class='page-item $prev_disabled'><a class='page-link' href='klasifikasi_surat.php?" . http_build_query($prev_params) . "'><i class='fas fa-chevron-left'></i> Sebelumnya</a></li>";

                // Logika Halaman Terpotong
                $range = 2;
                for ($i = 1; $i <= $total_pages; $i++) {
                    if ($i == 1 || $i == $total_pages || ($i >= $page - $range && $i <= $page + $range)) {
                        $active = ($i == $page) ? 'active' : '';
                        $page_params = array_merge($query_params_base, ['page' => $i]);
                        echo "<li class='page-item $active'><a class='page-link' href='klasifikasi_surat.php?" . http_build_query($page_params) . "'>$i</a></li>";
                    } elseif ($i == $page - $range - 1 || $i == $page + $range + 1) {
                        echo "<li class='page-item disabled'><span class='page-link'>...</span></li>";
                    }
                }

                // Tombol Selanjutnya
                $next_disabled = ($page >= $total_pages) ? 'disabled' : '';
                $next_page = ($page < $total_pages) ? $page + 1 : $total_pages;
                $next_params = array_merge($query_params_base, ['page' => $next_page]);
                echo "<li class='page-item $next_disabled'><a class='page-link' href='klasifikasi_surat.php?" . http_build_query($next_params) . "'>Selanjutnya <i class='fas fa-chevron-right'></i></a></li>";
                ?>
            </ul>
        </div>

        <div class="text-muted mt-3">
            Total data: <?php echo $total_data; ?>
        </div>
    </div>
</div>

<!-- Modal Tambah Klasifikasi -->
<div class="modal fade" id="klasifikasiModal" tabindex="-1" aria-labelledby="klasifikasiModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="klasifikasiForm" action="../core/klasifikasi_aksi.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="klasifikasiModalLabel">Tambah Klasifikasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="action" value="add">

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
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Reset form saat modal tambah dibuka
        $('#klasifikasiModal').on('shown.bs.modal', function() {
            $('#klasifikasiForm')[0].reset();
        });
    });
</script>

<?php
require_once 'template_footer.php';
?>