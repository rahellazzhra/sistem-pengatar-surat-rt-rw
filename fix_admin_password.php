<?php
/**
 * Script untuk memperbaiki password admin (tidak di-hash)
 */
try {
    $db = new PDO('mysql:host=localhost;dbname=surat_rt_rw', 'root', '');
    
    echo "<h1>Memperbaiki Password Admin</h1>";
    
    // Set admin password to plain text (unhashed)
    $admin_password = 'admin123';
    $stmt = $db->prepare('UPDATE users SET password = ? WHERE username = ?');
    
    if ($stmt->execute([$admin_password, 'admin'])) {
        echo "<p style='color: green;'>✓ Password admin diperbarui (unhashed): admin123</p>";
        
        // Verify the update
        $stmt = $db->prepare('SELECT password FROM users WHERE username = ?');
        $stmt->execute(['admin']);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<p><strong>Database password:</strong> " . $row['password'] . "</p>";
        echo "<p><strong>Direct match check:</strong> " . (('admin123' === $row['password']) ? 'MATCH' : 'NO MATCH') . "</p>";
    } else {
        echo "<p style='color: red;'>✗ Gagal memperbarui password admin</p>";
    }
    
    echo "<p style='margin-top: 20px;'>Hapus file ini setelah digunakan</p>";
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>