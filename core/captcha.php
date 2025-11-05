<?php
session_start();

// Buat string acak untuk CAPTCHA
$captcha_text = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 6);

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
// Tambahkan beberapa garis acak
for ($i = 0; $i < 5; $i++) {
    imageline($image, 0, rand() % $height, $width, rand() % $height, $noise_color);
}
// Tambahkan beberapa titik acak
for ($i = 0; $i < 1000; $i++) {
    imagesetpixel($image, rand() % $width, rand() % $height, $noise_color);
}

// Tulis teks CAPTCHA ke gambar
$font_file = '../assets/fonts/OpenSans-Regular.ttf';
$font_size = 30;

// Dapatkan ukuran kotak teks
$textbox = imagettfbbox($font_size, 0, $font_file, $captcha_text);
$text_width = $textbox[2] - $textbox[0];
$text_height = $textbox[7] - $textbox[1];

// Hitung posisi x dan y agar teks berada di tengah
$x = ($width - $text_width) / 2;
$y = ($height - $text_height) / 2;

imagettftext($image, $font_size, 0, $x, $y, $text_color, $font_file, $captcha_text);

// Atur header dan output gambar
header('Content-Type: image/png');
imagepng($image);

// Hancurkan gambar untuk membebaskan memori
imagedestroy($image);
?>
