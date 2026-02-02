<?php
session_start();
require_once 'template_header.php';
require_once '../config/koneksi.php';

// Logika Filter dan Pencarian
$dari_tanggal = $_GET['dari'] ?? '';
$sampai_tanggal = $_GET['sampai'] ?? '';
$keyword = $_GET['keyword'] ?? '';
$klasifikasi_id = isset($_GET['klasifikasi_id']) ? (int)$_GET['klasifikasi_id'] : 0;

// --- Logika Pengurutan ---
$sort_columns = ['kode_arsip', 'nomor_surat', 'tujuan_surat', 'perihal', 'tanggal_kirim'];
$is_user_sort = isset($_GET['sort']) && in_array($_GET['sort'], $sort_columns);

if ($is_user_sort) {
    $sort_by = $_GET['sort'];
    $sort_dir = isset($_GET['dir']) && in_array(strtoupper($_GET['dir']), ['ASC', 'DESC']) ? strtoupper($_GET['dir']) : 'DESC';
    $order_by_clause = "ORDER BY $sort_by $sort_dir";
} else {
    // Urutan default
    $sort_by = 'nomor_surat'; // Atur untuk header agar ikon ditampilkan dengan benar saat default
    $sort_dir = 'DESC';
    $order_by_clause = "ORDER BY sk.nomor_surat DESC";
}

// Fungsi bantuan untuk membuat link header tabel
function sortable_header($title, $column, $current_sort, $current_dir) {
    $dir = ($current_sort == $column && $current_dir == 'ASC') ? 'DESC' : 'ASC';
    $icon = '';
    if ($current_sort == $column) {
        $icon = $current_dir == 'ASC' ? ' <i class="fas fa-sort-up"></i>' : ' <i class="fas fa-sort-down"></i>';
    }

    // Pertahankan parameter query yang ada
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
$query = "SELECT sk.*, ks.kode as kode_klasifikasi, ks.jenis_surat
          FROM surat_keluar sk
          LEFT JOIN klasifikasi_surat ks ON sk.klasifikasi_id = ks.id";
$where_clauses = [];

if (!empty($dari_tanggal) && !empty($sampai_tanggal)) {
    $where_clauses[] = "sk.tanggal_kirim BETWEEN ? AND ?";
    $types .= 'ss';
    array_push($params, $dari_tanggal, $sampai_tanggal);
}

if (!empty($keyword)) {
    $where_clauses[] = "(sk.kode_arsip LIKE ? OR sk.nomor_surat LIKE ? OR sk.perihal LIKE ? OR sk.tujuan_surat LIKE ?)";
    $types .= 'ssss';
    $keyword_param = "%" . $keyword . "%";
    array_push($params, $keyword_param, $keyword_param, $keyword_param, $keyword_param);
}

if (!empty($klasifikasi_id)) {
    $where_clauses[] = "sk.klasifikasi_id = ?";
    $types .= 'i';
    array_push($params, $klasifikasi_id);
}

if (count($where_clauses) > 0) {
    $query .= " WHERE " . implode(' AND ', $where_clauses);
}

// Logika Pagination
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
if (!in_array($limit, [10, 20, 30, 40, 50])) $limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// --- Query untuk menghitung total data ---
$count_query = "SELECT COUNT(*) as total
                FROM surat_keluar sk
                LEFT JOIN klasifikasi_surat ks ON sk.klasifikasi_id = ks.id";
if (count($where_clauses) > 0) {
    $count_query .= " WHERE " . implode(' AND ', $where_clauses);
}

$stmt_count = $koneksi->prepare($count_query);
if ($stmt_count && !empty($types)) {
    // Create a temporary array for count parameters, excluding limit and offset
    $count_params = array_slice($params, 0, count($params));
    $count_types = substr($types, 0, strlen($types));
    $stmt_count->bind_param($count_types, ...$count_params);
}

if ($stmt_count) {
    $stmt_count->execute();
    $total_data = $stmt_count->get_result()->fetch_assoc()['total'];
    $stmt_count->close();
} else {
    $total_data = 0;
}
$total_pages = ceil($total_data / $limit);


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
    <h1 class="h2">Surat Keluar</h1>
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

<!-- Area Aksi dan Filter -->
<div class="row mb-4">
    <!-- Tombol Aksi -->
    <div class="col-lg-6 col-12 mb-3">
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#suratKeluarModal" id="btnTambah">
                <i class="fas fa-plus"></i> Tambah Data
            </button>
            <a href="../core/export_xlsx.php?jenis=surat_keluar&dari=<?php echo $dari_tanggal; ?>&sampai=<?php echo $sampai_tanggal; ?>&keyword=<?php echo urlencode($keyword); ?>" class="btn btn-success">
                <i class="fas fa-file-excel"></i> Ekspor XLSX
            </a>
            <a href="../core/export_zip.php?jenis=surat_keluar&dari=<?php echo $dari_tanggal; ?>&sampai=<?php echo $sampai_tanggal; ?>&keyword=<?php echo urlencode($keyword); ?>" class="btn btn-info text-white">
                <i class="fas fa-file-archive"></i> Unduh ZIP
            </a>
        </div>
    </div>
    <!-- Form Pencarian dan Filter -->
    <div class="col-lg-6 col-12 mb-3">
        <form method="GET" action="surat_keluar.php">
            <div class="input-group">
                <input type="date" class="form-control" name="dari" value="<?php echo $dari_tanggal; ?>" title="Dari Tanggal">
                <input type="date" class="form-control" name="sampai" value="<?php echo $sampai_tanggal; ?>" title="Sampai Tanggal">
                <select name="klasifikasi_id" id="klasifikasi_filter" class="form-select">
                    <option value="">Semua Klasifikasi</option>
                    <?php
                    $q_klasifikasi = mysqli_query($koneksi, "SELECT * FROM klasifikasi_surat ORDER BY jenis_surat ASC");
                    while ($klas = mysqli_fetch_assoc($q_klasifikasi)) {
                        $selected = ($klasifikasi_id == $klas['id']) ? 'selected' : '';
                        echo "<option value='{$klas['id']}' {$selected}>{$klas['kode']} - {$klas['jenis_surat']}</option>";
                    }
                    ?>
                </select>
                <input type="text" class="form-control" name="keyword" placeholder="Cari..." value="<?php echo htmlspecialchars($keyword); ?>">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                <a href="surat_keluar.php" class="btn btn-secondary"><i class="fas fa-sync-alt"></i></a>
            </div>
        </form>
    </div>
</div>


<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-table"></i> Daftar Surat Keluar</span>
        <div>
            <form method="GET" action="surat_keluar.php" class="d-inline-block">
                <input type="hidden" name="dari" value="<?php echo $dari_tanggal; ?>">
                <input type="hidden" name="sampai" value="<?php echo $sampai_tanggal; ?>">
                <input type="hidden" name="keyword" value="<?php echo htmlspecialchars($keyword); ?>">
                <select name="limit" class="form-select form-select-sm d-inline-block" style="width: auto;" onchange="this.form.submit()">
                    <option value="10" <?php if ($limit == 10) echo 'selected'; ?>>10</option>
                    <option value="20" <?php if ($limit == 20) echo 'selected'; ?>>20</option>
                    <option value="30" <?php if ($limit == 30) echo 'selected'; ?>>30</option>
                    <option value="40" <?php if ($limit == 40) echo 'selected'; ?>>40</option>
                    <option value="50" <?php if ($limit == 50) echo 'selected'; ?>>50</option>
                </select>
            </form>
            <button type="button" class="btn btn-primary btn-sm d-sm-none d-md-inline-block" data-bs-toggle="modal" data-bs-target="#suratKeluarModal" id="btnTambah">
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
                        <th><?php echo sortable_header('Kode Arsip', 'kode_arsip', $sort_by, $sort_dir); ?></th>
                        <th><?php echo sortable_header('Nomor Surat', 'nomor_surat', $sort_by, $sort_dir); ?></th>
                        <th><?php echo sortable_header('Tujuan', 'tujuan_surat', $sort_by, $sort_dir); ?></th>
                        <th><?php echo sortable_header('Perihal', 'perihal', $sort_by, $sort_dir); ?></th>
                        <th><?php echo sortable_header('Tgl. Kirim', 'tanggal_kirim', $sort_by, $sort_dir); ?></th>
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
                                <td data-label="Kode Arsip" class="fw-bold"><?php echo htmlspecialchars($row['kode_arsip']); ?></td>
                                <td data-label="Nomor Surat">
                                    <?php echo htmlspecialchars($row['nomor_surat']); ?>
                                    <br>
                                    <small class="text-muted"><?php echo htmlspecialchars($row['kode_klasifikasi']); ?> - <?php echo htmlspecialchars($row['jenis_surat']); ?></small>
                                </td>
                                <td data-label="Tujuan"><?php echo htmlspecialchars($row['tujuan_surat']); ?></td>
                                <td data-label="Perihal"><?php echo htmlspecialchars($row['perihal']); ?></td>
                                <td data-label="Tgl. Kirim"><?php echo date('d-m-Y', strtotime($row['tanggal_kirim'])); ?></td>
                                <td data-label="Berkas">
                                    <?php if (!empty($row['nama_file_pdf'])) : ?>
                                        <a href="../uploads/surat_keluar/<?php echo htmlspecialchars($row['nama_file_pdf']); ?>" target="_blank" class="btn btn-outline-dark btn-sm">
                                            <i class="fas fa-eye"></i> Lihat
                                        </a>
                                    <?php else : ?>
                                        <span class="text-muted">No File</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Aksi">
                                    <a href="surat_keluar_edit.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="../core/surat_keluar_aksi.php" method="POST" style="display:inline-block;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
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
                            <td colspan="8" class="text-center">Tidak ada data yang ditemukan.</td>
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
                        <a class="page-link" href="surat_keluar.php?page=<?php echo $i; ?>&<?php echo $query_params; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>

        <div class="text-muted mt-3">
            Total data: <?php echo $total_data; ?>
        </div>
    </div>
</div>

<!-- Modal Tambah Surat Keluar -->
<div class="modal fade" id="suratKeluarModal" tabindex="-1" aria-labelledby="suratKeluarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="suratKeluarForm" action="../core/surat_keluar_aksi.php" method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="suratKeluarModalLabel">Tambah Surat Keluar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="action" value="add">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="klasifikasi_id_add" class="form-label">Klasifikasi Surat</label>
                            <select class="form-select" id="klasifikasi_id_add" name="klasifikasi_id" required>
                                <option value="">-- Pilih Klasifikasi --</option>
                                <?php
                                $q_klasifikasi = mysqli_query($koneksi, "SELECT * FROM klasifikasi_surat ORDER BY jenis_surat ASC");
                                while ($klas = mysqli_fetch_assoc($q_klasifikasi)) {
                                    echo "<option value='{$klas['id']}'>{$klas['kode']} - {$klas['jenis_surat']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="nomor_surat" class="form-label">Nomor Surat</label>
                            <input type="text" class="form-control" id="nomor_surat" name="nomor_surat" required>
                            <small class="form-text text-muted">Contoh: 001/A/UNDANGAN/X/2024</small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tujuan_surat" class="form-label">Tujuan Surat</label>
                            <input type="text" class="form-control" id="tujuan_surat" name="tujuan_surat" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="perihal" class="form-label">Perihal</label>
                        <textarea class="form-control" id="perihal" name="perihal" rows="2" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tanggal_kirim" class="form-label">Tanggal Kirim</label>
                            <input type="date" class="form-control" id="tanggal_kirim" name="tanggal_kirim" required>
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
        // Initialize Select2 for the filter dropdown
        $('#klasifikasi_filter').select2({
            theme: 'bootstrap-5',
            width: '100%' // Ensure it fits well in the input group
        });

        // Event listener for when the modal is shown
        $('#suratKeluarModal').on('shown.bs.modal', function(e) {
            // Initialize Select2 on the dropdown inside the modal
            if ($('#klasifikasi_id_add').data('select2')) {
                $('#klasifikasi_id_add').select2('destroy');
            }
            $('#klasifikasi_id_add').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#suratKeluarModal')
            });

            // Reset form
            $('#suratKeluarForm')[0].reset();
            $('#klasifikasi_id_add').val(null).trigger('change');
        });
    });
</script>

<?php
require_once 'template_footer.php';
?>
