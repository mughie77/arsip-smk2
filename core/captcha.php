<?php
session_start();

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

// --- Cek Dependensi ---
// 1. Cek apakah ekstensi GD dimuat
if (!extension_loaded('gd')) {
    $error_image = create_error_image("GD Library is missing");
    header('Content-Type: image/png');
    imagepng($error_image);
    imagedestroy($error_image);
    exit;
}

// 2. Cek apakah file font ada dan dapat dibaca
$font_file = __DIR__ . '/../assets/fonts/OpenSans-Regular.ttf';
if (!is_readable($font_file)) {
    $error_image = create_error_image("Font file not found");
    header('Content-Type: image/png');
    imagepng($error_image);
    imagedestroy($error_image);
    exit;
}


// --- Generasi CAPTCHA ---
// Karakter yang ambigu (misal: O, 0, I, l, 1) dihilangkan untuk meningkatkan keterbacaan.
$character_set = "23456789abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ";
$captcha_text = substr(str_shuffle($character_set), 0, 6);

// Simpan teks CAPTCHA di session
$_SESSION['captcha'] = $captcha_text;

// Buat gambar CAPTCHA
$width = 300;
$height = 80;
$image = imagecreatetruecolor($width, $height);

// Definisikan warna
$bg_color = imagecolorallocate($image, 230, 230, 230); // Latar belakang abu-abu muda
$text_color = imagecolorallocate($image, 10, 38, 71);    // Teks biru tua
$noise_color = imagecolorallocate($image, 150, 150, 150); // Warna noise

// Isi latar belakang
imagefilledrectangle($image, 0, 0, $width, $height, $bg_color);

// Tambahkan noise (garis dan titik)
for ($i = 0; $i < 5; $i++) {
    imageline($image, 0, rand() % $height, $width, rand() % $height, $noise_color);
}
for ($i = 0; $i < 1000; $i++) {
    imagesetpixel($image, rand() % $width, rand() % $height, $noise_color);
}

// Tulis teks CAPTCHA ke gambar
$font_size = 30;
$textbox = imagettfbbox($font_size, 0, $font_file, $captcha_text);
$text_width = $textbox[2] - $textbox[0];
$text_height = $textbox[7] - $textbox[1];
$x = ($width - $text_width) / 2;
$y = ($height - $text_height) / 2;
imagettftext($image, $font_size, 0, $x, $y, $text_color, $font_file, $captcha_text);

// Atur header dan output gambar
header('Content-Type: image/png');
imagepng($image);
imagedestroy($image);
?>
