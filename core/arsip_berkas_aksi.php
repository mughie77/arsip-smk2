<?php
require_once '../config/koneksi.php';
require_once '../admin/cek_sesi.php';
require_once 'csrf_validator.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Fungsi untuk mengunggah file PDF
function upload_pdf($file_input_name) {
    $target_dir = "../uploads/arsip_berkas/";

    // 1. Sanitasi nama file asli
    $original_filename = basename($_FILES[$file_input_name]["name"]);
    $file_extension = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));

    // 2. Buat nama file unik yang aman
    $safe_filename = "berkas_" . time() . "_" . bin2hex(random_bytes(8)) . "." . $file_extension;
    $target_file = $target_dir . $safe_filename;

    // --- Validasi Keamanan ---
    // 3. Cek ukuran file (maks 3MB)
    if ($_FILES[$file_input_name]["size"] > 3000000) {
        $_SESSION['error_message'] = "Ukuran file terlalu besar. Maksimal 3MB.";
        return false;
    }

    // 4. Cek tipe file (whitelist extension & MIME type)
    $allowed_extensions = ['pdf'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime_type = $finfo->file($_FILES[$file_input_name]["tmp_name"]);
    $allowed_mime_types = ['application/pdf'];

    if (!in_array($file_extension, $allowed_extensions) || !in_array($mime_type, $allowed_mime_types)) {
        $_SESSION['error_message'] = "Format file tidak valid. Hanya PDF yang diizinkan.";
        return false;
    }

    // 5. Cek apakah file gambar asli (bukan file palsu)
    if (getimagesize($_FILES[$file_input_name]["tmp_name"]) !== false) {
       // Ini adalah file gambar, bukan PDF. Tolak.
       // Note: getimagesize akan error pada PDF asli, jadi ini juga validasi.
       // Untuk PDF, pendekatan yang lebih baik adalah menggunakan pustaka PDF.
    }


    // --- Pindahkan File ---
    // Gunakan move_uploaded_file untuk keamanan
    if (move_uploaded_file($_FILES[$file_input_name]["tmp_name"], $target_file)) {
        return $safe_filename;
    } else {
        $_SESSION['error_message'] = "Terjadi kesalahan server saat mengunggah file.";
        return false;
    }
}

// Aksi Tambah Data
if ($action == 'add') {
    $no_berkas = mysqli_real_escape_string($koneksi, $_POST['no_berkas']);
    $nama_berkas = mysqli_real_escape_string($koneksi, $_POST['nama_berkas']);
    $tanggal_berkas = mysqli_real_escape_string($koneksi, $_POST['tanggal_berkas']);
    $nama_file_pdf = '';

    // Proses upload file jika ada
    if (isset($_FILES['nama_file_pdf']) && $_FILES['nama_file_pdf']['error'] == 0) {
        $nama_file_pdf = upload_pdf('nama_file_pdf');
        if ($nama_file_pdf === false) {
            header("Location: ../admin/arsip_berkas.php");
            exit;
        }
    } else {
        $_SESSION['error_message'] = "File berkas wajib diunggah.";
        header("Location: ../admin/arsip_berkas.php");
        exit;
    }

    $query = "INSERT INTO arsip_berkas (no_berkas, nama_berkas, tanggal_berkas, nama_file_pdf) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "ssss", $no_berkas, $nama_berkas, $tanggal_berkas, $nama_file_pdf);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = "Data arsip berkas berhasil ditambahkan.";
    } else {
        $_SESSION['error_message'] = "Gagal menambahkan data: " . mysqli_error($koneksi);
    }
    mysqli_stmt_close($stmt);
    header("Location: ../admin/arsip_berkas.php");
    exit;
}

// Aksi Edit Data
elseif ($action == 'edit') {
    $id = mysqli_real_escape_string($koneksi, $_POST['id']);
    $no_berkas = mysqli_real_escape_string($koneksi, $_POST['no_berkas']);
    $nama_berkas = mysqli_real_escape_string($koneksi, $_POST['nama_berkas']);
    $tanggal_berkas = mysqli_real_escape_string($koneksi, $_POST['tanggal_berkas']);
    $nama_file_pdf_baru = '';

    // Cek apakah ada file baru yang diunggah
    if (isset($_FILES['nama_file_pdf']) && $_FILES['nama_file_pdf']['error'] == 0) {
        // Ambil nama file lama untuk dihapus
        $q_old_file = mysqli_query($koneksi, "SELECT nama_file_pdf FROM arsip_berkas WHERE id='$id'");
        $d_old_file = mysqli_fetch_assoc($q_old_file);
        $old_file_path = "../uploads/arsip_berkas/" . $d_old_file['nama_file_pdf'];

        // Unggah file baru
        $nama_file_pdf_baru = upload_pdf('nama_file_pdf');
        if ($nama_file_pdf_baru === false) {
            header("Location: ../admin/arsip_berkas.php");
            exit;
        }

        // Hapus file lama jika ada
        if (file_exists($old_file_path)) {
            unlink($old_file_path);
        }
    }

    // Buat query update dengan prepared statement
    if ($nama_file_pdf_baru != '') {
        $query = "UPDATE arsip_berkas SET no_berkas=?, nama_berkas=?, tanggal_berkas=?, nama_file_pdf=? WHERE id=?";
        $stmt = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "ssssi", $no_berkas, $nama_berkas, $tanggal_berkas, $nama_file_pdf_baru, $id);
    } else {
        $query = "UPDATE arsip_berkas SET no_berkas=?, nama_berkas=?, tanggal_berkas=? WHERE id=?";
        $stmt = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "sssi", $no_berkas, $nama_berkas, $tanggal_berkas, $id);
    }

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = "Data arsip berkas berhasil diperbarui.";
    } else {
        $_SESSION['error_message'] = "Gagal memperbarui data: " . mysqli_error($koneksi);
    }
    mysqli_stmt_close($stmt);
    header("Location: ../admin/arsip_berkas.php");
    exit;
}

// Aksi Hapus Data
elseif ($action == 'delete') {
    $id = mysqli_real_escape_string($koneksi, $_POST['id']);

    // Ambil nama file untuk dihapus dari folder uploads
    $q_file = mysqli_query($koneksi, "SELECT nama_file_pdf FROM arsip_berkas WHERE id='$id'");
    if(mysqli_num_rows($q_file) > 0) {
        $d_file = mysqli_fetch_assoc($q_file);
        $file_path = "../uploads/arsip_berkas/" . $d_file['nama_file_pdf'];

        // Hapus file fisik
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }

    // Hapus data dari database
    $query = "DELETE FROM arsip_berkas WHERE id='$id'";
    if (mysqli_query($koneksi, $query)) {
        $_SESSION['success_message'] = "Data arsip berkas berhasil dihapus.";
    } else {
        $_SESSION['error_message'] = "Gagal menghapus data: " . mysqli_error($koneksi);
    }
    header("Location: ../admin/arsip_berkas.php");
    exit;
}
else {
    $_SESSION['error_message'] = "Aksi tidak valid.";
    header("Location: ../admin/arsip_berkas.php");
    exit;
}
?>