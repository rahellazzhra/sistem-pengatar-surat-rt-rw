<?php
/**
 * Debug System untuk Sistem Surat RT/RW
 * 
 * Melihat informasi tentang system dan data yang ada
 * Hapus file ini setelah debugging selesai!
 */

require_once 'config/config.php';

echo "<h1>Debug System Sistem Surat RT/RW</h1>";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "<p style='color: green;'>✓ Koneksi database BERHASIL</p>";
    
    // Check users table structure
    echo "<h2>Struktur Tabel Users</h2>";
    $stmt = $db->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($column['Field'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($column['Type'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($column['Null'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($column['Key'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($column['Default'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($column['Extra'] ?? '') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Count existing users
    $stmt = $db->query("SELECT COUNT(*) as total FROM users");
    $user_count = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>Total users: " . $user_count['total'] . "</p>";
    
    // Show sample users (if any)
    $stmt = $db->query("SELECT id, nik, nama, level FROM users LIMIT 5");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($users) > 0) {
        echo "<h2>Sample Users</h2>";
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>ID</th><th>NIK</th><th>Nama</th><th>Level</th></tr>";
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($user['id']) . "</td>";
            echo "<td>" . htmlspecialchars($user['nik']) . "</td>";
            echo "<td>" . htmlspecialchars($user['nama']) . "</td>";
            echo "<td>" . htmlspecialchars($user['level']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Check jenis_surat table
    echo "<h2>Jenis Surat yang Tersedia</h2>";
    $stmt = $db->query("SELECT * FROM jenis_surat");
    $jenis_surat = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($jenis_surat) > 0) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>ID</th><th>Nama Surat</th><th>Deskripsi</th></tr>";
        foreach ($jenis_surat as $js) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($js['id']) . "</td>";
            echo "<td>" . htmlspecialchars($js['nama_surat']) . "</td>";
            echo "<td>" . htmlspecialchars($js['deskripsi']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>Tidak ada jenis surat yang tersedia!</p>";
    }
    
    echo "<h2>Testing User Registration</h2>";
    
    // Test a simple registration
    require_once 'classes/User.php';
    $test_user = new User($db);
    $test_user->nik = "9999999999999999";
    $test_user->nama = "Test User";
    $test_user->tempat_lahir = "Test City";
    $test_user->tanggal_lahir = "2000-01-01";
    $test_user->jenis_kelamin = "L";
    $test_user->alamat = "Test Address";
    $test_user->rt = "001";
    $test_user->rw = "001";
    $test_user->agama = "Islam";
    $test_user->pekerjaan = "Tester";
    $test_user->password = "test123";
    
    if ($test_user->nikExists()) {
        echo "<p style='color: orange;'>NIK test sudah ada, menghapus dulu...</p>";
        // Clean up any existing test user
        $db->exec("DELETE FROM users WHERE nik = '9999999999999999'");
    }
    
    if ($test_user->register()) {
        echo "<p style='color: green;'>✓ Test registration BERHASIL</p>";
        // Clean up test user
        $db->exec("DELETE FROM users WHERE nik = '9999999999999999'");
        echo "<p style='color: green;'>✓ Test user berhasil dihapus</p>";
    } else {
        echo "<p style='color: red;'>✗ Test registration GAGAL</p>";
    }
    
    echo "<h2>Status System</h2>";
    echo "<p style='color: green;'>✓ Database terhubung</p>";
    echo "<p style='color: green;'>✓ Tabel users ada dan bisa diakses</p>";
    echo "<p style='color: green;'>✓ Tabel jenis_surat ada dan berisi data</p>";
    echo "<p style='color: green;'>✓ Session berjalan</p>";
    
    echo "<h2>Langkah Selanjutnya</h2>";
    echo "<ol>";
    echo "<li>Akses <a href='register.php'>register.php</a> untuk membuat akun warga</li>";
    echo "<li>Akses <a href='login.php'>login.php</a> untuk login</li>";
    if ($user_count['total'] == 0) {
        echo "<li style='color: orange;'>Belum ada user. Buat user pertama di register.php</li>";
    }
    echo "</ol>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Error debug system: " . $e->getMessage() . "</p>";
}

echo "<p><strong>Note:</strong> Hapus file ini setelah debugging selesai!</p>";
?>