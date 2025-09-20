<?php
// File: core/surat_keluar_aksi.php

require_once '../config/koneksi.php';
require_once '../admin/cek_sesi.php';

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

// --- FUNGSI UNTUK MENGHANDLE UPLOAD FILE ---
function upload_file($file_input) {
    $target_dir = "../uploads/surat_keluar/";
    $file_extension = strtolower(pathinfo($file_input["name"], PATHINFO_EXTENSION));
    $new_file_name = "SK-" . date("Ymd-His") . "-" . uniqid() . "." . $file_extension;
    $target_file = $target_dir . $new_file_name;

    if ($file_input["size"] > 3000000) {
        return ['status' => 'error', 'message' => 'Ukuran file terlalu besar. Maksimal 3MB.'];
    }
    if ($file_extension != "pdf") {
        return ['status' => 'error', 'message' => 'Hanya file format PDF yang diizinkan.'];
    }
    if (move_uploaded_file($file_input["tmp_name"], $target_file)) {
        return ['status' => 'success', 'filename' => $new_file_name];
    } else {
        return ['status' => 'error', 'message' => 'Terjadi kesalahan saat mengunggah file.'];
    }
}

// --- FUNGSI UNTUK MENGHAPUS FILE LAMA ---
function delete_old_file($filename) {
    $filepath = "../uploads/surat_keluar/" . $filename;
    if (file_exists($filepath)) {
        unlink($filepath);
    }
}

// --- ROUTING BERDASARKAN AKSI ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $safe_post = sanitize_input($_POST);
    $nomor_surat = $safe_post['nomor_surat'];
    $perihal = $safe_post['perihal'];
    $tujuan_surat = $safe_post['tujuan_surat'];
    $tanggal_kirim = $safe_post['tanggal_kirim'];
    $acc_kepada = $safe_post['acc_kepada'];

    switch ($action) {
        case 'add':
            if (!isset($_FILES['nama_file_pdf']) || $_FILES['nama_file_pdf']['error'] == UPLOAD_ERR_NO_FILE) {
                header("Location: ../admin/surat_keluar?error=File PDF wajib diunggah.");
                exit();
            }

            $upload_result = upload_file($_FILES['nama_file_pdf']);
            if ($upload_result['status'] == 'error') {
                header("Location: ../admin/surat_keluar?error=" . urlencode($upload_result['message']));
                exit();
            }
            $nama_file_pdf = $upload_result['filename'];
            $nomor_arsip = generate_nomor_arsip('SK');

            $sql = "INSERT INTO surat_keluar (nomor_arsip, nomor_surat, perihal, tujuan_surat, tanggal_kirim, acc_kepada, nama_file_pdf) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($koneksi, $sql);
            mysqli_stmt_bind_param($stmt, "sssssss", $nomor_arsip, $nomor_surat, $perihal, $tujuan_surat, $tanggal_kirim, $acc_kepada, $nama_file_pdf);

            if(mysqli_stmt_execute($stmt)){
                header("Location: ../admin/surat_keluar?success=Data berhasil ditambahkan.");
            } else {
                header("Location: ../admin/surat_keluar?error=Gagal menyimpan data.");
            }
            mysqli_stmt_close($stmt);
            break;

        case 'edit':
            $id = intval($safe_post['id']);
            $nama_file_pdf_lama = '';

            $sql_get_file = "SELECT nama_file_pdf FROM surat_keluar WHERE id=?";
            $stmt_get_file = mysqli_prepare($koneksi, $sql_get_file);
            mysqli_stmt_bind_param($stmt_get_file, "i", $id);
            mysqli_stmt_execute($stmt_get_file);
            $result_get_file = mysqli_stmt_get_result($stmt_get_file);
            if($row = mysqli_fetch_assoc($result_get_file)){
                $nama_file_pdf_lama = $row['nama_file_pdf'];
            }
            mysqli_stmt_close($stmt_get_file);

            $nama_file_pdf_baru = $nama_file_pdf_lama;

            if (isset($_FILES['nama_file_pdf']) && $_FILES['nama_file_pdf']['error'] == UPLOAD_ERR_OK) {
                $upload_result = upload_file($_FILES['nama_file_pdf']);
                if ($upload_result['status'] == 'error') {
                    header("Location: ../admin/surat_keluar?error=" . urlencode($upload_result['message']));
                    exit();
                }
                $nama_file_pdf_baru = $upload_result['filename'];
                delete_old_file($nama_file_pdf_lama);
            }

            $sql = "UPDATE surat_keluar SET nomor_surat=?, perihal=?, tujuan_surat=?, tanggal_kirim=?, acc_kepada=?, nama_file_pdf=? WHERE id=?";
            $stmt = mysqli_prepare($koneksi, $sql);
            mysqli_stmt_bind_param($stmt, "ssssssi", $nomor_surat, $perihal, $tujuan_surat, $tanggal_kirim, $acc_kepada, $nama_file_pdf_baru, $id);

            if(mysqli_stmt_execute($stmt)){
                header("Location: ../admin/surat_keluar?success=Data berhasil diperbarui.");
            } else {
                header("Location: ../admin/surat_keluar?error=Gagal memperbarui data.");
            }
            mysqli_stmt_close($stmt);
            break;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'delete') {
    $id = intval($_GET['id']);

    $sql_get_file = "SELECT nama_file_pdf FROM surat_keluar WHERE id=?";
    $stmt_get_file = mysqli_prepare($koneksi, $sql_get_file);
    mysqli_stmt_bind_param($stmt_get_file, "i", $id);
    mysqli_stmt_execute($stmt_get_file);
    $result_get_file = mysqli_stmt_get_result($stmt_get_file);
    if($row = mysqli_fetch_assoc($result_get_file)){
        delete_old_file($row['nama_file_pdf']);
    }
    mysqli_stmt_close($stmt_get_file);

    $sql = "DELETE FROM surat_keluar WHERE id=?";
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);

    if(mysqli_stmt_execute($stmt)){
        header("Location: ../admin/surat_keluar?success=Data berhasil dihapus.");
    } else {
        header("Location: ../admin/surat_keluar?error=Gagal menghapus data.");
    }
    mysqli_stmt_close($stmt);

} else {
    header("Location: ../admin/surat_keluar");
    exit();
}

mysqli_close($koneksi);
?>
