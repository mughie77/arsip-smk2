<?php
// File: core/notulen_aksi.php

require_once '../config/koneksi.php';
require_once '../admin/cek_sesi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'csrf_validator.php';
}

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

// ... (definisi fungsi upload_file dan delete_old_file)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($action) {
        case 'add':
        case 'edit':
            $safe_post = sanitize_input($_POST);
            $kegiatan = $safe_post['kegiatan'];
            $tanggal = $safe_post['tanggal'];

            if ($action == 'add') {
                // ... (logika tambah data)
            } else { // edit
                // ... (logika edit data)
            }
            break;

        case 'delete':
            $id = intval($_POST['id']);
            // ... (logika hapus data)
            break;

        default:
            header("Location: ../admin/notulen?error=Aksi tidak valid.");
            break;
    }
} else {
    header("Location: ../admin/notulen");
    exit();
}

mysqli_close($koneksi);
?>
