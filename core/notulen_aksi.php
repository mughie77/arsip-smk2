<?php
// File: core/notulen_aksi.php

require_once '../config/koneksi.php';
require_once '../admin/cek_sesi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'csrf_validator.php';
}

$action = $_REQUEST['action'] ?? '';

// --- FUNGSI UNTUK MENGHANDLE UPLOAD FILE ---
function upload_file($file_input) {
    $target_dir = "../uploads/notulen/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    $file_extension = strtolower(pathinfo($file_input["name"], PATHINFO_EXTENSION));
    $new_file_name = "NTL-" . date("Ymd-His") . "-" . uniqid() . "." . $file_extension;
    $target_file = $target_dir . $new_file_name;

    // Validasi file
    if ($file_input["size"] > 3 * 1024 * 1024) { // Maks 3MB
        return ['status' => 'error', 'message' => 'Ukuran file terlalu besar. Maksimal 3MB.'];
    }
    $allowed_types = ['pdf', 'doc', 'docx'];
    if (!in_array($file_extension, $allowed_types)) {
        return ['status' => 'error', 'message' => 'Hanya file format PDF, DOC, atau DOCX yang diizinkan.'];
    }

    // Validasi tipe MIME
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file_input['tmp_name']);
    finfo_close($finfo);
    $allowed_mime_types = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    if (!in_array($mime_type, $allowed_mime_types)) {
        return ['status' => 'error', 'message' => 'Tipe file tidak valid.'];
    }

    if (move_uploaded_file($file_input["tmp_name"], $target_file)) {
        return ['status' => 'success', 'filename' => $new_file_name];
    } else {
        return ['status' => 'error', 'message' => 'Terjadi kesalahan saat mengunggah file.'];
    }
}

// --- FUNGSI UNTUK MENGHAPUS FILE LAMA ---
function delete_old_file($filename) {
    if (empty($filename)) return;
    $filepath = "../uploads/notulen/" . $filename;
    if (file_exists($filepath)) {
        unlink($filepath);
    }
}

// --- ROUTING BERDASARKAN AKSI ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($action) {
        case 'add':
            $kegiatan = trim($_POST['kegiatan'] ?? '');
            $tanggal = trim($_POST['tanggal'] ?? '');

            if (empty($kegiatan) || empty($tanggal)) {
                 $_SESSION['error_message'] = "Kegiatan dan Tanggal tidak boleh kosong.";
                 header("Location: ../admin/notulen.php");
                 exit;
            }

            $nama_file = '';
            if (isset($_FILES['nama_file']) && $_FILES['nama_file']['error'] == UPLOAD_ERR_OK) {
                $upload_result = upload_file($_FILES['nama_file']);
                if ($upload_result['status'] == 'error') {
                    $_SESSION['error_message'] = $upload_result['message'];
                    header("Location: ../admin/notulen.php");
                    exit;
                }
                $nama_file = $upload_result['filename'];
            }

            $stmt = $koneksi->prepare("INSERT INTO notulen (tanggal, kegiatan, nama_file) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $tanggal, $kegiatan, $nama_file);

            if($stmt->execute()){
                $_SESSION['success_message'] = "Data notulen berhasil ditambahkan.";
            } else {
                $_SESSION['error_message'] = "Gagal menyimpan data: " . $stmt->error;
            }
            $stmt->close();
            header("Location: ../admin/notulen.php");
            exit;

        case 'edit':
            $id = (int)($_POST['id'] ?? 0);
            $kegiatan = trim($_POST['kegiatan'] ?? '');
            $tanggal = trim($_POST['tanggal'] ?? '');
            $nama_file_lama = trim($_POST['nama_file_existing'] ?? '');

            if (empty($id) || empty($kegiatan) || empty($tanggal)) {
                $_SESSION['error_message'] = "Data tidak lengkap.";
                header("Location: ../admin/notulen.php");
                exit;
            }

            $nama_file_baru = $nama_file_lama;
            if (isset($_FILES['nama_file']) && $_FILES['nama_file']['error'] == UPLOAD_ERR_OK) {
                $upload_result = upload_file($_FILES['nama_file']);
                if ($upload_result['status'] == 'error') {
                     $_SESSION['error_message'] = $upload_result['message'];
                     header("Location: ../admin/notulen.php");
                     exit;
                }
                $nama_file_baru = $upload_result['filename'];
                delete_old_file($nama_file_lama);
            }

            $stmt = $koneksi->prepare("UPDATE notulen SET tanggal=?, kegiatan=?, nama_file=? WHERE id=?");
            $stmt->bind_param("sssi", $tanggal, $kegiatan, $nama_file_baru, $id);

            if($stmt->execute()){
                $_SESSION['success_message'] = "Data notulen berhasil diperbarui.";
            } else {
                 $_SESSION['error_message'] = "Gagal memperbarui data: " . $stmt->error;
            }
            $stmt->close();
            header("Location: ../admin/notulen.php");
            exit;

        case 'delete':
            $id = (int)($_POST['id'] ?? 0);
            if ($id === 0) {
                $_SESSION['error_message'] = "ID tidak valid.";
                header("Location: ../admin/notulen.php");
                exit;
            }

            $stmt_get = $koneksi->prepare("SELECT nama_file FROM notulen WHERE id=?");
            $stmt_get->bind_param("i", $id);
            $stmt_get->execute();
            $result = $stmt_get->get_result();
            if($row = $result->fetch_assoc()){
                delete_old_file($row['nama_file']);
            }
            $stmt_get->close();

            $stmt_del = $koneksi->prepare("DELETE FROM notulen WHERE id=?");
            $stmt_del->bind_param("i", $id);

            if($stmt_del->execute()){
                $_SESSION['success_message'] = "Data notulen berhasil dihapus.";
            } else {
                $_SESSION['error_message'] = "Gagal menghapus data: " . $stmt_del->error;
            }
            $stmt_del->close();
            header("Location: ../admin/notulen.php");
            exit;

        default:
            $_SESSION['error_message'] = "Aksi tidak valid.";
            header("Location: ../admin/notulen.php");
            exit;
    }
} else {
    header("Location: ../admin/notulen.php");
    exit();
}
?>
