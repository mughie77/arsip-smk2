<?php
require_once '../config/koneksi.php';
require_once '../admin/cek_sesi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'csrf_validator.php';
}

$action = $_REQUEST['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($action) {
        case 'add':
            $kode = mysqli_real_escape_string($koneksi, $_POST['kode']);
            $jenis_surat = mysqli_real_escape_string($koneksi, $_POST['jenis_surat']);

            if (empty($kode) || empty($jenis_surat)) {
                $_SESSION['error_message'] = "Kode dan Jenis Surat tidak boleh kosong.";
            } else {
                $query = "INSERT INTO klasifikasi_surat (kode, jenis_surat) VALUES (?, ?)";
                $stmt = mysqli_prepare($koneksi, $query);
                mysqli_stmt_bind_param($stmt, "ss", $kode, $jenis_surat);
                if (mysqli_stmt_execute($stmt)) {
                    $_SESSION['success_message'] = "Klasifikasi berhasil ditambahkan.";
                } else {
                    $_SESSION['error_message'] = "Error: " . mysqli_error($koneksi);
                }
                mysqli_stmt_close($stmt);
            }
            header("Location: ../admin/klasifikasi_surat.php");
            exit;

        case 'edit':
            $id = (int)$_POST['id'];
            $kode = mysqli_real_escape_string($koneksi, $_POST['kode']);
            $jenis_surat = mysqli_real_escape_string($koneksi, $_POST['jenis_surat']);

            if (empty($id) || empty($kode) || empty($jenis_surat)) {
                $_SESSION['error_message'] = "Data tidak lengkap.";
            } else {
                $query = "UPDATE klasifikasi_surat SET kode=?, jenis_surat=? WHERE id=?";
                $stmt = mysqli_prepare($koneksi, $query);
                mysqli_stmt_bind_param($stmt, "ssi", $kode, $jenis_surat, $id);
                if (mysqli_stmt_execute($stmt)) {
                    $_SESSION['success_message'] = "Klasifikasi berhasil diperbarui.";
                } else {
                    $_SESSION['error_message'] = "Error: " . mysqli_error($koneksi);
                }
                mysqli_stmt_close($stmt);
            }
            header("Location: ../admin/klasifikasi_surat.php");
            exit;

        case 'delete':
            $id = (int)$_POST['id'];

            if (empty($id)) {
                $_SESSION['error_message'] = "ID tidak valid.";
            } else {
                $query = "DELETE FROM klasifikasi_surat WHERE id=?";
                $stmt = mysqli_prepare($koneksi, $query);
                mysqli_stmt_bind_param($stmt, "i", $id);
                if (mysqli_stmt_execute($stmt)) {
                    $_SESSION['success_message'] = "Klasifikasi berhasil dihapus.";
                } else {
                    $_SESSION['error_message'] = "Error: " . mysqli_error($koneksi);
                }
                mysqli_stmt_close($stmt);
            }
            header("Location: ../admin/klasifikasi_surat.php");
            exit;

        default:
            $_SESSION['error_message'] = "Aksi tidak valid.";
            header("Location: ../admin/klasifikasi_surat.php");
            exit;
    }
} else {
    // Redirect jika bukan request POST
    header("Location: ../admin/klasifikasi_surat.php");
    exit();
}

mysqli_close($koneksi);
?>
