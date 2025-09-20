<?php
// File: core/export_zip.php

require_once '../config/koneksi.php';
require_once '../admin/cek_sesi.php';

// --- Ambil parameter dari URL ---
$jenis = isset($_GET['jenis']) ? $_GET['jenis'] : '';
$dari_tanggal = isset($_GET['dari']) ? $_GET['dari'] : '';
$sampai_tanggal = isset($_GET['sampai']) ? $_GET['sampai'] : '';

// --- Konfigurasi berdasarkan jenis data ---
$sql = "";
$upload_folder = "";
$file_column = "";
$date_column = "";

switch ($jenis) {
    case 'surat_masuk':
        $sql = "SELECT nama_file_pdf FROM surat_masuk";
        $upload_folder = "../uploads/surat_masuk/";
        $file_column = 'nama_file_pdf';
        $date_column = 'tanggal_diterima';
        break;
    case 'surat_keluar':
        $sql = "SELECT nama_file_pdf FROM surat_keluar";
        $upload_folder = "../uploads/surat_keluar/";
        $file_column = 'nama_file_pdf';
        $date_column = 'tanggal_kirim';
        break;
    case 'notulen':
        $sql = "SELECT nama_file FROM notulen";
        $upload_folder = "../uploads/notulen/";
        $file_column = 'nama_file';
        $date_column = 'tanggal';
        break;
    default:
        die("Jenis arsip tidak valid.");
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

$files_to_zip = [];
while ($row = mysqli_fetch_assoc($result)) {
    $files_to_zip[] = $row[$file_column];
}

mysqli_stmt_close($stmt);
mysqli_close($koneksi);

// --- Proses pembuatan ZIP ---
if (count($files_to_zip) > 0) {
    $zip = new ZipArchive();
    $zip_filename = sys_get_temp_dir() . "/arsip_" . $jenis . "_" . time() . ".zip";

    if ($zip->open($zip_filename, ZipArchive::CREATE) !== TRUE) {
        die("Tidak dapat membuka arsip ZIP.");
    }

    foreach ($files_to_zip as $filename) {
        $file_path = $upload_folder . $filename;
        if (file_exists($file_path)) {
            $zip->addFile($file_path, $filename);
        }
    }

    $zip->close();

    // --- Atur header untuk download ---
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="arsip-' . $jenis . '.zip"');
    header('Content-Length: ' . filesize($zip_filename));
    header('Pragma: no-cache');
    header('Expires: 0');

    // Baca file dan kirim ke output
    readfile($zip_filename);

    // Hapus file sementara
    unlink($zip_filename);
    exit();

} else {
    // Redirect kembali dengan pesan error jika tidak ada file
    $redirect_page = '';
    switch($jenis) {
        case 'surat_masuk': $redirect_page = 'surat_masuk.php'; break;
        case 'surat_keluar': $redirect_page = 'surat_keluar.php'; break;
        case 'notulen': $redirect_page = 'notulen.php'; break;
    }
    header("Location: ../admin/" . $redirect_page . "?error=Tidak ada file arsip yang ditemukan untuk diekspor pada rentang tanggal yang dipilih.");
    exit();
}
?>
