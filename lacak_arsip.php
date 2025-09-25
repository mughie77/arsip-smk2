<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lacak Arsip - Sistem Arsip Digital Premium</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <!-- Header / Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-archive"></i>
                Arsip<strong>Digital</strong>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="lacak_arsip.php">Lacak Arsip</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-outline-premium ms-lg-3" href="login.php">
                            <i class="fas fa-sign-in-alt"></i> Login Admin
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="fas fa-search"></i> Lacak Status Arsip Anda</h4>
                    </div>
                    <div class="card-body">
                        <p>Masukkan Nomor Arsip yang Anda terima untuk melihat detail dan status dokumen.</p>
                        <form action="lacak_arsip.php" method="GET">
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" name="nomor_arsip" placeholder="Contoh: SM-20240520-001" required>
                                <button class="btn btn-primary" type="submit">Lacak Arsip</button>
                            </div>
                        </form>

                        <div id="hasilLacak" class="mt-4">
                            <?php
                            require_once 'config/koneksi.php';

                            if (isset($_GET['nomor_arsip']) && !empty($_GET['nomor_arsip'])) {
                                $nomor_arsip = mysqli_real_escape_string($koneksi, $_GET['nomor_arsip']);

                                $sql = "
                                    (SELECT
                                        id, nomor_arsip as no_identitas, nomor_surat, perihal, asal_surat AS pihak_terkait, tanggal_diterima AS tanggal, nama_file_pdf, 'Surat Masuk' as jenis, 'surat_masuk' as tipe_folder
                                    FROM surat_masuk WHERE nomor_arsip = ? OR nomor_surat = ?)
                                    UNION
                                    (SELECT
                                        id, kode_arsip as no_identitas, nomor_surat, perihal, tujuan_surat AS pihak_terkait, tanggal_kirim AS tanggal, nama_file_pdf, 'Surat Keluar' as jenis, 'surat_keluar' as tipe_folder
                                    FROM surat_keluar WHERE kode_arsip = ? OR nomor_surat = ?)
                                    UNION
                                    (SELECT
                                        id, no_berkas as no_identitas, nama_berkas as nomor_surat, '-' as perihal, '-' as pihak_terkait, tanggal_berkas as tanggal, nama_file_pdf, 'Arsip Berkas' as jenis, 'arsip_berkas' as tipe_folder
                                    FROM arsip_berkas WHERE no_berkas = ?)
                                ";

                                if ($stmt = mysqli_prepare($koneksi, $sql)) {
                                    mysqli_stmt_bind_param($stmt, "sssss", $nomor_arsip, $nomor_arsip, $nomor_arsip, $nomor_arsip, $nomor_arsip);
                                    mysqli_stmt_execute($stmt);
                                    $result = mysqli_stmt_get_result($stmt);

                                    if (mysqli_num_rows($result) > 0) {
                                        $data = mysqli_fetch_assoc($result);
                                        $file_url = 'uploads/' . $data['tipe_folder'] . '/' . htmlspecialchars($data['nama_file_pdf']);
                                        $pihak_terkait_label = ($data['jenis'] == 'Surat Masuk') ? 'Asal Surat' : 'Tujuan Surat';
                            ?>
                                        <div class="card">
                                            <div class="card-header bg-success text-white">
                                                <i class="fas fa-check-circle"></i> Arsip Ditemukan
                                            </div>
                                            <div class="card-body">
                                                <h5 class="card-title"><?php echo htmlspecialchars($data['perihal']); ?></h5>
                                                <hr>
                                                <table class="table table-borderless table-sm">
                                                    <tbody>
                                                        <tr>
                                                            <th style="width: 150px;">Nomor Identitas</th>
                                                            <td>: <?php echo htmlspecialchars($data['no_identitas']); ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th>Jenis Arsip</th>
                                                            <td>: <span class="badge bg-info"><?php echo htmlspecialchars($data['jenis']); ?></span></td>
                                                        </tr>
                                                        <tr>
                                                            <th>Nomor Surat</th>
                                                            <td>: <?php echo htmlspecialchars($data['nomor_surat']); ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th><?php echo $pihak_terkait_label; ?></th>
                                                            <td>: <?php echo htmlspecialchars($data['pihak_terkait']); ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th>Tanggal</th>
                                                            <td>: <?php echo date('d F Y', strtotime($data['tanggal'])); ?></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                <a href="<?php echo $file_url; ?>" target="_blank" class="btn btn-primary mt-3">
                                                    <i class="fas fa-file-pdf"></i> Lihat Berkas PDF
                                                </a>
                                            </div>
                                        </div>
                            <?php
                                    } else {
                                        echo '<div class="alert alert-danger"><i class="fas fa-times-circle"></i> Arsip dengan nomor <strong>' . htmlspecialchars($nomor_arsip) . '</strong> tidak ditemukan.</div>';
                                    }
                                    mysqli_stmt_close($stmt);
                                } else {
                                    echo '<div class="alert alert-danger">Terjadi kesalahan pada sistem.</div>';
                                }
                                mysqli_close($koneksi);
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer mt-auto py-3 bg-light border-top">
        <div class="container text-center">
            <p class="text-muted">&copy; 2024 Sistem Arsip Digital Premium. Hak Cipta Dilindungi.</p>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery (opsional, jika Anda ingin menambahkan interaktivitas) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

</body>
</html>
