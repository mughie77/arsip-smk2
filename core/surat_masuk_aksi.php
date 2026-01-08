<?php
// File: core/surat_masuk_aksi.php
require_once '../config/koneksi.php';
require_once '../admin/cek_sesi.php';

// Validasi CSRF hanya untuk request POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'csrf_validator.php';
}

$action = $_REQUEST['action'] ?? '';

// --- FUNGSI-FUNGSI BANTUAN ---
function upload_file($file_input)
{
    $target_dir = "../uploads/surat_masuk/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    $file_extension = strtolower(pathinfo($file_input["name"], PATHINFO_EXTENSION));
    $new_file_name = "SM-" . date("Ymd-His") . "-" . uniqid() . "." . $file_extension;
    $target_file = $target_dir . $new_file_name;

    // Validasi file
    if ($file_input["size"] > 3 * 1024 * 1024) { // Maks 3MB
        return ['status' => 'error', 'message' => 'Ukuran file terlalu besar. Maksimal 3MB.'];
    }
    if ($file_extension !== 'pdf') {
        return ['status' => 'error', 'message' => 'Hanya file format PDF yang diizinkan.'];
    }

    // Validasi tipe MIME - dinonaktifkan karena ekstensi fileinfo tidak tersedia
    /*
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file_input['tmp_name']);
    finfo_close($finfo);

    if ($mime_type !== 'application/pdf') {
        return ['status' => 'error', 'message' => 'Tipe file tidak valid.'];
    }
    */

    if (move_uploaded_file($file_input["tmp_name"], $target_file)) {
        return ['status' => 'success', 'filename' => $new_file_name];
    } else {
        return ['status' => 'error', 'message' => 'Terjadi kesalahan saat mengunggah file.'];
    }
}

function delete_old_file($filename)
{
    if (empty($filename)) return;
    $filepath = "../uploads/surat_masuk/" . $filename;
    if (file_exists($filepath)) {
        unlink($filepath);
    }
}

// --- ROUTING AKSI UTAMA ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize all POST data
    $_POST = sanitize_input($_POST);

    switch ($action) {
        case 'add':
            $nomor_surat = $_POST['nomor_surat'] ?? '';
            $perihal = $_POST['perihal'] ?? '';
            $asal_surat = $_POST['asal_surat'] ?? '';
            $tanggal_diterima = $_POST['tanggal_diterima'] ?? '';
            $acc_kepada = $_POST['acc_kepada'] ?? '';

            // Validasi input dasar
            if (empty($nomor_surat) || empty($perihal) || empty($asal_surat) || empty($tanggal_diterima)) {
                $_SESSION['error_message'] = "Semua field wajib diisi, kecuali 'Diteruskan Kepada'.";
                header("Location: ../admin/surat_masuk.php");
                exit;
            }

            $nama_file_pdf = '';
            if (isset($_FILES['nama_file_pdf']) && $_FILES['nama_file_pdf']['error'] == UPLOAD_ERR_OK) {
                $upload_result = upload_file($_FILES['nama_file_pdf']);
                if ($upload_result['status'] == 'error') {
                    $_SESSION['error_message'] = $upload_result['message'];
                    header("Location: ../admin/surat_masuk.php");
                    exit;
                }
                $nama_file_pdf = $upload_result['filename'];
            }

            // generate_nomor_arsip function needs to be defined or included
            // For now, let's assume a placeholder or a simple logic
            $nomor_arsip = 'SM/' . date('Ymd') . '/' . mt_rand(100, 999);

            $stmt = $koneksi->prepare("INSERT INTO surat_masuk (nomor_arsip, nomor_surat, perihal, asal_surat, tanggal_diterima, acc_kepada, nama_file_pdf) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $nomor_arsip, $nomor_surat, $perihal, $asal_surat, $tanggal_diterima, $acc_kepada, $nama_file_pdf);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Data surat masuk berhasil ditambahkan.";
            } else {
                $_SESSION['error_message'] = "Gagal menyimpan data: " . $stmt->error;
            }
            $stmt->close();
            header("Location: ../admin/surat_masuk.php");
            exit;

        case 'edit':
            $id = (int)($_POST['id'] ?? 0);
            $nomor_surat = trim($_POST['nomor_surat'] ?? '');
            $perihal = trim($_POST['perihal'] ?? '');
            $asal_surat = trim($_POST['asal_surat'] ?? '');
            $tanggal_diterima = trim($_POST['tanggal_diterima'] ?? '');
            $acc_kepada = trim($_POST['acc_kepada'] ?? '');
            $nama_file_pdf_lama = trim($_POST['nama_file_pdf_existing'] ?? '');

            if (empty($id) || empty($nomor_surat) || empty($perihal) || empty($asal_surat) || empty($tanggal_diterima)) {
                $_SESSION['error_message'] = "Data tidak lengkap.";
                header("Location: ../admin/surat_masuk_edit.php?id=" . $id);
                exit;
            }

            // Cek apakah tanggal diubah untuk regenerasi nomor arsip
            $stmt_cek = $koneksi->prepare("SELECT tanggal_diterima, nomor_arsip FROM surat_masuk WHERE id=?");
            $stmt_cek->bind_param("i", $id);
            $stmt_cek->execute();
            $result_cek = $stmt_cek->get_result();
            $data_lama = $result_cek->fetch_assoc();
            $stmt_cek->close();

            $nomor_arsip = $data_lama['nomor_arsip'];
            if ($tanggal_diterima !== $data_lama['tanggal_diterima']) {
                $nomor_arsip = 'SM/' . date('Ymd', strtotime($tanggal_diterima)) . '/' . mt_rand(100, 999);
            }

            $nama_file_pdf_baru = $nama_file_pdf_lama;

            if (isset($_FILES['nama_file_pdf']) && $_FILES['nama_file_pdf']['error'] == UPLOAD_ERR_OK) {
                $upload_result = upload_file($_FILES['nama_file_pdf']);
                if ($upload_result['status'] == 'error') {
                    $_SESSION['error_message'] = $upload_result['message'];
                    header("Location: ../admin/surat_masuk_edit.php?id=" . $id);
                    exit;
                }
                $nama_file_pdf_baru = $upload_result['filename'];
                delete_old_file($nama_file_pdf_lama);
            }

            $stmt = $koneksi->prepare("UPDATE surat_masuk SET nomor_arsip=?, nomor_surat=?, perihal=?, asal_surat=?, tanggal_diterima=?, acc_kepada=?, nama_file_pdf=? WHERE id=?");
            $stmt->bind_param("sssssssi", $nomor_arsip, $nomor_surat, $perihal, $asal_surat, $tanggal_diterima, $acc_kepada, $nama_file_pdf_baru, $id);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Data surat masuk berhasil diperbarui.";
            } else {
                $_SESSION['error_message'] = "Gagal memperbarui data: " . $stmt->error;
            }
            $stmt->close();
            header("Location: ../admin/surat_masuk.php");
            exit;

        case 'delete':
            $id = (int)($_POST['id'] ?? 0);
            if ($id === 0) {
                $_SESSION['error_message'] = "ID tidak valid.";
                header("Location: ../admin/surat_masuk.php");
                exit;
            }

            $stmt_get = $koneksi->prepare("SELECT nama_file_pdf FROM surat_masuk WHERE id=?");
            $stmt_get->bind_param("i", $id);
            $stmt_get->execute();
            $result = $stmt_get->get_result();
            if ($row = $result->fetch_assoc()) {
                delete_old_file($row['nama_file_pdf']);
            }
            $stmt_get->close();

            $stmt_del = $koneksi->prepare("DELETE FROM surat_masuk WHERE id=?");
            $stmt_del->bind_param("i", $id);
            if ($stmt_del->execute()) {
                $_SESSION['success_message'] = "Data surat masuk berhasil dihapus.";
            } else {
                $_SESSION['error_message'] = "Gagal menghapus data: " . $stmt_del->error;
            }
            $stmt_del->close();
            header("Location: ../admin/surat_masuk.php");
            exit;

        default:
            $_SESSION['error_message'] = "Aksi tidak valid.";
            header("Location: ../admin/surat_masuk.php");
            exit;
    }
} else {
    header("Location: ../admin/surat_masuk.php");
    exit();
}
?>
