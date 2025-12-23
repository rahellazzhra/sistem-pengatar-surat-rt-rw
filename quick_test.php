<?php
/**
 * Quick Test untuk melihat struktur database yang sebenarnya
 */
require_once 'config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "<h1>Quick Database Test</h1>";
    
    // Get actual column names from users table
    $stmt = $db->query("SHOW COLUMNS FROM users");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h2>Columns in users table:</h2>";
    echo "<pre>";
    print_r($columns);
    echo "</pre>";
    
    // Try to select from table to see if it works
    $stmt = $db->query("SELECT * FROM users LIMIT 1");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Sample user data:</h2>";
    echo "<pre>";
    print_r($users);
    echo "</pre>";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>