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

// --- Ambil Data Bulanan untuk Grafik (Tahun Berjalan) ---
$current_year = date('Y');
$monthly_sm = array_fill(1, 12, 0);
$monthly_sk = array_fill(1, 12, 0);

// Data Bulanan Surat Masuk
$query_sm_monthly = "SELECT MONTH(tanggal_diterima) as bulan, COUNT(id) as total
                     FROM surat_masuk
                     WHERE YEAR(tanggal_diterima) = '$current_year'
                     GROUP BY MONTH(tanggal_diterima)";
$res_sm_monthly = mysqli_query($koneksi, $query_sm_monthly);
while ($row = mysqli_fetch_assoc($res_sm_monthly)) {
    $monthly_sm[(int)$row['bulan']] = (int)$row['total'];
}

// Data Bulanan Surat Keluar
$query_sk_monthly = "SELECT MONTH(tanggal_kirim) as bulan, COUNT(id) as total
                     FROM surat_keluar
                     WHERE YEAR(tanggal_kirim) = '$current_year'
                     GROUP BY MONTH(tanggal_kirim)";
$res_sk_monthly = mysqli_query($koneksi, $query_sk_monthly);
while ($row = mysqli_fetch_assoc($res_sk_monthly)) {
    $monthly_sk[(int)$row['bulan']] = (int)$row['total'];
}

$months_labels = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agt", "Sep", "Okt", "Nov", "Des"];
$data_sm_values = array_values($monthly_sm);
$data_sk_values = array_values($monthly_sk);
?>

<!-- Judul Halaman -->
<div class="row mb-4">
    <div class="col-md-12">
        <h1 class="h3">Dashboard</h1>
        <p class="text-muted">Ringkasan data arsip digital Anda.</p>
    </div>
</div>

<!-- Kartu Statistik -->
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card h-100 shadow-sm card-statistic">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <i class="fas fa-envelope-open-text fa-3x text-primary"></i>
                    </div>
                    <div class="col">
                        <h5 class="card-title text-muted mb-1">Surat Masuk</h5>
                        <h3 class="fw-bold"><?php echo $total_surat_masuk; ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card h-100 shadow-sm card-statistic">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <i class="fas fa-paper-plane fa-3x text-success"></i>
                    </div>
                    <div class="col">
                        <h5 class="card-title text-muted mb-1">Surat Keluar</h5>
                        <h3 class="fw-bold"><?php echo $total_surat_keluar; ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card h-100 shadow-sm card-statistic">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <i class="fas fa-file-alt fa-3x text-warning"></i>
                    </div>
                    <div class="col">
                        <h5 class="card-title text-muted mb-1">Notulen</h5>
                        <h3 class="fw-bold"><?php echo $total_notulen; ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card h-100 shadow-sm card-statistic">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <i class="fas fa-tags fa-3x text-info"></i>
                    </div>
                    <div class="col">
                        <h5 class="card-title text-muted mb-1">Klasifikasi</h5>
                        <h3 class="fw-bold"><?php echo $total_klasifikasi; ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card h-100 shadow-sm card-statistic">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <i class="fas fa-archive fa-3x text-purple"></i>
                    </div>
                    <div class="col">
                        <h5 class="card-title text-muted mb-1">Arsip Berkas</h5>
                        <h3 class="fw-bold"><?php echo $total_arsip_berkas; ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Grafik Entry Surat -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="card-title mb-0">Grafik Entry Surat (<?php echo $current_year; ?>)</h5>
            </div>
            <div class="card-body">
                <canvas id="arsipChart"></canvas>
            </div>
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
            labels: <?php echo json_encode($months_labels); ?>,
            datasets: [
                {
                    label: 'Surat Masuk',
                    data: <?php echo json_encode($data_sm_values); ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3
                },
                {
                    label: 'Surat Keluar',
                    data: <?php echo json_encode($data_sk_values); ?>,
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
                    text: 'Statistik Entry Surat Masuk & Keluar Bulanan'
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
