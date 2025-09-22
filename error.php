<?php
// error.php
$error_code = isset($_GET['code']) ? htmlspecialchars($_GET['code']) : 'Error';
$error_message = isset($_GET['message']) ? htmlspecialchars($_GET['message']) : 'An unexpected error occurred on the page.';

// Custom messages for specific codes if no message is provided
if (!isset($_GET['message'])) {
    switch ($error_code) {
        case '404':
            $error_message = 'The page or resource you are looking for could not be found.';
            break;
        case '403':
            $error_message = 'You do not have permission to access this page.';
            break;
        case '500':
            $error_message = 'The server encountered an internal error. Please try again later.';
            break;
        case 'File Not Found':
             $error_message = 'The requested file does not exist on the server or may have been deleted.';
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error <?php echo $error_code; ?> - Arsip Digital</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            font-family: 'Poppins', sans-serif;
        }
        .error-container {
            text-align: center;
            max-width: 600px;
            padding: 20px;
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        .error-code {
            font-size: 5rem;
            font-weight: 700;
            color: #0A2647; /* Primary Color */
        }
        .error-heading {
            font-size: 2rem;
            margin-top: 0;
            margin-bottom: 1rem;
            color: #144272; /* Secondary Color */
        }
        .error-message {
            font-size: 1.1rem;
            color: #6c757d;
        }
        .home-link {
            margin-top: 2rem;
            background-color: #0A2647;
            border-color: #0A2647;
            font-weight: 600;
        }
        .home-link:hover {
            background-color: #144272;
            border-color: #144272;
        }
    </style>
</head>
<body>
    <div class="container error-container">
        <div class="error-code"><?php echo $error_code; ?></div>
        <h1 class="error-heading">Oops! Terjadi Masalah</h1>
        <p class="error-message"><?php echo $error_message; ?></p>
        <a href="/" class="btn btn-primary btn-lg home-link">
            <i class="fas fa-home"></i> Kembali ke Beranda
        </a>
    </div>
</body>
</html>
