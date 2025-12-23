-- PERBAIKAN DATABASE SCHEMA
-- Jalankan script ini untuk memperbaiki dan upgrade database

-- 1. UPDATE USERS TABLE - Tambah RT/RW role dan timestamp update
ALTER TABLE users ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
ALTER TABLE users MODIFY COLUMN level ENUM('warga', 'rt', 'rw', 'admin') DEFAULT 'warga';
ALTER TABLE users ADD COLUMN IF NOT EXISTS no_telp VARCHAR(15);
ALTER TABLE users ADD COLUMN IF NOT EXISTS status ENUM('aktif', 'nonaktif') DEFAULT 'aktif';

-- 2. UPDATE SURAT TABLE - Fix enum values dan tambah kolom
ALTER TABLE surat MODIFY COLUMN status ENUM('pending', 'approved_rt', 'approved_rw', 'rejected_rt', 'rejected_rw', 'selesai') DEFAULT 'pending';
ALTER TABLE surat ADD COLUMN IF NOT EXISTS user_id INT NOT NULL;
ALTER TABLE surat ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
ALTER TABLE surat ADD COLUMN IF NOT EXISTS approval_date_rt DATE;
ALTER TABLE surat ADD COLUMN IF NOT EXISTS approval_date_rw DATE;
ALTER TABLE surat ADD COLUMN IF NOT EXISTS rejection_reason_rt TEXT;
ALTER TABLE surat ADD COLUMN IF NOT EXISTS rejection_reason_rw TEXT;
ALTER TABLE surat ADD COLUMN IF NOT EXISTS approved_by_rt INT;
ALTER TABLE surat ADD COLUMN IF NOT EXISTS approved_by_rw INT;

-- 3. CREATE INDICES untuk optimization
CREATE INDEX IF NOT EXISTS idx_surat_user_id ON surat(user_id);
CREATE INDEX IF NOT EXISTS idx_surat_status ON surat(status);
CREATE INDEX IF NOT EXISTS idx_surat_tanggal ON surat(tanggal_pengajuan);
CREATE INDEX IF NOT EXISTS idx_users_level ON users(level);
CREATE INDEX IF NOT EXISTS idx_users_rt_rw ON users(rt, rw);

-- 4. CREATE AUDIT LOG TABLE
CREATE TABLE IF NOT EXISTS audit_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    surat_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    action_by INT NOT NULL,
    role VARCHAR(50) NOT NULL,
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (surat_id) REFERENCES surat(id),
    FOREIGN KEY (action_by) REFERENCES users(id),
    INDEX idx_audit_surat (surat_id),
    INDEX idx_audit_action (action),
    INDEX idx_audit_date (created_at)
);

-- 5. CREATE NOTIFIKASI TABLE
CREATE TABLE IF NOT EXISTS notifikasi (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    surat_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'warning', 'success', 'error') DEFAULT 'info',
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (surat_id) REFERENCES surat(id),
    INDEX idx_notif_user (user_id),
    INDEX idx_notif_read (is_read)
);

-- 6. CREATE LETTER TEMPLATE TABLE
CREATE TABLE IF NOT EXISTS template_surat (
    id INT PRIMARY KEY AUTO_INCREMENT,
    jenis_surat_id INT NOT NULL,
    template_text TEXT NOT NULL,
    status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (jenis_surat_id) REFERENCES jenis_surat(id)
);

-- 7. INSERT DEFAULT RT/RW USERS (GANTI DENGAN DATA REAL)
INSERT INTO users (nik, username, nama, tempat_lahir, tanggal_lahir, jenis_kelamin, alamat, rt, rw, agama, pekerjaan, password, level, no_telp) 
VALUES 
('9999999999999901', 'rt003', 'Ketua RT 003', 'Tangerang', '1970-01-01', 'L', 'Jl. Sultan Ageng Tirtayas', '003', '013', 'Islam', 'RT', SHA2('rt123456', 256), 'rt', '081234567890'),
('9999999999999902', 'rw013', 'Ketua RW 013', 'Tangerang', '1970-01-01', 'L', 'Jl. Sultan Ageng Tirtayas', '003', '013', 'Islam', 'RW', SHA2('rw123456', 256), 'rw', '081234567891')
ON DUPLICATE KEY UPDATE username=VALUES(username), nama=VALUES(nama), password=VALUES(password), level=VALUES(level), no_telp=VALUES(no_telp);

-- 8. INSERT DEFAULT ADMIN ACCOUNTS
INSERT INTO users (nik, username, nama, tempat_lahir, tanggal_lahir, jenis_kelamin, alamat, rt, rw, agama, pekerjaan, password, level, no_telp) 
VALUES 
('0000000000000001', 'admin', 'Administrator', 'Tangerang', '1990-01-01', 'L', 'Jl. Admin', '001', '001', 'Islam', 'Admin', SHA2('admin123456', 256), 'admin', '081234567892')
ON DUPLICATE KEY UPDATE username=VALUES(username), nama=VALUES(nama), password=VALUES(password), level=VALUES(level), no_telp=VALUES(no_telp);

-- 9. CREATE SETTINGS TABLE
CREATE TABLE IF NOT EXISTS settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_setting_key (setting_key)
);

-- 10. INSERT DEFAULT SETTINGS
INSERT INTO settings (setting_key, setting_value) 
VALUES 
('app_name', 'Sistem Surat RT/RW Kota Tangerang'),
('institusi_nama', 'PEMERINTAH KOTA TANGERANG'),
('institusi_unit1', 'KECAMATAN PINANG'),
('institusi_unit2', 'KELURAHAN KUNCIRAN INDAH'),
('institusi_rt_rw', 'RT 003 / RW 013'),
('institusi_alamat', 'Jl. Sultan Ageng Tirtayas RT.003/RW.013, Kunciran Indah, Kec. Pinang, Kota Tangerang, Banten 15144'),
('max_upload_size', '10485760'),
('letter_processing_days', '3'),
('support_email', 'support@cbaaa.local')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);

-- STATUS UPDATE
-- ✅ Database schema telah diperbaiki
-- ✅ Indices ditambahkan untuk optimization
-- ✅ Audit logging table dibuat
-- ✅ Notifikasi system table dibuat
-- ✅ Settings table dibuat untuk centralized configuration
