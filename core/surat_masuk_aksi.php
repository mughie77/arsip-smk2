<?php
// File: core/surat_masuk_aksi.php
/*
 * File ini bertindak sebagai controller untuk semua aksi CRUD (Create, Read, Update, Delete)
 * yang berkaitan dengan data Surat Masuk.
 * Penggunaan parameter 'action' (via POST atau GET) menentukan operasi yang akan dijalankan.
 */

require_once '../config/koneksi.php';
require_once '../admin/cek_sesi.php'; // Memastikan hanya admin yang bisa mengakses skrip ini

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

/**
 * Mengelola unggahan file PDF.
 * Termasuk validasi ukuran, tipe file, dan pembuatan nama file yang unik.
 *
 * @param array $file_input Data file dari $_FILES.
 * @return array Status hasil upload, berisi 'status' dan 'message' atau 'filename'.
 */
function upload_file($file_input) {
    $target_dir = "../uploads/surat_masuk/";
    // Buat nama file unik untuk menghindari konflik
    $file_extension = strtolower(pathinfo($file_input["name"], PATHINFO_EXTENSION));
    $new_file_name = "SM-" . date("Ymd-His") . "-" . uniqid() . "." . $file_extension;
    $target_file = $target_dir . $new_file_name;

    // Validasi file
    // 1. Cek ukuran file (maks 3MB)
    if ($file_input["size"] > 3000000) {
        return ['status' => 'error', 'message' => 'Ukuran file terlalu besar. Maksimal 3MB.'];
    }
    // 2. Cek tipe file (hanya PDF)
    if ($file_extension != "pdf") {
        return ['status' => 'error', 'message' => 'Hanya file format PDF yang diizinkan.'];
    }
    // 3. Pindahkan file
    if (move_uploaded_file($file_input["tmp_name"], $target_file)) {
        return ['status' => 'success', 'filename' => $new_file_name];
    } else {
        return ['status' => 'error', 'message' => 'Terjadi kesalahan saat mengunggah file.'];
    }
}

/**
 * Menghapus file fisik dari server.
 * Digunakan saat data dihapus atau saat file diganti pada proses edit.
 *
 * @param string $filename Nama file yang akan dihapus.
 */
function delete_old_file($filename) {
    $filepath = "../uploads/surat_masuk/" . $filename;
    if (file_exists($filepath) && !empty($filename)) {
        unlink($filepath);
    }
}


// --- ROUTING AKSI UTAMA ---
// Memproses permintaan berdasarkan metode (POST untuk add/edit, GET untuk delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil dan sanitasi data POST
    $safe_post = sanitize_input($_POST);
    $nomor_surat = $safe_post['nomor_surat'];
    $perihal = $safe_post['perihal'];
    $asal_surat = $safe_post['asal_surat'];
    $tanggal_diterima = $safe_post['tanggal_diterima'];
    $acc_kepada = $safe_post['acc_kepada'];

    switch ($action) {
        // --- AKSI TAMBAH DATA ---
        // --- AKSI TAMBAH DATA ---
        case 'add':
            $nama_file_pdf = ''; // Default value jika tidak ada file
            // Cek apakah ada file yang diunggah dan tidak ada error
            if (isset($_FILES['nama_file_pdf']) && $_FILES['nama_file_pdf']['error'] == UPLOAD_ERR_OK) {
                // Proses upload file
                $upload_result = upload_file($_FILES['nama_file_pdf']);
                if ($upload_result['status'] == 'error') {
                    header("Location: ../admin/surat_masuk?error=" . urlencode($upload_result['message']));
                    exit();
                }
                $nama_file_pdf = $upload_result['filename'];
            }

            $nomor_arsip = generate_nomor_arsip('SM'); // Buat nomor arsip unik

            // Query INSERT dengan prepared statement untuk keamanan
            $sql = "INSERT INTO surat_masuk (nomor_arsip, nomor_surat, perihal, asal_surat, tanggal_diterima, acc_kepada, nama_file_pdf) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($koneksi, $sql);
            mysqli_stmt_bind_param($stmt, "sssssss", $nomor_arsip, $nomor_surat, $perihal, $asal_surat, $tanggal_diterima, $acc_kepada, $nama_file_pdf);

            if(mysqli_stmt_execute($stmt)){
                header("Location: ../admin/surat_masuk?success=Data berhasil ditambahkan.");
            } else {
                header("Location: ../admin/surat_masuk?error=Gagal menyimpan data.");
            }
            mysqli_stmt_close($stmt);
            break;

        // --- AKSI EDIT DATA ---
        case 'edit':
            $id = intval($safe_post['id']);
            $nama_file_pdf_lama = '';

            // Langkah 1: Ambil nama file yang saat ini ada di database untuk referensi
            $sql_get_file = "SELECT nama_file_pdf FROM surat_masuk WHERE id=?";
            $stmt_get_file = mysqli_prepare($koneksi, $sql_get_file);
            mysqli_stmt_bind_param($stmt_get_file, "i", $id);
            mysqli_stmt_execute($stmt_get_file);
            $result_get_file = mysqli_stmt_get_result($stmt_get_file);
            if($row = mysqli_fetch_assoc($result_get_file)){
                $nama_file_pdf_lama = $row['nama_file_pdf'];
            }
            mysqli_stmt_close($stmt_get_file);

            $nama_file_pdf_baru = $nama_file_pdf_lama;

            // Langkah 2: Cek apakah user mengunggah file baru
            if (isset($_FILES['nama_file_pdf']) && $_FILES['nama_file_pdf']['error'] == UPLOAD_ERR_OK) {
                // Jika ya, proses upload file baru
                $upload_result = upload_file($_FILES['nama_file_pdf']);
                if ($upload_result['status'] == 'error') {
                    header("Location: ../admin/surat_masuk?error=" . urlencode($upload_result['message']));
                    exit();
                }
                $nama_file_pdf_baru = $upload_result['filename'];
                // Langkah 3: Hapus file lama setelah file baru berhasil diunggah
                delete_old_file($nama_file_pdf_lama);
            }

            // Langkah 4: Update data di database dengan prepared statement
            $sql = "UPDATE surat_masuk SET nomor_surat=?, perihal=?, asal_surat=?, tanggal_diterima=?, acc_kepada=?, nama_file_pdf=? WHERE id=?";
            $stmt = mysqli_prepare($koneksi, $sql);
            mysqli_stmt_bind_param($stmt, "ssssssi", $nomor_surat, $perihal, $asal_surat, $tanggal_diterima, $acc_kepada, $nama_file_pdf_baru, $id);

            if(mysqli_stmt_execute($stmt)){
                header("Location: ../admin/surat_masuk?success=Data berhasil diperbarui.");
            } else {
                header("Location: ../admin/surat_masuk?error=Gagal memperbarui data.");
            }
            mysqli_stmt_close($stmt);
            break;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'delete') {
    // --- AKSI HAPUS DATA (melalui metode GET dari link) ---
    $id = intval($_GET['id']);

    // Langkah 1: Ambil nama file dari DB agar bisa dihapus dari server
    $sql_get_file = "SELECT nama_file_pdf FROM surat_masuk WHERE id=?";
    $stmt_get_file = mysqli_prepare($koneksi, $sql_get_file);
    mysqli_stmt_bind_param($stmt_get_file, "i", $id);
    mysqli_stmt_execute($stmt_get_file);
    $result_get_file = mysqli_stmt_get_result($stmt_get_file);
    if($row = mysqli_fetch_assoc($result_get_file)){
        // Langkah 2: Hapus file fisik dari folder uploads
        delete_old_file($row['nama_file_pdf']);
    }
    mysqli_stmt_close($stmt_get_file);

    // Langkah 3: Hapus record dari database
    $sql = "DELETE FROM surat_masuk WHERE id=?";
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);

    if(mysqli_stmt_execute($stmt)){
        header("Location: ../admin/surat_masuk?success=Data berhasil dihapus.");
    } else {
        header("Location: ../admin/surat_masuk?error=Gagal menghapus data.");
    }
    mysqli_stmt_close($stmt);

} else {
    header("Location: ../admin/surat_masuk");
    exit();
}

mysqli_close($koneksi);
?>
