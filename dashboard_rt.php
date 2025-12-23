<?php
require_once 'config/config.php';

// Check if logged in as RT
if (!isLoggedIn() || $_SESSION['level'] !== 'rt') {
    header("Location: login_rt.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$letter = new Letter($db);

// Get statistics
$query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'approved_rt' THEN 1 ELSE 0 END) as approved,
    SUM(CASE WHEN status = 'rejected_rt' THEN 1 ELSE 0 END) as rejected
    FROM surat";

$stmt = $db->prepare($query);
$stmt->execute();
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Get pending letters
$query = "SELECT s.*, u.nama as nama_warga, j.nama as jenis_surat 
    FROM surat s
    JOIN users u ON s.user_id = u.id
    JOIN jenis_surat j ON s.jenis_id = j.id
    WHERE s.status = 'pending' 
    ORDER BY s.created_at DESC
    LIMIT 10";

$stmt = $db->prepare($query);
$stmt->execute();
$pending_letters = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get approved letters
$query = "SELECT s.*, u.nama as nama_warga, j.nama as jenis_surat 
    FROM surat s
    JOIN users u ON s.user_id = u.id
    JOIN jenis_surat j ON s.jenis_id = j.id
    WHERE s.status_rt = 'approved'
    ORDER BY s.created_at DESC
    LIMIT 10";

$stmt = $db->prepare($query);
$stmt->execute();
$approved_letters = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard RT - Sistem Surat RT/RW</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            background-color: #f5f6fa;
        }
        
        .navbar {
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .navbar-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .navbar-title {
            font-size: 1.5rem;
            font-weight: bold;
        }
        
        .navbar-role {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
        }
        
        .navbar-right {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }
        
        .navbar-user {
            text-align: right;
        }
        
        .navbar-logout {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.4);
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .navbar-logout:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.6);
        }
        
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-left: 4px solid #f39c12;
        }
        
        .stat-card.pending {
            border-left-color: #e74c3c;
        }
        
        .stat-card.approved {
            border-left-color: #27ae60;
        }
        
        .stat-card.rejected {
            border-left-color: #c0392b;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: #333;
            margin: 0.5rem 0;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: #666;
        }
        
        .section {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        
        .section-title {
            font-size: 1.3rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #f39c12;
        }
        
        .letter-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .letter-table thead {
            background-color: #f5f6fa;
        }
        
        .letter-table th {
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: #555;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .letter-table td {
            padding: 1rem;
            border-bottom: 1px solid #e0e0e0;
            color: #333;
        }
        
        .letter-table tbody tr:hover {
            background-color: #f9f9f9;
        }
        
        .letter-table tbody tr:hover td {
            color: #333;
        }
        
        .badge {
            display: inline-block;
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-align: center;
            min-width: 80px;
        }
        
        .badge-pending {
            background-color: #ffe6e6;
            color: #e74c3c;
        }
        
        .badge-approved {
            background-color: #e6ffe6;
            color: #27ae60;
        }
        
        .badge-rejected {
            background-color: #f0e6ff;
            color: #8e44ad;
        }
        
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn-action {
            padding: 0.4rem 0.8rem;
            border: none;
            border-radius: 6px;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-view {
            background-color: #3498db;
            color: white;
        }
        
        .btn-view:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
        }
        
        .btn-approve {
            background-color: #27ae60;
            color: white;
        }
        
        .btn-approve:hover {
            background-color: #229954;
            transform: translateY(-2px);
        }
        
        .btn-reject {
            background-color: #e74c3c;
            color: white;
        }
        
        .btn-reject:hover {
            background-color: #c0392b;
            transform: translateY(-2px);
        }
        
        .empty-message {
            text-align: center;
            padding: 2rem;
            color: #999;
        }
        
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 1rem;
                align-items: stretch;
                text-align: center;
            }
            
            .navbar-left, .navbar-right {
                justify-content: center;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .letter-table {
                font-size: 0.9rem;
            }
            
            .letter-table th, .letter-table td {
                padding: 0.75rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-left">
            <div class="navbar-title">🏘️ Dashboard RT</div>
            <span class="navbar-role">KETUA RUKUN TETANGGA</span>
        </div>
        <div class="navbar-right">
            <div class="navbar-user">
                <div style="font-weight: 600;"><?php echo e($_SESSION['nama']); ?></div>
                <div style="font-size: 0.85rem; opacity: 0.9;"><?php echo e($_SESSION['username']); ?></div>
            </div>
            <a href="logout.php" class="navbar-logout">🚪 Logout</a>
        </div>
    </nav>

    <div class="container">
        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">📊 Total Surat</div>
                <div class="stat-value"><?php echo $stats['total'] ?? 0; ?></div>
            </div>
            <div class="stat-card pending">
                <div class="stat-label">⏳ Menunggu Persetujuan</div>
                <div class="stat-value"><?php echo $stats['pending'] ?? 0; ?></div>
            </div>
            <div class="stat-card approved">
                <div class="stat-label">✅ Sudah Disetujui</div>
                <div class="stat-value"><?php echo $stats['approved'] ?? 0; ?></div>
            </div>
            <div class="stat-card rejected">
                <div class="stat-label">❌ Ditolak</div>
                <div class="stat-value"><?php echo $stats['rejected'] ?? 0; ?></div>
            </div>
        </div>

        <!-- Pending Letters -->
        <div class="section">
            <h2 class="section-title">📋 Surat Menunggu Persetujuan RT</h2>
            
            <?php if (!empty($pending_letters)): ?>
                <div style="overflow-x: auto;">
                    <table class="letter-table">
                        <thead>
                            <tr>
                                <th>Nama Warga</th>
                                <th>Jenis Surat</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pending_letters as $s): ?>
                            <tr>
                                <td><?php echo e($s['nama_warga']); ?></td>
                                <td><?php echo e($s['jenis_surat']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($s['created_at'])); ?></td>
                                <td><span class="badge badge-pending">Pending</span></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-action btn-view" onclick="viewLetter(<?php echo $s['id']; ?>)">Lihat</button>
                                        <button class="btn-action btn-approve" onclick="approveLetter(<?php echo $s['id']; ?>)">Setuju</button>
                                        <button class="btn-action btn-reject" onclick="rejectLetter(<?php echo $s['id']; ?>)">Tolak</button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-message">
                    <p>✅ Tidak ada surat menunggu persetujuan</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Approved Letters -->
        <div class="section">
            <h2 class="section-title">✅ Surat Sudah Disetujui RT</h2>
            
            <?php if (!empty($approved_letters)): ?>
                <div style="overflow-x: auto;">
                    <table class="letter-table">
                        <thead>
                            <tr>
                                <th>Nama Warga</th>
                                <th>Jenis Surat</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($approved_letters as $s): ?>
                            <tr>
                                <td><?php echo e($s['nama_warga']); ?></td>
                                <td><?php echo e($s['jenis_surat']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($s['created_at'])); ?></td>
                                <td><span class="badge badge-approved">Disetujui</span></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-action btn-view" onclick="viewLetter(<?php echo $s['id']; ?>)">Lihat</button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-message">
                    <p>📭 Belum ada surat yang disetujui</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function viewLetter(id) {
            window.location.href = `detail_surat.php?id=${id}`;
        }

        function approveLetter(id) {
            if (confirm('Setujui surat ini?')) {
                window.location.href = `update_letter_status.php?id=${id}&status=approved_rt`;
            }
        }

        function rejectLetter(id) {
            const reason = prompt('Alasan penolakan:');
            if (reason) {
                window.location.href = `update_letter_status.php?id=${id}&status=rejected_rt&reason=${encodeURIComponent(reason)}`;
            }
        }
    </script>
</body>
</html>
