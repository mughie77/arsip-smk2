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

<div class="mb-8">
    <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Arsip Berkas</h1>
    <p class="text-slate-500 mt-1 font-medium">Manajemen dokumen dan berkas penting dalam format digital yang aman.</p>
</div>

<!-- Area Aksi dan Filter -->
<div class="flex flex-col xl:flex-row gap-6 mb-8">
    <div class="flex flex-wrap gap-3">
        <button type="button" class="inline-flex items-center gap-2 bg-accent hover:bg-accent/90 text-white px-5 py-2.5 rounded-xl font-bold shadow-lg shadow-accent/30 transition-all active:scale-95" data-bs-toggle="modal" data-bs-target="#arsipBerkasModal" id="btnTambah">
            <i class="fas fa-plus"></i> Tambah Data
        </button>
    </div>

    <div class="flex-1">
        <form method="GET" action="arsip_berkas.php" class="bg-white p-2 rounded-2xl shadow-sm border border-slate-100 flex flex-wrap xl:flex-nowrap gap-2">
            <div class="relative flex-1 min-w-[200px]">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" class="w-full bg-slate-50 border-none focus:ring-2 focus:ring-accent rounded-xl pl-11 pr-4 py-2 text-sm font-medium text-slate-700" name="keyword" placeholder="Cari nomor atau nama berkas..." value="<?php echo htmlspecialchars($keyword); ?>">
            </div>
            <button type="submit" class="bg-primary text-white px-6 py-2 rounded-xl font-bold hover:bg-primary/90 transition-all">Filter</button>
            <a href="arsip_berkas.php" class="bg-slate-100 text-slate-600 px-4 py-2 rounded-xl font-bold hover:bg-slate-200 transition-all flex items-center justify-center"><i class="fas fa-sync-alt"></i></a>
        </form>
    </div>
</div>


<div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden">
    <div class="px-8 py-6 border-b border-slate-50 flex items-center justify-between bg-slate-50/50">
        <h3 class="font-bold text-slate-900 flex items-center gap-3">
            <div class="w-2 h-6 bg-purple-500 rounded-full"></div>
            Daftar Arsip Berkas Digital
        </h3>
        <div class="flex items-center gap-4">
            <form method="GET" action="arsip_berkas.php" class="flex items-center gap-3">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Tampilkan</span>
                <select name="limit" class="bg-white border-slate-200 rounded-lg text-xs font-bold text-slate-700 focus:ring-accent focus:border-accent" onchange="this.form.submit()">
                    <?php foreach([10, 20, 50, 100] as $l): ?>
                        <option value="<?php echo $l; ?>" <?php echo ($limit == $l) ? 'selected' : ''; ?>><?php echo $l; ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    </div>

    <div class="p-0">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 text-slate-400 text-[11px] uppercase tracking-[0.2em] font-black">
                        <th class="px-8 py-5 border-b border-slate-100">No</th>
                        <th class="px-6 py-5 border-b border-slate-100"><?php echo sortable_header('Nomor Berkas', 'no_berkas', $sort_by, $sort_dir); ?></th>
                        <th class="px-6 py-5 border-b border-slate-100"><?php echo sortable_header('Nama Berkas', 'nama_berkas', $sort_by, $sort_dir); ?></th>
                        <th class="px-6 py-5 border-b border-slate-100"><?php echo sortable_header('Tanggal', 'tanggal_berkas', $sort_by, $sort_dir); ?></th>
                        <th class="px-6 py-5 border-b border-slate-100">Berkas</th>
                        <th class="px-8 py-5 border-b border-slate-100 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if (mysqli_num_rows($result) > 0) : ?>
                        <?php $no = 1; ?>
                        <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                            <tr class="hover:bg-slate-50/80 transition-colors group text-sm md:text-base">
                                <td data-label="No" class="px-8 py-5 text-sm font-bold text-slate-400"><?php echo $no++; ?></td>
                                <td data-label="Nomor Berkas" class="px-6 py-5 text-sm font-bold text-slate-900"><?php echo htmlspecialchars($row['no_berkas']); ?></td>
                                <td data-label="Nama Berkas" class="px-6 py-5 text-sm font-semibold text-slate-700"><?php echo htmlspecialchars($row['nama_berkas']); ?></td>
                                <td data-label="Tanggal" class="px-6 py-5">
                                    <span class="inline-flex bg-purple-50 text-purple-600 px-3 py-1 rounded-full text-xs font-bold tracking-tight border border-purple-100">
                                        <?php echo date('d M Y', strtotime($row['tanggal_berkas'])); ?>
                                    </span>
                                </td>
                                <td data-label="Berkas" class="px-6 py-5 text-center md:text-left">
                                    <?php if (!empty($row['file_path'])) : ?>
                                        <a href="../uploads/berkas/<?php echo htmlspecialchars($row['file_path']); ?>" target="_blank" class="inline-flex items-center gap-2 text-accent hover:text-accent/80 font-bold text-xs transition-colors">
                                            <i class="fas fa-file-alt text-lg"></i> LIHAT
                                        </a>
                                    <?php else : ?>
                                        <span class="text-[10px] font-black text-slate-300 italic uppercase">TIDAK ADA</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Aksi" class="px-8 py-5 text-right">
                                    <div class="flex items-center justify-end gap-2 transition-opacity">
                                        <a href="arsip_berkas_edit.php?id=<?php echo $row['id']; ?>" class="w-10 h-10 flex items-center justify-center rounded-xl bg-amber-50 text-amber-500 hover:bg-amber-500 hover:text-white transition-all shadow-md">
                                            <i class="fas fa-edit text-base"></i>
                                        </a>
                                        <form action="../core/arsip_berkas_aksi.php" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" class="w-10 h-10 flex items-center justify-center rounded-xl bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition-all shadow-md">
                                                <i class="fas fa-trash text-base"></i>
                                            </button>
                                        </form>
                                    </div>
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

    </div>

    <!-- Footer Table -->
    <div class="px-8 py-6 bg-slate-50/30 border-t border-slate-50 flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="text-sm font-bold text-slate-400">
            MENAMPILKAN <span class="text-slate-900"><?php echo min($total_data, $offset + 1); ?> - <?php echo min($total_data, $offset + $limit); ?></span> DARI <span class="text-slate-900"><?php echo $total_data; ?></span> DATA
        </div>

        <!-- Pagination -->
        <nav class="flex items-center gap-2">
            <?php
            $query_params_base = array_filter([
                'limit' => $limit,
                'dari' => $dari_tanggal,
                'sampai' => $sampai_tanggal,
                'keyword' => $keyword,
                'sort' => $is_user_sort ? $sort_by : null,
                'dir' => $is_user_sort ? $sort_dir : null
            ]);

            // Previous button
            $prev_disabled = ($page <= 1);
            $prev_page = ($page > 1) ? $page - 1 : 1;
            $prev_params = array_merge($query_params_base, ['page' => $prev_page]);
            $prev_link = $prev_disabled ? '#' : 'arsip_berkas.php?' . http_build_query($prev_params);
            ?>
            <a href="<?php echo $prev_link; ?>" class="w-10 h-10 flex items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 transition-all <?php echo $prev_disabled ? 'opacity-30 cursor-not-allowed' : ''; ?>">
                <i class="fas fa-chevron-left text-xs"></i>
            </a>

            <?php
            $range = 2;
            for ($i = 1; $i <= $total_pages; $i++) {
                if ($i == 1 || $i == $total_pages || ($i >= $page - $range && $i <= $page + $range)) {
                    $active = ($i == $page);
                    $page_params = array_merge($query_params_base, ['page' => $i]);
                    $activeClass = $active ? 'bg-accent text-white border-accent shadow-lg shadow-accent/30' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50';
                    echo "<a href='arsip_berkas.php?" . http_build_query($page_params) . "' class='w-10 h-10 flex items-center justify-center rounded-xl border font-bold text-sm transition-all $activeClass'>$i</a>";
                } elseif ($i == $page - $range - 1 || $i == $page + $range + 1) {
                    echo "<span class='w-10 h-10 flex items-center justify-center text-slate-400 font-bold text-sm'>...</span>";
                }
            }

            // Next button
            $next_disabled = ($page >= $total_pages);
            $next_page = ($page < $total_pages) ? $page + 1 : $total_pages;
            $next_params = array_merge($query_params_base, ['page' => $next_page]);
            $next_link = $next_disabled ? '#' : 'arsip_berkas.php?' . http_build_query($next_params);
            ?>
            <a href="<?php echo $next_link; ?>" class="w-10 h-10 flex items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 transition-all <?php echo $next_disabled ? 'opacity-30 cursor-not-allowed' : ''; ?>">
                <i class="fas fa-chevron-right text-xs"></i>
            </a>
        </nav>
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
