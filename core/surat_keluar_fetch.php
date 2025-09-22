<?php
// File: core/surat_keluar_fetch.php

header('Content-Type: application/json');

require_once '../config/koneksi.php';
require_once '../admin/cek_sesi.php';

$response = ['status' => 'error', 'message' => 'Permintaan tidak valid.'];

if (isset($_POST['id'])) {
    $id = intval($_POST['id']);

    $query = "SELECT nomor_surat, tujuan_surat, perihal, tanggal_kirim FROM surat_keluar WHERE id = ?";
    if ($stmt = mysqli_prepare($koneksi, $query)) {
        mysqli_stmt_bind_param($stmt, "i", $id);

        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            if ($data = mysqli_fetch_assoc($result)) {
                $response = [
                    'status' => 'success',
                    'data' => $data
                ];
            } else {
                $response['message'] = 'Data tidak ditemukan.';
            }
        } else {
            $response['message'] = 'Eksekusi query gagal.';
        }
        mysqli_stmt_close($stmt);
    } else {
        $response['message'] = 'Gagal mempersiapkan query.';
    }
}

mysqli_close($koneksi);
echo json_encode($response);
?>
