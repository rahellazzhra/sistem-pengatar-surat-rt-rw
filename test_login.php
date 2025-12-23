<?php
/**
 * Test Login untuk Sistem Surat RT/RW
 */
require_once 'config/config.php';
require_once 'classes/User.php';

echo "<h1>Test Login</h1>";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $user = new User($db);
    
    // Test with existing admin user
    $user->nik = "1234567890123456";
    $user->password = "admin123";
    
    if ($user->login()) {
        echo "<p style='color: green;'>✓ Login BERHASIL!</p>";
        echo "<p>User ID: " . $user->id . "</p>";
        echo "<p>NIK: " . $user->nik . "</p>";
        echo "<p>Nama: " . $user->nama . "</p>";
        echo "<p>Level: " . $user->level . "</p>";
    } else {
        echo "<p style='color: red;'>✗ Login GAGAL!</p>";
        echo "<p>Pastikan password admin adalah 'admin123'</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>