<?php
require_once '../config/koneksi.php';
require_once '../admin/cek_sesi.php';

$action = $_POST['action'] ?? '';

if ($action == 'update') {
    $nama_sekolah = mysqli_real_escape_string($koneksi, $_POST['nama_sekolah']);
    $kode_sekolah = mysqli_real_escape_string($koneksi, $_POST['kode_sekolah']);

    if (empty($nama_sekolah) || empty($kode_sekolah)) {
        $_SESSION['error_message'] = "Nama dan Kode Sekolah tidak boleh kosong.";
    } else {
        // Update data di tabel pengaturan (asumsi id=1)
        $query = "UPDATE pengaturan SET nama_sekolah='$nama_sekolah', kode_sekolah='$kode_sekolah' WHERE id=1";

        if (mysqli_query($koneksi, $query)) {
            $_SESSION['success_message'] = "Pengaturan berhasil diperbarui.";
        } else {
            $_SESSION['error_message'] = "Error: " . mysqli_error($koneksi);
        }
    }
    header("Location: ../admin/pengaturan.php");
    exit;
} else {
    $_SESSION['error_message'] = "Aksi tidak valid.";
    header("Location: ../admin/pengaturan.php");
    exit;
}

mysqli_close($koneksi);
?>