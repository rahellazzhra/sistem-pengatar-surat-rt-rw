<?php
require_once 'config/config.php';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Redirect admin/rt/rw to their respective dashboards
if (isAdmin()) {
    header("Location: dashboard_admin.php");
    exit();
}

if ($_SESSION['level'] === 'rt') {
    header("Location: dashboard_rt.php");
    exit();
}

if ($_SESSION['level'] === 'rw') {
    header("Location: dashboard_rw.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$error = '';
$success = '';

// Get letter types
$query = "SELECT * FROM jenis_surat ORDER BY nama_surat";
$stmt = $db->prepare($query);
$stmt->execute();
$jenis_surat = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Process form submission
if ($_POST) {
    require_once 'classes/Letter.php';
    
    $letter = new Letter($db);
    
    $letter->user_id = $_SESSION['user_id'];
    $letter->jenis_surat_id = $_POST['jenis_surat_id'];
    $letter->keperluan = $_POST['keperluan'];
    $letter->tanggal_pengajuan = date('Y-m-d');
    
    if ($letter->create()) {
        $success = "Pengajuan surat berhasil dikirim!";
    } else {
        $error = "Terjadi kesalahan saat mengajukan surat. Silakan coba lagi.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Surat - Sistem Surat RT/RW</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="warga-page">
    <div class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">Sistem Surat RT/RW</div>
                <nav class="nav">
                    <ul>
                        <li><a href="index.php">Dashboard</a></li>
                        <li><a href="pengajuan.php">Pengajuan Surat</a></li>
                        <li><a href="surat_saya.php">Surat Saya</a></li>
                        <?php if (isAdmin()): ?>
                            <li><a href="admin.php">Admin Panel</a></li>
                        <?php endif; ?>
                        <li><a href="logout.php">Logout</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <div class="main">
        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Pengajuan Surat Baru</h2>
                </div>
                <div class="card-content">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo e($error); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo e($success); ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="jenis_surat_id" class="form-label">Jenis Surat *</label>
                            <select class="form-control form-select" id="jenis_surat_id" name="jenis_surat_id" required>
                                <option value="">Pilih Jenis Surat</option>
                                <?php foreach ($jenis_surat as $js): ?>
                                    <option value="<?php echo $js['id']; ?>"><?php echo e($js['nama_surat']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (count($jenis_surat) > 0): ?>
                                <small class="text-muted">Deskripsi: <?php echo e($jenis_surat[0]['deskripsi']); ?></small>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="keperluan" class="form-label">Keperluan *</label>
                            <textarea class="form-control" id="keperluan" name="keperluan" required 
                                      placeholder="Jelaskan keperluan surat ini secara detail..." rows="6"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Ajukan Surat</button>
                            <a href="index.php" class="btn btn-secondary">Kembali</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Informasi Pengajuan</h2>
                </div>
                <div class="card-content">
                    <p><strong>Proses Pengajuan:</strong></p>
                    <ol>
                        <li>Isi form pengajuan surat dengan data yang benar</li>
                        <li>Surat akan masuk dalam status "Pending"</li>
                        <li>Admin akan memproses surat Anda</li>
                        <li>Anda akan mendapatkan notifikasi ketika surat selesai</li>
                        <li>Surat dapat diambil di kantor RT/RW</li>
                    </ol>
                    
                    <p><strong>Catatan:</strong></p>
                    <ul>
                        <li>Pastikan data yang dimasukkan akurat dan benar</li>
                        <li>Proses verifikasi membutuhkan waktu 1-3 hari kerja</li>
                        <li>Hubungi admin jika ada pertanyaan</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        <div class="container">
            <p>&copy; 2024 Sistem Surat RT/RW. All rights reserved.</p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectElement = document.getElementById('jenis_surat_id');
            const jenisSuratData = <?php echo json_encode($jenis_surat); ?>;
            
            if (selectElement && jenisSuratData) {
                // Add descriptions to options
                jenisSuratData.forEach(js => {
                    const option = selectElement.querySelector(`option[value="${js.id}"]`);
                    if (option) {
                        option.setAttribute('data-description', js.deskripsi || 'Tidak ada deskripsi');
                    }
                });

                // Show description for selected letter type
                selectElement.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    const description = selectedOption.getAttribute('data-description');
                    const descElement = document.querySelector('.text-muted');
                    
                    if (descElement && description) {
                        descElement.textContent = 'Deskripsi: ' + description;
                    } else if (descElement) {
                        descElement.textContent = 'Deskripsi: Tidak ada deskripsi';
                    }
                });
            }
        });
    </script>
</body>
</html>