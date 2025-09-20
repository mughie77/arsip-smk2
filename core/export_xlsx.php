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
$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';

// --- Konfigurasi berdasarkan jenis data ---
$table = "";
$filename = "laporan.xlsx";
$headers = [];
$columns = "";
$date_column = "";
$search_columns = [];

switch ($jenis) {
    case 'surat_masuk':
        $table = "surat_masuk";
        $columns = "nomor_arsip, nomor_surat, perihal, asal_surat, tanggal_diterima, acc_kepada";
        $filename = "laporan-surat-masuk.xlsx";
        $headers = ['Nomor Arsip', 'Nomor Surat', 'Perihal', 'Asal Surat', 'Tanggal Diterima', 'Acc Kepada'];
        $date_column = 'tanggal_diterima';
        $search_columns = ['nomor_arsip', 'nomor_surat', 'perihal', 'asal_surat'];
        break;
    case 'surat_keluar':
        $table = "surat_keluar";
        $columns = "nomor_arsip, nomor_surat, perihal, tujuan_surat, tanggal_kirim, acc_kepada";
        $filename = "laporan-surat-keluar.xlsx";
        $headers = ['Nomor Arsip', 'Nomor Surat', 'Perihal', 'Tujuan Surat', 'Tanggal Kirim', 'Acc Kepada'];
        $date_column = 'tanggal_kirim';
        $search_columns = ['nomor_arsip', 'nomor_surat', 'perihal', 'tujuan_surat'];
        break;
    case 'notulen':
        $table = "notulen";
        $columns = "tanggal, kegiatan";
        $filename = "laporan-notulen.xlsx";
        $headers = ['Tanggal', 'Kegiatan'];
        $date_column = 'tanggal';
        $search_columns = ['kegiatan'];
        break;
    default:
        die("Jenis laporan tidak valid.");
}

// --- Bangun Query secara dinamis ---
$sql = "SELECT $columns FROM $table";
$where_clauses = [];
$params = [];
$types = "";

if (!empty($dari_tanggal) && !empty($sampai_tanggal)) {
    $where_clauses[] = "$date_column BETWEEN ? AND ?";
    $params[] = $dari_tanggal;
    $params[] = $sampai_tanggal;
    $types .= "ss";
}

if (!empty($keyword)) {
    $search_parts = [];
    $like_keyword = "%" . $keyword . "%";
    foreach ($search_columns as $col) {
        $search_parts[] = "$col LIKE ?";
        $params[] = $like_keyword;
        $types .= "s";
    }
    $where_clauses[] = "(" . implode(' OR ', $search_parts) . ")";
}

if (count($where_clauses) > 0) {
    $sql .= " WHERE " . implode(' AND ', $where_clauses);
}

$stmt = mysqli_prepare($koneksi, $sql);
if (count($params) > 0) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
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
