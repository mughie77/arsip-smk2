<?php
session_start();
// Jika sudah login, redirect ke dashboard admin
if (isset($_SESSION['admin_id'])) {
    header("Location: admin/index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Sistem Arsip Digital</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">

    <!-- Menambahkan gambar background kustom jika ada -->
    <style>
        .login-page {
            /* Ganti 'background.jpg' dengan nama file gambar Anda di folder assets/img/ */
            background: linear-gradient(rgba(10, 38, 71, 0.8), rgba(10, 38, 71, 0.8)), url('https://placehold.co/1920x1080/0A2647/FFFFFF?text=Background+Login') no-repeat center center;
            background-size: cover;
        }
        #captcha-image {
            border-radius: .25rem;
            max-height: 38px; /* Menyamakan tinggi dengan input field */
        }
    </style>
</head>
<body class="login-page">

    <div class="login-box">
        <div class="logo">
            <h2><i class="fas fa-archive"></i> Arsip<strong>Digital</strong></h2>
            <p class="text-muted">Silakan login untuk melanjutkan</p>
        </div>

        <?php
        // Tampilkan pesan error jika ada
        if (isset($_GET['error'])) {
            echo '<div class="alert alert-danger" role="alert">' . htmlspecialchars($_GET['error']) . '</div>';
        }
        ?>

        <form action="core/login_aksi.php" method="POST">
            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
                <label for="username">Username</label>
            </div>
            <div class="form-floating mb-4">
                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                <label for="password">Password</label>
            </div>
            <div class="mb-3">
                 <div class="row g-2 align-items-center">
                    <div class="col-7">
                        <img src="core/captcha.php" alt="CAPTCHA" class="img-fluid" id="captcha-image">
                    </div>
                    <div class="col-5">
                        <div class="input-group">
                            <input type="text" class="form-control" id="captcha" name="captcha" placeholder="Captcha" required>
                            <button type="button" class="btn btn-outline-secondary" id="refresh-captcha" title="Refresh Captcha">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-login">Login</button>
            </div>
        </form>
        <div class="text-center mt-4">
            <a href="./" class="text-decoration-none"><i class="fas fa-arrow-left"></i> Kembali ke Beranda</a>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const refreshButton = document.getElementById('refresh-captcha');
            const captchaImage = document.getElementById('captcha-image');

            refreshButton.addEventListener('click', function () {
                // Tambahkan timestamp acak untuk mencegah caching
                captchaImage.src = 'core/captcha.php?' + new Date().getTime();
            });
        });
    </script>
</body>
</html>
