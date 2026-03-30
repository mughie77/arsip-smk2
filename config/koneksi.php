<?php
// File: config/koneksi.php

// --- Konfigurasi Database ---
$db_host = 'localhost';     // Host database, biasanya 'localhost'
$db_user = 'root';          // Username database
$db_pass = '';              // Password database
$db_name = 'db_arsip_digital'; // Nama database

// --- Membuat Koneksi ke Database ---
$koneksi = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

// --- Cek Koneksi ---
if (!$koneksi) {
    // Jika koneksi gagal, hentikan eksekusi dan tampilkan pesan error
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}

// --- Mengatur zona waktu default ---
date_default_timezone_set('Asia/Jakarta');

/**
 * Fungsi untuk sanitasi input agar lebih aman dari XSS.
 * @param array $data Data yang akan disanitasi (misal: $_POST atau $_GET).
 * @return array Data yang sudah bersih.
 */
function sanitize_input($data) {
    $cleaned_data = [];
    foreach ($data as $key => $value) {
        $cleaned_data[$key] = htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
    }
    return $cleaned_data;
}

/**
 * Fungsi untuk menghasilkan nomor arsip unik.
 * @param string $prefix Prefix untuk nomor arsip (misal: 'SM' untuk Surat Masuk).
 * @return string Nomor arsip yang unik.
 */
function generate_nomor_arsip($prefix) {
    return $prefix . '-' . date('Ymd') . '-' . substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 6);
}

/**
 * Fungsi bantuan untuk membuat link header tabel yang dapat diurutkan.
 */
function sortable_header($title, $column, $current_sort, $current_dir) {
    $dir = ($current_sort == $column && $current_dir == 'ASC') ? 'DESC' : 'ASC';

    if ($current_sort == $column) {
        $icon = $current_dir == 'ASC' ? ' <i class="fas fa-sort-up"></i>' : ' <i class="fas fa-sort-down"></i>';
    } else {
        $icon = ' <i class="fas fa-sort"></i>';
    }

    $query_params = $_GET;
    $query_params['sort'] = $column;
    $query_params['dir'] = $dir;

    return '<a href="?' . http_build_query($query_params) . '">' . htmlspecialchars($title) . $icon . '</a>';
}