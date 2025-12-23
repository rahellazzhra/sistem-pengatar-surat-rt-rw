<?php
/**
 * Create Admin User
 * File untuk membuat user admin dengan password plain text
 */

require_once 'config/config.php';

$message = '';
$error = '';

if ($_POST) {
    $nik = $_POST['nik'];
    $nama = $_POST['nama'];
    $password = $_POST['password'];
    
    if (empty($nik) || empty($nama) || empty($password)) {
        $error = "Semua field harus diisi!";
    } else {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            // Check if NIK already exists
            $query = "SELECT id FROM users WHERE nik = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$nik]);
            
            if ($stmt->rowCount() > 0) {
                $error = "NIK sudah terdaftar!";
            } else {
                // Insert admin user dengan password plain text (tidak di-hash)
                $query = "INSERT INTO users (nik, nama, tempat_lahir, tanggal_lahir, jenis_kelamin, alamat, rt, rw, agama, pekerjaan, password, level) 
                         VALUES (?, ?, 'Jakarta', '1990-01-01', 'L', 'Alamat Admin', '001', '001', 'Islam', 'Admin RT/RW', ?, 'admin')";
                $stmt = $db->prepare($query);
                
                if ($stmt->execute([$nik, $nama, $password])) {
                    $message = "✓ Admin berhasil dibuat! <br>
                               NIK: $nik <br>
                               Password: $password <br><br>
                               <a href='login.php' style='color: blue;'>Login sekarang</a>";
                } else {
                    $error = "Gagal membuat admin!";
                }
            }
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Admin - Sistem Surat RT/RW</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">Sistem Surat RT/RW</div>
            </div>
        </div>
    </div>

    <div class="main">
        <div class="container">
            <div class="auth-container">
                <div class="auth-form">
                    <h2 class="auth-title">Buat Akun Admin</h2>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo e($error); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($message): ?>
                        <div class="alert alert-success"><?php echo $message; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="nik" class="form-label">NIK (16 digit)</label>
                            <input type="text" class="form-control" id="nik" name="nik" required 
                                   placeholder="Contoh: 1234567890123456" maxlength="16" pattern="[0-9]{16}">
                        </div>

                        <div class="form-group">
                            <label for="nama" class="form-label">Nama</label>
                            <input type="text" class="form-control" id="nama" name="nama" required 
                                   placeholder="Nama lengkap admin">
                        </div>

                        <div class="form-group">
                            <label for="password" class="form-label">Password</label>
                            <input type="text" class="form-control" id="password" name="password" required 
                                   placeholder="Masukkan password (plain text, tidak akan di-hash)">
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%;">Buat Admin</button>
                    </form>

                    <div style="text-align: center; margin-top: 15px;">
                        <a href="login.php" style="color: blue;">Kembali ke login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
