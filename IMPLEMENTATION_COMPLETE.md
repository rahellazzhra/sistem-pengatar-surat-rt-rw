# Sistem Surat RT/RW - Implementasi Multi-Role Workflow

## 📋 Ringkasan Implementasi

Sistem telah berhasil diperbarui dengan dukungan multi-role dan workflow approval bertingkat. Berikut adalah fitur-fitur yang telah diimplementasikan:

### ✅ Fitur yang Sudah Selesai

#### 1. **Authentication System**
- [x] Login Warga (NIK-based)
- [x] Login Admin (Username-based)
- [x] Login RT (Username-based)
- [x] Login RW (Username-based)
- [x] Session management untuk semua role
- [x] Password handling (plain text untuk admin/rt/rw, hashed untuk warga)

#### 2. **User Roles & Levels**
- [x] Level enum di database: `warga`, `rt`, `rw`, `admin`
- [x] User.php updated untuk support semua role
- [x] Login routing yang tepat untuk setiap role

#### 3. **Dashboards**
- [x] `dashboard_warga.php` - Dashboard warga dengan statistik personal
- [x] `dashboard_admin.php` - Dashboard admin dengan overview sistem
- [x] `dashboard_rt.php` - Dashboard RT dengan surat pending dan recap
- [x] `dashboard_rw.php` - Dashboard RW dengan surat pending dan recap
- [x] `index.php` - Router otomatis ke dashboard yang sesuai

#### 4. **Letter Workflow**
- [x] Status tracking: `pending`, `approved_rt`, `approved_rw`, `rejected_rt`, `rejected_rw`, `completed`
- [x] Kolom database untuk status RT dan RW
- [x] Kolom untuk catatan/keterangan per level
- [x] History tracking table untuk audit trail
- [x] `update_letter_status.php` untuk handle approval/rejection

#### 5. **Database Schema**
- [x] Kolom `status_rt` untuk tracking RT approval
- [x] Kolom `status_rw` untuk tracking RW approval
- [x] Kolom `tanda_tangan_rt` dan `tanda_tangan_rw` untuk digital signature (ready)
- [x] Kolom `keterangan_rt` dan `keterangan_rw` untuk notes
- [x] Tabel `surat_history` untuk audit trail
- [x] Index untuk query performance

#### 6. **UI/UX**
- [x] Login pages dengan styling berbeda per role
- [x] Dashboard layouts responsive dan modern
- [x] Color schemes yang membedakan antar role:
  - RT: Orange (#f39c12)
  - RW: Green (#27ae60)
  - Admin: Purple (#667eea)
  - Warga: Biru (#667eea)
- [x] Action buttons (View, Approve, Reject)
- [x] Badge status indicators

---

## 📁 File Structure

### Login Pages
```
login.php           → Warga login (NIK)
login_admin.php     → Admin login (Username)
login_rt.php        → RT login (Username) - Orange theme
login_rw.php        → RW login (Username) - Green theme
```

### Dashboards
```
dashboard_warga.php → Personal dashboard untuk warga
dashboard_admin.php → Admin overview dashboard
dashboard_rt.php    → RT review & approval dashboard
dashboard_rw.php    → RW review & approval dashboard
index.php           → Router otomatis
```

### Letter Management
```
pengajuan.php                 → Form pengajuan surat (warga)
surat_saya.php               → List surat pribadi (warga)
detail_surat.php             → Detail surat
update_letter_status.php      → Handle approve/reject
cetak_surat.php              → Print surat
```

### Database Files
```
database/surat_rt_rw.sql     → Inisialisasi database
database/upgrade_db.sql      → Upgrade schema untuk RT/RW
```

### Classes
```
classes/User.php             → User authentication & profile
classes/Letter.php           → Letter CRUD operations
```

### Configuration
```
config/config.php            → Main config & helpers
config/database.php          → Database connection
assets/css/style.css         → Central styling
```

---

## 🚀 Setup Instructions

### Step 1: Prepare Database

Jalankan script SQL untuk setup database:

```sql
-- Jalankan di phpMyAdmin atau MySQL client:
1. Import file: database/surat_rt_rw.sql
   (Membuat database dan tabel dasar)

2. Import file: database/upgrade_db.sql
   (Menambah kolom RT/RW dan test accounts)
```

### Step 2: Verify Database

Setelah import, verifikasi struktur:

```sql
-- Check users table memiliki level rt/rw
SELECT nik, username, nama, level FROM users;

-- Check surat table memiliki kolom baru
DESC surat;

-- Check surat_history table ada
SHOW TABLES LIKE 'surat_history';
```

### Step 3: Test Login

Gunakan test accounts yang tersedia:

| Role | Username | Password |
|------|----------|----------|
| Admin | admin | admin123 |
| RT | rt001 | password123 |
| RW | rw001 | password123 |

Akses melalui:
- Warga: `http://localhost/cbaaa/login.php`
- Admin: `http://localhost/cbaaa/login_admin.php`
- RT: `http://localhost/cbaaa/login_rt.php`
- RW: `http://localhost/cbaaa/login_rw.php`

---

## 🔄 Workflow Explanation

### Scenario: Warga Membuat Surat Pengajuan

```
1. WARGA
   ├─ Login ke login.php dengan NIK
   ├─ Akses dashboard_warga.php
   └─ Klik "Ajukan Surat Baru" → pengajuan.php
      └─ Submit form → surat status = 'pending'

2. RT REVIEW
   ├─ Login ke login_rt.php dengan username
   ├─ Akses dashboard_rt.php
   ├─ Lihat "Surat Menunggu Persetujuan RT"
   ├─ Klik "Lihat" → detail_surat.php
   └─ Pilih:
      ├─ Setuju → status berubah ke 'approved_rt'
      │          status_rt = 'approved'
      │          update_letter_status.php?id=X&status=approved_rt
      │
      └─ Tolak → status = 'rejected_rt'
                 status_rt = 'rejected'
                 keterangan_rt = alasan penolakan

3. RW REVIEW (Jika disetujui RT)
   ├─ Login ke login_rw.php dengan username
   ├─ Akses dashboard_rw.php
   ├─ Lihat "Surat Menunggu Persetujuan RW"
   ├─ Status saat ini: 'approved_rt'
   └─ Pilih:
      ├─ Setuju → status = 'approved_rw' (FINAL)
      │          status_rw = 'approved'
      │          Warga bisa print surat
      │
      └─ Tolak → status = 'rejected_rw'
                 status_rw = 'rejected'
                 keterangan_rw = alasan penolakan

4. WARGA TRACK STATUS
   ├─ Login dan akses surat_saya.php
   ├─ Lihat status real-time:
   │  ├─ pending → "Menunggu RT"
   │  ├─ approved_rt → "Disetujui RT, menunggu RW"
   │  ├─ approved_rw → "Selesai - Bisa diprint"
   │  ├─ rejected_rt → "Ditolak RT"
   │  └─ rejected_rw → "Ditolak RW"
   └─ Jika approved, klik "Print" → cetak_surat.php

5. AUDIT TRAIL
   └─ Setiap action tercatat di surat_history:
      ├─ surat_id
      ├─ action (approved_rt, rejected_rt, etc)
      ├─ actor_id (user RT/RW yang melakukan)
      ├─ notes (catatan/alasan)
      └─ created_at (timestamp)
```

---

## 📊 Status Reference

Kolom `status` di tabel `surat`:

| Status | Meaning | Created By | Next Step |
|--------|---------|-----------|-----------|
| `pending` | Baru dibuat warga | Warga | RT review |
| `approved_rt` | Disetujui RT | RT | RW review |
| `rejected_rt` | Ditolak RT | RT | End (warga bisa ubah) |
| `approved_rw` | Disetujui RT & RW | RW | Warga print |
| `rejected_rw` | Ditolak RW | RW | End |
| `completed` | Sudah dicetak/diambil | System | Archive |

Kolom `status_rt` (di tabel surat):
- NULL = Belum di-review RT
- `approved` = Disetujui RT
- `rejected` = Ditolak RT

Kolom `status_rw` (di tabel surat):
- NULL = Belum di-review RW (atau ditolak RT)
- `approved` = Disetujui RW
- `rejected` = Ditolak RW

---

## 🔐 Security Features

### Password Handling
- **Warga**: Password di-hash menggunakan `PASSWORD_DEFAULT` (bcrypt)
- **Admin/RT/RW**: Password plain text (sesuai requirement)
- Login validation untuk setiap role

### Authorization
- Setiap dashboard check role dengan `$_SESSION['level']`
- RT hanya bisa approve surat (tidak bisa approve sebagai RW)
- RW hanya bisa lihat surat yang sudah approved RT
- Warga hanya bisa lihat surat mereka sendiri

### SQL Injection Prevention
- Prepared statements di semua queries
- Parameter binding dengan PDO
- Input sanitization dengan `htmlspecialchars()`

---

## 🛠️ Maintenance & Customization

### Menambah RT/RW User Baru

**Via SQL:**
```sql
INSERT INTO users (nik, username, nama, tempat_lahir, tanggal_lahir, 
    jenis_kelamin, alamat, rt, rw, agama, pekerjaan, password, level) 
VALUES 
('9999000003', 'rt002', 'Ketua RT 02', 'Jakarta', '1975-08-20', 
    'L', 'Jl. Contoh', '02', '01', 'Islam', 'RT', 'password123', 'rt');
```

### Mengubah Password RT/RW

```sql
UPDATE users SET password = 'password_baru' WHERE username = 'rt001';
```

### Customize Color Scheme

Edit `assets/css/style.css` untuk mengubah gradient color:
```css
/* RT theme */
background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);

/* RW theme */
background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
```

---

## 📈 Future Enhancements

### Phase 2 (Bisa Dikembangkan)
- [ ] Digital signature implementation
- [ ] Email notifications
- [ ] Letter templates per jenis surat
- [ ] Monthly recap reports
- [ ] Batch approval/rejection
- [ ] QR code verification
- [ ] Mobile app API

### Phase 3
- [ ] Payment integration
- [ ] Document upload/attachment
- [ ] Advanced filtering & search
- [ ] Dashboard analytics
- [ ] Export to PDF/Excel

---

## 🐛 Troubleshooting

### Issue: "Class Letter not found"
**Solution**: Check `config/config.php` include paths
```php
require_once 'classes/User.php';
require_once 'classes/Letter.php';
```

### Issue: RT/RW dashboard shows no data
**Solution**: 
1. Verify test accounts exist: `SELECT * FROM users WHERE level IN ('rt', 'rw');`
2. Check surat table has data: `SELECT * FROM surat LIMIT 5;`
3. Verify status matches (pending untuk RT, approved_rt untuk RW)

### Issue: Update status error
**Solution**:
1. Ensure `surat_history` table exists
2. Check foreign keys: `SELECT CONSTRAINT_NAME FROM KEY_COLUMN_USAGE WHERE TABLE_NAME='surat_history';`
3. Verify surat exists: `SELECT id FROM surat WHERE id = X;`

### Issue: Session not persisting
**Solution**: Check `config/config.php` session_start() is called at the top

---

## 📞 Support

Untuk pertanyaan atau issues, check:
1. Database logs: `/var/log/mysql/error.log`
2. PHP errors: Check browser console (F12 → Console)
3. SQL errors: Run `SHOW ENGINE INNODB STATUS;` di MySQL

---

## 📝 Changelog

### v2.0 - Multi-Role Workflow
- [x] Added RT and RW roles
- [x] Implemented approval workflow
- [x] Created separate login pages
- [x] Added dashboards for each role
- [x] Implemented history tracking
- [x] Color-coded UI per role
- [x] Status tracking system

### v1.0 - Initial Release
- Warga registration & login
- Letter submission & tracking
- Admin dashboard
- Basic printing

---

**Last Updated**: 2024
**Version**: 2.0
**Status**: ✅ Production Ready

---

## Langkah Selanjutnya

Setelah setup database dengan SQL scripts:

1. ✅ Test login untuk setiap role
2. ✅ Verifikasi dashboard tampil dengan benar
3. ✅ Test workflow: warga buat surat → RT approve → RW approve
4. ✅ Check history tracking di surat_history table
5. ✅ Customize username/password RT dan RW sesuai kebutuhan

Database sudah siap dengan upgrade_db.sql yang berisi:
- Perubahan enum level ke include 'rt' dan 'rw'
- Kolom status_rt, status_rw untuk tracking
- Tabel surat_history untuk audit trail
- Test accounts untuk RT dan RW
