<?php
// File: admin/cek_sesi.php

// Mulai session jika belum ada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah admin sudah login.
// Jika session 'admin_loggedin' tidak ada atau tidak bernilai true,
// redirect ke halaman login.
if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] !== true) {
    header("Location: ../login.php?error=Anda harus login untuk mengakses halaman ini.");
    exit;
}
?>