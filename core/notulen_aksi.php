<?php
// File: core/notulen_aksi.php

require_once '../config/koneksi.php';
require_once '../admin/cek_sesi.php';

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

// --- FUNGSI UNTUK MENGHANDLE UPLOAD FILE ---
function upload_file($file_input) {
    $target_dir = "../uploads/notulen/";
    $file_extension = strtolower(pathinfo($file_input["name"], PATHINFO_EXTENSION));
    $new_file_name = "NTL-" . date("Ymd-His") . "-" . uniqid() . "." . $file_extension;
    $target_file = $target_dir . $new_file_name;

    // Validasi file
    // 1. Cek ukuran file (maks 3MB)
    if ($file_input["size"] > 3000000) {
        return ['status' => 'error', 'message' => 'Ukuran file terlalu besar. Maksimal 3MB.'];
    }
    // 2. Cek tipe file (PDF, DOC, DOCX)
    $allowed_types = ['pdf', 'doc', 'docx'];
    if (!in_array($file_extension, $allowed_types)) {
        return ['status' => 'error', 'message' => 'Hanya file format PDF, DOC, atau DOCX yang diizinkan.'];
    }
    // 3. Pindahkan file
    if (move_uploaded_file($file_input["tmp_name"], $target_file)) {
        return ['status' => 'success', 'filename' => $new_file_name];
    } else {
        return ['status' => 'error', 'message' => 'Terjadi kesalahan saat mengunggah file.'];
    }
}

// --- FUNGSI UNTUK MENGHAPUS FILE LAMA ---
function delete_old_file($filename) {
    $filepath = "../uploads/notulen/" . $filename;
    if (file_exists($filepath)) {
        unlink($filepath);
    }
}

// --- ROUTING BERDASARKAN AKSI ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $safe_post = sanitize_input($_POST);
    $kegiatan = $safe_post['kegiatan'];
    $tanggal = $safe_post['tanggal'];

    switch ($action) {
        case 'add':
            if (!isset($_FILES['nama_file']) || $_FILES['nama_file']['error'] == UPLOAD_ERR_NO_FILE) {
                header("Location: ../admin/notulen?error=File wajib diunggah.");
                exit();
            }

            $upload_result = upload_file($_FILES['nama_file']);
            if ($upload_result['status'] == 'error') {
                header("Location: ../admin/notulen?error=" . urlencode($upload_result['message']));
                exit();
            }
            $nama_file = $upload_result['filename'];

            $sql = "INSERT INTO notulen (tanggal, kegiatan, nama_file) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($koneksi, $sql);
            mysqli_stmt_bind_param($stmt, "sss", $tanggal, $kegiatan, $nama_file);

            if(mysqli_stmt_execute($stmt)){
                header("Location: ../admin/notulen?success=Data berhasil ditambahkan.");
            } else {
                header("Location: ../admin/notulen?error=Gagal menyimpan data.");
            }
            mysqli_stmt_close($stmt);
            break;

        case 'edit':
            $id = intval($safe_post['id']);

            // Inisialisasi query dan parameter
            $sql_parts = [
                "tanggal=?",
                "kegiatan=?"
            ];
            $params = [
                $tanggal,
                $kegiatan
            ];
            $types = "ss";

            // Cek apakah ada file baru yang diunggah
            if (isset($_FILES['nama_file']) && $_FILES['nama_file']['error'] == UPLOAD_ERR_OK) {
                // Ambil nama file lama untuk dihapus
                $sql_get_file = "SELECT nama_file FROM notulen WHERE id=?";
                $stmt_get_file = mysqli_prepare($koneksi, $sql_get_file);
                mysqli_stmt_bind_param($stmt_get_file, "i", $id);
                mysqli_stmt_execute($stmt_get_file);
                $result_get_file = mysqli_stmt_get_result($stmt_get_file);
                if ($row = mysqli_fetch_assoc($result_get_file)) {
                    delete_old_file($row['nama_file']);
                }
                mysqli_stmt_close($stmt_get_file);

                // Unggah file baru
                $upload_result = upload_file($_FILES['nama_file']);
                if ($upload_result['status'] == 'error') {
                    header("Location: ../admin/notulen?error=" . urlencode($upload_result['message']));
                    exit();
                }

                // Tambahkan field file ke query
                $sql_parts[] = "nama_file=?";
                $params[] = $upload_result['filename'];
                $types .= "s";
            }

            // Tambahkan ID ke parameter
            $params[] = $id;
            $types .= "i";

            // Bangun query final
            $sql = "UPDATE notulen SET " . implode(", ", $sql_parts) . " WHERE id=?";

            $stmt = mysqli_prepare($koneksi, $sql);
            mysqli_stmt_bind_param($stmt, $types, ...$params);

            if(mysqli_stmt_execute($stmt)){
                header("Location: ../admin/notulen?success=Data berhasil diperbarui.");
            } else {
                header("Location: ../admin/notulen?error=Gagal memperbarui data.");
            }
            mysqli_stmt_close($stmt);
            break;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'delete') {
    $id = intval($_GET['id']);

    $sql_get_file = "SELECT nama_file FROM notulen WHERE id=?";
    $stmt_get_file = mysqli_prepare($koneksi, $sql_get_file);
    mysqli_stmt_bind_param($stmt_get_file, "i", $id);
    mysqli_stmt_execute($stmt_get_file);
    $result_get_file = mysqli_stmt_get_result($stmt_get_file);
    if($row = mysqli_fetch_assoc($result_get_file)){
        delete_old_file($row['nama_file']);
    }
    mysqli_stmt_close($stmt_get_file);

    $sql = "DELETE FROM notulen WHERE id=?";
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);

    if(mysqli_stmt_execute($stmt)){
        header("Location: ../admin/notulen?success=Data berhasil dihapus.");
    } else {
        header("Location: ../admin/notulen?error=Gagal menghapus data.");
    }
    mysqli_stmt_close($stmt);

} else {
    header("Location: ../admin/notulen");
    exit();
}

mysqli_close($koneksi);
?>
