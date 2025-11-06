<?php
require_once '../config/koneksi.php';
require_once '../admin/cek_sesi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'csrf_validator.php';
}

$action = $_REQUEST['action'] ?? '';

// ... (definisi fungsi upload_pdf)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($action) {
        case 'add':
        case 'edit':
            // ... (logika add/edit)
            break;

        case 'delete':
            $id = (int)$_POST['id'];

            // Ambil nama file untuk dihapus dari folder uploads
            $query = "SELECT nama_file_pdf FROM arsip_berkas WHERE id=?";
            $stmt = mysqli_prepare($koneksi, $query);
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if ($row = mysqli_fetch_assoc($result)) {
                $file_path = "../uploads/arsip_berkas/" . $row['nama_file_pdf'];
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }
            mysqli_stmt_close($stmt);

            // Hapus data dari database
            $query = "DELETE FROM arsip_berkas WHERE id=?";
            $stmt = mysqli_prepare($koneksi, $query);
            mysqli_stmt_bind_param($stmt, "i", $id);
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success_message'] = "Data arsip berkas berhasil dihapus.";
            } else {
                $_SESSION['error_message'] = "Gagal menghapus data: " . mysqli_error($koneksi);
            }
            mysqli_stmt_close($stmt);

            header("Location: ../admin/arsip_berkas.php");
            exit;

        default:
            $_SESSION['error_message'] = "Aksi tidak valid.";
            header("Location: ../admin/arsip_berkas.php");
            exit;
    }
} else {
    // Redirect jika bukan request POST
    header("Location: ../admin/arsip_berkas.php");
    exit();
}
?>
