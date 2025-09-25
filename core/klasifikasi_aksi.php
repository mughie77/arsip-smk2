<?php
// Aktifkan pelaporan error untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../config/koneksi.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ---- DEBUGGING ----
echo "<pre>";
echo "Aksi yang diterima: " . htmlspecialchars($action) . "\n";
echo "Data POST yang diterima:\n";
print_r($_POST);
echo "Data GET yang diterima:\n";
print_r($_GET);
echo "</pre>";
// ---- END DEBUGGING ----

// Tambah Klasifikasi
if ($action == 'add') {
    $kode = mysqli_real_escape_string($koneksi, $_POST['kode']);
    $jenis_surat = mysqli_real_escape_string($koneksi, $_POST['jenis_surat']);

    if (empty($kode) || empty($jenis_surat)) {
        die("Error: Kode dan Jenis Surat tidak boleh kosong.");
    } else {
        $query = "INSERT INTO klasifikasi_surat (kode, jenis_surat) VALUES ('$kode', '$jenis_surat')";
        if (mysqli_query($koneksi, $query)) {
            $_SESSION['success_message'] = "Klasifikasi berhasil ditambahkan.";
            header("Location: ../admin/klasifikasi_surat.php");
            exit;
        } else {
            // Tampilkan error database secara langsung
            die("Error Database saat menambah: " . mysqli_error($koneksi));
        }
    }
}

// Edit Klasifikasi
elseif ($action == 'edit') {
    $id = (int)$_POST['id'];
    $kode = mysqli_real_escape_string($koneksi, $_POST['kode']);
    $jenis_surat = mysqli_real_escape_string($koneksi, $_POST['jenis_surat']);

    if (empty($id) || empty($kode) || empty($jenis_surat)) {
        die("Error: Data tidak lengkap.");
    } else {
        $query = "UPDATE klasifikasi_surat SET kode='$kode', jenis_surat='$jenis_surat' WHERE id=$id";
        if (mysqli_query($koneksi, $query)) {
            $_SESSION['success_message'] = "Klasifikasi berhasil diperbarui.";
            header("Location: ../admin/klasifikasi_surat.php");
            exit;
        } else {
            die("Error Database saat mengedit: " . mysqli_error($koneksi));
        }
    }
}

// Hapus Klasifikasi
elseif ($action == 'delete') {
    $id = (int)$_GET['id'];

    if (empty($id)) {
        die("Error: ID tidak valid.");
    } else {
        $query = "DELETE FROM klasifikasi_surat WHERE id=$id";
        if (mysqli_query($koneksi, $query)) {
            $_SESSION['success_message'] = "Klasifikasi berhasil dihapus.";
            header("Location: ../admin/klasifikasi_surat.php");
            exit;
        } else {
            die("Error Database saat menghapus: " . mysqli_error($koneksi));
        }
    }
}

// Jika tidak ada aksi yang valid
else {
    die("Error: Aksi tidak valid atau tidak diberikan.");
}

mysqli_close($koneksi);
?>