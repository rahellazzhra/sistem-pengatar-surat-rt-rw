<?php
require_once 'config/config.php';

// Check if logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Only RT and RW can update status
if (!in_array($_SESSION['level'], ['rt', 'rw', 'admin'])) {
    header("Location: " . ($_SESSION['level'] === 'warga' ? 'dashboard_warga.php' : 'login.php'));
    exit();
}

$database = new Database();
$db = $database->getConnection();

$id = $_GET['id'] ?? null;
$status = $_GET['status'] ?? null;
$reason = $_GET['reason'] ?? '';

if (!$id || !$status) {
    header("Location: " . ($_SESSION['level'] === 'rt' ? 'dashboard_rt.php' : 'dashboard_rw.php'));
    exit();
}

try {
    $query = "UPDATE surat SET status = ? WHERE id = ?";
    
    if ($_SESSION['level'] === 'rt') {
        if (in_array($status, ['approved_rt', 'rejected_rt'])) {
            $update_fields = "status = ?";
            
            if ($status === 'approved_rt') {
                $query = "UPDATE surat SET status = ?, status_rt = 'approved', updated_at = NOW() WHERE id = ?";
            } else {
                $query = "UPDATE surat SET status = ?, status_rt = 'rejected', keterangan_rt = ?, updated_at = NOW() WHERE id = ?";
            }
        }
    } elseif ($_SESSION['level'] === 'rw') {
        if (in_array($status, ['approved_rw', 'rejected_rw'])) {
            if ($status === 'approved_rw') {
                $query = "UPDATE surat SET status = ?, status_rw = 'approved', updated_at = NOW() WHERE id = ?";
            } else {
                $query = "UPDATE surat SET status = ?, status_rw = 'rejected', keterangan_rw = ?, updated_at = NOW() WHERE id = ?";
            }
        }
    }
    
    $stmt = $db->prepare($query);
    
    if ($status === 'rejected_rt' || $status === 'rejected_rw') {
        if ($_SESSION['level'] === 'rt') {
            $stmt->execute([$status, $reason, $id]);
        } else {
            $stmt->execute([$status, $reason, $id]);
        }
    } else {
        $stmt->execute([$status, $id]);
    }
    
    // Record in history
    $history_query = "INSERT INTO surat_history (surat_id, action, actor_id, notes, created_at) VALUES (?, ?, ?, ?, NOW())";
    $hist_stmt = $db->prepare($history_query);
    $hist_stmt->execute([$id, $status, $_SESSION['user_id'], $reason]);
    
    // Redirect back
    if ($_SESSION['level'] === 'rt') {
        header("Location: dashboard_rt.php");
    } elseif ($_SESSION['level'] === 'rw') {
        header("Location: dashboard_rw.php");
    } else {
        header("Location: dashboard_admin.php");
    }
    exit();
    
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
