<?php
require_once 'config/config.php';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

require_once 'classes/Letter.php';
$letter = new Letter($db);

$letter->id = $_GET['id'];
if (!$letter->readOne()) {
    header("Location: index.php");
    exit();
}

// Check if user has permission to view this letter
if (!isAdmin() && $letter->user_id != $_SESSION['user_id']) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Surat - Sistem Surat RT/RW</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { background: var(--light); min-height: 100vh; margin: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: var(--dark); }
        .header { background: <?php echo isAdmin() ? 'linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%)' : 'linear-gradient(135deg, var(--secondary) 0%, var(--secondary-dark) 100%)'; ?>; color: white; padding: 1.5rem 0; box-shadow: 0 4px 12px rgba(0,0,0,0.08); position: sticky; top: 0; z-index: 100; border-bottom: 3px solid <?php echo isAdmin() ? 'var(--accent)' : 'var(--muted-purple)'; ?>; }
        .header-content { display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 1.8rem; font-weight: bold; color: <?php echo isAdmin() ? 'var(--accent)' : 'var(--muted-purple)'; ?>; text-shadow: 0 2px 4px rgba(0,0,0,0.2); }
        .nav ul { list-style: none; display: flex; gap: 2rem; margin: 0; padding: 0; }
        .nav a { color: white; text-decoration: none; font-weight: 500; transition: all 0.3s; padding: 0.5rem 1rem; border-radius: 6px; }
        .nav a:hover { background: rgba(255,255,255,0.1); color: <?php echo isAdmin() ? 'var(--accent)' : 'var(--muted-purple)'; ?>; }
        .main { flex: 1; padding: 2rem 0; }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 1rem; }
        .footer { background: <?php echo isAdmin() ? 'linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%)' : 'linear-gradient(135deg, var(--secondary) 0%, var(--secondary-dark) 100%)'; ?>; color: white; text-align: center; padding: 1.5rem; margin-top: 2rem; }
        .card { background: white; border-radius: 12px; box-shadow: 0 8px 25px rgba(0,0,0,0.1); overflow: hidden; }
        .card-header { background: linear-gradient(135deg, #f5f7fa 0%, #e8ebf2 100%); padding: 2rem; border-bottom: 3px solid <?php echo isAdmin() ? 'var(--accent)' : 'var(--muted-purple)'; ?>; }
        .card-title { margin: 0; color: <?php echo isAdmin() ? 'var(--primary)' : 'var(--secondary)'; ?>; font-size: 1.8rem; font-weight: 600; }
        .card-content { padding: 2rem; }
        .btn { display: inline-block; padding: 0.6rem 1.2rem; border-radius: 6px; text-decoration: none; font-weight: 600; border: none; cursor: pointer; transition: all 0.3s; font-size: 0.9rem; }
        .btn-primary { background: <?php echo isAdmin() ? 'linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%)' : 'linear-gradient(135deg, var(--secondary) 0%, var(--secondary-dark) 100%)'; ?>; color: white; box-shadow: 0 4px 12px rgba(0,0,0,0.2); }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,0,0,0.3); }
        .btn-secondary { background: #e0e0e0; color: #333; }
        .btn-secondary:hover { background: #d0d0d0; }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo"><?php echo isAdmin() ? '🏛️ ADMIN PANEL' : '🏛️ DASHBOARD WARGA'; ?></div>
                <nav class="nav">
                    <ul>
                        <?php if (isAdmin()): ?>
                            <li><a href="dashboard_admin.php" class="nav-link">📊 Dashboard</a></li>
                            <li><a href="admin.php" class="nav-link">📋 Kelola Surat</a></li>
                            <li><a href="admin_users.php" class="nav-link">👥 Manage Users</a></li>
                        <?php else: ?>
                            <li><a href="dashboard_warga.php" class="nav-link">📊 Dashboard</a></li>
                            <li><a href="surat_saya.php" class="nav-link">📋 Surat Saya</a></li>
                        <?php endif; ?>
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
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h2 class="card-title">Detail Surat</h2>
                        <div>
                            <a href="surat_saya.php" class="btn btn-secondary">Kembali</a>
                            <?php if ($letter->status == 'selesai'): ?>
                                <a href="cetak_surat.php?id=<?php echo $letter->id; ?>" class="btn btn-primary" target="_blank">Cetak Surat</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="card-content">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                        <div>
                            <h3 style="margin-bottom: 1rem; color: var(--dark);">Informasi Surat</h3>
                            <div style="background: #f8f9fa; padding: 1.5rem; border-radius: var(--border-radius);">
                                <div style="display: grid; gap: 1rem;">
                                    <div>
                                        <strong>Jenis Surat:</strong><br>
                                        <?php echo e($letter->nama_surat); ?>
                                    </div>
                                    <div>
                                        <strong>No. Surat:</strong><br>
                                        <?php if ($letter->no_surat): ?>
                                            <?php echo e($letter->no_surat); ?>
                                        <?php else: ?>
                                            <span class="text-muted">Belum ada nomor surat</span>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <strong>Status:</strong><br>
                                        <span class="badge badge-<?php echo $letter->status; ?>">
                                            <?php echo ucfirst($letter->status); ?>
                                        </span>
                                    </div>
                                    <div>
                                        <strong>Tanggal Pengajuan:</strong><br>
                                        <?php echo date('d F Y', strtotime($letter->tanggal_pengajuan)); ?>
                                    </div>
                                    <?php if ($letter->tanggal_selesai): ?>
                                        <div>
                                            <strong>Tanggal Selesai:</strong><br>
                                            <?php echo date('d F Y', strtotime($letter->tanggal_selesai)); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div>
                            <h3 style="margin-bottom: 1rem; color: var(--dark);">Data Pemohon</h3>
                            <div style="background: #f8f9fa; padding: 1.5rem; border-radius: var(--border-radius);">
                                <div style="display: grid; gap: 1rem;">
                                    <div>
                                        <strong>Nama:</strong><br>
                                        <?php echo e($letter->nama); ?>
                                    </div>
                                    <div>
                                        <strong>NIK:</strong><br>
                                        <?php echo e($letter->nik); ?>
                                    </div>
                                    <div>
                                        <strong>Alamat:</strong><br>
                                        <?php echo e($letter->alamat); ?><br>
                                        RT <?php echo e($letter->rt); ?> / RW <?php echo e($letter->rw); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div style="margin-top: 2rem;">
                        <h3 style="margin-bottom: 1rem; color: var(--dark);">Keperluan Surat</h3>
                        <div style="background: #f8f9fa; padding: 1.5rem; border-radius: var(--border-radius);">
                            <?php echo nl2br(e($letter->keperluan)); ?>
                        </div>
                    </div>
                    
                    <?php if ($letter->catatan_admin): ?>
                        <div style="margin-top: 2rem;">
                            <h3 style="margin-bottom: 1rem; color: var(--dark);">Catatan Admin</h3>
                            <div style="background: #f8f9fa; padding: 1.5rem; border-radius: var(--border-radius);">
                                <?php echo nl2br(e($letter->catatan_admin)); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isAdmin()): ?>
                        <div style="margin-top: 2rem; text-align: center;">
                            <a href="admin.php" class="btn btn-secondary">Kembali ke Admin Panel</a>
                            <button type="button" class="btn btn-primary" onclick="openModal(<?php echo $letter->id; ?>, '<?php echo $letter->status; ?>', '<?php echo e($letter->no_surat); ?>', '<?php echo e($letter->catatan_admin); ?>')">
                                Update Status
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for updating status -->
    <?php if (isAdmin()): ?>
    <div id="statusModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 2rem; border-radius: 8px; width: 90%; max-width: 500px;">
            <h3 style="margin-bottom: 1rem;">Update Status Surat</h3>
            <form method="POST" action="admin.php">
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
    <?php endif; ?>

    <div class="footer">
        <div class="container">
            <p>&copy; 2024 Sistem Surat RT/RW. All rights reserved.</p>
        </div>
    </div>

    <script>
        function openModal(letterId, status, noSurat, catatanAdmin) {
            document.getElementById('modal_letter_id').value = letterId;
            document.getElementById('modal_status').value = status;
            document.getElementById('modal_no_surat').value = noSurat || '';
            document.getElementById('modal_catatan_admin').value = catatanAdmin || '';
            
            // Set today's date for tanggal_selesai if status is completed
            if (status === 'selesai') {
                const today = new Date().toISOString().split('T')[0];
                document.getElementById('modal_tanggal_selesai').value = today;
            } else {
                document.getElementById('modal_tanggal_selesai').value = '';
            }
            
            document.getElementById('statusModal').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('statusModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        <?php if (isAdmin()): ?>
        document.getElementById('statusModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>