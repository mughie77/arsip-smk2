<?php
// File: core/notulen_aksi.php

require_once '../config/koneksi.php';
require_once '../admin/cek_sesi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'csrf_validator.php';
}

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

// --- FUNGSI UNTUK MENGHANDLE UPLOAD FILE ---
function upload_file($file_input) {
    // ... (kode fungsi upload_file tidak berubah)
}

// --- FUNGSI UNTUK MENGHAPUS FILE LAMA ---
function delete_old_file($filename) {
    // ... (kode fungsi delete_old_file tidak berubah)
}

// --- ROUTING BERDASARKAN AKSI ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($action) {
        case 'add':
            $safe_post = sanitize_input($_POST);
            $kegiatan = $safe_post['kegiatan'];
            $tanggal = $safe_post['tanggal'];
            // ... (logika tambah data)
            break;

        case 'edit':
            $safe_post = sanitize_input($_POST);
            $id = intval($safe_post['id']);
            $kegiatan = $safe_post['kegiatan'];
            $tanggal = $safe_post['tanggal'];
            // ... (logika edit data)
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
