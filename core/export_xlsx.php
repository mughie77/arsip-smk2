<?php
// File: core/export_xlsx.php
// Skrip ini bertanggung jawab untuk mengekspor data ke format XLSX (Excel).

require_once '../config/koneksi.php';
require_once '../admin/cek_sesi.php';
require_once 'lib/xlsxwriter.class.php';

// --- FUNGSI UTAMA ---
$jenis = isset($_GET['jenis']) ? $_GET['jenis'] : '';
$dari_tanggal = isset($_GET['dari']) ? $_GET['dari'] : '';
$sampai_tanggal = isset($_GET['sampai']) ? $_GET['sampai'] : '';
$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';

// Validasi jenis ekspor
$allowed_types = ['surat_masuk', 'surat_keluar', 'notulen'];
if (!in_array($jenis, $allowed_types)) {
    die("Tipe ekspor tidak valid.");
}

// Tentukan query dan header berdasarkan jenis
$table = '';
$headers = [];
$filename = "export_" . $jenis . "_" . date('Y-m-d') . ".xlsx";

$base_query = "";
$where_clauses = [];
$search_columns = [];
$date_column = '';

switch ($jenis) {
    case 'surat_masuk':
        $table = "surat_masuk";
        $headers = ['Nomor Arsip'=>'string', 'Nomor Surat'=>'string', 'Perihal'=>'string', 'Asal Surat'=>'string', 'Tanggal Diterima'=>'date', 'Diteruskan Kepada'=>'string'];
        $base_query = "SELECT nomor_arsip, nomor_surat, perihal, asal_surat, tanggal_diterima, acc_kepada FROM surat_masuk";
        $search_columns = ['nomor_arsip', 'nomor_surat', 'perihal', 'asal_surat'];
        $date_column = 'tanggal_diterima';
        break;
    case 'surat_keluar':
        $table = "surat_keluar";
        $headers = ['Nomor Surat'=>'string', 'Perihal'=>'string', 'Tujuan Surat'=>'string', 'Tanggal Kirim'=>'date'];
        $base_query = "SELECT nomor_surat, perihal, tujuan_surat, tanggal_kirim FROM surat_keluar";
        $search_columns = ['nomor_surat', 'perihal', 'tujuan_surat'];
        $date_column = 'tanggal_kirim';
        break;
    case 'notulen':
        $table = "notulen";
        $headers = ['Tanggal'=>'date', 'Kegiatan'=>'string'];
        $base_query = "SELECT tanggal, kegiatan FROM notulen";
        $search_columns = ['kegiatan'];
        $date_column = 'tanggal';
        break;
}

// Bangun klausa WHERE berdasarkan filter
if (!empty($dari_tanggal) && !empty($sampai_tanggal)) {
    $where_clauses[] = "$date_column BETWEEN '$dari_tanggal' AND '$sampai_tanggal'";
}
if (!empty($keyword) && count($search_columns) > 0) {
    $search_parts = [];
    foreach ($search_columns as $col) {
        $search_parts[] = "$col LIKE '%" . mysqli_real_escape_string($koneksi, $keyword) . "%'";
    }
    $where_clauses[] = "(" . implode(' OR ', $search_parts) . ")";
}

$query = $base_query;
if (count($where_clauses) > 0) {
    $query .= " WHERE " . implode(' AND ', $where_clauses);
}

// Eksekusi query
$result = mysqli_query($koneksi, $query);
if (!$result) {
    die("Query Error: " . mysqli_error($koneksi));
}

// Buat file XLSX
$writer = new XLSXWriter();
$writer->writeSheetHeader('Sheet1', $headers);
while ($row = mysqli_fetch_assoc($result)) {
    $writer->writeSheetRow('Sheet1', $row);
}

// Atur header untuk download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer->writeToStdOut();
mysqli_close($koneksi);
exit();
?>
