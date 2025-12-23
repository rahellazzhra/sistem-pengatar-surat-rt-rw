-- Upgrade Database untuk Sistem RT/RW yang Terintegrasi
-- Jalankan perintah ini untuk upgrade database lama

-- 1. Ubah kolom level di tabel users
ALTER TABLE users MODIFY COLUMN level ENUM('warga', 'rt', 'rw', 'admin') DEFAULT 'warga';

-- 2. Tambah kolom status surat pengantar
ALTER TABLE surat ADD COLUMN status_rt VARCHAR(50) DEFAULT NULL AFTER status;
ALTER TABLE surat ADD COLUMN status_rw VARCHAR(50) DEFAULT NULL AFTER status_rt;
ALTER TABLE surat ADD COLUMN tanda_tangan_rt VARCHAR(255) DEFAULT NULL AFTER catatan_admin;
ALTER TABLE surat ADD COLUMN tanda_tangan_rw VARCHAR(255) DEFAULT NULL AFTER tanda_tangan_rt;
ALTER TABLE surat ADD COLUMN keterangan_rt TEXT DEFAULT NULL AFTER tanda_tangan_rw;
ALTER TABLE surat ADD COLUMN keterangan_rw TEXT DEFAULT NULL AFTER keterangan_rt;

-- 3. Buat tabel untuk tracking surat (history)
CREATE TABLE IF NOT EXISTS surat_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    surat_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    actor_level VARCHAR(50) NOT NULL,
    actor_id INT NOT NULL,
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (surat_id) REFERENCES surat(id)
);

-- 4. Insert data RT dan RW
-- Ubah username sesuai kebutuhan
INSERT INTO users (nik, username, nama, tempat_lahir, tanggal_lahir, jenis_kelamin, alamat, rt, rw, agama, pekerjaan, password, level) 
VALUES 
('9999999999999901', 'rt001', 'Ketua RT 001', 'Jakarta', '1980-01-01', 'L', 'Jl. Contoh', '001', '001', 'Islam', 'RT', 'rtpassword', 'rt'),
('9999999999999902', 'rw001', 'Ketua RW 001', 'Jakarta', '1980-01-01', 'L', 'Jl. Contoh', '001', '001', 'Islam', 'RW', 'rwpassword', 'rw');
