<?php
// File: admin/cek_sesi.php

// Mulai session jika belum ada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- Pemeriksaan Keamanan Session ---

// 1. Cek apakah admin sudah login
if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] !== true) {
    header("Location: ../login.php?error=Anda harus login untuk mengakses halaman ini.");
    exit;
}

// 2. Cek User Agent untuk mencegah session hijacking
// Fitur ini dinonaktifkan sementara karena dapat menyebabkan masalah bagi pengguna
// dengan User Agent yang dinamis atau browser yang sering update di latar belakang.
/*
if (!isset($_SESSION['user_agent']) || $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
    session_unset();
    session_destroy();
    header("Location: ../login.php?error=Sesi tidak valid. Silakan login kembali.");
    exit;
}
*/

// 3. Cek IP Address (opsional, bisa menyebabkan masalah jika IP pengguna dinamis)
// Untuk keamanan tambahan, baris ini bisa diaktifkan
/*
if (!isset($_SESSION['user_ip']) || $_SESSION['user_ip'] !== $_SERVER['REMOTE_ADDR']) {
    session_unset();
    session_destroy();
    header("Location: ../login.php?error=Alamat IP berubah. Silakan login kembali.");
    exit;
}
*/

// 4. Cek batas waktu sesi (contoh: 30 menit)
$session_timeout = 30 * 60; // 30 menit
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > $session_timeout)) {
    session_unset();
    session_destroy();
    header("Location: ../login.php?error=Sesi Anda telah berakhir. Silakan login kembali.");
    exit;
}

// Perbarui waktu aktivitas terakhir
$_SESSION['login_time'] = time();
?>