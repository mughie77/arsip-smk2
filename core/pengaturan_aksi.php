<?php
session_start();
require_once '../config/koneksi.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit;
}

$action = $_POST['action'] ?? '';

// Fungsi untuk redirect dengan pesan
function redirect_with_message($message, $status) {
    header("Location: ../admin/pengaturan.php?message=" . urlencode($message) . "&status=" . $status);
    exit;
}

if ($action == 'update') {
    $nama_sekolah = mysqli_real_escape_string($koneksi, $_POST['nama_sekolah']);
    $kode_sekolah = mysqli_real_escape_string($koneksi, $_POST['kode_sekolah']);

    if (empty($nama_sekolah) || empty($kode_sekolah)) {
        redirect_with_message("Nama dan Kode Sekolah tidak boleh kosong.", "error");
    }

    // Update data di tabel pengaturan (asumsi id=1)
    $query = "UPDATE pengaturan SET nama_sekolah='$nama_sekolah', kode_sekolah='$kode_sekolah' WHERE id=1";

    if (mysqli_query($koneksi, $query)) {
        redirect_with_message("Pengaturan berhasil diperbarui.", "success");
    } else {
        redirect_with_message("Error: " . mysqli_error($koneksi), "error");
    }
} else {
    redirect_with_message("Aksi tidak valid.", "error");
}

mysqli_close($koneksi);
?>
