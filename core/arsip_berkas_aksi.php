<?php
require_once '../config/koneksi.php';
require_once '../admin/cek_sesi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'csrf_validator.php';
}

$action = $_REQUEST['action'] ?? '';

function upload_pdf($file, $id = null)
{
    $target_dir = "../uploads/berkas/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $file_name = ($id ? "berkas_" . $id : "berkas_" . time()) . "." . $file_extension;
    $target_file = $target_dir . $file_name;
    $allowed_types = ['pdf'];
    $max_file_size = 5 * 1024 * 1024; // 5 MB

    if (!in_array($file_extension, $allowed_types)) {
        return ['error' => "Hanya file PDF yang diizinkan."];
    }

    if ($file["size"] > $max_file_size) {
        return ['error' => "Ukuran file maksimal adalah 5 MB."];
    }

    // Validasi tipe MIME
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if ($mime_type !== 'application/pdf') {
        return ['error' => "Tipe file tidak valid. Hanya PDF yang diizinkan."];
    }

    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return ['success' => $file_name];
    } else {
        return ['error' => "Gagal mengunggah file."];
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize all POST data
    $_POST = sanitize_input($_POST);

    switch ($action) {
        case 'add':
            $no_berkas = $_POST['no_berkas'] ?? '';
            $nama_berkas = $_POST['nama_berkas'] ?? '';
            $tanggal_berkas = $_POST['tanggal_berkas'] ?? '';
            $uraian = $_POST['uraian'] ?? '';

            $file_result = null;
            if (isset($_FILES['nama_file_pdf']) && $_FILES['nama_file_pdf']['error'] == 0) {
                $file_result = upload_pdf($_FILES['nama_file_pdf']);
                if (isset($file_result['error'])) {
                    $_SESSION['error_message'] = $file_result['error'];
                    header("Location: ../admin/arsip_berkas.php?action=add");
                    exit;
                }
            }

            $file_path = isset($file_result['success']) ? $file_result['success'] : null;

            $stmt = $koneksi->prepare("INSERT INTO arsip_berkas (no_berkas, nama_berkas, tanggal_berkas, uraian, file_path) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $no_berkas, $nama_berkas, $tanggal_berkas, $uraian, $file_path);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Data arsip berkas berhasil ditambahkan.";
            } else {
                $_SESSION['error_message'] = "Gagal menambahkan data: " . $stmt->error;
            }
            $stmt->close();
            header("Location: ../admin/arsip_berkas.php");
            exit;

        case 'edit':
            $id = (int)($_POST['id'] ?? 0);
            $no_berkas = $_POST['no_berkas'] ?? '';
            $nama_berkas = $_POST['nama_berkas'] ?? '';
            $tanggal_berkas = $_POST['tanggal_berkas'] ?? '';
            $uraian = $_POST['uraian'] ?? '';

            if (empty($id) || empty($no_berkas) || empty($nama_berkas) || empty($tanggal_berkas)) {
                $_SESSION['error_message'] = "Semua field wajib diisi.";
                header("Location: ../admin/arsip_berkas_edit.php?id=" . $id);
                exit;
            }

            $file_path = $_POST['file_path_existing']; // File lama

            if (isset($_FILES['nama_file_pdf']) && $_FILES['nama_file_pdf']['error'] == 0) {
                $file_result = upload_pdf($_FILES['nama_file_pdf'], $id);
                if (isset($file_result['error'])) {
                    $_SESSION['error_message'] = $file_result['error'];
                    header("Location: ../admin/arsip_berkas_edit.php?id=" . $id);
                    exit;
                }
                if (!empty($file_path) && file_exists("../uploads/berkas/" . $file_path)) {
                    unlink("../uploads/berkas/" . $file_path);
                }
                $file_path = $file_result['success'];
            }

            $stmt = $koneksi->prepare("UPDATE arsip_berkas SET no_berkas=?, nama_berkas=?, tanggal_berkas=?, uraian=?, file_path=? WHERE id=?");
            $stmt->bind_param("sssssi", $no_berkas, $nama_berkas, $tanggal_berkas, $uraian, $file_path, $id);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Data arsip berkas berhasil diperbarui.";
            } else {
                $_SESSION['error_message'] = "Gagal memperbarui data: " . $stmt->error;
            }
            $stmt->close();
            header("Location: ../admin/arsip_berkas.php");
            exit;

        case 'delete':
            $id = (int)$_POST['id'];

            $stmt_select = $koneksi->prepare("SELECT file_path FROM arsip_berkas WHERE id=?");
            $stmt_select->bind_param("i", $id);
            $stmt_select->execute();
            $result = $stmt_select->get_result();
            $row = $result->fetch_assoc();
            $stmt_select->close();

            if ($row && !empty($row['file_path']) && file_exists("../uploads/berkas/" . $row['file_path'])) {
                unlink("../uploads/berkas/" . $row['file_path']);
            }

            $stmt_delete = $koneksi->prepare("DELETE FROM arsip_berkas WHERE id=?");
            $stmt_delete->bind_param("i", $id);

            if ($stmt_delete->execute()) {
                $_SESSION['success_message'] = "Data arsip berkas berhasil dihapus.";
            } else {
                $_SESSION['error_message'] = "Gagal menghapus data: " . $stmt_delete->error;
            }
            $stmt_delete->close();
            header("Location: ../admin/arsip_berkas.php");
            exit;

        default:
            $_SESSION['error_message'] = "Aksi tidak valid.";
            header("Location: ../admin/arsip_berkas.php");
            exit;
    }
} else {
    // Redirect jika bukan request POST
    header("Location: ../admin/arsip_berkas.php");
    exit();
}
?>
