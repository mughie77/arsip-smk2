<?php
// File: core/csrf_validator.php

// Mulai session jika belum ada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Fungsi untuk memvalidasi token CSRF
function validate_csrf_token() {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        // Jika token tidak valid, hentikan eksekusi dan tampilkan error
        die("Error: Aksi tidak diizinkan. Token CSRF tidak valid.");
    }
}

// Panggil fungsi validasi setiap kali file ini di-include
validate_csrf_token();
?>
