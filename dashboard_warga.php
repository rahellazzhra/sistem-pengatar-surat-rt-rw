<?php
require_once 'config/config.php';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Redirect admin to admin dashboard
if (isAdmin()) {
    header("Location: dashboard_admin.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

require_once 'classes/Letter.php';
$letter = new Letter($db);

// Get user's letter statistics
$user_id = $_SESSION['user_id'];
$user_stats_query = "SELECT
                    COUNT(*) as total,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending,
                    COUNT(CASE WHEN status = 'diproses' THEN 1 END) as diproses,
                    COUNT(CASE WHEN status = 'selesai' THEN 1 END) as selesai,
                    COUNT(CASE WHEN status = 'ditolak' THEN 1 END) as ditolak
                    FROM surat
                    WHERE user_id = ?";
$stmt = $db->prepare($user_stats_query);
$stmt->execute([$user_id]);
$user_stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Get user's recent letters
$recent_query = "SELECT s.*, j.nama_surat 
                FROM surat s 
                JOIN jenis_surat j ON s.jenis_surat_id = j.id 
                WHERE s.user_id = ? 
                ORDER BY s.tanggal_pengajuan DESC 
                LIMIT 5";
$stmt = $db->prepare($recent_query);
$stmt->execute([$user_id]);
$recent_letters = $stmt;

// Get completed letters
$completed_query = "SELECT s.*, j.nama_surat 
                   FROM surat s 
                   JOIN jenis_surat j ON s.jenis_surat_id = j.id 
                   WHERE s.user_id = ? AND s.status = 'selesai'
                   ORDER BY s.tanggal_selesai DESC 
                   LIMIT 5";
$stmt = $db->prepare($completed_query);
$stmt->execute([$user_id]);
$completed_letters = $stmt;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem Surat RT/RW</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* WARGA DASHBOARD STYLING - SAME AS ADMIN */
        body {
            background: var(--light);
            min-height: 100vh;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--dark);
        }

        .header {
            background: linear-gradient(135deg, var(--secondary) 0%, var(--secondary-dark) 100%);
            color: white;
            padding: 1.5rem 0;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--muted-purple);
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .nav ul {
            list-style: none;
            display: flex;
            gap: 2rem;
            margin: 0;
            padding: 0;
        }

        .nav a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            padding: 0.5rem 1rem;
            border-radius: 6px;
        }

        .nav a:hover {
            background: rgba(255,255,255,0.1);
            color: var(--muted-purple);
        }

        .main {
            flex: 1;
            padding: 2rem 0;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .footer {
            background: linear-gradient(135deg, var(--secondary) 0%, var(--secondary-dark) 100%);
            color: white;
            text-align: center;
            padding: 1.5rem;
            margin-top: 2rem;
        }

        .admin-header {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            border-left: 5px solid var(--muted-purple);
        }

        .admin-header h1 {
            margin: 0 0 0.5rem 0;
            color: var(--secondary);
            font-size: 2rem;
        }

        .admin-header p {
            margin: 0;
            color: #888;
            font-size: 0.95rem;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
            text-align: center;
            border-top: 4px solid var(--warning);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(102, 126, 234, 0.2);
        }

        .stat-card.pending {
            border-top-color: var(--warning);
        }

        .stat-card.diproses {
            border-top-color: var(--info);
        }

        .stat-card.selesai {
            border-top-color: var(--success);
        }

        .stat-card.ditolak {
            border-top-color: var(--danger);
        }

        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            opacity: 0.8;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--secondary);
            margin: 0.5rem 0;
        }

        .stat-card.pending .stat-number {
            color: #f39c12;
        }

        .stat-card.diproses .stat-number {
            color: #3498db;
        }

        .stat-card.selesai .stat-number {
            color: #2ecc71;
        }

        .stat-card.ditolak .stat-number {
            color: #e74c3c;
        }

        .stat-label {
            color: #888;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .action-btn {
            background: linear-gradient(135deg, var(--secondary) 0%, var(--secondary-dark) 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 10px;
            text-align: center;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            box-shadow: 0 8px 15px rgba(102, 126, 234, 0.3);
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            align-items: center;
        }

        .action-btn-icon {
            font-size: 2rem;
        }

        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 25px rgba(102, 126, 234, 0.4);
            background: linear-gradient(135deg, var(--secondary-dark) 0%, var(--secondary) 100%);
        }

        .recent-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(600px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .recent-list {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            border-top: 4px solid var(--muted-purple);
        }

        .recent-list h3 {
            margin: 0 0 1.5rem 0;
            color: var(--secondary);
            font-size: 18px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .recent-list-icon {
            font-size: 1.5rem;
        }

        .letter-item {
            padding: 1rem;
            border-bottom: 1px solid #f0f0f0;
            transition: all 0.3s ease;
            border-radius: 6px;
            margin-bottom: 0.5rem;
        }

        .letter-item:last-child {
            border-bottom: none;
        }

        .letter-item:hover {
            background: #f9f9f9;
            padding-left: 1.5rem;
        }

        .letter-info {
            display: flex;
            justify-content: space-between;
            align-items: start;
            gap: 1rem;
        }

        .letter-name {
            font-weight: 600;
            color: var(--secondary);
            margin-bottom: 0.3rem;
        }

        .letter-type {
            font-size: 0.9rem;
            color: #888;
            margin-bottom: 0.3rem;
        }

        .letter-date {
            font-size: 0.85rem;
            color: #aaa;
        }

        .badge {
            display: inline-block;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .badge-pending {
            background: #fff3cd;
            color: #856404;
        }

        .badge-diproses {
            background: #d1ecf1;
            color: #0c5460;
        }

        .badge-selesai {
            background: #d4edda;
            color: #155724;
        }

        .badge-ditolak {
            background: #f8d7da;
            color: #721c24;
        }

        .recent-item {
            padding: 1rem;
            border-bottom: 1px solid #f0f0f0;
            transition: all 0.3s ease;
            border-radius: 6px;
            margin-bottom: 0.5rem;
        }

        .recent-item:last-child {
            border-bottom: none;
        }

        .recent-item:hover {
            background: #f9f9f9;
            padding-left: 1.5rem;
        }

        .recent-item-type {
            font-weight: 600;
            color: var(--secondary);
            margin-bottom: 0.3rem;
        }

        .recent-item-status {
            display: inline-block;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-diproses {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .status-selesai {
            background-color: #d4edda;
            color: #155724;
        }

        .status-ditolak {
            background-color: #f8d7da;
            color: #721c24;
        }

        .warga-actions {
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .warga-actions a {
            display: inline-block;
            background: linear-gradient(135deg, var(--secondary) 0%, var(--secondary-dark) 100%);
            color: white;
            padding: 0.8rem 2rem;
            margin: 0.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .warga-actions a:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }
    </style>
</head>
<body class="warga-page">
    <div class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">🏛️ DASHBOARD WARGA</div>
                <nav class="nav">
                    <ul>
                        <li><a href="dashboard_warga.php" class="nav-link nav-link--active nav-link--muted">📊 Dashboard</a></li>
                        <li><a href="pengajuan.php" class="nav-link">✍️ Pengajuan Surat</a></li>
                        <li><a href="surat_saya.php" class="nav-link">📋 Surat Saya</a></li>
                        <li><a href="logout.php" class="nav-link">🚪 Logout</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <div class="main">
        <div class="container">
            <!-- Header Section -->
            <div class="admin-header">
                <h1>
                    <span>👋</span> Selamat Datang, <span style="color: var(--muted-purple);"><?php echo e($_SESSION['nama']); ?></span>
                </h1>
                <p>Dashboard Warga - Kelola pengajuan surat Anda</p>
            </div>

            <!-- Statistics Cards -->
            <div class="dashboard-grid">
                <div class="stat-card">
                    <div class="stat-icon">📊</div>
                    <div class="stat-label">Total Pengajuan</div>
                    <div class="stat-number"><?php echo $user_stats['total']; ?></div>
                </div>
                <div class="stat-card pending">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-label">Menunggu</div>
                    <div class="stat-number"><?php echo $user_stats['pending']; ?></div>
                </div>
                <div class="stat-card diproses">
                    <div class="stat-icon">⚙️</div>
                    <div class="stat-label">Diproses</div>
                    <div class="stat-number"><?php echo $user_stats['diproses']; ?></div>
                </div>
                <div class="stat-card selesai">
                    <div class="stat-icon">✅</div>
                    <div class="stat-label">Selesai</div>
                    <div class="stat-number"><?php echo $user_stats['selesai']; ?></div>
                </div>
                <div class="stat-card ditolak">
                    <div class="stat-icon">❌</div>
                    <div class="stat-label">Ditolak</div>
                    <div class="stat-number"><?php echo $user_stats['ditolak']; ?></div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <a href="surat_saya.php" class="action-btn">
                    <span class="action-btn-icon">📋</span>
                    <span>Lihat Semua Surat</span>
                </a>
                <a href="surat_saya.php" class="action-btn">
                    <span class="action-btn-icon">📝</span>
                    <span>Detail Surat</span>
                </a>
                <a href="dashboard_warga.php" class="action-btn">
                    <span class="action-btn-icon">🔄</span>
                    <span>Refresh</span>
                </a>
            </div>

            <!-- Recent Activity -->
            <div class="recent-section">
                <!-- Recent Submissions -->
                <div class="recent-list">
                    <h3>
                        <span class="recent-list-icon">📝</span>
                        Pengajuan Terbaru (Top 5)
                    </h3>
                    <?php if ($recent_letters && $recent_letters->rowCount() > 0): ?>
                        <?php while ($row = $recent_letters->fetch(PDO::FETCH_ASSOC)): ?>
                            <div class="letter-item">
                                <div class="letter-info">
                                    <div style="flex: 1;">
                                        <div class="letter-name">📄 <?php echo e($row['nama_surat']); ?></div>
                                        <div class="letter-type">Status: <?php echo ucfirst($row['status']); ?></div>
                                        <div class="letter-date">📅 <?php echo date('d M Y', strtotime($row['tanggal_pengajuan'])); ?></div>
                                    </div>
                                    <span class="badge badge-<?php echo $row['status']; ?>"><?php echo strtoupper($row['status']); ?></span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div style="text-align: center; padding: 2rem; color: #999;">
                            <p style="font-size: 3rem;">📭</p>
                            <p>Anda belum mengajukan surat</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Completed Letters -->
                <div class="recent-list">
                    <h3>
                        <span class="recent-list-icon">✅</span>
                        Surat Selesai (Top 5)
                    </h3>
                    <?php if ($completed_letters && $completed_letters->rowCount() > 0): ?>
                        <?php while ($row = $completed_letters->fetch(PDO::FETCH_ASSOC)): ?>
                            <div class="letter-item">
                                <div class="letter-info">
                                    <div style="flex: 1;">
                                        <div class="letter-name">📄 <?php echo e($row['nama_surat']); ?></div>
                                        <?php if ($row['no_surat']): ?>
                                            <div class="letter-type">No. Surat: <strong><?php echo e($row['no_surat']); ?></strong></div>
                                        <?php endif; ?>
                                        <div class="letter-date">📅 <?php echo date('d M Y', strtotime($row['tanggal_selesai'])); ?></div>
                                    </div>
                                    <span class="badge badge-selesai">SELESAI</span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div style="text-align: center; padding: 2rem; color: #999;">
                            <p style="font-size: 3rem;">📭</p>
                            <p>Belum ada surat yang selesai</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Warga Actions -->
            <div class="warga-actions">
                <h3 style="margin-top: 0; color: var(--secondary);">📋 Menu Warga</h3>
                <a href="surat_saya.php">📋 Lihat Semua Surat</a>
                <a href="surat_saya.php">📝 Detail Surat</a>
                <a href="dashboard_warga.php">🔄 Refresh Dashboard</a>
            </div>
        </div>
    </div>

    <div class="footer">
        <div class="container">
            <p>&copy; 2024 Sistem Surat RT/RW - Warga Dashboard. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
