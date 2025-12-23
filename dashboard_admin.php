<?php
require_once 'config/config.php';

// Redirect to login if not logged in or not admin
if (!isLoggedIn() || !isAdmin()) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

require_once 'classes/Letter.php';
$letter = new Letter($db);

// Get letter statistics
$stats = $letter->getStats();

// Get all pending letters
$pending_query = "SELECT s.*, u.nama, u.nik, j.nama_surat 
                 FROM surat s 
                 JOIN users u ON s.user_id = u.id 
                 JOIN jenis_surat j ON s.jenis_surat_id = j.id 
                 WHERE s.status = 'pending' 
                 ORDER BY s.tanggal_pengajuan DESC 
                 LIMIT 5";
$pending_result = $db->query($pending_query);

// Get recently completed letters
$completed_query = "SELECT s.*, u.nama, u.nik, j.nama_surat 
                   FROM surat s 
                   JOIN users u ON s.user_id = u.id 
                   JOIN jenis_surat j ON s.jenis_surat_id = j.id 
                   WHERE s.status = 'selesai' 
                   ORDER BY s.tanggal_selesai DESC 
                   LIMIT 5";
$completed_result = $db->query($completed_query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Sistem Surat RT/RW</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* ADMIN DASHBOARD STYLING - PREMIUM LOOK */
        body {
            background: var(--light);
            min-height: 100vh;
            color: var(--dark);
        }

        .header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border-bottom: 3px solid var(--accent);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
        }

        .logo {
            font-size: 24px;
            font-weight: 700;
            color: var(--accent);
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .main {
            padding: 2rem 0;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .admin-header {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            border-left: 5px solid var(--accent);
        }

        .admin-header h1 {
            margin: 0;
            color: var(--primary);
            font-size: 28px;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .admin-header p {
            margin: 0.5rem 0 0 0;
            color: #666;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            text-align: center;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            border-top: 4px solid var(--secondary);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            pointer-events: none;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 35px rgba(0,0,0,0.15);
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

        .stat-card.total {
            border-top-color: var(--muted-purple);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary);
            margin: 0.5rem 0;
        }
        
        .stat-card.pending .stat-number {
            color: var(--warning);
        }
        
        .stat-card.diproses .stat-number {
            color: var(--info);
        }
        
        .stat-card.selesai .stat-number {
            color: var(--success);
        }
        
        .stat-card.ditolak .stat-number {
            color: var(--danger);
        }

        .stat-card.total .stat-number {
            color: var(--muted-purple);
        }
        
        .stat-label {
            color: #888;
            font-size: 0.95rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            opacity: 0.3;
        }
        
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .action-btn {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 10px;
            text-align: center;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            box-shadow: 0 8px 15px rgba(30, 60, 114, 0.3);
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
            box-shadow: 0 12px 25px rgba(30, 60, 114, 0.4);
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
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
            border-top: 4px solid var(--accent);
        }
        
        .recent-list h3 {
            margin: 0 0 1.5rem 0;
            color: var(--primary);
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
            color: var(--primary);
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

        .admin-actions {
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .admin-actions a {
            display: inline-block;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 0.8rem 2rem;
            margin: 0.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .admin-actions a:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(30, 60, 114, 0.3);
        }
        
        .recent-item {
            padding: 0.75rem 0;
            border-bottom: 1px solid #f0f0f0;
            font-size: 0.9rem;
        }
        
        .recent-item:last-child {
            border-bottom: none;
        }
        
        .recent-item-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.25rem;
        }
        
        .recent-item-type {
            color: #666;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">🏛️ ADMIN DASHBOARD</div>
                <nav class="nav">
                    <ul>
                        <li><a href="dashboard_admin.php" class="nav-link nav-link--active">📊 Dashboard</a></li>
                        <li><a href="admin.php" class="nav-link">📋 Kelola Surat</a></li>
                        <li><a href="admin_users.php" class="nav-link">👥 Manage Users</a></li>
                        <li><a href="login_history.php" class="nav-link">🔐 Riwayat Login</a></li>
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
                    <span>👋</span> Selamat Datang, <span style="color: var(--accent);"><?php echo e($_SESSION['nama']); ?></span>
                </h1>
                <p>Administrator Sistem Surat RT/RW Kota Tangerang</p>
            </div>

            <!-- Statistics Cards -->
            <div class="dashboard-grid">
                <div class="stat-card total">
                    <div class="stat-icon">📊</div>
                    <div class="stat-label">Total Surat</div>
                    <div class="stat-number"><?php echo $stats['total']; ?></div>
                </div>
                <div class="stat-card pending">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-label">Menunggu Approval</div>
                    <div class="stat-number"><?php echo $stats['pending']; ?></div>
                </div>
                <div class="stat-card diproses">
                    <div class="stat-icon">⚙️</div>
                    <div class="stat-label">Sedang Diproses</div>
                    <div class="stat-number"><?php echo $stats['diproses']; ?></div>
                </div>
                <div class="stat-card selesai">
                    <div class="stat-icon">✅</div>
                    <div class="stat-label">Selesai</div>
                    <div class="stat-number"><?php echo $stats['selesai']; ?></div>
                </div>
                <div class="stat-card ditolak">
                    <div class="stat-icon">❌</div>
                    <div class="stat-label">Ditolak</div>
                    <div class="stat-number"><?php echo $stats['ditolak']; ?></div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <a href="admin.php" class="action-btn">
                    <span class="action-btn-icon">📋</span>
                    <span>Kelola Semua Surat</span>
                </a>
                <a href="admin_users.php" class="action-btn">
                    <span class="action-btn-icon">👥</span>
                    <span>Manage Users</span>
                </a>
                <a href="approval_surat.php" class="action-btn">
                    <span class="action-btn-icon">✓</span>
                    <span>Approval Surat</span>
                </a>
                <a href="dashboard_admin.php" class="action-btn">
                    <span class="action-btn-icon">🔄</span>
                    <span>Refresh</span>
                </a>
            </div>

            <!-- Recent Activity -->
            <div class="recent-section">
                <!-- Pending Letters -->
                <div class="recent-list">
                    <h3>
                        <span class="recent-list-icon">⏳</span>
                        Surat Menunggu Approval (Top 5)
                    </h3>
                    <?php if ($pending_result && $pending_result->rowCount() > 0): ?>
                        <?php while ($row = $pending_result->fetch(PDO::FETCH_ASSOC)): ?>
                            <div class="letter-item">
                                <div class="letter-info">
                                    <div style="flex: 1;">
                                        <div class="letter-name">👤 <?php echo e($row['nama']); ?></div>
                                        <div class="letter-type">📄 <?php echo e($row['nama_surat']); ?></div>
                                        <div class="letter-date">📅 <?php echo date('d M Y', strtotime($row['tanggal_pengajuan'])); ?></div>
                                    </div>
                                    <span class="badge badge-pending">PENDING</span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div style="text-align: center; padding: 2rem; color: #999;">
                            <p style="font-size: 3rem;">✅</p>
                            <p>Tidak ada surat menunggu approval</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Completed Letters -->
                <div class="recent-list">
                    <h3>
                        <span class="recent-list-icon">✅</span>
                        Surat Selesai (Top 5)
                    </h3>
                    <?php if ($completed_result && $completed_result->rowCount() > 0): ?>
                        <?php while ($row = $completed_result->fetch(PDO::FETCH_ASSOC)): ?>
                            <div class="letter-item">
                                <div class="letter-info">
                                    <div style="flex: 1;">
                                        <div class="letter-name">👤 <?php echo e($row['nama']); ?></div>
                                        <div class="letter-type">📄 <?php echo e($row['nama_surat']); ?></div>
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

            <!-- Admin Control Panel -->
            <div class="admin-actions">
                <h3 style="margin-top: 0; color: var(--primary);">⚙️ Panel Kontrol Admin</h3>
                <a href="admin.php">📋 Kelola Semua Surat</a>
                <a href="admin_users.php">👥 Manage Users & Roles</a>
                <a href="approval_surat.php">✓ Approval Surat RT/RW</a>
                <a href="dashboard_admin.php">🔄 Refresh Dashboard</a>
            </div>
        </div>
    </div>

    <div class="footer">
        <div class="container">
            <p>&copy; 2024 Sistem Surat RT/RW - Admin Dashboard. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
