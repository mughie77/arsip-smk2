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
  `kode_arsip` varchar(255) DEFAULT NULL,
  `nomor_surat` varchar(100) NOT NULL,
  `perihal` varchar(255) NOT NULL,
  `tujuan_surat` varchar(255) NOT NULL,
  `tanggal_kirim` date NOT NULL,
  `klasifikasi_id` int(11) DEFAULT NULL,
  `nama_file_pdf` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `klasifikasi_id` (`klasifikasi_id`)
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

-- --------------------------------------------------------

--
-- Struktur dari tabel `klasifikasi_surat`
--
CREATE TABLE `klasifikasi_surat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode` varchar(50) NOT NULL,
  `jenis_surat` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengaturan`
--
CREATE TABLE `pengaturan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_sekolah` varchar(255) NOT NULL,
  `kode_sekolah` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `pengaturan`
--
INSERT INTO `pengaturan` (`id`, `nama_sekolah`, `kode_sekolah`) VALUES
(1, 'Nama Sekolah Anda', 'KODE01');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `surat_keluar`
--
ALTER TABLE `surat_keluar`
  ADD CONSTRAINT `surat_keluar_ibfk_1` FOREIGN KEY (`klasifikasi_id`) REFERENCES `klasifikasi_surat` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
