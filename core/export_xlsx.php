<?php
// File: core/export_xlsx.php

require '../vendor/autoload.php';
require_once '../config/koneksi.php';
require_once '../admin/cek_sesi.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// --- Ambil parameter dari URL ---
$jenis = isset($_GET['jenis']) ? $_GET['jenis'] : '';
$dari_tanggal = isset($_GET['dari']) ? $_GET['dari'] : '';
$sampai_tanggal = isset($_GET['sampai']) ? $_GET['sampai'] : '';

// --- Konfigurasi berdasarkan jenis data ---
$sql = "";
$filename = "laporan.xlsx";
$headers = [];

switch ($jenis) {
    case 'surat_masuk':
        $sql = "SELECT nomor_arsip, nomor_surat, perihal, asal_surat, tanggal_diterima, acc_kepada FROM surat_masuk";
        $filename = "laporan-surat-masuk.xlsx";
        $headers = ['Nomor Arsip', 'Nomor Surat', 'Perihal', 'Asal Surat', 'Tanggal Diterima', 'Acc Kepada'];
        $date_column = 'tanggal_diterima';
        break;
    case 'surat_keluar':
        $sql = "SELECT nomor_arsip, nomor_surat, perihal, tujuan_surat, tanggal_kirim, acc_kepada FROM surat_keluar";
        $filename = "laporan-surat-keluar.xlsx";
        $headers = ['Nomor Arsip', 'Nomor Surat', 'Perihal', 'Tujuan Surat', 'Tanggal Kirim', 'Acc Kepada'];
        $date_column = 'tanggal_kirim';
        break;
    case 'notulen':
        $sql = "SELECT tanggal, kegiatan FROM notulen";
        $filename = "laporan-notulen.xlsx";
        $headers = ['Tanggal', 'Kegiatan'];
        $date_column = 'tanggal';
        break;
    default:
        die("Jenis laporan tidak valid.");
}

// --- Terapkan filter tanggal jika ada ---
if (!empty($dari_tanggal) && !empty($sampai_tanggal)) {
    $sql .= " WHERE $date_column BETWEEN ? AND ?";
}

$stmt = mysqli_prepare($koneksi, $sql);
if (!empty($dari_tanggal) && !empty($sampai_tanggal)) {
    mysqli_stmt_bind_param($stmt, "ss", $dari_tanggal, $sampai_tanggal);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// --- Buat objek Spreadsheet ---
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// --- Tulis Header ---
$column = 'A';
foreach ($headers as $header) {
    $sheet->setCellValue($column . '1', $header);
    $column++;
}

// --- Tulis Data ---
$row = 2;
while ($data = mysqli_fetch_assoc($result)) {
    $column = 'A';
    foreach ($data as $value) {
        $sheet->setCellValue($column . $row, $value);
        $column++;
    }
    $row++;
}

// --- Atur header untuk download ---
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

// --- Tulis file ke output ---
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');

mysqli_stmt_close($stmt);
mysqli_close($koneksi);
exit();
?>
