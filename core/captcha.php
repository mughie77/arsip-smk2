<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- Fungsi untuk membuat gambar error ---
function create_error_image($message) {
    $width = 300;
    $height = 80;
    $image = imagecreatetruecolor($width, $height);
    $bg_color = imagecolorallocate($image, 255, 255, 255); // Latar belakang putih
    $text_color = imagecolorallocate($image, 200, 0, 0);   // Teks merah
    imagefilledrectangle($image, 0, 0, $width, $height, $bg_color);

    // Tulis pesan error di tengah gambar
    $font_size = 5;
    $x = (imagesx($image) - (strlen($message) * imagefontwidth($font_size))) / 2;
    $y = (imagesy($image) - imagefontheight($font_size)) / 2;
    imagestring($image, $font_size, $x, $y, $message, $text_color);

    return $image;
}

try {
    // --- Cek Dependensi ---
    // 1. Cek apakah ekstensi GD dimuat
    if (!extension_loaded('gd')) {
        throw new Exception("GD Library is missing");
    }

    // 2. Cek apakah file font ada dan dapat dibaca
    $font_file = __DIR__ . '/../assets/fonts/OpenSans-Regular.ttf';
    if (!is_readable($font_file)) {
        throw new Exception("Font file not found");
    }

    // --- Generasi CAPTCHA ---
    $character_set = "23456789abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ";
    $captcha_text = substr(str_shuffle($character_set), 0, 6);
    $_SESSION['captcha'] = $captcha_text;

    $width = 300;
    $height = 80;
    $image = imagecreatetruecolor($width, $height);

    if (!$image) {
        throw new Exception("Failed to create image resource.");
    }

    $bg_color = imagecolorallocate($image, 230, 230, 230);
    $text_color = imagecolorallocate($image, 10, 38, 71);
    $noise_color = imagecolorallocate($image, 150, 150, 150);

    imagefilledrectangle($image, 0, 0, $width, $height, $bg_color);

    for ($i = 0; $i < 5; $i++) {
        imageline($image, 0, rand() % $height, $width, rand() % $height, $noise_color);
    }
    for ($i = 0; $i < 1000; $i++) {
        imagesetpixel($image, rand() % $width, rand() % $height, $noise_color);
    }

    $font_size = 30;
    $textbox = imagettfbbox($font_size, 0, $font_file, $captcha_text);
    if (!$textbox) {
        throw new Exception("Failed to get font bounding box.");
    }
    $text_width = $textbox[2] - $textbox[0];
    $text_height = $textbox[7] - $textbox[1];
    $x = ($width - $text_width) / 2;
    $y = ($height - $text_height) / 2;
    imagettftext($image, $font_size, 0, $x, $y, $text_color, $font_file, $captcha_text);

    header('Content-Type: image/png');
    imagepng($image);
    imagedestroy($image);

} catch (Exception $e) {
    // Log error ke file log sistem
    error_log("CAPTCHA Generation Error: " . $e->getMessage());

    // Tampilkan gambar error kepada pengguna
    $error_image = create_error_image("CAPTCHA Error");
    header('Content-Type: image/png');
    imagepng($error_image);
    imagedestroy($error_image);
    exit;
}
?>
