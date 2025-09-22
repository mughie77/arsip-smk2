<?php
// File: core/view_berkas.php
// Skrip ini bertanggung jawab untuk menyajikan file secara aman.

require_once '../config/koneksi.php';
require_once '../admin/cek_sesi.php'; // Pastikan hanya pengguna yang login yang bisa akses

// Validasi input GET
if (!isset($_GET['type']) || !isset($_GET['id'])) {
    header("Location: /error.php?code=400&message=Permintaan tidak valid.");
    exit();
}

$type = $_GET['type'];
$id = intval($_GET['id']);

$table_map = [
    'surat_masuk' => ['table' => 'surat_masuk', 'column' => 'nama_file_pdf', 'path' => 'surat_masuk'],
    'surat_keluar' => ['table' => 'surat_keluar', 'column' => 'nama_file_pdf', 'path' => 'surat_keluar'],
    'notulen' => ['table' => 'notulen', 'column' => 'nama_file', 'path' => 'notulen']
];

// Cek apakah tipe valid
if (!array_key_exists($type, $table_map)) {
    header("Location: /error.php?code=400&message=Tipe berkas tidak valid.");
    exit();
}

$table = $table_map[$type]['table'];
$column = $table_map[$type]['column'];
$path = $table_map[$type]['path'];

// Ambil nama file dari database
$sql = "SELECT $column FROM $table WHERE id = ?";
$stmt = mysqli_prepare($koneksi, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);
mysqli_close($koneksi);

// Cek apakah record atau nama file ditemukan
if (!$row || empty($row[$column])) {
    header("Location: /error.php?code=404&message=Data berkas tidak ditemukan di database.");
    exit();
}

$filename = $row[$column];
$filepath = "../uploads/$path/" . $filename;

// Cek apakah file fisik ada di server
if (!file_exists($filepath)) {
    header("Location: /error.php?code=File Not Found");
    exit();
}

// Sajikan file ke browser
// Dapatkan tipe MIME untuk keamanan
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $filepath);
finfo_close($finfo);

header('Content-Type: ' . $mime_type);
header('Content-Disposition: inline; filename="' . basename($filepath) . '"');
header('Content-Length: ' . filesize($filepath));
header('Accept-Ranges: bytes');

readfile($filepath);
exit();
?>
