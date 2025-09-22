<?php
session_start();
require_once '../config/koneksi.php';

header('Content-Type: application/json');

// Cek apakah admin sudah login
if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak. Silakan login terlebih dahulu.']);
    exit;
}

// Inisialisasi respons default
$response = ['status' => 'error', 'message' => 'Permintaan tidak valid.'];

if (isset($_POST['id'])) {
    $id = (int)$_POST['id'];

    if ($id > 0) {
        $query = "SELECT * FROM klasifikasi_surat WHERE id = ?";
        $stmt = mysqli_prepare($koneksi, $query);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($data = mysqli_fetch_assoc($result)) {
                $response = ['status' => 'success', 'data' => $data];
            } else {
                $response['message'] = 'Data tidak ditemukan.';
            }
            mysqli_stmt_close($stmt);
        } else {
            $response['message'] = 'Query gagal disiapkan: ' . mysqli_error($koneksi);
        }
    } else {
        $response['message'] = 'ID tidak valid.';
    }
}

mysqli_close($koneksi);
echo json_encode($response);
?>
