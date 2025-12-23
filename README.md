# Sistem Pelayanan Pengantar Surat RT/RW

Website pelayanan pengantar surat untuk RT/RW yang dibangun dengan PHP, sangat responsif dan rapih.

## Fitur Utama

- **Autentikasi Multi-level**: Login untuk warga, admin, RT, dan RW dengan hak akses berbeda
- **Pengajuan Surat**: Warga dapat mengajukan berbagai jenis surat dengan form lengkap
- **Persetujuan Berjenjang**: Sistem approval dua tingkat (RT dan RW) dengan workflow yang jelas
- **Manajemen Surat**: Admin dapat mengelola status surat, memberikan nomor, dan catatan
- **Dashboard Interaktif**: Tampilan statistik dan informasi surat real-time untuk setiap level pengguna
- **Responsif**: Desain yang optimal di berbagai perangkat (desktop, tablet, mobile)
- **Cetak Surat**: Fitur cetak surat yang sudah selesai dengan header institusi resmi
- **Riwayat Login**: Pelacakan aktivitas login dengan IP dan user agent
- **Audit Trail**: Log perubahan status surat untuk keperluan auditing
- **Notifikasi Sistem**: Pemberitahuan internal untuk pengguna terkait status surat
- **Keamanan Tingkat Lanjut**: CSRF protection, password hashing, SQL injection prevention, session management

## Jenis Surat yang Tersedia

1. Surat Keterangan Domisili
2. Surat Keterangan Usaha  
3. Surat Keterangan Tidak Mampu
4. Surat Keterangan Kelahiran
5. Surat Keterangan Kematian
6. Surat Pengantar

## Teknologi yang Digunakan

- **PHP 7.4+** - Backend programming language
- **MySQL 5.7+** - Database management system
- **HTML5 & CSS3** - Frontend dengan desain responsif menggunakan CSS Grid dan Flexbox
- **JavaScript** - Interaktivitas client-side
- **PDO** - Database abstraction layer dengan prepared statements
- **Bootstrap 4** - Framework CSS untuk komponen UI (via CDN)

## Struktur Database

### Tabel `users`
- id (Primary Key)
- nik (Nomer Induk Kependudukan) - UNIQUE
- username (optional)
- nama (Nama lengkap)
- tempat_lahir
- tanggal_lahir
- jenis_kelamin (L/P)
- alamat
- rt, rw
- agama
- pekerjaan
- password (hashed)
- level (warga/rt/rw/admin)
- created_at

### Tabel `jenis_surat`
- id (Primary Key)
- nama_surat
- deskripsi
- created_at

### Tabel `surat`
- id (Primary Key)
- no_surat (unique)
- user_id (Foreign Key)
- jenis_surat_id (Foreign Key)
- keperluan
- tanggal_pengajuan
- status (pending/diproses/selesai/ditolak)
- status_rt (pending/approved/rejected)
- status_rw (pending/approved/rejected)
- tanggal_selesai
- catatan_admin
- tanda_tangan_rt
- tanda_tangan_rw
- keterangan_rt
- keterangan_rw
- created_at

### Tabel `login_history`
- id (Primary Key)
- user_id
- nik
- username
- role
- success (boolean)
- ip_address
- user_agent
- additional_info
- created_at

### Tabel `audit_log`
- id (Primary Key)
- surat_id (Foreign Key)
- action
- action_by
- role
- details
- created_at

### Tabel `notifikasi`
- id (Primary Key)
- user_id
- surat_id
- title
- message
- type (info/success/warning/error)
- is_read (boolean)
- created_at

### Tabel `surat_history`
- id (Primary Key)
- surat_id (Foreign Key)
- action
- actor_level
- actor_id
- keterangan
- created_at

## Instalasi

### 1. Persyaratan Sistem
- Web server (Apache/Nginx)
- PHP 7.4 atau lebih baru dengan ekstensi PDO MySQL
- MySQL 5.7 atau lebih baru
- Browser modern

### 2. Setup Database
1. Buat database baru dengan nama `surat_rt_rw`
2. Import file `database/surat_rt_rw.sql` untuk skema dasar
3. Import file `database/create_login_history.sql` untuk tabel riwayat login
4. Jalankan `database/upgrade_db.sql` untuk menambahkan fitur RT/RW dan kolom tambahan
5. (Opsional) Jalankan `database/fix_schema.sql` jika ada masalah skema

### 3. Konfigurasi
1. Edit file `config/database.php` untuk koneksi database:
   ```php
   private $host = "localhost";
   private $db_name = "surat_rt_rw";
   private $username = "root";
   private $password = "";
   ```

2. Sesuaikan BASE_URL di `config/config.php` sesuai lokasi instalasi:
   ```php
   define('BASE_URL', 'http://localhost/sistem%20surat%20rt%20rw/');
   ```
   Ganti dengan path yang sesuai jika dihosting di subdirektori.

3. Konfigurasi institusi di `config/institusi.php`:
   ```php
   define('INSTITUSI_NAMA', 'PEMERINTAH KOTA TANGERANG');
   define('INSTITUSI_UNIT1', 'KECAMATAN PINANG');
   define('INSTITUSI_UNIT2', 'KELURAHAN KUNCIRAN INDAH');
   define('INSTITUSI_RT_RW', 'RT 003 / RW 013');
   ```

### 4. Akses Aplikasi
1. Buka browser dan akses `http://localhost/sistem%20surat%20rt%20rw/` (sesuaikan dengan BASE_URL)
2. Untuk pertama kali, daftar sebagai warga baru melalui halaman register
3. Untuk akses admin, login dengan:
   - NIK: `9999999999999901` / Password: `rtpassword` (level RT)
   - NIK: `9999999999999902` / Password: `rwpassword` (level RW)
   - Atau buat user dengan level 'admin' langsung di database

## Cara Penggunaan

### Untuk Warga:
1. **Registrasi**: Daftar akun baru dengan data lengkap (NIK, nama, alamat, dll.)
2. **Login**: Masuk dengan NIK dan password
3. **Ajukan Surat**: Pilih jenis surat, isi keperluan, dan submit
4. **Pantau Status**: Lihat status surat di dashboard (pending, diproses, selesai, ditolak)
5. **Cetak Surat**: Download/print surat yang sudah selesai dengan nomor resmi

### Untuk RT/RW:
1. **Login**: Masuk dengan akun RT/RW
2. **Dashboard**: Lihat daftar surat yang perlu persetujuan
3. **Review Surat**: Tinjau detail surat dan setujui/tolak dengan catatan
4. **Berikan Tanda Tangan**: Upload tanda tangan digital (opsional)
5. **Lanjutkan ke Level Berikutnya**: Surat yang disetujui RT akan ke RW, begitu seterusnya

### Untuk Admin:
1. **Login**: Masuk dengan akun admin
2. **Kelola Semua Surat**: Lihat semua pengajuan surat dari semua level
3. **Update Status**: Ubah status dan beri nomor surat
4. **Berikan Catatan**: Tambahkan catatan jika diperlukan
5. **Kelola Pengguna**: Tambah/edit/hapus user (warga, RT, RW, admin)

## Struktur File

```
sistem surat rt rw/
├── config/
│   ├── config.php          # Konfigurasi aplikasi, fungsi helper
│   ├── database.php        # Koneksi database
│   └── institusi.php       # Konfigurasi header institusi
├── classes/
│   ├── User.php            # Class untuk operasi user
│    └── Letter.php          # Class untuk operasi surat
├── assets/
│    └── css/
│        └── style.css      # Stylesheet utama
├── database/
│   ├── surat_rt_rw.sql           # Skema database dasar
│   ├── create_login_history.sql  # Tabel riwayat login
│   ├── upgrade_db.sql            # Upgrade untuk fitur RT/RW
│    └── fix_schema.sql            # Perbaikan skema
├── index.php               # Halaman utama
├── login.php              # Login umum
├── login_warga.php        # Login khusus warga
├── login_rt.php           # Login khusus RT
├── login_rw.php           # Login khusus RW
├── login_admin.php        # Login khusus admin
├── register.php           # Registrasi warga
├── pengajuan.php          # Form pengajuan surat
├── surat_saya.php         # Daftar surat pengguna
├── dashboard_warga.php    # Dashboard warga
├── dashboard_rt.php       # Dashboard RT
├── dashboard_rw.php       # Dashboard RW
├── dashboard_admin.php    # Dashboard admin
├── admin.php              # Admin panel
├── admin_users.php        # Kelola user
├── approval_surat.php     # Persetujuan surat (RT/RW)
├── detail_surat.php       # Detail surat
├── cetak_surat.php        # Cetak surat
├── update_letter_status.php # Update status surat
├── login_history.php      # Riwayat login
├── verify_login_activity.php # Verifikasi aktivitas login
├── logout.php             # Logout
└── README.md              # Dokumentasi ini
```

## Keamanan

- Password di-hash menggunakan `password_hash()` dengan algoritma bcrypt
- Validasi input form di server-side dan client-side
- Protection terhadap SQL Injection dengan PDO prepared statements
- Session management yang aman dengan regenerasi session ID
- CSRF token protection untuk semua form penting
- Authorization checks untuk akses halaman berdasarkan level user
- Logging aktivitas login dan perubahan data untuk audit trail
- Sanitasi output dengan `htmlspecialchars()` untuk mencegah XSS

## Responsif Design

Website menggunakan CSS Grid dan Flexbox untuk layout yang responsif:
- Optimal di desktop (≥1200px), tablet (768px-1199px), dan mobile (<768px)
- Media queries untuk berbagai ukuran layar
- UI yang intuitif dan user-friendly dengan komponen Bootstrap
- Navigasi yang mudah diakses di semua perangkat

## Pengembangan Lebih Lanjut

Fitur yang dapat ditambahkan:
- [ ] Notifikasi email/SMS
- [ ] Upload dokumen pendukung (foto KTP, KK, dll.)
- [ ] Laporan statistik lebih detail dengan grafik
- [ ] Multi-level admin dengan hak akses berbeda
- [ ] Backup database otomatis
- [ ] Integrasi dengan e-signature resmi
- [ ] API untuk integrasi dengan sistem eksternal
- [ ] Multi-bahasa (Indonesia, Inggris)
- [ ] Theme dark/light mode

## Kontak

Untuk pertanyaan atau bantuan teknis, silakan hubungi pengembang.

---

**Dibuat dengan  ❤️ untuk mempermudah pelayanan RT/RW**