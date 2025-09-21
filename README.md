# Sistem Informasi Arsip Surat dan Notulensi Digital

Selamat datang di Sistem Informasi Arsip Surat dan Notulensi Digital. Aplikasi ini adalah solusi komprehensif yang dirancang untuk mengelola arsip surat (masuk dan keluar) serta notulensi rapat secara digital. Dibuat dengan desain premium, fungsionalitas penuh, dan antarmuka yang responsif.

## ✨ Fitur Unggulan

- **Desain Premium & Responsif**: Tampilan mewah dan elegan yang dapat diakses dengan baik di berbagai perangkat (desktop, tablet, mobile).
- **Dashboard Admin**: Halaman utama admin dengan ringkasan statistik (jumlah surat masuk, keluar, notulen) dan grafik perbandingan.
- **Manajemen Surat Masuk**: Fungsionalitas CRUD (Create, Read, Update, Delete) penuh untuk arsip surat masuk, termasuk unggah file PDF.
- **Manajemen Surat Keluar**: Fungsionalitas CRUD penuh untuk arsip surat keluar, termasuk unggah file PDF.
- **Manajemen Notulen**: Fungsionalitas CRUD penuh untuk notulensi rapat atau kegiatan, dengan dukungan unggah file PDF, DOC, dan DOCX.
- **Filter & Pencarian Canggih**: Saring data berdasarkan rentang tanggal dan cari data spesifik menggunakan kata kunci di setiap modul.
- **Ekspor Data Fleksibel**:
    - **Ekspor ke XLSX**: Unduh daftar data yang telah difilter dalam format Excel (`.xlsx`).
    - **Ekspor ke ZIP**: Kumpulkan semua berkas fisik (PDF/Word) dari data yang telah difilter ke dalam satu file `.zip`.
- **Lacak Arsip Publik**: Fitur pada halaman utama yang memungkinkan pengguna umum untuk melacak status arsip berdasarkan nomor arsip unik.
- **URL Bersih (Clean URLs)**: URL yang ramah pengguna tanpa ekstensi `.php` (misalnya, `/login` bukan `/login.php`).
- **Keamanan**: Dibangun dengan praktik keamanan dasar seperti penggunaan *prepared statements* untuk mencegah SQL Injection dan sanitasi output untuk mencegah XSS.

## 🛠️ Teknologi yang Digunakan

- **Bahasa Pemrograman**: PHP 8+ (Native)
- **Database**: MySQL / MariaDB
- **Frontend**: HTML5, CSS3, Bootstrap 5
- **JavaScript**: Native JS & jQuery
- **Library PHP**:
    - `phpoffice/phpspreadsheet` untuk ekspor ke XLSX.
- **Server**: Apache (dengan `mod_rewrite` aktif direkomendasikan)

## 🚀 Petunjuk Instalasi

Berikut adalah langkah-langkah untuk menginstal dan menjalankan aplikasi ini di server lokal (misalnya XAMPP, WAMP) atau server hosting Anda.

### 1. Prasyarat

- Pastikan Anda memiliki server web yang menjalankan **PHP 8** atau lebih baru.
- Pastikan **MySQL** atau **MariaDB** sudah terinstal.
- Pastikan server **Apache** Anda memiliki modul `mod_rewrite` yang aktif (ini diperlukan untuk fitur URL bersih).
- Pastikan **Composer** sudah terinstal untuk manajemen dependensi PHP.

### 2. Clone Repository

Unduh atau clone repositori ini ke direktori server web Anda (misalnya, `htdocs` di XAMPP).

```bash
git clone [URL_REPOSITORY_ANDA] arsip-digital
cd arsip-digital
```

### 3. Setup Database

1.  Buka antarmuka database Anda (misalnya, phpMyAdmin).
2.  Buat database baru. Beri nama, misalnya, `db_arsip_digital`.
3.  Pilih database yang baru saja Anda buat, lalu impor file `database.sql` yang ada di direktori root proyek. Ini akan membuat semua tabel yang diperlukan dan satu akun admin default.

### 4. Konfigurasi Koneksi

1.  Buka file `config/koneksi.php`.
2.  Sesuaikan variabel berikut dengan konfigurasi database Anda:
    ```php
    $db_host = 'localhost';     // Host database Anda
    $db_user = 'root';          // Username database Anda
    $db_pass = '';              // Password database Anda
    $db_name = 'db_arsip_digital'; // Nama database yang Anda buat di langkah 3
    ```

### 5. Instal Dependensi

Buka terminal atau command prompt di direktori root proyek, lalu jalankan Composer untuk menginstal library yang diperlukan (`PhpSpreadsheet`).

```bash
composer install
```
Ini akan membuat folder `vendor` yang berisi semua dependensi.

### 6. Atur Hak Akses Folder

Pastikan folder `uploads/` dan semua subdirektorinya (`surat_masuk`, `surat_keluar`, `notulen`) dapat ditulis oleh server web. Jika Anda menggunakan sistem berbasis Linux/macOS, Anda bisa menjalankan perintah berikut dari root proyek:

```bash
chmod -R 775 uploads
```

### 7. Selesai!

Sekarang Anda dapat mengakses aplikasi melalui browser Anda.
- **Halaman Utama**: `http://localhost/arsip-digital/`
- **Halaman Login**: `http://localhost/arsip-digital/login`

**Kredensial Login Default:**
- **Username**: `admin`
- **Password**: `admin123`

## 📂 Struktur Direktori Proyek

```
/
├── .htaccess             # Aturan untuk URL bersih
├── README.md             # File ini
├── admin/                # Berisi semua halaman khusus admin
│   ├── cek_sesi.php
│   ├── index.php         # Dashboard
│   ├── surat_masuk.php
│   ├── surat_keluar.php
│   └── notulen.php
├── assets/               # File CSS, JS, dan gambar
│   ├── css/
│   └── ...
├── config/               # File konfigurasi
│   └── koneksi.php
├── core/                 # Logika bisnis inti (aksi CRUD, ekspor, login)
│   ├── login_aksi.php
│   ├── surat_masuk_aksi.php
│   └── ...
├── uploads/              # Tempat penyimpanan file yang diunggah (Wajib writable)
│   ├── surat_masuk/
│   ├── surat_keluar/
│   └── notulen/
├── vendor/               # Dependensi dari Composer (dibuat setelah `composer install`)
├── index.php             # Halaman utama publik
├── login.php             # Halaman login
└── logout.php            # Skrip untuk logout
```

---
© 2024. Dibuat dengan penuh dedikasi.
