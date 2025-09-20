<?php
// File: core/login_aksi.php

// Mulai session
session_start();

// Panggil file koneksi
require_once '../config/koneksi.php';

// Cek apakah form telah disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Ambil data dari form dan sanitasi dasar
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Validasi dasar: pastikan input tidak kosong
    if (empty($username) || empty($password)) {
        header("Location: ../login?error=Username dan password tidak boleh kosong");
        exit();
    }

    // Gunakan prepared statements untuk keamanan
    $sql = "SELECT id, username, password FROM admins WHERE username = ?";

    if ($stmt = mysqli_prepare($koneksi, $sql)) {
        // Bind variabel ke prepared statement sebagai parameter
        mysqli_stmt_bind_param($stmt, "s", $username);

        // Eksekusi statement
        if (mysqli_stmt_execute($stmt)) {
            // Simpan hasil
            mysqli_stmt_store_result($stmt);

            // Cek jika username ada
            if (mysqli_stmt_num_rows($stmt) == 1) {
                // Bind hasil ke variabel
                mysqli_stmt_bind_result($stmt, $id, $db_username, $db_password);

                if (mysqli_stmt_fetch($stmt)) {
                    // Verifikasi password
                    // Catatan: Di aplikasi production, gunakan password_verify($password, $db_password)
                    // dan simpan password di DB menggunakan password_hash().
                    // Untuk tujuan proyek ini, kita gunakan perbandingan teks biasa sesuai permintaan.
                    if ($password === $db_password) {
                        // Password benar, mulai session baru

                        // Hapus session lama dan regenerasi ID
                        session_regenerate_id(true);

                        // Simpan data ke dalam session
                        $_SESSION['admin_loggedin'] = true;
                        $_SESSION['admin_id'] = $id;
                        $_SESSION['admin_username'] = $db_username;

                        // Redirect ke dashboard admin
                        header("Location: ../admin");
                        exit();
                    } else {
                        // Password salah
                        header("Location: ../login?error=Password yang Anda masukkan salah.");
                        exit();
                    }
                }
            } else {
                // Username tidak ditemukan
                header("Location: ../login?error=Username tidak ditemukan.");
                exit();
            }
        } else {
            header("Location: ../login?error=Terjadi kesalahan sistem. Silakan coba lagi.");
            exit();
        }

        // Tutup statement
        mysqli_stmt_close($stmt);
    } else {
        die("Error pada prepared statement: " . mysqli_error($koneksi));
    }

    // Tutup koneksi
    mysqli_close($koneksi);

} else {
    // Jika diakses langsung, redirect ke halaman login
    header("Location: ../login");
    exit();
}
?>
