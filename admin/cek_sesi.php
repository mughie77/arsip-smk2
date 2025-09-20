<?php
// File: admin/cek_sesi.php

// Mulai session jika belum ada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah admin sudah login
// Jika session 'admin_loggedin' tidak ada atau tidak bernilai true,
// redirect ke halaman login.
if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] !== true) {
    // Simpan pesan untuk ditampilkan di halaman login
    // (opsional, tapi bisa membantu user)
    // session_destroy(); // Hapus session yang mungkin korup
    header("Location: ../login.php?error=Anda harus login untuk mengakses halaman ini.");
    exit;
}

// Opsional: Cek aktivitas terakhir untuk auto-logout
// $timeout_duration = 1800; // 30 menit
// if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
//     session_unset();
//     session_destroy();
//     header("Location: ../login.php?error=Sesi Anda telah berakhir karena tidak ada aktivitas.");
//     exit;
// }
// $_SESSION['last_activity'] = time(); // Perbarui waktu aktivitas terakhir

?>
