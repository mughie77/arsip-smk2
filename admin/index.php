<?php
// Sertakan header template
require_once 'template_header.php';
// Sertakan file koneksi
require_once '../config/koneksi.php';

// --- Ambil Data untuk Kartu Statistik ---

// 1. Jumlah Surat Masuk
$query_sm = "SELECT COUNT(id) as total_surat_masuk FROM surat_masuk";
$result_sm = mysqli_query($koneksi, $query_sm);
$data_sm = mysqli_fetch_assoc($result_sm);
$total_surat_masuk = $data_sm['total_surat_masuk'];

// 2. Jumlah Surat Keluar
$query_sk = "SELECT COUNT(id) as total_surat_keluar FROM surat_keluar";
$result_sk = mysqli_query($koneksi, $query_sk);
$data_sk = mysqli_fetch_assoc($result_sk);
$total_surat_keluar = $data_sk['total_surat_keluar'];

// 3. Jumlah Notulen
$query_n = "SELECT COUNT(id) as total_notulen FROM notulen";
$result_n = mysqli_query($koneksi, $query_n);
$data_n = mysqli_fetch_assoc($result_n);
$total_notulen = $data_n['total_notulen'];

// 4. Jumlah Klasifikasi
$query_k = "SELECT COUNT(id) as total_klasifikasi FROM klasifikasi_surat";
$result_k = mysqli_query($koneksi, $query_k);
$data_k = mysqli_fetch_assoc($result_k);
$total_klasifikasi = $data_k['total_klasifikasi'];

// 5. Jumlah Arsip Berkas
$query_ab = "SELECT COUNT(id) as total_arsip_berkas FROM arsip_berkas";
$result_ab = mysqli_query($koneksi, $query_ab);
$data_ab = mysqli_fetch_assoc($result_ab);
$total_arsip_berkas = $data_ab['total_arsip_berkas'];

// --- Ambil Data 30 Hari Terakhir untuk Grafik (Dioptimalkan) ---
$labels = [];
$daily_sm_raw = [];
$daily_sk_raw = [];
$start_date = date('Y-m-d', strtotime('-29 days'));

// 1. Ambil Data Surat Masuk (Satu Query)
$q_sm = mysqli_query($koneksi, "SELECT DATE(tanggal_diterima) as tgl, COUNT(id) as total FROM surat_masuk WHERE tanggal_diterima >= '$start_date' GROUP BY DATE(tanggal_diterima)");
while($row = mysqli_fetch_assoc($q_sm)) { $daily_sm_raw[$row['tgl']] = (int)$row['total']; }

// 2. Ambil Data Surat Keluar (Satu Query)
$q_sk = mysqli_query($koneksi, "SELECT DATE(tanggal_kirim) as tgl, COUNT(id) as total FROM surat_keluar WHERE tanggal_kirim >= '$start_date' GROUP BY DATE(tanggal_kirim)");
while($row = mysqli_fetch_assoc($q_sk)) { $daily_sk_raw[$row['tgl']] = (int)$row['total']; }

// 3. Gabungkan Data untuk 30 Hari Terakhir
$daily_sm = [];
$daily_sk = [];
for ($i = 29; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $labels[] = date('d M', strtotime($date));
    $daily_sm[] = $daily_sm_raw[$date] ?? 0;
    $daily_sk[] = $daily_sk_raw[$date] ?? 0;
}
?>

<!-- Judul Halaman -->
<div class="mb-8">
    <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Dashboard</h1>
    <p class="text-slate-500 mt-1 font-medium text-lg">Ringkasan data arsip digital Anda secara real-time.</p>
</div>

<!-- Kartu Statistik -->
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-5 gap-6">
    <?php
    $stats = [
        ['label' => 'Surat Masuk', 'value' => $total_surat_masuk, 'icon' => 'envelope-open-text', 'color' => 'blue'],
        ['label' => 'Surat Keluar', 'value' => $total_surat_keluar, 'icon' => 'paper-plane', 'color' => 'emerald'],
        ['label' => 'Notulen', 'value' => $total_notulen, 'icon' => 'file-alt', 'color' => 'amber'],
        ['label' => 'Klasifikasi', 'value' => $total_klasifikasi, 'icon' => 'indigo', 'color' => 'indigo'],
        ['label' => 'Arsip Berkas', 'value' => $total_arsip_berkas, 'icon' => 'archive', 'color' => 'purple'],
    ];

    foreach ($stats as $stat):
        $colorClass = [
            'blue' => 'from-blue-500 to-blue-600 text-blue-500 bg-blue-50',
            'emerald' => 'from-emerald-500 to-emerald-600 text-emerald-500 bg-emerald-50',
            'amber' => 'from-amber-500 to-amber-600 text-amber-500 bg-amber-50',
            'indigo' => 'from-indigo-500 to-indigo-600 text-indigo-500 bg-indigo-50',
            'purple' => 'from-purple-500 to-purple-600 text-purple-500 bg-purple-50',
        ][$stat['color']];

        $gradient = explode(' ', $colorClass)[0] . ' ' . explode(' ', $colorClass)[1];
        $textColor = explode(' ', $colorClass)[2];
        $bgColor = explode(' ', $colorClass)[3];
    ?>
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 hover:shadow-xl transition-all duration-300 group">
            <div class="flex items-center justify-between">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center <?php echo $bgColor . ' ' . $textColor; ?> group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-<?php echo $stat['icon']; ?> text-2xl"></i>
                </div>
                <div class="text-right">
                    <p class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-1"><?php echo $stat['label']; ?></p>
                    <h3 class="text-3xl font-black text-slate-900 leading-none"><?php echo $stat['value']; ?></h3>
                </div>
            </div>
            <div class="mt-6 w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
                <div class="bg-gradient-to-r <?php echo $gradient; ?> h-full w-full opacity-70 group-hover:opacity-100 transition-opacity"></div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Grafik Entry Surat -->
<div class="mt-10">
    <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-slate-100">
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
            <div>
                <h3 class="text-xl font-bold text-slate-900">Analisis Aktivitas Surat</h3>
                <p class="text-slate-500 font-medium">Tren input surat masuk dan keluar dalam 30 hari terakhir.</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="flex items-center gap-2 text-xs font-bold text-slate-500 bg-slate-100 px-3 py-1.5 rounded-full">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span> SURAT MASUK
                </span>
                <span class="flex items-center gap-2 text-xs font-bold text-slate-500 bg-slate-100 px-3 py-1.5 rounded-full">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span> SURAT KELUAR
                </span>
            </div>
        </div>
        <div class="relative h-[450px]">
            <canvas id="arsipChart"></canvas>
        </div>
    </div>
</div>


<script>
// Menunggu dokumen siap sebelum menjalankan skrip Chart.js
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('arsipChart').getContext('2d');
    const arsipChart = new Chart(ctx, {
        type: 'line', // Tipe grafik adalah line chart
        data: {
            labels: <?php echo json_encode($labels); ?>,
            datasets: [
                {
                    label: 'Surat Masuk',
                    data: <?php echo json_encode($daily_sm); ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3
                },
                {
                    label: 'Surat Keluar',
                    data: <?php echo json_encode($daily_sk); ?>,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        // Memastikan hanya integer yang ditampilkan di sumbu Y
                        stepSize: 1,
                        callback: function(value) { if (Number.isInteger(value)) { return value; } },
                    }
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                title: {
                    display: true,
                    text: 'Statistik Entry Surat Masuk & Keluar 30 Hari Terakhir'
                }
            }
        }
    });
});
</script>

<?php
// Sertakan footer template
require_once 'template_footer.php';
?>
