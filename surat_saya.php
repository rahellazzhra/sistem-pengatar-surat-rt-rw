<?php
require_once 'config/config.php';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

require_once 'classes/Letter.php';
$letter = new Letter($db);

// Get user's letters
$user_letters = $letter->readAll($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Saya - Sistem Surat RT/RW</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Ensure table text is visible */
        .table td {
            color: #333 !important;
        }
        
        .table tbody tr:hover td {
            color: #333 !important;
        }
        
        .text-muted {
            color: #666 !important;
        }
    </style>
</head>
<body class="warga-page">
    <div class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">🏛️ Sistem Surat RT/RW</div>
                <nav class="nav">
                    <ul>
                        <li><a href="dashboard_warga.php">Dashboard</a></li>
                        <li><a href="surat_saya.php" style="color: var(--secondary); border-bottom: 2px solid var(--secondary);">Surat Saya</a></li>
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
                    <h2 class="card-title">Surat Saya</h2>
                </div>
                <div class="card-content">
                    <?php if ($user_letters->rowCount() > 0): ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Jenis Surat</th>
                                    <th>No. Surat</th>
                                    <th>Tanggal Pengajuan</th>
                                    <th>Status</th>
                                    <th>Tanggal Selesai</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; while ($row = $user_letters->fetch(PDO::FETCH_ASSOC)): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo e($row['nama_surat']); ?></td>
                                    <td>
                                        <?php if ($row['no_surat']): ?>
                                            <?php echo e($row['no_surat']); ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($row['tanggal_pengajuan'])); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $row['status']; ?>">
                                            <?php echo ucfirst($row['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($row['tanggal_selesai']): ?>
                                            <?php echo date('d/m/Y', strtotime($row['tanggal_selesai'])); ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="detail_surat.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm">Detail</a>
                                        <?php if ($row['status'] == 'selesai' && strtolower($row['nama_surat']) == 'surat pengantar'): ?>
                                            <a href="cetak_surat.php?id=<?php echo $row['id']; ?>" class="btn btn-secondary btn-sm" target="_blank">Cetak Surat Pengantar</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="text-center">
                            <p>Belum ada surat yang diajukan.</p>
                            <a href="pengajuan.php" class="btn btn-primary">Ajukan Surat Pertama</a>
                        </div>
                    <?php endif; ?>
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