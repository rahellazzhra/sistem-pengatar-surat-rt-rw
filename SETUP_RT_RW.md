# Panduan Setup Sistem Surat RT/RW - Multi-Role Workflow

## Fitur yang Ditambahkan

### 1. **Multi-Role System (Warga, RT, RW, Admin)**
- **Warga**: Pengguna biasa yang mengajukan surat
- **RT (Rukun Tetangga)**: Ketua RT yang memverifikasi dan menyetujui surat dari warga
- **RW (Rukun Warga)**: Ketua RW yang memverifikasi lanjutan dari RT dan memberikan persetujuan final
- **Admin**: Administrator sistem

### 2. **Letter Workflow**
```
Warga membuat surat
       ↓
RT merevisi dan menyetujui/menolak
       ↓
RW merevisi lanjutan dan menyetujui/menolak
       ↓
Surat selesai (Completed)
```

### 3. **Login Pages**
- `login.php` - Login untuk Warga (dengan NIK)
- `login_admin.php` - Login untuk Admin (dengan username)
- `login_rt.php` - Login untuk RT (dengan username)
- `login_rw.php` - Login untuk RW (dengan username)

### 4. **Dashboards**
- `dashboard_warga.php` - Dashboard warga untuk mengajukan dan tracking surat
- `dashboard_admin.php` - Dashboard admin untuk overview sistem
- `dashboard_rt.php` - Dashboard RT dengan list surat untuk persetujuan dan recap
- `dashboard_rw.php` - Dashboard RW dengan list surat untuk persetujuan dan recap

## Setup Database

### Step 1: Jalankan Script Inisialisasi
```bash
# Di phpMyAdmin atau MySQL client, jalankan file:
database/surat_rt_rw.sql
```

Ini akan membuat database dan tabel dasar dengan struktur:
- `users` (untuk semua user: warga, rt, rw, admin)
- `jenis_surat` (tipe-tipe surat)
- `surat` (data surat)

### Step 2: Upgrade Database untuk Workflow RT/RW
```bash
# Jalankan file upgrade:
database/upgrade_db.sql
```

File ini akan:
- Menambah kolom `status_rt` dan `status_rw` ke tabel `surat`
- Menambah kolom untuk tanda tangan digital dan keterangan
- Membuat tabel `surat_history` untuk tracking
- Menambah test accounts:
  - **RT**: username `rt001` password `password123`
  - **RW**: username `rw001` password `password123`

## Test Accounts

Setelah upgrade database, test accounts yang tersedia:

| Role | Username | Password | NIK |
|------|----------|----------|-----|
| Admin | admin | admin123 | - |
| RT | rt001 | password123 | 9999000001 |
| RW | rw001 | password123 | 9999000002 |
| Warga | (daftar baru) | (pilih sendiri) | (16 digit) |

## Fitur Workflow

### Dashboard RT
- **Statistik**: Total surat, menunggu persetujuan, sudah disetujui, ditolak
- **Surat Menunggu Persetujuan**: List surat dari warga yang perlu diverifikasi
- **Aksi**:
  - ✅ Setujui surat
  - ❌ Tolak surat dengan alasan
  - 👁️ Lihat detail surat
- **Recap**: Daftar surat yang sudah disetujui RT

### Dashboard RW
- **Statistik**: Total surat, menunggu persetujuan RW, sudah disetujui, ditolak
- **Surat Menunggu Persetujuan**: List surat yang sudah disetujui RT, perlu approval RW
- **Aksi**: Sama dengan RT (Setujui, Tolak, Lihat)
- **Recap**: Daftar surat yang sudah disetujui RW

### Update Status Flow
File `update_letter_status.php` menangani:
1. Validasi user role (hanya RT/RW yang bisa update)
2. Update status surat dengan status_rt atau status_rw
3. Record action ke `surat_history`
4. Redirect ke dashboard yang sesuai

## Status Values

Surat bisa memiliki status berikut:
```
pending          = Baru dibuat, belum diproses RT
approved_rt      = Disetujui RT, menunggu RW
rejected_rt      = Ditolak RT
approved_rw      = Disetujui RT dan RW, selesai
rejected_rw      = Ditolak RW
completed        = Surat sudah dicetak/diambil
```

## Files yang Ditambahkan

### Authentication
- `login_rt.php` - Login page untuk RT
- `login_rw.php` - Login page untuk RW

### Dashboards
- `dashboard_rt.php` - Dashboard Ketua RT
- `dashboard_rw.php` - Dashboard Ketua RW

### Letter Management
- `update_letter_status.php` - Handle update status surat (approve/reject)

### Database
- `database/upgrade_db.sql` - Script upgrade untuk RT/RW workflow

## Customization

### Menambah RT/RW User Baru
Bisa melalui:
1. **Manual SQL**:
```sql
INSERT INTO users (nik, username, nama, tempat_lahir, tanggal_lahir, jenis_kelamin, alamat, rt, rw, agama, pekerjaan, password, level) 
VALUES ('9999000003', 'rt002', 'Ketua RT 02', 'Jakarta', '1975-08-20', 'L', 'Jl. Contoh', '02', '01', 'Islam', 'RT', 'password123', 'rt');
```

2. **Admin Panel** (bisa di-develop lebih lanjut):
Membuat form untuk admin membuat account RT/RW

### Edit Password RT/RW
Password disimpan plain text untuk RT/RW (sama seperti admin). Untuk mengubah:
```sql
UPDATE users SET password = 'password_baru' WHERE username = 'rt001';
```

## Flow Visualization

```
┌─────────────────────────────────────────────────┐
│  WARGA                                          │
│  - Daftar akun (register.php)                  │
│  - Login (login.php)                           │
│  - Dashboard (dashboard_warga.php)             │
│  - Buat surat pengajuan (pengajuan.php)        │
│  - Lihat status surat (surat_saya.php)         │
│  - Print surat (cetak_surat.php)               │
└─────────────────────────────────────────────────┘
                        ↓
           ┌────────────────────────┐
           │  SURAT HISTORY TABLE   │
           │  Tracking lengkap      │
           └────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────┐
│  RT (RUKUN TETANGGA)                           │
│  - Login (login_rt.php)                        │
│  - Dashboard (dashboard_rt.php)                │
│  - Review surat dari warga                     │
│  - Approve/Reject dengan catatan               │
│  - Lihat recap persetujuan                     │
│  - Tanda tangan digital (feature)              │
└─────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────┐
│  RW (RUKUN WARGA)                              │
│  - Login (login_rw.php)                        │
│  - Dashboard (dashboard_rw.php)                │
│  - Review surat dari RT                        │
│  - Approve/Reject dengan catatan               │
│  - Lihat recap persetujuan                     │
│  - Tanda tangan digital (feature)              │
└─────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────┐
│  SURAT SELESAI                                 │
│  - Status: Approved/Rejected                   │
│  - History tracking lengkap                    │
│  - Bisa dicetak/diambil                        │
└─────────────────────────────────────────────────┘
```

## Fitur yang Bisa Dikembangkan Lebih Lanjut

1. **Tanda Tangan Digital**: Implementasi digital signature untuk RT dan RW
2. **Notifikasi**: Email notification untuk setiap approval
3. **Template Surat**: Berbagai template surat yang bisa disesuaikan
4. **Reporting**: Dashboard reporting yang lebih detail per bulan/tahun
5. **API**: REST API untuk integrasi dengan sistem lain
6. **Mobile App**: Aplikasi mobile untuk RT/RW review surat
7. **QR Code**: QR code untuk verifikasi surat asli
8. **Batch Processing**: Approve/reject multiple surat sekaligus

## Troubleshooting

### 1. "Class Letter not found"
- Pastikan `classes/Letter.php` ada
- Check `config/config.php` include path

### 2. RT/RW tidak bisa login
- Cek database apakah akun sudah ada: `SELECT * FROM users WHERE level IN ('rt', 'rw');`
- Pastikan password sesuai (case-sensitive)

### 3. Update status error
- Cek apakah `surat_history` table sudah ada
- Pastikan surat dengan ID tersebut ada di database

### 4. Dashboard tidak muncul data
- Cek query di `dashboard_rt.php` atau `dashboard_rw.php`
- Pastikan ada surat dengan status yang sesuai di database
