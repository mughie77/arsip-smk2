<?php
require_once '../config/koneksi.php';

// Pastikan hanya request POST yang diterima
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Pastikan ID ada dan merupakan integer
    if (isset($_POST['id']) && filter_var($_POST['id'], FILTER_VALIDATE_INT)) {
        $id = $_POST['id'];

        // Ambil data dari database
        $query = "SELECT * FROM arsip_berkas WHERE id = ?";
        $stmt = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            // Kirim data sebagai response JSON
            echo json_encode(['status' => 'success', 'data' => $row]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Data tidak ditemukan.']);
        }

        mysqli_stmt_close($stmt);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'ID tidak valid.']);
    }
} else {
    // Jika bukan request POST, kirim error
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['status' => 'error', 'message' => 'Metode request tidak diizinkan.']);
}
?>