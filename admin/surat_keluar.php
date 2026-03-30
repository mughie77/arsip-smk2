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
    $sort_by = 'nomor_surat';
    $sort_dir = 'DESC';
    $order_by_clause = "ORDER BY sk.nomor_surat DESC";
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

<div class="mb-8">
    <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Surat Keluar</h1>
    <p class="text-slate-500 mt-1 font-medium">Pantau dan kelola seluruh arsip surat keluar institusi Anda.</p>
</div>

<?php
if (isset($_SESSION['success_message'])) {
    echo '<div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 mb-6 rounded-r-xl">
            <div class="flex items-center">
                <div class="flex-shrink-0"><i class="fas fa-check-circle text-emerald-500"></i></div>
                <div class="ml-3 text-sm font-bold text-emerald-700">' . $_SESSION['success_message'] . '</div>
            </div>
          </div>';
    unset($_SESSION['success_message']);
}
?>

<!-- Area Aksi dan Filter -->
<div class="flex flex-col xl:flex-row gap-6 mb-8">
    <div class="flex flex-wrap gap-2">
        <button type="button" class="inline-flex items-center gap-1.5 bg-accent hover:bg-accent/90 text-white px-3.5 py-2 rounded-lg text-sm font-bold shadow-md shadow-accent/20 transition-all active:scale-95" data-bs-toggle="modal" data-bs-target="#suratKeluarModal" id="btnTambah">
            <i class="fas fa-plus text-xs"></i> Tambah Data
        </button>
        <a href="../core/export_xlsx.php?jenis=surat_keluar&dari=<?php echo $dari_tanggal; ?>&sampai=<?php echo $sampai_tanggal; ?>&keyword=<?php echo urlencode($keyword); ?>" class="inline-flex items-center gap-1.5 bg-emerald-500 hover:bg-emerald-600 text-white px-3.5 py-2 rounded-lg text-sm font-bold shadow-md shadow-emerald-500/20 transition-all active:scale-95">
            <i class="fas fa-file-excel text-xs"></i> Ekspor XLSX
        </a>
    </div>

    <div class="flex-1">
        <form method="GET" action="surat_keluar.php" class="bg-white p-2 rounded-2xl shadow-sm border border-slate-100 flex flex-wrap xl:flex-nowrap gap-2">
            <input type="date" class="bg-slate-50 border-none focus:ring-2 focus:ring-accent rounded-xl px-4 py-2 text-sm font-medium text-slate-700 w-full lg:w-40" name="dari" value="<?php echo $dari_tanggal; ?>">
            <input type="date" class="bg-slate-50 border-none focus:ring-2 focus:ring-accent rounded-xl px-4 py-2 text-sm font-medium text-slate-700 w-full lg:w-40" name="sampai" value="<?php echo $sampai_tanggal; ?>">
            <select name="klasifikasi_id" id="klasifikasi_filter" class="bg-slate-50 border-none focus:ring-2 focus:ring-accent rounded-xl px-4 py-2 text-sm font-medium text-slate-700 w-full lg:w-48">
                <option value="">Semua Klasifikasi</option>
                <?php
                $q_klasifikasi = mysqli_query($koneksi, "SELECT * FROM klasifikasi_surat ORDER BY jenis_surat ASC");
                while ($klas = mysqli_fetch_assoc($q_klasifikasi)) {
                    $selected = ($klasifikasi_id == $klas['id']) ? 'selected' : '';
                    echo "<option value='{$klas['id']}' {$selected}>{$klas['kode']}</option>";
                }
                ?>
            </select>
            <div class="relative flex-1 min-w-[200px]">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" class="w-full bg-slate-50 border-none focus:ring-2 focus:ring-accent rounded-xl pl-11 pr-4 py-2 text-sm font-medium text-slate-700" name="keyword" placeholder="Cari..." value="<?php echo htmlspecialchars($keyword); ?>">
            </div>
            <button type="submit" class="bg-primary text-white px-6 py-2 rounded-xl font-bold hover:bg-primary/90 transition-all">Filter</button>
        </form>
    </div>
</div>


<div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden">
    <div class="px-8 py-6 border-b border-slate-50 flex items-center justify-between bg-slate-50/50">
        <h3 class="font-bold text-slate-900 flex items-center gap-3">
            <div class="w-2 h-6 bg-emerald-500 rounded-full"></div>
            Daftar Arsip Surat Keluar
        </h3>
        <div class="flex items-center gap-4">
            <form method="GET" action="surat_keluar.php" class="flex items-center gap-3">
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
                        <th class="px-6 py-5 border-b border-slate-100"><?php echo sortable_header('Kode Arsip', 'kode_arsip', $sort_by, $sort_dir); ?></th>
                        <th class="px-6 py-5 border-b border-slate-100"><?php echo sortable_header('Nomor Surat', 'nomor_surat', $sort_by, $sort_dir); ?></th>
                        <th class="px-6 py-5 border-b border-slate-100"><?php echo sortable_header('Tujuan', 'tujuan_surat', $sort_by, $sort_dir); ?></th>
                        <th class="px-6 py-5 border-b border-slate-100"><?php echo sortable_header('Perihal', 'perihal', $sort_by, $sort_dir); ?></th>
                        <th class="px-6 py-5 border-b border-slate-100 text-center"><?php echo sortable_header('Dikirim', 'tanggal_kirim', $sort_by, $sort_dir); ?></th>
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
                                <td data-label="Kode Arsip" class="px-6 py-5 text-xs font-bold text-slate-900"><?php echo htmlspecialchars($row['kode_arsip']); ?></td>
                                <td data-label="Nomor Surat" class="px-6 py-5">
                                    <span class="text-sm font-semibold text-slate-700 block leading-tight"><?php echo htmlspecialchars($row['nomor_surat']); ?></span>
                                    <span class="text-[10px] font-medium text-slate-400 uppercase tracking-wider leading-tight"><?php echo htmlspecialchars($row['kode_klasifikasi']); ?> - <?php echo htmlspecialchars($row['jenis_surat']); ?></span>
                                </td>
                                <td data-label="Tujuan" class="px-6 py-5">
                                    <span class="text-sm text-slate-700"><?php echo htmlspecialchars($row['tujuan_surat']); ?></span>
                                </td>
                                <td data-label="Perihal" class="px-6 py-5">
                                    <span class="text-sm text-slate-600 font-medium line-clamp-1" title="<?php echo htmlspecialchars($row['perihal']); ?>"><?php echo htmlspecialchars($row['perihal']); ?></span>
                                </td>
                                <td data-label="Dikirim" class="px-6 py-5 text-center">
                                    <span class="inline-flex bg-emerald-50 text-emerald-600 px-3 py-1 rounded-full text-xs font-bold tracking-tight border border-emerald-100">
                                        <?php echo date('d M Y', strtotime($row['tanggal_kirim'])); ?>
                                    </span>
                                </td>
                                <td data-label="Berkas" class="px-6 py-5 text-center md:text-left">
                                    <?php if (!empty($row['nama_file_pdf'])) : ?>
                                        <a href="../uploads/surat_keluar/<?php echo htmlspecialchars($row['nama_file_pdf']); ?>" target="_blank" class="inline-flex items-center gap-2 text-accent hover:text-accent/80 font-bold text-xs transition-colors">
                                            <i class="fas fa-file-pdf text-lg"></i> LIHAT
                                        </a>
                                    <?php else : ?>
                                        <span class="text-[10px] font-black text-slate-300 italic uppercase">TIDAK ADA</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Aksi" class="px-8 py-5 text-right">
                                    <div class="flex items-center justify-end gap-2 transition-opacity">
                                        <a href="surat_keluar_edit.php?id=<?php echo $row['id']; ?>" class="w-10 h-10 flex items-center justify-center rounded-xl bg-amber-50 text-amber-500 hover:bg-amber-500 hover:text-white transition-all shadow-md">
                                            <i class="fas fa-edit text-base"></i>
                                        </a>
                                        <form action="../core/surat_keluar_aksi.php" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
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
                'klasifikasi_id' => $klasifikasi_id ?: null,
                'keyword' => $keyword,
                'sort' => $is_user_sort ? $sort_by : null,
                'dir' => $is_user_sort ? $sort_dir : null
            ]);

            // Previous button
            $prev_disabled = ($page <= 1);
            $prev_page = ($page > 1) ? $page - 1 : 1;
            $prev_params = array_merge($query_params_base, ['page' => $prev_page]);
            $prev_link = $prev_disabled ? '#' : 'surat_keluar.php?' . http_build_query($prev_params);
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
                    echo "<a href='surat_keluar.php?" . http_build_query($page_params) . "' class='w-10 h-10 flex items-center justify-center rounded-xl border font-bold text-sm transition-all $activeClass'>$i</a>";
                } elseif ($i == $page - $range - 1 || $i == $page + $range + 1) {
                    echo "<span class='w-10 h-10 flex items-center justify-center text-slate-400 font-bold text-sm'>...</span>";
                }
            }

            // Next button
            $next_disabled = ($page >= $total_pages);
            $next_page = ($page < $total_pages) ? $page + 1 : $total_pages;
            $next_params = array_merge($query_params_base, ['page' => $next_page]);
            $next_link = $next_disabled ? '#' : 'surat_keluar.php?' . http_build_query($next_params);
            ?>
            <a href="<?php echo $next_link; ?>" class="w-10 h-10 flex items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 transition-all <?php echo $next_disabled ? 'opacity-30 cursor-not-allowed' : ''; ?>">
                <i class="fas fa-chevron-right text-xs"></i>
            </a>
        </nav>
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
