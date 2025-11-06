<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generator Hash Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Generator Hash Password (BCRYPT)</h4>
                    </div>
                    <div class="card-body">
                        <p>Gunakan alat ini untuk membuat hash password yang aman. Salin hash yang dihasilkan dan masukkan ke dalam kolom 'password' di tabel 'admins' pada database.</p>
                        <form method="POST" action="hash_test.php">
                            <div class="mb-3">
                                <label for="password" class="form-label">Masukkan Password:</label>
                                <input type="text" class="form-control" name="password" id="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Buat Hash</button>
                        </form>

                        <?php
                        if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['password'])) {
                            $password = $_POST['password'];
                            $hash = password_hash($password, PASSWORD_DEFAULT);
                        ?>
                            <div class="mt-4">
                                <h5>Password Asli:</h5>
                                <p class="text-danger"><?php echo htmlspecialchars($password); ?></p>
                                <h5>Hash yang Dihasilkan:</h5>
                                <div class="alert alert-success">
                                    <p style="word-wrap: break-word;"><?php echo htmlspecialchars($hash); ?></p>
                                </div>
                                <p><strong>Penting:</strong> Simpan hash ini dengan aman. Jangan pernah menyimpan password asli.</p>
                            </div>
                        <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>