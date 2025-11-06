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
        case 'edit':
            // Ambil data dari form
            $id = (int)($_POST['id'] ?? 0);
            $nomor_surat = trim($_POST['nomor_surat'] ?? '');
            $klasifikasi_id = (int)($_POST['klasifikasi_id'] ?? 0);
            $tujuan_surat = trim($_POST['tujuan_surat'] ?? '');
            $perihal = trim($_POST['perihal'] ?? '');
            $tanggal_kirim = trim($_POST['tanggal_kirim'] ?? '');
            $nama_file_pdf_existing = trim($_POST['nama_file_pdf_existing'] ?? '');

            // ... (logika validasi, generate kode arsip, dan upload file)

            if ($action == 'add') {
                // ... (logika tambah data)
            } else { // edit
                // ... (logika edit data)
            }
            break;

        case 'delete':
            // Logika Hapus Data
            $id = (int)($_POST['id'] ?? 0);
            if ($id === 0) {
                $_SESSION['error_message'] = "ID tidak valid.";
                header("Location: ../admin/surat_keluar.php");
                exit;
            }

            // ... (logika hapus data)
            break;

        default:
            $_SESSION['error_message'] = "Aksi tidak valid.";
            header("Location: ../admin/index.php");
            exit;
    }
} else {
    // Redirect jika bukan request POST
    header("Location: ../admin/surat_keluar.php");
    exit();
}

mysqli_close($koneksi);
?>
