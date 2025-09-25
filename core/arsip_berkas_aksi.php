<?php
require_once '../config/koneksi.php';
require_once '../admin/cek_sesi.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Fungsi untuk mengunggah file PDF
function upload_pdf($file_input_name) {
    $target_dir = "../uploads/arsip_berkas/";
    // Buat nama file unik untuk menghindari penimpaan file
    $file_extension = strtolower(pathinfo($_FILES[$file_input_name]["name"], PATHINFO_EXTENSION));
    $unique_filename = "berkas_" . time() . "_" . uniqid() . "." . $file_extension;
    $target_file = $target_dir . $unique_filename;
    $uploadOk = 1;

    // Cek apakah file adalah PDF
    if($file_extension != "pdf") {
        $_SESSION['error_message'] = "Hanya file PDF yang diizinkan.";
        $uploadOk = 0;
    }

    // Cek ukuran file (misal: max 3MB)
    if ($_FILES[$file_input_name]["size"] > 3000000) {
        $_SESSION['error_message'] = "Ukuran file terlalu besar. Maksimal 3MB.";
        $uploadOk = 0;
    }

    // Jika semua pengecekan lolos, coba unggah file
    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES[$file_input_name]["tmp_name"], $target_file)) {
            return $unique_filename;
        } else {
            $_SESSION['error_message'] = "Terjadi kesalahan saat mengunggah file.";
            return false;
        }
    }
    return false;
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

    $query = "INSERT INTO arsip_berkas (no_berkas, nama_berkas, tanggal_berkas, nama_file_pdf) VALUES ('$no_berkas', '$nama_berkas', '$tanggal_berkas', '$nama_file_pdf')";

    if (mysqli_query($koneksi, $query)) {
        $_SESSION['success_message'] = "Data arsip berkas berhasil ditambahkan.";
    } else {
        $_SESSION['error_message'] = "Gagal menambahkan data: " . mysqli_error($koneksi);
    }
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

    // Buat query update
    $query = "UPDATE arsip_berkas SET
                no_berkas='$no_berkas',
                nama_berkas='$nama_berkas',
                tanggal_berkas='$tanggal_berkas'";

    if ($nama_file_pdf_baru != '') {
        $query .= ", nama_file_pdf='$nama_file_pdf_baru'";
    }

    $query .= " WHERE id='$id'";

    if (mysqli_query($koneksi, $query)) {
        $_SESSION['success_message'] = "Data arsip berkas berhasil diperbarui.";
    } else {
        $_SESSION['error_message'] = "Gagal memperbarui data: " . mysqli_error($koneksi);
    }
    header("Location: ../admin/arsip_berkas.php");
    exit;
}

// Aksi Hapus Data
elseif ($action == 'delete') {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);

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