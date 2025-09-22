-- Skema Database untuk Sistem Informasi Arsip Surat dan Notulensi Digital

--
-- Struktur dari tabel `admins`
--
CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `admins`
--
INSERT INTO `admins` (`id`, `username`, `password`) VALUES
(1, 'admin', 'admin123');
-- Catatan: Dalam aplikasi nyata, gunakan password_hash() PHP. 'admin123' hanya untuk pengembangan.

-- --------------------------------------------------------

--
-- Struktur dari tabel `surat_masuk`
--
CREATE TABLE `surat_masuk` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nomor_arsip` varchar(100) NOT NULL,
  `nomor_surat` varchar(100) NOT NULL,
  `perihal` varchar(255) NOT NULL,
  `asal_surat` varchar(255) NOT NULL,
  `tanggal_diterima` date NOT NULL,
  `acc_kepada` varchar(255) DEFAULT NULL,
  `nama_file_pdf` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `surat_keluar`
--
CREATE TABLE `surat_keluar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nomor_surat` varchar(100) NOT NULL,
  `perihal` varchar(255) NOT NULL,
  `tujuan_surat` varchar(255) NOT NULL,
  `tanggal_kirim` date NOT NULL,
  `acc_kepada` varchar(255) DEFAULT NULL,
  `nama_file_pdf` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `notulen`
--
CREATE TABLE `notulen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tanggal` date NOT NULL,
  `kegiatan` varchar(255) NOT NULL,
  `nama_file` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
