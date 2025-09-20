<?php
// File: logout.php

// Mulai session
session_start();

// Hapus semua variabel session
$_SESSION = array();

// Hancurkan session
session_destroy();

// Redirect ke halaman login dengan pesan sukses logout
header("Location: login.php?message=Anda telah berhasil logout.");
exit;
?>
