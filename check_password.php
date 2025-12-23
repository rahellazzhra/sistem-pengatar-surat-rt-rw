<?php
// Check the admin password in database
try {
    $db = new PDO('mysql:host=localhost;dbname=surat_rt_rw', 'root', '');
    $stmt = $db->prepare('SELECT password FROM users WHERE username = ?');
    $stmt->execute(['admin']);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "Database password: " . $row['password'] . "\n";
    echo "Password length: " . strlen($row['password']) . "\n";
    
    // Check if it's hashed
    $is_hashed = password_verify('admin123', $row['password']);
    echo "Is password_hashed? " . ($is_hashed ? 'YES' : 'NO') . "\n";
    
    // Direct comparison
    $direct_match = ('admin123' === $row['password']);
    echo "Direct match with 'admin123'? " . ($direct_match ? 'YES' : 'NO') . "\n";
    
} catch(PDOException $e) {
    echo 'Error: ' . $e->getMessage();
}
?>