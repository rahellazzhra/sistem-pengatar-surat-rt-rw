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

$error = '';
$success = '';

// Process status update
if ($_POST && isset($_POST['update_status'])) {
    $letter->id = $_POST['letter_id'];
    $letter->status = $_POST['status'];
    $letter->no_surat = $_POST['no_surat'];
    $letter->tanggal_selesai = $_POST['tanggal_selesai'];
    $letter->catatan_admin = $_POST['catatan_admin'];
    
    if ($letter->updateStatus()) {
        $success = "Status surat berhasil diperbarui!";
    } else {
        $error = "Terjadi kesalahan saat memperbarui status surat. Periksa browser console untuk detail error.";
    }
}

// Get all letters for admin
$all_letters = $letter->readAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Sistem Surat RT/RW</title>
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
        .card { background: white; border-radius: 12px; box-shadow: 0 8px 25px rgba(0,0,0,0.1); overflow: hidden; }
        .card-header { background: linear-gradient(135deg, #f5f7fa 0%, #e8ebf2 100%); padding: 2rem; border-bottom: 3px solid var(--accent); border-left: 5px solid var(--primary); }
        .card-title { margin: 0; color: var(--primary); font-size: 1.8rem; font-weight: 600; }
        .card-content { padding: 2rem; }
        .table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        .table thead { background: linear-gradient(135deg, #f5f7fa 0%, #e8ebf2 100%); }
        .table th { padding: 1rem; text-align: left; font-weight: 600; color: var(--primary); border-bottom: 2px solid var(--accent); }
        .table td { padding: 1rem; border-bottom: 1px solid #e0e0e0; color: #333; }
        .table tbody tr:hover { background: #f9f9f9; }
        .table tbody tr:hover td { color: #333; }
        .btn { display: inline-block; padding: 0.6rem 1.2rem; border-radius: 6px; text-decoration: none; font-weight: 600; border: none; cursor: pointer; transition: all 0.3s; font-size: 0.9rem; }
        .btn-primary { background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); color: white; box-shadow: 0 4px 12px rgba(30, 60, 114, 0.3); }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(30, 60, 114, 0.4); }
        .alert { padding: 1rem; border-radius: 6px; margin-bottom: 1rem; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">🏛️ ADMIN PANEL</div>
                <nav class="nav">
                    <ul>
                        <li><a href="dashboard_admin.php" class="nav-link">📊 Dashboard</a></li>
                        <li><a href="admin.php" class="nav-link nav-link--active">📋 Kelola Surat</a></li>
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
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">📋 Kelola Semua Surat</h2>
                </div>
                <div class="card-content">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo e($error); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo e($success); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($all_letters->rowCount() > 0): ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Warga</th>
                                    <th>NIK</th>
                                    <th>Jenis Surat</th>
                                    <th>No. Surat</th>
                                    <th>Tanggal Pengajuan</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; while ($row = $all_letters->fetch(PDO::FETCH_ASSOC)): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo e($row['nama']); ?></td>
                                    <td><?php echo e($row['nik']); ?></td>
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
                                        <a href="detail_surat.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm">Detail</a>
                                        <button type="button" class="btn btn-secondary btn-sm" onclick="openModal(<?php echo $row['id']; ?>, '<?php echo $row['status']; ?>', '<?php echo isset($row['no_surat']) ? e($row['no_surat']) : ''; ?>', '<?php echo isset($row['catatan_admin']) ? e($row['catatan_admin']) : ''; ?>')">
                                            Update Status
                                        </button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="text-center">
                            <p>Belum ada surat yang diajukan.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for updating status -->
    <div id="statusModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 2rem; border-radius: 8px; width: 90%; max-width: 500px;">
            <h3 style="margin-bottom: 1rem;">Update Status Surat</h3>
            <form method="POST" action="">
                <input type="hidden" name="letter_id" id="modal_letter_id">
                <input type="hidden" name="update_status" value="1">
                
                <div class="form-group">
                    <label for="modal_status" class="form-label">Status</label>
                    <select class="form-control form-select" id="modal_status" name="status" required>
                        <option value="pending">Pending</option>
                        <option value="diproses">Diproses</option>
                        <option value="selesai">Selesai</option>
                        <option value="ditolak">Ditolak</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="modal_no_surat" class="form-label">No. Surat</label>
                    <input type="text" class="form-control" id="modal_no_surat" name="no_surat" 
                           placeholder="Contoh: 001/RT-01/RW-02/XII/2024">
                </div>
                
                <div class="form-group">
                    <label for="modal_tanggal_selesai" class="form-label">Tanggal Selesai</label>
                    <input type="date" class="form-control" id="modal_tanggal_selesai" name="tanggal_selesai">
                </div>
                
                <div class="form-group">
                    <label for="modal_catatan_admin" class="form-label">Catatan Admin</label>
                    <textarea class="form-control" id="modal_catatan_admin" name="catatan_admin" 
                              placeholder="Masukkan catatan jika ada..." rows="3"></textarea>
                </div>
                
                <div class="form-group" style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <div class="footer">
        <div class="container">
            <p>&copy; 2024 Sistem Surat RT/RW. All rights reserved.</p>
        </div>
    </div>

    <script>
        function openModal(letterId, status, noSurat, catatanAdmin) {
            console.log('Opening modal with:', {letterId, status, noSurat, catatanAdmin});
            
            document.getElementById('modal_letter_id').value = letterId;
            document.getElementById('modal_status').value = status;
            document.getElementById('modal_no_surat').value = noSurat || '';
            document.getElementById('modal_catatan_admin').value = catatanAdmin || '';
            
            document.getElementById('statusModal').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('statusModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        document.getElementById('statusModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
        
        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });
    </script>
</body>
</html>