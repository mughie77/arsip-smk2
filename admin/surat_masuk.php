<?php
require_once 'template_header.php';
require_once '../config/koneksi.php';

// Logika Filter dan Pencarian
$dari_tanggal = $_GET['dari'] ?? '';
$sampai_tanggal = $_GET['sampai'] ?? '';
$keyword = $_GET['keyword'] ?? '';

// --- Logika Pengurutan ---
$sort_columns = ['nomor_arsip', 'nomor_surat', 'perihal', 'asal_surat', 'tanggal_diterima', 'acc_kepada'];
$is_user_sort = isset($_GET['sort']) && in_array($_GET['sort'], $sort_columns);

if ($is_user_sort) {
    $sort_by = $_GET['sort'];
    $sort_dir = isset($_GET['dir']) && in_array(strtoupper($_GET['dir']), ['ASC', 'DESC']) ? strtoupper($_GET['dir']) : 'DESC';
    $order_by_clause = "ORDER BY $sort_by $sort_dir";
} else {
    // Urutan default
    $sort_by = 'tanggal_diterima';
    $sort_dir = 'DESC';
    $order_by_clause = "ORDER BY tanggal_diterima DESC";
}

// --- Akhir Logika Pengurutan ---

// Array untuk menyimpan parameter dan tipe data untuk bind_param
$params = [];
$types = '';

// Query dasar
$query = "SELECT * FROM surat_masuk";
$where_clauses = [];

if (!empty($dari_tanggal) && !empty($sampai_tanggal)) {
    $where_clauses[] = "tanggal_diterima BETWEEN ? AND ?";
    $types .= 'ss';
    array_push($params, $dari_tanggal, $sampai_tanggal);
}

if (!empty($keyword)) {
    $where_clauses[] = "(nomor_arsip LIKE ? OR nomor_surat LIKE ? OR perihal LIKE ? OR asal_surat LIKE ?)";
    $types .= 'ssss';
    $keyword_param = "%" . $keyword . "%";
    array_push($params, $keyword_param, $keyword_param, $keyword_param, $keyword_param);
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
$count_query = "SELECT COUNT(*) as total FROM surat_masuk";
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
    <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Surat Masuk</h1>
    <p class="text-slate-500 mt-1 font-medium">Kelola dan arsipkan semua surat masuk institusi Anda.</p>
</div>

<!-- Area Aksi dan Filter -->
<div class="flex flex-col xl:flex-row gap-6 mb-8">
    <!-- Tombol Aksi -->
    <div class="flex flex-wrap gap-3">
        <button type="button" class="inline-flex items-center gap-2 bg-accent hover:bg-accent/90 text-white px-5 py-2.5 rounded-xl font-bold shadow-lg shadow-accent/30 transition-all active:scale-95" data-bs-toggle="modal" data-bs-target="#suratMasukModal" id="btnTambah">
            <i class="fas fa-plus"></i> Tambah Data
        </button>
        <a href="../core/export_xlsx.php?jenis=surat_masuk&dari=<?php echo $dari_tanggal; ?>&sampai=<?php echo $sampai_tanggal; ?>&keyword=<?php echo urlencode($keyword); ?>" class="inline-flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white px-5 py-2.5 rounded-xl font-bold shadow-lg shadow-emerald-500/30 transition-all active:scale-95">
            <i class="fas fa-file-excel"></i> Ekspor XLSX
        </a>
        <a href="../core/export_zip.php?jenis=surat_masuk&dari=<?php echo $dari_tanggal; ?>&sampai=<?php echo $sampai_tanggal; ?>&keyword=<?php echo urlencode($keyword); ?>" class="inline-flex items-center gap-2 bg-indigo-500 hover:bg-indigo-600 text-white px-5 py-2.5 rounded-xl font-bold shadow-lg shadow-indigo-500/30 transition-all active:scale-95">
            <i class="fas fa-file-archive"></i> Unduh ZIP
        </a>
    </div>

    <!-- Form Pencarian dan Filter -->
    <div class="flex-1">
        <form method="GET" action="surat_masuk.php" class="bg-white p-2 rounded-2xl shadow-sm border border-slate-100 flex flex-wrap lg:flex-nowrap gap-2">
            <input type="date" class="bg-slate-50 border-none focus:ring-2 focus:ring-accent rounded-xl px-4 py-2 text-sm font-medium text-slate-700 w-full lg:w-40" name="dari" value="<?php echo $dari_tanggal; ?>" title="Dari Tanggal">
            <input type="date" class="bg-slate-50 border-none focus:ring-2 focus:ring-accent rounded-xl px-4 py-2 text-sm font-medium text-slate-700 w-full lg:w-40" name="sampai" value="<?php echo $sampai_tanggal; ?>" title="Sampai Tanggal">
            <div class="relative flex-1 min-w-[200px]">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" class="w-full bg-slate-50 border-none focus:ring-2 focus:ring-accent rounded-xl pl-11 pr-4 py-2 text-sm font-medium text-slate-700" name="keyword" placeholder="Cari nomor, perihal, asal..." value="<?php echo htmlspecialchars($keyword); ?>">
            </div>
            <button type="submit" class="bg-primary text-white px-6 py-2 rounded-xl font-bold hover:bg-primary/90 transition-all">Filter</button>
            <a href="surat_masuk.php" class="bg-slate-100 text-slate-600 px-4 py-2 rounded-xl font-bold hover:bg-slate-200 transition-all flex items-center justify-center"><i class="fas fa-sync-alt"></i></a>
        </form>
    </div>
</div>


<div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden">
    <div class="px-8 py-6 border-b border-slate-50 flex items-center justify-between bg-slate-50/50">
        <h3 class="font-bold text-slate-900 flex items-center gap-3">
            <div class="w-2 h-6 bg-accent rounded-full"></div>
            Daftar Arsip Surat Masuk
        </h3>
        <div class="flex items-center gap-4">
            <form method="GET" action="surat_masuk.php" class="flex items-center gap-3">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Tampilkan</span>
                <select name="limit" class="bg-white border-slate-200 rounded-lg text-xs font-bold text-slate-700 focus:ring-accent focus:border-accent" onchange="this.form.submit()">
                    <?php foreach([10, 20, 30, 40, 50] as $l): ?>
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
                        <th class="px-6 py-5 border-b border-slate-100"><?php echo sortable_header('Nomor Arsip', 'nomor_arsip', $sort_by, $sort_dir); ?></th>
                        <th class="px-6 py-5 border-b border-slate-100"><?php echo sortable_header('Nomor Surat', 'nomor_surat', $sort_by, $sort_dir); ?></th>
                        <th class="px-6 py-5 border-b border-slate-100"><?php echo sortable_header('Perihal', 'perihal', $sort_by, $sort_dir); ?></th>
                        <th class="px-6 py-5 border-b border-slate-100"><?php echo sortable_header('Asal Surat', 'asal_surat', $sort_by, $sort_dir); ?></th>
                        <th class="px-6 py-5 border-b border-slate-100 text-center"><?php echo sortable_header('Diterima', 'tanggal_diterima', $sort_by, $sort_dir); ?></th>
                        <th class="px-6 py-5 border-b border-slate-100">Berkas</th>
                        <th class="px-8 py-5 border-b border-slate-100 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if (mysqli_num_rows($result) > 0) : ?>
                        <?php $no = 1; ?>
                        <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                            <tr class="hover:bg-slate-50/80 transition-colors group text-sm md:text-base">
                                <td class="px-8 py-5 text-sm font-bold text-slate-400"><?php echo $no++; ?></td>
                                <td class="px-6 py-5 text-sm font-bold text-slate-900"><?php echo htmlspecialchars($row['nomor_arsip']); ?></td>
                                <td class="px-6 py-5">
                                    <span class="text-sm font-semibold text-slate-700 block"><?php echo htmlspecialchars($row['nomor_surat']); ?></span>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">REF: #<?php echo $row['id']; ?></span>
                                </td>
                                <td class="px-6 py-5">
                                    <span class="text-sm text-slate-600 font-medium line-clamp-1"><?php echo htmlspecialchars($row['perihal']); ?></span>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500 text-xs uppercase font-bold">
                                            <?php echo substr($row['asal_surat'], 0, 1); ?>
                                        </div>
                                        <span class="text-sm text-slate-700 font-bold"><?php echo htmlspecialchars($row['asal_surat']); ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <span class="inline-flex bg-blue-50 text-blue-600 px-3 py-1 rounded-full text-xs font-bold tracking-tight border border-blue-100">
                                        <?php echo date('d M Y', strtotime($row['tanggal_diterima'])); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-5">
                                    <?php if (!empty($row['nama_file_pdf'])) : ?>
                                        <a href="../uploads/surat_masuk/<?php echo htmlspecialchars($row['nama_file_pdf']); ?>" target="_blank" class="inline-flex items-center gap-2 text-accent hover:text-accent/80 font-bold text-xs transition-colors">
                                            <i class="fas fa-file-pdf text-lg"></i> LIHAT
                                        </a>
                                    <?php else : ?>
                                        <span class="text-[10px] font-black text-slate-300 italic uppercase">TIDAK ADA</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <a href="surat_masuk_edit.php?id=<?php echo $row['id']; ?>" class="w-9 h-9 flex items-center justify-center rounded-xl bg-amber-50 text-amber-500 hover:bg-amber-500 hover:text-white transition-all shadow-sm">
                                            <i class="fas fa-edit text-sm"></i>
                                        </a>
                                        <form action="../core/surat_masuk_aksi.php" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" class="w-9 h-9 flex items-center justify-center rounded-xl bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition-all shadow-sm">
                                                <i class="fas fa-trash text-sm"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="8" class="px-8 py-12 text-center text-slate-400 font-medium italic">
                                <i class="fas fa-folder-open text-4xl mb-4 block opacity-20"></i>
                                Tidak ada data yang ditemukan.
                            </td>
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
            $prev_link = $prev_disabled ? '#' : 'surat_masuk.php?' . http_build_query($prev_params);
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
                    echo "<a href='surat_masuk.php?" . http_build_query($page_params) . "' class='w-10 h-10 flex items-center justify-center rounded-xl border font-bold text-sm transition-all $activeClass'>$i</a>";
                } elseif ($i == $page - $range - 1 || $i == $page + $range + 1) {
                    echo "<span class='w-10 h-10 flex items-center justify-center text-slate-400 font-bold text-sm'>...</span>";
                }
            }

            // Next button
            $next_disabled = ($page >= $total_pages);
            $next_page = ($page < $total_pages) ? $page + 1 : $total_pages;
            $next_params = array_merge($query_params_base, ['page' => $next_page]);
            $next_link = $next_disabled ? '#' : 'surat_masuk.php?' . http_build_query($next_params);
            ?>
            <a href="<?php echo $next_link; ?>" class="w-10 h-10 flex items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 transition-all <?php echo $next_disabled ? 'opacity-30 cursor-not-allowed' : ''; ?>">
                <i class="fas fa-chevron-right text-xs"></i>
            </a>
        </nav>
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
