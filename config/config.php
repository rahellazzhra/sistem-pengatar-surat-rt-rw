<?php
// Start session
session_start();

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set default timezone
date_default_timezone_set('Asia/Jakarta');

// Base URL configuration
define('BASE_URL', 'http://localhost/sistem%20surat%20rt%20rw/');

// Include database connection
require_once 'database.php';

// Include class files
require_once __DIR__ . '/../classes/User.php';
require_once 'institusi.php';
require_once __DIR__ . '/../classes/Letter.php';

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to check if user is admin
function isAdmin() {
    return isset($_SESSION['level']) && $_SESSION['level'] == 'admin';
}

// Function to redirect with message
function redirect($location, $message = null) {
    if ($message) {
        $_SESSION['message'] = $message;
    }
    header("Location: " . BASE_URL . $location);
    exit();
}

// Function to escape HTML output
function e($string) {
    if ($string === null) {
        return '';
    }
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Function to check CSRF token
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Function to verify CSRF token
function verifyCSRFToken($token) {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

// Function to display CSRF token in forms
function csrfInput() {
    return '<input type="hidden" name="csrf_token" value="' . generateCSRFToken() . '">';
}

// Function to check user role
function isRT() {
    return isset($_SESSION['level']) && $_SESSION['level'] == 'rt';
}

function isRW() {
    return isset($_SESSION['level']) && $_SESSION['level'] == 'rw';
}

function isWarga() {
    return isset($_SESSION['level']) && $_SESSION['level'] == 'warga';
}

// Function to log audit trail
function logAudit($db, $surat_id, $action, $details = null) {
    try {
        $query = "INSERT INTO audit_log (surat_id, action, action_by, role, details) 
                 VALUES (:surat_id, :action, :action_by, :role, :details)";
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':surat_id' => $surat_id,
            ':action' => $action,
            ':action_by' => $_SESSION['user_id'] ?? 0,
            ':role' => $_SESSION['level'] ?? 'unknown',
            ':details' => $details
        ]);
        return true;
    } catch (Exception $e) {
        error_log("Error logging audit: " . $e->getMessage());
        return false;
    }
}

// Function to create notification
function createNotification($db, $user_id, $surat_id, $title, $message, $type = 'info') {
    try {
        $query = "INSERT INTO notifikasi (user_id, surat_id, title, message, type) 
                 VALUES (:user_id, :surat_id, :title, :message, :type)";
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':user_id' => $user_id,
            ':surat_id' => $surat_id,
            ':title' => $title,
            ':message' => $message,
            ':type' => $type
        ]);
        return true;
    } catch (Exception $e) {
        error_log("Error creating notification: " . $e->getMessage());
        return false;
    }
}

// Function to get unread notifications
function getUnreadNotifications($db, $user_id, $limit = 5) {
    try {
        $query = "SELECT * FROM notifikasi 
                 WHERE user_id = :user_id AND is_read = 0 
                 ORDER BY created_at DESC 
                 LIMIT :limit";
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':user_id' => $user_id,
            ':limit' => $limit
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error getting notifications: " . $e->getMessage());
        return [];
    }
}

// Function to format date in Indonesian
function formatTanggalIndonesia($date) {
    $months = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    
    if (!$date) return '';
    
    $timestamp = strtotime($date);
    $day = date('d', $timestamp);
    $month = (int)date('m', $timestamp);
    $year = date('Y', $timestamp);
    
    return $day . ' ' . $months[$month] . ' ' . $year;
}

// Function to get status badge
function getStatusBadge($status) {
    $badges = [
        'pending' => '<span class="badge badge-warning">Menunggu Persetujuan</span>',
        'approved_rt' => '<span class="badge badge-info">Disetujui RT</span>',
        'approved_rw' => '<span class="badge badge-success">Disetujui RW</span>',
        'rejected_rt' => '<span class="badge badge-danger">Ditolak RT</span>',
        'rejected_rw' => '<span class="badge badge-danger">Ditolak RW</span>',
        'selesai' => '<span class="badge badge-success">Selesai</span>'
    ];
    return $badges[$status] ?? '<span class="badge badge-secondary">Unknown</span>';
}

// Function to log login activity (version without additional_info column)
function logLoginActivity($db, $user_id = null, $nik = null, $username = null, $role = null, $success = false, $additional_info = null) {
    try {
        // Get client IP address (handling various proxy scenarios)
        $ip_address = getClientIP();
        
        // Get user agent
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
        
        // Prepare SQL query without additional_info column
        $query = "INSERT INTO login_history (user_id, nik, username, role, success, ip_address, user_agent, created_at)
                 VALUES (:user_id, :nik, :username, :role, :success, :ip_address, :user_agent, NOW())";
        
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':user_id' => $user_id,
            ':nik' => $nik,
            ':username' => $username,
            ':role' => $role,
            ':success' => $success ? 1 : 0,
            ':ip_address' => $ip_address,
            ':user_agent' => $user_agent
        ]);
        
        return true;
    } catch (Exception $e) {
        error_log("Error logging login activity: " . $e->getMessage());
        return false;
    }
}

// Function to get client IP address
function getClientIP() {
    $ip_keys = [
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR'
    ];
    
    foreach ($ip_keys as $key) {
        if (isset($_SERVER[$key]) && !empty($_SERVER[$key])) {
            $ip = $_SERVER[$key];
            if (is_string($ip)) {
                $ip_list = explode(',', $ip);
                foreach ($ip_list as $single_ip) {
                    $single_ip = trim($single_ip);
                    // Validate IP address format
                    if (filter_var($single_ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                        return $single_ip;
                    }
                }
            }
        }
    }
    
    return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
}

// Function to safely get login identifier (username or NIK)
function getLoginIdentifier($username = null, $nik = null) {
    if (!empty($username)) {
        return ['type' => 'username', 'value' => $username];
    } elseif (!empty($nik)) {
        return ['type' => 'nik', 'value' => $nik];
    }
    return ['type' => 'unknown', 'value' => 'unknown'];
}
?>
