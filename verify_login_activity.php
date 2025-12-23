<?php
/**
 * Test script untuk verifikasi login activity tracking
 */
require_once 'config/config.php';

echo "<h1>Test Login Activity Tracking</h1>";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "<div style='margin: 20px; padding: 15px; border: 2px solid #ccc;'>";
    echo "<h3>Testing Login History Table</h3>";
    
    // Check if login_history table exists
    $tableQuery = "SHOW TABLES LIKE 'login_history'";
    $stmt = $db->prepare($tableQuery);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✓ Table login_history ditemukan</p>";
        
        // Get recent login history entries
        $historyQuery = "SELECT * FROM login_history ORDER BY created_at DESC LIMIT 5";
        $historyStmt = $db->query($historyQuery);
        $entries = $historyStmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h4>5 Entri Terbaru di Login History:</h4>";
        if (count($entries) > 0) {
            echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
            echo "<tr style='background: #f5f5f5;'>
                    <th>ID</th>
                    <th>User/NIK</th>
                    <th>Role</th>
                    <th>Success</th>
                    <th>IP</th>
                    <th>Waktu</th>
                  </tr>";
            foreach ($entries as $entry) {
                $success = $entry['success'] ? '✅ Berhasil' : '❌ Gagal';
                $user = $entry['username'] ?: $entry['nik'] ?: 'Unknown';
                echo "<tr>
                        <td>{$entry['id']}</td>
                        <td>{$user}</td>
                        <td>{$entry['role']}</td>
                        <td>{$success}</td>
                        <td>{$entry['ip_address']}</td>
                        <td>{$entry['created_at']}</td>
                      </tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color: blue;'>⚠️ Tidak ada entri di login history</p>";
            echo "<p>Silakan coba login di halaman berikut untuk menguji:</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Table login_history tidak ditemukan</p>";
        echo "<p>Membuat table login_history...</p>";
        
        // Try to create the table
        try {
            $createQuery = "CREATE TABLE IF NOT EXISTS login_history (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NULL,
                nik VARCHAR(32) NULL,
                username VARCHAR(64) NULL,
                role VARCHAR(20) NULL,
                success TINYINT(1) NOT NULL DEFAULT 0,
                ip_address VARCHAR(45) NULL,
                user_agent TEXT NULL,
                additional_info TEXT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX (user_id),
                INDEX (nik),
                INDEX (username),
                INDEX (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            $db->exec($createQuery);
            echo "<p style='color: green;'>✓ Table login_history berhasil dibuat</p>";
        } catch (PDOException $e) {
            echo "<p style='color: red;'>✗ Gagal membuat table: " . $e->getMessage() . "</p>";
        }
    }
    
    echo "</div>";
    
    echo "<div style='margin: 20px; padding: 15px; border: 2px solid #ccc;'>";
    echo "<h3>Test Login Track Function</h3>";
    
    // Test the logLoginActivity function
    try {
        echo "<p>Testing centralized login tracking function...</p>";
        $test_result = logLoginActivity($db, 999, '1111111111111111', 'test_user', 'test', true, 'Test login tracking function');
        if ($test_result) {
            echo "<p style='color: green;'>✓ Centralized login tracking function working correctly</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Login tracking function may have issues</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Login tracking function error: " . $e->getMessage() . "</p>";
    }
    
    echo "<h3>Test Login Pages</h3>";
    echo "<p>Klik link berikut untuk menguji tracking login:</p>";
    echo "<ul>";
    echo "<li><a href='login_admin.php' target='_blank'>Login Admin</a> - Track admin attempts</li>";
    echo "<li><a href='login.php' target='_blank'>Login Warga</a> - Track warga attempts</li>";
    echo "<li><a href='login_rt.php' target='_blank'>Login RT</a> - Track RT attempts</li>";
    echo "<li><a href='login_rw.php' target='_blank'>Login RW</a> - Track RW attempts</li>";
    echo "<li><a href='login_history.php' target='_blank'>View Login History</a></li>";
    echo "</ul>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Database Error: " . $e->getMessage() . "</p>";
}
?>