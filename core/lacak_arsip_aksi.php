<?php
// File: core/lacak_arsip_aksi.php

require_once '../config/koneksi.php';

// Atur header untuk memastikan output adalah HTML
header('Content-Type: text/html');

if (isset($_POST['nomor_arsip'])) {
    $nomor_arsip = sanitize_input($_POST)['nomor_arsip'];

    if (empty($nomor_arsip)) {
        echo '<div class="alert alert-warning">Nomor arsip tidak boleh kosong.</div>';
        exit();
    }

    // Query menggunakan UNION untuk mencari di kedua tabel (surat_masuk dan surat_keluar)
    // 'jenis' digunakan untuk membedakan asal tabel dan path file
    $sql = "
        (SELECT
            id, nomor_arsip, nomor_surat, perihal, asal_surat AS pihak_terkait, tanggal_diterima AS tanggal, nama_file_pdf, 'Surat Masuk' as jenis
        FROM surat_masuk WHERE nomor_arsip = ?)
        UNION
        (SELECT
            id, nomor_arsip, nomor_surat, perihal, tujuan_surat AS pihak_terkait, tanggal_kirim AS tanggal, nama_file_pdf, 'Surat Keluar' as jenis
        FROM surat_keluar WHERE nomor_arsip = ?)
    ";

    if ($stmt = mysqli_prepare($koneksi, $sql)) {
        mysqli_stmt_bind_param($stmt, "ss", $nomor_arsip, $nomor_arsip);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            $data = mysqli_fetch_assoc($result);

            // Tentukan path file berdasarkan jenis surat
            $file_path = ($data['jenis'] == 'Surat Masuk') ? 'uploads/surat_masuk/' : 'uploads/surat_keluar/';
            $file_url = '../' . $file_path . htmlspecialchars($data['nama_file_pdf']);

            // Tentukan label untuk pihak terkait
            $pihak_terkait_label = ($data['jenis'] == 'Surat Masuk') ? 'Asal Surat' : 'Tujuan Surat';

            // Format output dalam bentuk HTML yang rapi
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
                                <th style="width: 150px;">Nomor Arsip</th>
                                <td>: <?php echo htmlspecialchars($data['nomor_arsip']); ?></td>
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
            // Jika tidak ditemukan
            echo '<div class="alert alert-danger"><i class="fas fa-times-circle"></i> Arsip dengan nomor <strong>' . htmlspecialchars($nomor_arsip) . '</strong> tidak ditemukan.</div>';
        }
        mysqli_stmt_close($stmt);
    } else {
        echo '<div class="alert alert-danger">Terjadi kesalahan pada sistem.</div>';
    }
    mysqli_close($koneksi);
} else {
    echo '<div class="alert alert-warning">Permintaan tidak valid.</div>';
}
?>
