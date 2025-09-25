<?php
session_start();
require_once '../config/koneksi.php';

// Cek jika form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);

    // Cek apakah username dan password diisi
    if (empty($username) || empty($password)) {
        header("Location: ../login.php?error=Username dan password tidak boleh kosong.");
        exit;
    }

    // Ambil data admin dari database
    $query = "SELECT * FROM admins WHERE username = ?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    // Cek apakah username ditemukan
    if (mysqli_num_rows($result) == 1) {
        $admin = mysqli_fetch_assoc($result);

        // Verifikasi password menggunakan password_verify()
        if (password_verify($password, $admin['password'])) {
            // Jika password cocok, buat session
            $_SESSION['admin_loggedin'] = true;
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['login_time'] = time();

            // Redirect ke dashboard admin
            header("Location: ../admin/index.php");
            exit;
        } else {
            // Jika password salah
            header("Location: ../login.php?error=Username atau password salah.");
            exit;
        }
    } else {
        // Jika username tidak ditemukan
        header("Location: ../login.php?error=Username atau password salah.");
        exit;
    }

    mysqli_stmt_close($stmt);
    mysqli_close($koneksi);
} else {
    // Jika halaman diakses langsung tanpa POST, redirect ke halaman login
    header("Location: ../login.php");
    exit;
}
?>