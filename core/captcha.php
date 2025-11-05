<?php
session_start();

// Buat string acak untuk CAPTCHA
$captcha_text = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 6);

// Simpan teks CAPTCHA di session
$_SESSION['captcha'] = $captcha_text;

// Buat gambar CAPTCHA
$width = 150;
$height = 40;
$image = imagecreatetruecolor($width, $height);

// Definisikan warna
$bg_color = imagecolorallocate($image, 230, 230, 230); // Latar belakang abu-abu muda
$text_color = imagecolorallocate($image, 10, 38, 71);    // Teks biru tua
$noise_color = imagecolorallocate($image, 150, 150, 150); // Warna noise

// Isi latar belakang
imagefilledrectangle($image, 0, 0, $width, $height, $bg_color);

// Tambahkan noise (garis dan titik)
// Tambahkan beberapa garis acak
for ($i = 0; $i < 5; $i++) {
    imageline($image, 0, rand() % $height, $width, rand() % $height, $noise_color);
}
// Tambahkan beberapa titik acak
for ($i = 0; $i < 1000; $i++) {
    imagesetpixel($image, rand() % $width, rand() % $height, $noise_color);
}

// Tulis teks CAPTCHA ke gambar
// Anda mungkin perlu menyesuaikan path font ke file .ttf yang valid di server Anda
// Misalnya, jika Anda punya font 'Arial.ttf' di folder yang sama: $font = './Arial.ttf';
// Jika tidak ada font, gunakan font bawaan GD (nilai 1-5)
$font_size = 5;
$x = (imagesx($image) - (strlen($captcha_text) * imagefontwidth($font_size))) / 2;
$y = (imagesy($image) - imagefontheight($font_size)) / 2;
imagestring($image, $font_size, $x, $y, $captcha_text, $text_color);

// Atur header dan output gambar
header('Content-Type: image/png');
imagepng($image);

// Hancurkan gambar untuk membebaskan memori
imagedestroy($image);
?>
