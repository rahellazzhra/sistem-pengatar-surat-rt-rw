<?php
/**
 * Final Setup Database untuk Sistem Surat RT/RW
 * 
 * Membuat database yang sesuai dengan kode yang sudah dibuat
 * Hapus file ini setelah digunakan!
 */

echo "<h1>Final Setup Sistem Surat RT/RW</h1>";

try {
    // Create database connection
    $pdo = new PDO("mysql:host=localhost", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS surat_rt_rw");
    echo "<p style='color: green;'>✓ Database surat_rt_rw siap</p>";
    
    // Use the database
    $pdo->exec("USE surat_rt_rw");
    
    // First disable foreign key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    // Drop existing tables to start fresh
    $tables = array('surat', 'jenis_surat', 'users');
    foreach ($tables as $table) {
        try {
            $pdo->exec("DROP TABLE IF EXISTS $table");
        } catch (PDOException $e) {
            // Ignore errors if tables don't exist
        }
    }
    
    // Re-enable foreign key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    // Create users table that matches our code
    $pdo->exec("CREATE TABLE users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        nik VARCHAR(16) UNIQUE NOT NULL,
        nama VARCHAR(100) NOT NULL,
        tempat_lahir VARCHAR(50) NOT NULL,
        tanggal_lahir DATE NOT NULL,
        jenis_kelamin ENUM('L', 'P') NOT NULL,
        alamat TEXT NOT NULL,
        rt VARCHAR(3) NOT NULL,
        rw VARCHAR(3) NOT NULL,
        agama VARCHAR(20) NOT NULL,
        pekerjaan VARCHAR(50) NOT NULL,
        password VARCHAR(255) NOT NULL,
        level ENUM('warga', 'admin') DEFAULT 'warga',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "<p style='color: green;'>✓ Tabel users dibuat</p>";
    
    // Create jenis_surat table
    $pdo->exec("CREATE TABLE jenis_surat (
        id INT PRIMARY KEY AUTO_INCREMENT,
        nama_surat VARCHAR(100) NOT NULL,
        deskripsi TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "<p style='color: green;'>✓ Tabel jenis_surat dibuat</p>";
    
    // Create surat table
    $pdo->exec("CREATE TABLE surat (
        id INT PRIMARY KEY AUTO_INCREMENT,
        no_surat VARCHAR(20) UNIQUE,
        user_id INT NOT NULL,
        jenis_surat_id INT NOT NULL,
        keperluan TEXT NOT NULL,
        tanggal_pengajuan DATE NOT NULL,
        status ENUM('pending', 'diproses', 'selesai', 'ditolak') DEFAULT 'pending',
        tanggal_selesai DATE,
        catatan_admin TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (jenis_surat_id) REFERENCES jenis_surat(id)
    )");
    echo "<p style='color: green;'>✓ Tabel surat dibuat</p>";
    
    // Insert default letter types
    $pdo->exec("INSERT INTO jenis_surat (nama_surat, deskripsi) VALUES 
        ('Surat Keterangan Domisili', 'Surat keterangan tempat tinggal'),
        ('Surat Keterangan Usaha', 'Surat keterangan usaha'),
        ('Surat Keterangan Tidak Mampu', 'Surat keterangan tidak mampu'),
        ('Surat Keterangan Kelahiran', 'Surat keterangan kelahiran'),
        ('Surat Keterangan Kematian', 'Surat keterangan kematian'),
        ('Surat Pengantar', 'Surat pengantar umum')");
    echo "<p style='color: green;'>✓ Data jenis surat ditambahkan</p>";
    
    // Create default admin user
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->exec("INSERT INTO users (nik, nama, tempat_lahir, tanggal_lahir, jenis_kelamin, alamat, rt, rw, agama, pekerjaan, password, level) 
                VALUES ('1234567890123456', 'Administrator', 'Jakarta', '1990-01-01', 'L', 'Alamat Admin', '001', '001', 'Islam', 'Admin RT/RW', '$admin_password', 'admin')");
    echo "<p style='color: green;'>✓ User admin dibuat (NIK: 1234567890123456, Password: admin123)</p>";
    
    echo "<p style='color: green; font-weight: bold; font-size: 18px;'>✓ Setup berhasil! Sistem siap digunakan.</p>";
    
    echo "<h2>Langkah Selanjutnya:</h2>";
    echo "<ol>";
    echo "<li><a href='login.php' style='color: blue;'>Login sebagai admin</a> (NIK: 1234567890123456, Password: admin123)</li>";
    echo "<li><a href='register.php' style='color: blue;'>Daftar sebagai warga baru</a></li>";
    echo "<li><a href='index.php' style='color: blue;'>Akses dashboard</a></li>";
    echo "<li>Hapus file final_setup.php setelah ini</li>";
    echo "</ol>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Error setup: " . $e->getMessage() . "</p>";
}
?>