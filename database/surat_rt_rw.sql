-- Database: surat_rt_rw
CREATE DATABASE IF NOT EXISTS surat_rt_rw;
USE surat_rt_rw;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nik VARCHAR(16) UNIQUE NOT NULL,
    username VARCHAR(50) UNIQUE,
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
);

-- Letter types table
CREATE TABLE jenis_surat (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_surat VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Letters table
CREATE TABLE surat (
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
);

-- Insert default letter types
INSERT INTO jenis_surat (nama_surat, deskripsi) VALUES
('Surat Keterangan Domisili', 'Surat keterangan tempat tinggal'),
('Surat Keterangan Usaha', 'Surat keterangan usaha'),
('Surat Keterangan Tidak Mampu', 'Surat keterangan tidak mampu'),
('Surat Keterangan Kelahiran', 'Surat keterangan kelahiran'),
('Surat Keterangan Kematian', 'Surat keterangan kematian'),
('Surat Pengantar', 'Surat pengantar umum');