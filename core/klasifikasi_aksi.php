<?php
require_once '../config/koneksi.php';
require_once '../admin/cek_sesi.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Tambah Klasifikasi
if ($action == 'add') {
    $kode = mysqli_real_escape_string($koneksi, $_POST['kode']);
    $jenis_surat = mysqli_real_escape_string($koneksi, $_POST['jenis_surat']);

    if (empty($kode) || empty($jenis_surat)) {
        $_SESSION['error_message'] = "Kode dan Jenis Surat tidak boleh kosong.";
    } else {
        $query = "INSERT INTO klasifikasi_surat (kode, jenis_surat) VALUES ('$kode', '$jenis_surat')";
        if (mysqli_query($koneksi, $query)) {
            $_SESSION['success_message'] = "Klasifikasi berhasil ditambahkan.";
        } else {
            $_SESSION['error_message'] = "Error: " . mysqli_error($koneksi);
        }
    }
    header("Location: ../admin/klasifikasi_surat.php");
    exit;
}

// Edit Klasifikasi
elseif ($action == 'edit') {
    $id = (int)$_POST['id'];
    $kode = mysqli_real_escape_string($koneksi, $_POST['kode']);
    $jenis_surat = mysqli_real_escape_string($koneksi, $_POST['jenis_surat']);

    if (empty($id) || empty($kode) || empty($jenis_surat)) {
        $_SESSION['error_message'] = "Data tidak lengkap.";
    } else {
        $query = "UPDATE klasifikasi_surat SET kode='$kode', jenis_surat='$jenis_surat' WHERE id=$id";
        if (mysqli_query($koneksi, $query)) {
            $_SESSION['success_message'] = "Klasifikasi berhasil diperbarui.";
        } else {
            $_SESSION['error_message'] = "Error: " . mysqli_error($koneksi);
        }
    }
    header("Location: ../admin/klasifikasi_surat.php");
    exit;
}

// Hapus Klasifikasi
elseif ($action == 'delete') {
    $id = (int)$_GET['id'];

    if (empty($id)) {
        $_SESSION['error_message'] = "ID tidak valid.";
    } else {
        $query = "DELETE FROM klasifikasi_surat WHERE id=$id";
        if (mysqli_query($koneksi, $query)) {
            $_SESSION['success_message'] = "Klasifikasi berhasil dihapus.";
        } else {
            $_SESSION['error_message'] = "Error: " . mysqli_error($koneksi);
        }
    }
    header("Location: ../admin/klasifikasi_surat.php");
    exit;
}

// Jika tidak ada aksi yang valid
else {
    $_SESSION['error_message'] = "Aksi tidak valid.";
    header("Location: ../admin/klasifikasi_surat.php");
    exit;
}

mysqli_close($koneksi);
?>