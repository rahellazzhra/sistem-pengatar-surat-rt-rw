<?php
require_once 'config/config.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$message = '';
$error = '';

// Get all users
$query = "SELECT * FROM users ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get statistics
$stats_query = "SELECT 
                (SELECT COUNT(*) FROM users WHERE level = 'warga') as total_warga,
                (SELECT COUNT(*) FROM users WHERE level = 'rt') as total_rt,
                (SELECT COUNT(*) FROM users WHERE level = 'rw') as total_rw,
                (SELECT COUNT(*) FROM users WHERE level = 'admin') as total_admin,
                (SELECT COUNT(*) FROM surat WHERE status = 'pending') as pending_letters,
                (SELECT COUNT(*) FROM surat WHERE status = 'selesai') as completed_letters";
$stmt = $db->prepare($stats_query);
$stmt->execute();
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle delete user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $error = "CSRF token tidak valid.";
    } elseif ($_POST['action'] === 'delete' && isset($_POST['user_id'])) {
        $user_id = intval($_POST['user_id']);
        
        // Prevent deleting self
        if ($user_id === $_SESSION['user_id']) {
            $error = "Anda tidak dapat menghapus akun diri sendiri!";
        } else {
            $delete_query = "DELETE FROM users WHERE id = :id";
            $stmt = $db->prepare($delete_query);
            if ($stmt->execute([':id' => $user_id])) {
                $message = "User berhasil dihapus!";
                logAudit($db, 0, 'USER_DELETED', 'User ID: ' . $user_id);
            } else {
                $error = "Gagal menghapus user.";
            }
        }
    } elseif ($_POST['action'] === 'toggle_status' && isset($_POST['user_id'])) {
        $user_id = intval($_POST['user_id']);
        $new_status = $_POST['new_status'];
        
        $update_query = "UPDATE users SET status = :status WHERE id = :id";
        $stmt = $db->prepare($update_query);
        if ($stmt->execute([':status' => $new_status, ':id' => $user_id])) {
            $message = "Status user berhasil diubah!";
        } else {
            $error = "Gagal mengubah status user.";
        }
    }
    
    // Refresh data after action
    $stmt = $db->prepare($query);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Management - Sistem Surat RT/RW</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); min-height: 100vh; margin: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .header { background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); color: white; padding: 1.5rem 0; box-shadow: 0 4px 12px rgba(30, 60, 114, 0.3); position: sticky; top: 0; z-index: 100; border-bottom: 3px solid var(--accent); }
        .header-content { display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 1.8rem; font-weight: bold; color: var(--accent); text-shadow: 0 2px 4px rgba(0,0,0,0.2); }
        .nav ul { list-style: none; display: flex; gap: 2rem; margin: 0; padding: 0; }
        .nav a { color: white; text-decoration: none; font-weight: 500; transition: all 0.3s; padding: 0.5rem 1rem; border-radius: 6px; }
        .nav a:hover { background: rgba(255,255,255,0.1); color: var(--accent); }
        .main { flex: 1; padding: 2rem 0; }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 1rem; }
        .footer { background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); color: white; text-align: center; padding: 1.5rem; margin-top: 2rem; }
        .admin-container { max-width: 1200px; margin: 0 auto; padding: 2rem 0; }
        .admin-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-box { background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 8px 15px rgba(0,0,0,0.1); text-align: center; border-top: 4px solid var(--accent); transition: all 0.3s; }
        .stat-box:hover { transform: translateY(-3px); box-shadow: 0 12px 25px rgba(0,0,0,0.15); }
        .stat-box h3 { margin: 0 0 0.5rem 0; color: var(--primary); font-size: 2.5rem; font-weight: 700; }
        .stat-box p { margin: 0; color: #666; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px; }
        .users-table { width: 100%; background: white; border-collapse: collapse; border-radius: 12px; overflow: hidden; box-shadow: 0 8px 25px rgba(0,0,0,0.1); margin-top: 2rem; }
        .users-table thead { background: linear-gradient(135deg, #f5f7fa 0%, #e8ebf2 100%); }
        .users-table th { padding: 1rem; text-align: left; font-weight: 600; color: var(--primary); border-bottom: 2px solid var(--accent); }
        .users-table td { padding: 1rem; text-align: left; border-bottom: 1px solid #e0e0e0; }
        .users-table tbody tr:hover { background: #f9f9f9; }
        .badge { display: inline-block; padding: 0.4rem 0.8rem; border-radius: 12px; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; }
        .badge-warga { background: #d1ecf1; color: #0c5460; }
        .badge-rt { background: #fff3cd; color: #856404; }
        .badge-rw { background: #d4edda; color: #155724; }
        .badge-admin { background: #f8d7da; color: #721c24; }
        .badge-aktif { background: #d4edda; color: #155724; }
        .badge-nonaktif { background: #f8d7da; color: #721c24; }
        .action-buttons { display: flex; gap: 0.5rem; }
        .btn-small { padding: 0.4rem 0.8rem; font-size: 0.85rem; border: none; border-radius: 6px; cursor: pointer; transition: all 0.3s; font-weight: 600; }
        .btn-edit { background: linear-gradient(135deg, var(--info) 0%, #2980b9 100%); color: white; }
        .btn-edit:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3); }
        .btn-delete { background: var(--danger); color: white; }
        .btn-delete:hover { background: #c0392b; transform: translateY(-2px); }
        .btn-toggle { background: var(--success); color: white; }
        .btn-toggle:hover { background: #27ae60; transform: translateY(-2px); }
        .alert { padding: 1rem; border-radius: 6px; margin-bottom: 1rem; font-weight: 500; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .btn { display: inline-block; padding: 0.6rem 1.2rem; border-radius: 6px; text-decoration: none; font-weight: 600; border: none; cursor: pointer; transition: all 0.3s; font-size: 0.9rem; margin-right: 0.5rem; }
        .btn-primary { background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); color: white; box-shadow: 0 4px 12px rgba(30, 60, 114, 0.3); }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(30, 60, 114, 0.4); }
        .btn-secondary { background: #e0e0e0; color: #333; }
        .btn-secondary:hover { background: #d0d0d0; }
    </style>
</head>
<body>
    <!-- Navigation -->
    <div class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">🏛️ ADMIN PANEL</div>
                <nav class="nav">
                    <ul>
                        <li><a href="dashboard_admin.php" class="nav-link">📊 Dashboard</a></li>
                        <li><a href="admin.php" class="nav-link">📋 Kelola Surat</a></li>
                        <li><a href="admin_users.php" class="nav-link nav-link--active">👥 Manage Users</a></li>
                        <li><a href="login_history.php" class="nav-link">🔐 Riwayat Login</a></li>
                        <li><a href="logout.php" class="nav-link">🚪 Logout</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <div class="main">
        <div class="container">
            <div class="admin-container">
                <h1 style="color: white; margin-bottom: 2rem;">👥 Manage Users</h1>

                <!-- Messages -->
                <?php if ($message): ?>
                    <div class="alert alert-success"><?php echo e($message); ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo e($error); ?></div>
                <?php endif; ?>

                <!-- Statistics -->
                <div class="admin-grid">
                    <div class="stat-box">
                        <h3><?php echo $stats['total_warga']; ?></h3>
                        <p>Total Warga</p>
                    </div>
                    <div class="stat-box">
                        <h3><?php echo $stats['total_rt']; ?></h3>
                        <p>Ketua RT</p>
                    </div>
                    <div class="stat-box">
                        <h3><?php echo $stats['total_rw']; ?></h3>
                        <p>Ketua RW</p>
                    </div>
                    <div class="stat-box">
                        <h3><?php echo $stats['total_admin']; ?></h3>
                        <p>Admin</p>
                    </div>
                    <div class="stat-box">
                        <h3><?php echo $stats['pending_letters']; ?></h3>
                        <p>Surat Pending</p>
                    </div>
                    <div class="stat-box">
                        <h3><?php echo $stats['completed_letters']; ?></h3>
                        <p>Surat Selesai</p>
                    </div>
                </div>

                <!-- Users Table -->
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Username</th>
                            <th>Nama</th>
                            <th>NIK</th>
                            <th>Level</th>
                            <th>Status</th>
                            <th>RT/RW</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; ?>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo e($user['username']); ?></td>
                            <td><?php echo e($user['nama']); ?></td>
                            <td><?php echo e($user['nik']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo e($user['level']); ?>">
                                    <?php echo ucfirst(e($user['level'])); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo isset($user['status']) ? e($user['status']) : 'aktif'; ?>">
                                    <?php echo ucfirst(isset($user['status']) ? e($user['status']) : 'aktif'); ?>
                                </span>
                            </td>
                            <td><?php echo e($user['rt']); ?> / <?php echo e($user['rw']); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Ubah status user ini?');">
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <input type="hidden" name="new_status" value="<?php echo (isset($user['status']) && $user['status'] === 'aktif') ? 'nonaktif' : 'aktif'; ?>">
                                        <button type="submit" class="btn-small btn-toggle">
                                            <?php echo (isset($user['status']) && $user['status'] === 'aktif') ? 'Nonaktifkan' : 'Aktifkan'; ?>
                                        </button>
                                        <?php echo csrfInput(); ?>
                                    </form>
                                    <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus user ini? Ini tidak dapat dibatalkan!');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" class="btn-small btn-delete">Hapus</button>
                                        <?php echo csrfInput(); ?>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div style="margin-top: 2rem; text-align: center;">
                    <a href="dashboard_admin.php" class="btn btn-secondary">Kembali</a>
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        <div class="container">
            <p>&copy; 2024 Sistem Surat RT/RW - Admin Management. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
