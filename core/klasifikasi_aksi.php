<?php
session_start();
require_once '../config/koneksi.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit;
}

$action = $_REQUEST['action'] ?? '';

// Fungsi untuk redirect dengan pesan
function redirect_with_message($message, $status) {
    header("Location: ../admin/klasifikasi_surat.php?message=" . urlencode($message) . "&status=" . $status);
    exit;
}

// Tambah Klasifikasi
if ($action == 'add') {
    $kode = mysqli_real_escape_string($koneksi, $_POST['kode']);
    $jenis_surat = mysqli_real_escape_string($koneksi, $_POST['jenis_surat']);

    if (empty($kode) || empty($jenis_surat)) {
        redirect_with_message("Kode dan Jenis Surat tidak boleh kosong.", "error");
    }

    $query = "INSERT INTO klasifikasi_surat (kode, jenis_surat) VALUES ('$kode', '$jenis_surat')";
    if (mysqli_query($koneksi, $query)) {
        redirect_with_message("Klasifikasi berhasil ditambahkan.", "success");
    } else {
        redirect_with_message("Error: " . mysqli_error($koneksi), "error");
    }
}

// Edit Klasifikasi
elseif ($action == 'edit') {
    $id = (int)$_POST['id'];
    $kode = mysqli_real_escape_string($koneksi, $_POST['kode']);
    $jenis_surat = mysqli_real_escape_string($koneksi, $_POST['jenis_surat']);

    if (empty($id) || empty($kode) || empty($jenis_surat)) {
        redirect_with_message("Data tidak lengkap.", "error");
    }

    $query = "UPDATE klasifikasi_surat SET kode='$kode', jenis_surat='$jenis_surat' WHERE id=$id";
    if (mysqli_query($koneksi, $query)) {
        redirect_with_message("Klasifikasi berhasil diperbarui.", "success");
    } else {
        redirect_with_message("Error: " . mysqli_error($koneksi), "error");
    }
}

// Hapus Klasifikasi
elseif ($action == 'delete') {
    $id = (int)$_GET['id'];

    if (empty($id)) {
        redirect_with_message("ID tidak valid.", "error");
    }

    // Sebaiknya tambahkan pengecekan apakah klasifikasi ini sedang digunakan
    // oleh surat keluar sebelum menghapus. Untuk saat ini, kita langsung hapus.

    $query = "DELETE FROM klasifikasi_surat WHERE id=$id";
    if (mysqli_query($koneksi, $query)) {
        redirect_with_message("Klasifikasi berhasil dihapus.", "success");
    } else {
        redirect_with_message("Error: " . mysqli_error($koneksi), "error");
    }
}

else {
    redirect_with_message("Aksi tidak valid.", "error");
}

mysqli_close($koneksi);
?>
