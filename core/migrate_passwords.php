<?php
// File: core/migrate_passwords.php

// Panggil file koneksi
require_once '../config/koneksi.php';

echo "<h1>Password Migration Script</h1>";

// Ambil semua admin yang passwordnya belum di-hash (misal, panjang < 60)
$sql = "SELECT id, username, password FROM admins WHERE LENGTH(password) < 60";
$result = mysqli_query($koneksi, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    echo "<p>Found " . mysqli_num_rows($result) . " users to migrate.</p>";

    while ($row = mysqli_fetch_assoc($result)) {
        $id = $row['id'];
        $plainPassword = $row['password'];

        // Buat hash dari password lama
        $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

        // Update database dengan hash baru
        $update_sql = "UPDATE admins SET password = ? WHERE id = ?";

        if ($stmt = mysqli_prepare($koneksi, $update_sql)) {
            mysqli_stmt_bind_param($stmt, "si", $hashedPassword, $id);
            if (mysqli_stmt_execute($stmt)) {
                echo "<p style='color:green;'>Successfully migrated password for user: " . htmlspecialchars($row['username']) . "</p>";
            } else {
                echo "<p style='color:red;'>Failed to migrate password for user: " . htmlspecialchars($row['username']) . ". Error: " . mysqli_stmt_error($stmt) . "</p>";
            }
            mysqli_stmt_close($stmt);
        } else {
            echo "<p style='color:red;'>Failed to prepare statement for user: " . htmlspecialchars($row['username']) . "</p>";
        }
    }
} else if ($result) {
    echo "<p>No users with plain text passwords found. Migration might already be complete.</p>";
} else {
    echo "<p style='color:red;'>Error querying database: " . mysqli_error($koneksi) . "</p>";
}

echo "<hr><p><strong>Migration process finished.</strong></p>";
echo "<p><strong>PENTING:</strong> Setelah selesai, sangat disarankan untuk menghapus file ini dari server Anda demi keamanan.</p>";

// Tutup koneksi
mysqli_close($koneksi);
?>
