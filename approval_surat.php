<?php
require_once 'config/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Check if user is RT or RW
if (!isRT() && !isRW()) {
    header("Location: index.php");
    exit();
}

// Get letter ID from URL
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

// Get pemohon details
require_once 'classes/User.php';
$user = new User($db);
$user->id = $letter->user_id;
$user->readOne();

// Get letter type
$query_jenis = "SELECT * FROM jenis_surat WHERE id = ?";
$stmt_jenis = $db->prepare($query_jenis);
$stmt_jenis->execute([$letter->jenis_surat_id]);
$jenis_surat = $stmt_jenis->fetch(PDO::FETCH_ASSOC);

// Process form submission
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error = "CSRF token tidak valid. Silakan coba lagi.";
    } else {
        $action = $_POST['action'] ?? '';
        $keterangan = $_POST['keterangan'] ?? '';

        if ($action === 'approve') {
            // Approve letter
            $status = isRT() ? 'approved_rt' : 'approved_rw';
            $date_col = isRT() ? 'approval_date_rt' : 'approval_date_rw';
            $user_col = isRT() ? 'approved_by_rt' : 'approved_by_rw';

            $query = "UPDATE surat SET status = :status, " . $date_col . " = NOW(), " . $user_col . " = :user_id WHERE id = :id";
            $stmt = $db->prepare($query);
            
            if ($stmt->execute([':status' => $status, ':user_id' => $_SESSION['user_id'], ':id' => $letter->id])) {
                // Log audit
                logAudit($db, $letter->id, strtoupper(isRT() ? 'RT' : 'RW') . ' APPROVED', $keterangan);
                
                // Create notification for pemohon
                createNotification(
                    $db,
                    $letter->user_id,
                    $letter->id,
                    'Surat Disetujui ' . (isRT() ? 'RT' : 'RW'),
                    'Surat Anda telah disetujui oleh ' . (isRT() ? 'Ketua RT' : 'Ketua RW'),
                    'success'
                );
                
                $message = "Surat berhasil disetujui!";
            } else {
                $error = "Terjadi kesalahan saat menyetujui surat.";
            }
        } elseif ($action === 'reject') {
            // Reject letter
            $status = isRT() ? 'rejected_rt' : 'rejected_rw';
            $reason_col = isRT() ? 'rejection_reason_rt' : 'rejection_reason_rw';
            $user_col = isRT() ? 'approved_by_rt' : 'approved_by_rw';

            if (empty($keterangan)) {
                $error = "Alasan penolakan harus diisi!";
            } else {
                $query = "UPDATE surat SET status = :status, " . $reason_col . " = :keterangan, " . $user_col . " = :user_id WHERE id = :id";
                $stmt = $db->prepare($query);
                
                if ($stmt->execute([':status' => $status, ':keterangan' => $keterangan, ':user_id' => $_SESSION['user_id'], ':id' => $letter->id])) {
                    // Log audit
                    logAudit($db, $letter->id, strtoupper(isRT() ? 'RT' : 'RW') . ' REJECTED', $keterangan);
                    
                    // Create notification for pemohon
                    createNotification(
                        $db,
                        $letter->user_id,
                        $letter->id,
                        'Surat Ditolak ' . (isRT() ? 'RT' : 'RW'),
                        'Surat Anda telah ditolak oleh ' . (isRT() ? 'Ketua RT' : 'Ketua RW') . '. Alasan: ' . $keterangan,
                        'error'
                    );
                    
                    $message = "Surat berhasil ditolak!";
                } else {
                    $error = "Terjadi kesalahan saat menolak surat.";
                }
            }
        }

        if ($message && !$error) {
            // Refresh letter data
            $letter->readOne();
        }
    }
}

// Determine approval status
$bisa_approve = false;
if (isRT() && $letter->status === 'pending') {
    $bisa_approve = true;
} elseif (isRW() && $letter->status === 'approved_rt') {
    $bisa_approve = true;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approval Surat - Sistem Surat RT/RW</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .approval-container {
            max-width: 800px;
            margin: 2rem auto;
        }
        
        .approval-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-bottom: 1.5rem;
        }
        
        .approval-header {
            border-bottom: 2px solid var(--secondary);
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .letter-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin: 1rem 0;
        }
        
        .info-item label {
            font-weight: bold;
            color: #333;
            display: block;
            margin-bottom: 0.25rem;
        }
        
        .info-item p {
            margin: 0;
            color: #666;
        }
        
        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-approve {
            background: var(--success);
            color: white;
        }
        
        .btn-approve:hover {
            background: var(--success);
        }
        
        .btn-reject {
            background: var(--danger);
            color: white;
        }
        
        .btn-reject:hover {
            background: var(--danger-dark);
        }
        
        .btn-back {
            background: #95a5a6;
            color: white;
        }
        
        .btn-back:hover {
            background: #7f8c8d;
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 1rem;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-approved_rt {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .status-approved_rw {
            background: #d4edda;
            color: #155724;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 2rem;
            border: 1px solid #888;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .modal-header {
            margin-bottom: 1.5rem;
            border-bottom: 1px solid #ddd;
            padding-bottom: 1rem;
        }
        
        .modal-body {
            margin-bottom: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #333;
        }
        
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: inherit;
            font-size: 14px;
            resize: vertical;
            min-height: 100px;
        }
        
        .modal-footer {
            text-align: right;
            border-top: 1px solid #ddd;
            padding-top: 1rem;
        }
        
        .modal-footer button {
            padding: 0.5rem 1rem;
            margin-left: 0.5rem;
        }
    </style>
</head>
<body>
    <!-- Navigation Header -->
    <div class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">Sistem Surat RT/RW</div>
                <nav class="nav">
                    <ul>
                        <li><a href="index.php">Dashboard</a></li>
                        <?php if (isRT()): ?>
                            <li><a href="dashboard_rt.php">Daftar Surat</a></li>
                        <?php elseif (isRW()): ?>
                            <li><a href="dashboard_rw.php">Daftar Surat</a></li>
                        <?php endif; ?>
                        <li><a href="logout.php">Logout</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <div class="main">
        <div class="container">
            <div class="approval-container">
                <!-- Messages -->
                <?php if ($message): ?>
                    <div class="alert alert-success"><?php echo e($message); ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo e($error); ?></div>
                <?php endif; ?>

                <!-- Approval Form -->
                <div class="approval-card">
                    <div class="approval-header">
                        <h2>Approval Surat Pengantar</h2>
                        <span class="status-badge status-<?php echo e($letter->status); ?>">
                            <?php 
                            $status_labels = [
                                'pending' => 'Menunggu Persetujuan',
                                'approved_rt' => 'Disetujui RT',
                                'approved_rw' => 'Disetujui RW'
                            ];
                            echo $status_labels[$letter->status] ?? 'Unknown';
                            ?>
                        </span>
                    </div>

                    <!-- Letter Information -->
                    <h3>Data Surat</h3>
                    <div class="letter-info">
                        <div class="info-item">
                            <label>Nomor Surat</label>
                            <p><?php echo e($letter->no_surat); ?></p>
                        </div>
                        <div class="info-item">
                            <label>Jenis Surat</label>
                            <p><?php echo e($letter->nama_surat); ?></p>
                        </div>
                        <div class="info-item">
                            <label>Tanggal Pengajuan</label>
                            <p><?php echo formatTanggalIndonesia($letter->tanggal_pengajuan); ?></p>
                        </div>
                        <div class="info-item">
                            <label>Status</label>
                            <p><?php echo ucfirst(str_replace('_', ' ', $letter->status)); ?></p>
                        </div>
                    </div>

                    <!-- Pemohon Information -->
                    <h3 style="margin-top: 2rem;">Data Pemohon</h3>
                    <div class="letter-info">
                        <div class="info-item">
                            <label>Nama</label>
                            <p><?php echo e($user->nama); ?></p>
                        </div>
                        <div class="info-item">
                            <label>NIK</label>
                            <p><?php echo e($letter->nik); ?></p>
                        </div>
                        <div class="info-item">
                            <label>Alamat</label>
                            <p><?php echo e($user->alamat); ?></p>
                        </div>
                        <div class="info-item">
                            <label>RT / RW</label>
                            <p><?php echo e($letter->rt); ?> / <?php echo e($letter->rw); ?></p>
                        </div>
                    </div>

                    <!-- Keperluan -->
                    <h3 style="margin-top: 2rem;">Keperluan</h3>
                    <p style="padding: 1rem; background: #f8f9fa; border-left: 3px solid var(--secondary); border-radius: 4px;">
                        <?php echo nl2br(e($letter->keperluan)); ?>
                    </p>

                    <!-- Action Buttons -->
                    <?php if ($bisa_approve): ?>
                        <div class="action-buttons">
                            <button class="btn btn-approve" onclick="showApproveModal()">✓ Setujui</button>
                            <button class="btn btn-reject" onclick="showRejectModal()">✕ Tolak</button>
                            <a href="<?php echo isRT() ? 'dashboard_rt.php' : 'dashboard_rw.php'; ?>" class="btn btn-back">Kembali</a>
                        </div>
                    <?php else: ?>
                        <div class="action-buttons">
                            <p style="color: #666; margin: 0;">Surat ini sudah tidak dapat disetujui pada tahap <?php echo isRT() ? 'RT' : 'RW'; ?></p>
                            <a href="<?php echo isRT() ? 'dashboard_rt.php' : 'dashboard_rw.php'; ?>" class="btn btn-back">Kembali</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Approve Modal -->
    <div id="approveModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Konfirmasi Persetujuan</h3>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <p>Anda akan menyetujui surat ini. Apakah Anda yakin?</p>
                    <div class="form-group">
                        <label for="approveMemo">Catatan (Opsional)</label>
                        <textarea id="approveMemo" name="keterangan" placeholder="Catatan approval..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-back" onclick="closeApproveModal()">Batal</button>
                    <button type="submit" name="action" value="approve" class="btn btn-approve">Setujui</button>
                    <?php echo csrfInput(); ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Konfirmasi Penolakan</h3>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <p>Anda akan menolak surat ini. Alasan penolakan harus diisi.</p>
                    <div class="form-group">
                        <label for="rejectReason">Alasan Penolakan *</label>
                        <textarea id="rejectReason" name="keterangan" placeholder="Jelaskan alasan penolakan..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-back" onclick="closeRejectModal()">Batal</button>
                    <button type="submit" name="action" value="reject" class="btn btn-reject">Tolak</button>
                    <?php echo csrfInput(); ?>
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
        function showApproveModal() {
            document.getElementById('approveModal').style.display = 'block';
        }

        function closeApproveModal() {
            document.getElementById('approveModal').style.display = 'none';
        }

        function showRejectModal() {
            document.getElementById('rejectModal').style.display = 'block';
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const approveModal = document.getElementById('approveModal');
            const rejectModal = document.getElementById('rejectModal');
            
            if (event.target == approveModal) {
                approveModal.style.display = 'none';
            }
            if (event.target == rejectModal) {
                rejectModal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
