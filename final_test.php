<?php
/**
 * Final Test & Verification Script
 * Check if all components are properly set up
 */

require_once 'config/config.php';

$tests = [];
$database = null;
$db = null;

// Test 1: Database Connection
try {
    $database = new Database();
    $db = $database->getConnection();
    $tests['database'] = ['status' => '[OK]', 'message' => 'Database connection successful'];
} catch (Exception $e) {
    $tests['database'] = ['status' => '[FAIL]', 'message' => 'Database connection failed: ' . $e->getMessage()];
    $db = null;
}

// Test 2: Check Users Table
if ($db) {
    try {
        $query = "SELECT COUNT(*) as count FROM users";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $tests['users_table'] = ['status' => '✅', 'message' => 'Users table exists. Total users: ' . $result['count']];
    } catch (Exception $e) {
        $tests['users_table'] = ['status' => '❌', 'message' => 'Users table error: ' . $e->getMessage()];
    }
}

// Test 3: Check Surat Table
if ($db) {
    try {
        $query = "SELECT COUNT(*) as count FROM surat";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $tests['surat_table'] = ['status' => '✅', 'message' => 'Surat table exists. Total letters: ' . $result['count']];
    } catch (Exception $e) {
        $tests['surat_table'] = ['status' => '❌', 'message' => 'Surat table error: ' . $e->getMessage()];
    }
}

// Test 4: Check Surat History Table
if ($db) {
    try {
        $query = "SELECT COUNT(*) as count FROM surat_history";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $tests['surat_history_table'] = ['status' => '✅', 'message' => 'Surat history table exists. Total records: ' . $result['count']];
    } catch (Exception $e) {
        $tests['surat_history_table'] = ['status' => '❌', 'message' => 'Surat history table missing or error: ' . $e->getMessage()];
    }
}

// Test 5: Check for RT users
if ($db) {
    try {
        $query = "SELECT COUNT(*) as count FROM users WHERE level = 'rt'";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $rt_count = $result['count'];
        if ($rt_count > 0) {
            $tests['rt_users'] = ['status' => '✅', 'message' => "RT users found: $rt_count"];
        } else {
            $tests['rt_users'] = ['status' => '⚠️', 'message' => 'No RT users found. Run upgrade_db.sql to add test accounts.'];
        }
    } catch (Exception $e) {
        $tests['rt_users'] = ['status' => '❌', 'message' => 'Error checking RT users: ' . $e->getMessage()];
    }
}

// Test 6: Check for RW users
if ($db) {
    try {
        $query = "SELECT COUNT(*) as count FROM users WHERE level = 'rw'";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $rw_count = $result['count'];
        if ($rw_count > 0) {
            $tests['rw_users'] = ['status' => '✅', 'message' => "RW users found: $rw_count"];
        } else {
            $tests['rw_users'] = ['status' => '⚠️', 'message' => 'No RW users found. Run upgrade_db.sql to add test accounts.'];
        }
    } catch (Exception $e) {
        $tests['rw_users'] = ['status' => '❌', 'message' => 'Error checking RW users: ' . $e->getMessage()];
    }
}

// Test 7: Check Surat table columns for RT/RW workflow
if ($db) {
    try {
        $query = "SHOW COLUMNS FROM surat WHERE Field IN ('status_rt', 'status_rw')";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($result) === 2) {
            $tests['surat_columns'] = ['status' => '✅', 'message' => 'Surat table has status_rt and status_rw columns'];
        } else {
            $tests['surat_columns'] = ['status' => '⚠️', 'message' => 'Surat table missing RT/RW status columns. Run upgrade_db.sql'];
        }
    } catch (Exception $e) {
        $tests['surat_columns'] = ['status' => '❌', 'message' => 'Error checking columns: ' . $e->getMessage()];
    }
}

// Test 8: Check required files exist
$required_files = [
    'login.php' => 'Warga login',
    'login_admin.php' => 'Admin login',
    'login_rt.php' => 'RT login',
    'login_rw.php' => 'RW login',
    'dashboard_warga.php' => 'Warga dashboard',
    'dashboard_admin.php' => 'Admin dashboard',
    'dashboard_rt.php' => 'RT dashboard',
    'dashboard_rw.php' => 'RW dashboard',
    'update_letter_status.php' => 'Letter status update',
    'classes/User.php' => 'User class',
    'classes/Letter.php' => 'Letter class',
    'config/config.php' => 'Config file',
];

$missing = [];
foreach ($required_files as $file => $description) {
    if (!file_exists($file)) {
        $missing[$file] = $description;
    }
}

if (empty($missing)) {
    $tests['required_files'] = ['status' => '✅', 'message' => 'All required files exist'];
} else {
    $tests['required_files'] = ['status' => '❌', 'message' => 'Missing files: ' . implode(', ', array_keys($missing))];
}

// Test 9: Check User class can be instantiated
try {
    if ($db) {
        $user = new User($db);
        $tests['user_class'] = ['status' => '✅', 'message' => 'User class can be instantiated'];
    }
} catch (Exception $e) {
    $tests['user_class'] = ['status' => '❌', 'message' => 'User class error: ' . $e->getMessage()];
}

// Test 10: Check Letter class can be instantiated
try {
    if ($db) {
        $letter = new Letter($db);
        $tests['letter_class'] = ['status' => '✅', 'message' => 'Letter class can be instantiated'];
    }
} catch (Exception $e) {
    $tests['letter_class'] = ['status' => '❌', 'message' => 'Letter class error: ' . $e->getMessage()];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Test & Verification</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; padding: 2rem; }
        .container { max-width: 900px; margin: 0 auto; }
        h1 { color: #333; margin-bottom: 2rem; text-align: center; }
        .test-grid { display: grid; gap: 1rem; }
        .test-item { background: white; padding: 1.5rem; border-radius: 8px; border-left: 4px solid #ddd; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .test-item.pass { border-left-color: #27ae60; }
        .test-item.fail { border-left-color: #e74c3c; }
        .test-item.warning { border-left-color: #f39c12; }
        .test-name { font-weight: bold; color: #333; margin-bottom: 0.5rem; font-size: 0.95rem; }
        .test-message { color: #666; font-size: 0.9rem; }
        .status { font-size: 1.5rem; font-weight: bold; }
        .summary { background: white; padding: 2rem; border-radius: 8px; margin-bottom: 2rem; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .summary-number { font-size: 2.5rem; font-weight: bold; }
        .summary-label { color: #666; font-size: 0.95rem; }
        .next-steps { background: #e8f4f8; padding: 2rem; border-radius: 8px; margin-top: 2rem; border-left: 4px solid #3498db; }
        .next-steps h2 { color: #3498db; margin-bottom: 1rem; }
        .next-steps ol { margin-left: 1.5rem; color: #555; }
        .next-steps li { margin-bottom: 0.8rem; }
        .code { background: #f5f5f5; padding: 0.5rem 1rem; border-radius: 4px; font-family: 'Courier New', monospace; font-size: 0.85rem; margin: 0.5rem 0; display: inline-block; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Sistem Surat RT/RW - Test & Verification</h1>
        
        <div class="summary">
            <?php 
            $pass = count(array_filter($tests, function($t) { return strpos($t['status'], '✅') !== false; }));
            $total = count($tests);
            $percentage = ($pass / $total) * 100;
            ?>
            <div class="summary-number" style="color: <?php echo $percentage === 100 ? '#27ae60' : ($percentage >= 70 ? '#f39c12' : '#e74c3c'); ?>">
                <?php echo $percentage; ?>%
            </div>
            <div class="summary-label">
                Tests Passed: <?php echo $pass; ?>/<?php echo $total; ?>
            </div>
        </div>

        <div class="test-grid">
            <?php foreach ($tests as $name => $result): 
                $class = strpos($result['status'], '✅') !== false ? 'pass' : (strpos($result['status'], '⚠️') !== false ? 'warning' : 'fail');
            ?>
            <div class="test-item <?php echo $class; ?>">
                <div style="display: flex; justify-content: space-between; align-items: start;">
                    <div>
                        <div class="test-name"><?php echo ucwords(str_replace('_', ' ', $name)); ?></div>
                        <div class="test-message"><?php echo $result['message']; ?></div>
                    </div>
                    <div class="status"><?php echo $result['status']; ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if ($percentage === 100): ?>
        <div class="next-steps">
            <h2>✅ Sistem Siap Digunakan!</h2>
            <p>Semua komponen telah terverifikasi. Anda siap untuk mulai menggunakan sistem.</p>
            <ol>
                <li><strong>Test Login Warga:</strong> Akses <span class="code">login.php</span> dan daftar akun baru, atau gunakan akun yang sudah ada</li>
                <li><strong>Test Login RT:</strong> Akses <span class="code">login_rt.php</span> dengan username <span class="code">rt001</span> password <span class="code">password123</span></li>
                <li><strong>Test Login RW:</strong> Akses <span class="code">login_rw.php</span> dengan username <span class="code">rw001</span> password <span class="code">password123</span></li>
                <li><strong>Test Login Admin:</strong> Akses <span class="code">login_admin.php</span> dengan username <span class="code">admin</span> password <span class="code">admin123</span></li>
                <li><strong>Test Workflow:</strong> 
                    <ul style="margin-top: 0.5rem; margin-left: 1.5rem;">
                        <li>Warga buat surat pengajuan</li>
                        <li>RT login dan setujui/tolak surat</li>
                        <li>RW login dan setujui/tolak surat (jika disetujui RT)</li>
                        <li>Warga lihat status dan print jika sudah selesai</li>
                    </ul>
                </li>
            </ol>
        </div>
        <?php elseif ($percentage >= 70): ?>
        <div class="next-steps">
            <h2>⚠️ Beberapa Peringatan</h2>
            <p>Sistem hampir siap, tapi ada beberapa hal yang perlu diperhatikan:</p>
            <ol>
                <li>Jalankan file SQL di folder <span class="code">database/</span> untuk setup database</li>
                <li>Pastikan MySQL sudah running dan database sudah dibuat</li>
                <li>Check file konfigurasi di <span class="code">config/database.php</span></li>
                <li>Jalankan test ulang setelah melakukan perbaikan</li>
            </ol>
        </div>
        <?php else: ?>
        <div class="next-steps">
            <h2>❌ Ada Masalah Serius</h2>
            <p>Ada beberapa komponen yang tidak dapat diverifikasi. Silakan periksa:</p>
            <ol>
                <li>Koneksi database di <span class="code">config/database.php</span></li>
                <li>Pastikan MySQL sudah running: <span class="code">php -r "echo 'OK';"</span></li>
                <li>Jalankan SQL initialization: <span class="code">database/surat_rt_rw.sql</span></li>
                <li>Jalankan SQL upgrade: <span class="code">database/upgrade_db.sql</span></li>
                <li>Pastikan semua file PHP ada di folder yang benar</li>
            </ol>
        </div>
        <?php endif; ?>

        <div style="margin-top: 3rem; padding: 2rem; background: white; border-radius: 8px; text-align: center;">
            <h3 style="color: #333; margin-bottom: 1rem;">📚 Dokumentasi</h3>
            <p style="color: #666; margin-bottom: 1rem;">Untuk informasi lebih lengkap, baca file dokumentasi:</p>
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <a href="IMPLEMENTATION_COMPLETE.md" style="display: inline-block; padding: 0.75rem 1.5rem; background: #3498db; color: white; text-decoration: none; border-radius: 6px; font-size: 0.9rem;">Implementation Guide</a>
                <a href="SETUP_RT_RW.md" style="display: inline-block; padding: 0.75rem 1.5rem; background: #27ae60; color: white; text-decoration: none; border-radius: 6px; font-size: 0.9rem;">Setup Instructions</a>
                <a href="README.md" style="display: inline-block; padding: 0.75rem 1.5rem; background: #9b59b6; color: white; text-decoration: none; border-radius: 6px; font-size: 0.9rem;">Project README</a>
            </div>
        </div>
    </div>
</body>
</html>
