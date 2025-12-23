<?php
require_once 'config/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';

// Process registration form
if ($_POST) {
    require_once 'classes/User.php';
    
    $database = new Database();
    $db = $database->getConnection();
    
    $user = new User($db);
    
    // Set user properties
    $user->nik = $_POST['nik'];
    $user->nama = $_POST['nama'];
    $user->tempat_lahir = $_POST['tempat_lahir'];
    $user->tanggal_lahir = $_POST['tanggal_lahir'];
    $user->jenis_kelamin = $_POST['jenis_kelamin'];
    $user->alamat = $_POST['alamat'];
    $user->rt = $_POST['rt'];
    $user->rw = $_POST['rw'];
    $user->agama = $_POST['agama'];
    $user->pekerjaan = $_POST['pekerjaan'];
    $user->password = $_POST['password'];
    
    // Validate password confirmation
    if ($_POST['password'] !== $_POST['confirm_password']) {
        $error = "Password dan konfirmasi password tidak cocok!";
    } elseif ($user->nikExists()) {
        $error = "NIK sudah terdaftar!";
    } else {
        if ($user->register()) {
            $_SESSION['message'] = "Pendaftaran berhasil! Silakan login.";
            header("Location: login.php");
            exit();
        } else {
            $error = "Terjadi kesalahan saat mendaftar. Silakan coba lagi atau periksa data Anda.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Sistem Surat RT/RW</title>
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
                    <h2 class="auth-title">Daftar Akun Baru</h2>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo e($error); ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="nik" class="form-label">NIK *</label>
                            <input type="text" class="form-control" id="nik" name="nik" required 
                                   placeholder="Masukkan NIK 16 digit" maxlength="16" pattern="[0-9]{16}">
                        </div>
                        
                        <div class="form-group">
                            <label for="nama" class="form-label">Nama Lengkap *</label>
                            <input type="text" class="form-control" id="nama" name="nama" required 
                                   placeholder="Masukkan nama lengkap">
                        </div>
                        
                        <div class="form-group">
                            <label for="tempat_lahir" class="form-label">Tempat Lahir *</label>
                            <input type="text" class="form-control" id="tempat_lahir" name="tempat_lahir" required 
                                   placeholder="Masukkan tempat lahir">
                        </div>
                        
                        <div class="form-group">
                            <label for="tanggal_lahir" class="form-label">Tanggal Lahir *</label>
                            <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="jenis_kelamin" class="form-label">Jenis Kelamin *</label>
                            <select class="form-control form-select" id="jenis_kelamin" name="jenis_kelamin" required>
                                <option value="">Pilih Jenis Kelamin</option>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="alamat" class="form-label">Alamat Lengkap *</label>
                            <textarea class="form-control" id="alamat" name="alamat" required 
                                      placeholder="Masukkan alamat lengkap" rows="3"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="rt" class="form-label">RT *</label>
                            <input type="text" class="form-control" id="rt" name="rt" required 
                                   placeholder="Contoh: 001" maxlength="3" pattern="[0-9]{3}">
                        </div>
                        
                        <div class="form-group">
                            <label for="rw" class="form-label">RW *</label>
                            <input type="text" class="form-control" id="rw" name="rw" required 
                                   placeholder="Contoh: 002" maxlength="3" pattern="[0-9]{3}">
                        </div>
                        
                        <div class="form-group">
                            <label for="agama" class="form-label">Agama *</label>
                            <select class="form-control form-select" id="agama" name="agama" required>
                                <option value="">Pilih Agama</option>
                                <option value="Islam">Islam</option>
                                <option value="Kristen">Kristen</option>
                                <option value="Katolik">Katolik</option>
                                <option value="Hindu">Hindu</option>
                                <option value="Buddha">Buddha</option>
                                <option value="Konghucu">Konghucu</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="pekerjaan" class="form-label">Pekerjaan *</label>
                            <input type="text" class="form-control" id="pekerjaan" name="pekerjaan" required 
                                   placeholder="Masukkan pekerjaan">
                        </div>
                        
                        <div class="form-group">
                            <label for="password" class="form-label">Password *</label>
                            <input type="password" class="form-control" id="password" name="password" required 
                                   placeholder="Masukkan password" minlength="6">
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password" class="form-label">Konfirmasi Password *</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required 
                                   placeholder="Konfirmasi password">
                        </div>
                        
                        <button type="submit" class="btn btn-primary" style="width: 100%;">Daftar</button>
                    </form>
                    
                    <div class="auth-link">
                        <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        <div class="container">
            <p>&copy; 2024 Sistem Surat RT/RW. All rights reserved.</p>
        </div>
    </div>
</body>
</html>