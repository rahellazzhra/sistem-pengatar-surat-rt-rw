<?php
/**
 * Test script untuk login admin
 */
require_once 'config/config.php';

echo "<h1>Test Login Admin</h1>";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $user = new User($db);
    $user->username = 'admin';
    $user->password = 'admin123';
    
    echo "<div style='margin: 20px; padding: 15px; border: 2px solid #ccc;'>";
    echo "<h3>Testing Admin Login</h3>";
    echo "<p><strong>Username:</strong> " . $user->username . "</p>";
    echo "<p><strong>Password:</strong> " . $user->password . "</p>";
    
    if ($user->login()) {
        echo "<p style='color: green; font-weight: bold;'>✓ Login berhasil!</p>";
        echo "<p><strong>User ID:</strong> " . $user->id . "</p>";
        echo "<p><strong>NIK:</strong> " . $user->nik . "</p>";
        echo "<p><strong>Nama:</strong> " . $user->nama . "</p>";
        echo "<p><strong>Level:</strong> " . $user->level . "</p>";
        
        // Test session variables would be set
        echo "<p style='color: blue;'>✓ Session variables siap diset</p>";
    } else {
        echo "<p style='color: red; font-weight: bold;'>✗ Login gagal!</p>";
        echo "<p>Periksa username dan password</p>";
    }
    
    echo "</div>";
    
    // Test database query
    echo "<div style='margin: 20px; padding: 15px; border: 2px solid #ccc;'>";
    echo "<h3>Test Database Query</h3>";
    
    $query = "SELECT id, nik, username, nama, level FROM users WHERE username = 'admin'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p style='color: green;'>✓ Admin user ditemukan di database</p>";
        echo "<pre>" . print_r($user_data, true) . "</pre>";
    } else {
        echo "<p style='color: red;'>✗ Admin user tidak ditemukan</p>";
    }
    
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Database Error: " . $e->getMessage() . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}

echo "<h2 style='margin-top: 20px;'>Manual Testing:</h2>";
echo "<ul>";
echo "<li><a href='login_admin.php' target='_blank'>Login Admin Page</a></li>";
echo "<li><a href='login.php' target='_blank'>Login Warga Page</a></li>";
echo "</ul>";

echo "<p style='margin-top: 20px; color: gray;'>Hapus file ini setelah testing selesai</p>";
?>
