<?php
// error.php

// --- Configuration ---
$error_code = isset($_GET['code']) ? htmlspecialchars($_GET['code']) : 'Error';
$user_message = isset($_GET['message']) ? htmlspecialchars($_GET['message']) : null;

$error_details = [
    '403' => [
        'icon' => 'fa-shield-halved',
        'heading' => 'Akses Ditolak',
        'message' => 'Anda tidak memiliki izin untuk mengakses halaman ini. Silakan hubungi administrator jika Anda merasa ini adalah kesalahan.'
    ],
    '404' => [
        'icon' => 'fa-compass',
        'heading' => 'Halaman Tidak Ditemukan',
        'message' => 'Halaman yang Anda cari mungkin telah dipindahkan, dihapus, atau tidak pernah ada. Mari kami pandu Anda kembali.'
    ],
    '500' => [
        'icon' => 'fa-cogs',
        'heading' => 'Server Sedang Bermasalah',
        'message' => 'Terjadi kesalahan internal pada server kami. Tim kami telah diberitahu dan sedang menanganinya.'
    ],
    'File Not Found' => [
        'icon' => 'fa-file-circle-question',
        'heading' => 'Berkas Tidak Ditemukan',
        'message' => 'Berkas yang Anda minta tidak ada di server. Mungkin telah dihapus atau nama filenya berubah.'
    ],
    'default' => [
        'icon' => 'fa-triangle-exclamation',
        'heading' => 'Oops! Terjadi Masalah',
        'message' => 'Terjadi kesalahan yang tidak terduga. Silakan coba lagi nanti atau kembali ke beranda.'
    ]
];

$details = $error_details[$error_code] ?? $error_details['default'];
$icon_class = $details['icon'];
$heading = $details['heading'];
// Use the user-provided message if it exists, otherwise use the default one.
$message = $user_message ?? $details['message'];

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error <?php echo $error_code; ?> - Arsip Digital</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary-color: #0A2647;
            --secondary-color: #144272;
            --accent-color: #FFD700;
        }

        body {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: #fff;
            font-family: 'Montserrat', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            overflow: hidden;
        }

        .error-wrapper {
            text-align: center;
            animation: fadeIn 1s ease-in-out;
        }

        .error-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 3rem 4rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .error-icon {
            font-size: 5rem;
            color: var(--accent-color);
            animation: float 4s ease-in-out infinite;
        }

        .error-code {
            font-family: 'Playfair Display', serif;
            font-size: 6rem;
            font-weight: 700;
            color: #fff;
            margin: 1rem 0;
            text-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .error-heading {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .error-message {
            font-size: 1.1rem;
            max-width: 400px;
            margin: 0 auto 2.5rem auto;
            color: rgba(255, 255, 255, 0.8);
        }

        .home-link {
            background: var(--accent-color);
            color: var(--primary-color);
            border: none;
            border-radius: 50px;
            padding: 0.8rem 2.5rem;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .home-link:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
            100% { transform: translateY(0px); }
        }

    </style>
</head>
<body>
    <div class="error-wrapper">
        <div class="error-card">
            <i class="fa-solid <?php echo $icon_class; ?> error-icon"></i>
            <div class="error-code"><?php echo $error_code; ?></div>
            <h1 class="error-heading"><?php echo $heading; ?></h1>
            <p class="error-message"><?php echo $message; ?></p>
            <a href="/" class="home-link">Kembali ke Beranda</a>
        </div>
    </div>
</body>
</html>
