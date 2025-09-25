<?php
// Aktifkan pelaporan error untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../config/koneksi.php';

// --- DEBUGGING ---
if (!isset($_SESSION['username'])) {
    die("DEBUG: Sesi admin tidak ditemukan. Silakan login kembali.");
} else {
    echo "DEBUG: Sesi admin ditemukan: " . htmlspecialchars($_SESSION['username']) . "<br>";
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
if (empty($action)) {
    die("DEBUG: Tidak ada 'action' yang diterima. Pastikan form mengirimkan 'action'.");
} else {
    echo "DEBUG: Aksi yang diterima adalah: " . htmlspecialchars($action) . "<br>";
}
// --- END DEBUGGING ---


// Cek apakah admin sudah login
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit;
}

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
        // Sebaiknya tambahkan pengecekan apakah klasifikasi ini sedang digunakan
        // oleh surat keluar sebelum menghapus. Untuk saat ini, kita langsung hapus.
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