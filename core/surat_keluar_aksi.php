<?php
require_once '../config/koneksi.php';
require_once '../admin/cek_sesi.php';

$action = $_REQUEST['action'] ?? '';

// Aksi Tambah atau Edit
if ($action == 'add' || $action == 'edit') {
    // Ambil data dari form
    $id = (int)($_POST['id'] ?? 0);
    $nomor_surat = trim($_POST['nomor_surat'] ?? '');
    $klasifikasi_id = (int)($_POST['klasifikasi_id'] ?? 0);
    $tujuan_surat = trim($_POST['tujuan_surat'] ?? '');
    $perihal = trim($_POST['perihal'] ?? '');
    $tanggal_kirim = trim($_POST['tanggal_kirim'] ?? '');
    $nama_file_pdf_existing = trim($_POST['nama_file_pdf_existing'] ?? '');

    // Validasi dasar
    if (empty($nomor_surat) || empty($klasifikasi_id) || empty($tujuan_surat) || empty($perihal) || empty($tanggal_kirim)) {
        $_SESSION['error_message'] = "Semua field wajib diisi.";
        header("Location: ../admin/surat_keluar.php");
        exit;
    }

    // --- GENERATE KODE ARSIP ---
    $kode_klasifikasi = '';
    $stmt_klas = mysqli_prepare($koneksi, "SELECT kode FROM klasifikasi_surat WHERE id = ?");
    mysqli_stmt_bind_param($stmt_klas, "i", $klasifikasi_id);
    mysqli_stmt_execute($stmt_klas);
    $res_klas = mysqli_stmt_get_result($stmt_klas);
    if ($klas = mysqli_fetch_assoc($res_klas)) $kode_klasifikasi = $klas['kode'];
    mysqli_stmt_close($stmt_klas);
    if (empty($kode_klasifikasi)) {
        $_SESSION['error_message'] = "Klasifikasi tidak valid.";
        header("Location: ../admin/surat_keluar.php");
        exit;
    }

    $kode_sekolah = '';
    $stmt_set = mysqli_prepare($koneksi, "SELECT kode_sekolah FROM pengaturan WHERE id = 1");
    mysqli_stmt_execute($stmt_set);
    $res_set = mysqli_stmt_get_result($stmt_set);
    if ($set = mysqli_fetch_assoc($res_set)) $kode_sekolah = $set['kode_sekolah'];
    mysqli_stmt_close($stmt_set);
    if (empty($kode_sekolah)) {
        $_SESSION['error_message'] = "Pengaturan sekolah tidak ditemukan.";
        header("Location: ../admin/surat_keluar.php");
        exit;
    }

    $tahun = date('Y', strtotime($tanggal_kirim));
    $kode_arsip = "$kode_klasifikasi-$nomor_surat-$kode_sekolah-$tahun";
    // --- END GENERATE KODE ARSIP ---

    // --- LOGIKA UPLOAD FILE ---
    $nama_file_pdf_final = $nama_file_pdf_existing;
    if (isset($_FILES['nama_file_pdf']) && $_FILES['nama_file_pdf']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/surat_keluar/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

        if ($_FILES['nama_file_pdf']['size'] > 3 * 1024 * 1024) {
            $_SESSION['error_message'] = "Ukuran file maksimal 3 MB.";
            header("Location: ../admin/surat_keluar.php");
            exit;
        }
        if (strtolower(pathinfo($_FILES['nama_file_pdf']['name'], PATHINFO_EXTENSION)) != 'pdf') {
            $_SESSION['error_message'] = "Hanya file PDF yang diizinkan.";
            header("Location: ../admin/surat_keluar.php");
            exit;
        }

        $new_file_name = uniqid() . '_' . time() . '.pdf';
        if (move_uploaded_file($_FILES['nama_file_pdf']['tmp_name'], $upload_dir . $new_file_name)) {
            // Hapus file lama jika ada dan jika upload baru berhasil
            if (!empty($nama_file_pdf_existing)) {
                unlink($upload_dir . $nama_file_pdf_existing);
            }
            $nama_file_pdf_final = $new_file_name;
        } else {
            $_SESSION['error_message'] = "Gagal mengunggah file baru.";
            header("Location: ../admin/surat_keluar.php");
            exit;
        }
    }

    if ($action == 'add') {
        if (empty($nama_file_pdf_final)) {
            $_SESSION['error_message'] = "File PDF wajib diunggah.";
            header("Location: ../admin/surat_keluar.php");
            exit;
        }

        $query = "INSERT INTO surat_keluar (kode_arsip, nomor_surat, perihal, tujuan_surat, tanggal_kirim, klasifikasi_id, nama_file_pdf)
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "sssssis", $kode_arsip, $nomor_surat, $perihal, $tujuan_surat, $tanggal_kirim, $klasifikasi_id, $nama_file_pdf_final);
        $success_msg = "Surat keluar berhasil ditambahkan.";

    } else { // edit
        $query = "UPDATE surat_keluar SET kode_arsip=?, nomor_surat=?, perihal=?, tujuan_surat=?, tanggal_kirim=?, klasifikasi_id=?, nama_file_pdf=? WHERE id=?";
        $stmt = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "sssssisi", $kode_arsip, $nomor_surat, $perihal, $tujuan_surat, $tanggal_kirim, $klasifikasi_id, $nama_file_pdf_final, $id);
        $success_msg = "Surat keluar berhasil diperbarui.";
    }

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = $success_msg;
    } else {
        $_SESSION['error_message'] = "Error: " . mysqli_stmt_error($stmt);
    }
    mysqli_stmt_close($stmt);
    header("Location: ../admin/surat_keluar.php");
    exit;
}

// Hapus Surat Keluar
elseif ($action == 'delete') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id === 0) {
        $_SESSION['error_message'] = "ID tidak valid.";
        header("Location: ../admin/surat_keluar.php");
        exit;
    }

    // Ambil nama file untuk dihapus dari DB
    $stmt = mysqli_prepare($koneksi, "SELECT nama_file_pdf FROM surat_keluar WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($row = mysqli_fetch_assoc($result)) {
        if (!empty($row['nama_file_pdf'])) {
            $file_path = '../uploads/surat_keluar/' . $row['nama_file_pdf'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
    }
    mysqli_stmt_close($stmt);

    // Hapus record dari database
    $stmt = mysqli_prepare($koneksi, "DELETE FROM surat_keluar WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = "Surat keluar berhasil dihapus.";
    } else {
        $_SESSION['error_message'] = "Error saat menghapus: " . mysqli_stmt_error($stmt);
    }
    mysqli_stmt_close($stmt);
    header("Location: ../admin/surat_keluar.php");
    exit;
}

else {
    $_SESSION['error_message'] = "Aksi tidak valid.";
    header("Location: ../admin/index.php");
    exit;
}

mysqli_close($koneksi);
?>