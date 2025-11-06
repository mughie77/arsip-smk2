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
function upload_file($file_input) {
    $target_dir = "../uploads/surat_masuk/";
    // ... (kode fungsi upload_file tidak berubah)
}
function delete_old_file($filename) {
    $filepath = "../uploads/surat_masuk/" . $filename;
    if (file_exists($filepath) && !empty($filename)) {
        unlink($filepath);
    }
}

// --- ROUTING AKSI UTAMA ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($action) {
        case 'add':
            $safe_post = sanitize_input($_POST);
            $nomor_surat = $safe_post['nomor_surat'];
            $perihal = $safe_post['perihal'];
            $asal_surat = $safe_post['asal_surat'];
            $tanggal_diterima = $safe_post['tanggal_diterima'];
            $acc_kepada = $safe_post['acc_kepada'];

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

        case 'edit':
            $safe_post = sanitize_input($_POST);
            $id = intval($safe_post['id']);
            $nomor_surat = $safe_post['nomor_surat'];
            $perihal = $safe_post['perihal'];
            $asal_surat = $safe_post['asal_surat'];
            $tanggal_diterima = $safe_post['tanggal_diterima'];
            $acc_kepada = $safe_post['acc_kepada'];

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

        case 'delete':
            $id = intval($_POST['id']);

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
            break;

        default:
            header("Location: ../admin/surat_masuk?error=Aksi tidak valid.");
            break;
    }
} else {
    // Redirect jika bukan request POST (kecuali ada aksi GET yang diizinkan di masa depan)
    header("Location: ../admin/surat_masuk");
    exit();
}

mysqli_close($koneksi);
?>
