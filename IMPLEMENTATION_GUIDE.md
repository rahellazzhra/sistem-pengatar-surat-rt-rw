# 📖 PANDUAN LENGKAP IMPLEMENTASI SISTEM SURAT RT/RW

**Status:** 🟡 VERSION 2.0 - MAJOR UPGRADES  
**Last Updated:** December 7, 2025

---

## ✨ FITUR-FITUR SISTEM

### Core Features (✅ Complete)
- ✅ Multi-role authentication (Warga, RT, RW, Admin)
- ✅ 4 separate login pages dengan styling unik per role
- ✅ 4 role-specific dashboards
- ✅ Letter submission & tracking
- ✅ Two-level approval (RT → RW)
- ✅ Auto-generate nomor surat (Format: KODE/URUT/BULAN/TAHUN)
- ✅ Official government letter header (Tangerang)
- ✅ Print/cetak surat pengantar

### Security Features (🟡 Improved v2.0)
- ✅ Session-based authentication
- ✅ PDO prepared statements (SQL injection protected)
- ✅ HTML escaping (XSS protected)
- ✅ **NEW** CSRF token protection
- ✅ **NEW** Audit logging system
- ✅ Role-based access control

### Admin Features (🟢 New v2.0)
- ✅ **NEW** User management (admin_users.php)
- ✅ **NEW** Approval workflow page (approval_surat.php)
- ✅ **NEW** Audit log tracking
- ✅ **NEW** Notification framework
- ✅ Statistics dashboard

### Database Features (🟡 Improved v2.0)
- ✅ Fixed status enum values
- ✅ **NEW** Approval tracking columns
- ✅ **NEW** Audit log table
- ✅ **NEW** Notification table
- ✅ **NEW** Settings table
- ✅ **NEW** Database indices untuk optimization

---

## 🚀 QUICK START SETUP

### 1. Database Setup

**Create Database:**
```sql
CREATE DATABASE surat_rt_rw;
```

**Import Base Schema:**
```
1. Di phpMyAdmin → New → Import
2. Select file: database/surat_rt_rw.sql
3. Click Import
```

**Upgrade Schema (IMPORTANT):**
```
1. Di phpMyAdmin → SQL tab
2. Copy paste dari: database/fix_schema.sql
3. Execute
```

**OR Via Terminal:**
```bash
mysql -u root -p surat_rt_rw < c:\xampp\htdocs\cbaaa\database\surat_rt_rw.sql
mysql -u root -p surat_rt_rw < c:\xampp\htdocs\cbaaa\database\fix_schema.sql
```

### 2. Verify Database

**Check Tables:**
```sql
SHOW TABLES;
-- Should show: audit_log, jenis_surat, notifikasi, settings, surat, surat_history, template_surat, users
```

**Check Users:**
```sql
SELECT * FROM users;
-- Should show: admin, rt003, rw013 users
```

**Check Settings:**
```sql
SELECT * FROM settings;
-- Should show institution data
```

### 3. Access Application

**Open Browser:**
- URL: `http://localhost/cbaaa/`
- Browser: Chrome/Firefox/Edge

**Test Logins:**

| Role | Username | Password | URL |
|------|----------|----------|-----|
| Admin | admin | admin123456 | /dashboard_admin.php |
| RT | rt003 | rt123456 | /dashboard_rt.php |
| RW | rw013 | rw123456 | /dashboard_rw.php |
| Warga | (register) | (own) | /dashboard_warga.php |

---

## 📖 USER WORKFLOWS

### For Warga (Pemohon)

**1. Register Account**
- Go to: `/register.php`
- Fill form (NIK, Nama, Alamat, etc)
- Click Daftar

**2. Login**
- Go to: `/login_warga.php`
- Username & Password
- Dashboard opens

**3. Submit Letter Request**
- Click: "Pengajuan Surat"
- Select: Jenis Surat
- Fill: Keperluan
- Click: Ajukan Surat
- Status: Pending

**4. Track Status**
- Go to: "Surat Saya"
- View all your letters
- Check status progress

**5. Get Letter**
- Wait for approval (RT → RW)
- Status becomes: "Selesai"
- Click: "Cetak Surat Pengantar"
- Save/Print letter

---

### For RT (Ketua RT)

**1. Login**
- Go to: `/login_rt.php`
- Username: rt003
- Password: rt123456

**2. View Pending Letters**
- Dashboard shows pending letters from your RT
- Click letter untuk details
- Click "Lihat Detail" button

**3. Approve/Reject Letter**
- Go to: `/approval_surat.php?id={letter_id}`
- Read pemohon details & keperluan
- Choose action:
  - **✓ Setujui** → Modal approval dialog → Optional notes → Submit
  - **✕ Tolak** → Modal rejection dialog → REQUIRED reason → Submit
- Letter status changes to: approved_rt (atau rejected_rt)

**4. View History**
- Go to: "Daftar Surat"
- See all letters dan status

---

### For RW (Ketua RW)

**1. Login**
- Go to: `/login_rw.php`
- Username: rw013
- Password: rw123456

**2. View RT-Approved Letters**
- Dashboard shows approved_rt letters
- Click letter untuk details

**3. Final Approval/Rejection**
- Go to: `/approval_surat.php?id={letter_id}`
- Review dan make final decision:
  - **✓ Setujui** → Letter status: selesai → Pemohon can print
  - **✕ Tolak** → Letter status: rejected_rw → Back to pemohon

**4. Monitor Workflow**
- Dashboard statistics
- Recent letters list

---

### For Admin

**1. Login**
- Go to: `/login_admin.php`
- Username: admin
- Password: admin123456

**2. Manage Users**
- Go to: `/admin_users.php`
- See all users dengan roles
- Actions available:
  - **Edit** → Modify user details
  - **Nonaktifkan/Aktifkan** → Toggle status
  - **Hapus** → Delete user (with confirmation)
  - **+ Tambah User Baru** → Create new user

**3. View Dashboard**
- Go to: `/dashboard_admin.php`
- See overall statistics
- All letters in system
- Actions untuk approve/reject

**4. Check Audit Trail**
- Go to: `/audit_log` (SQL or admin page)
- See who did what when
- Track all approvals & rejections

---

## 🛠️ TECHNICAL DETAILS

### Directory Structure

```
cbaaa/
├── config/
│   ├── config.php          (Main configuration + helper functions)
│   ├── database.php        (PDO database connection)
│   └── institusi.php       (Institution constants)
├── classes/
│   ├── User.php            (User CRUD operations)
│   └── Letter.php          (Letter CRUD + auto-generate nomor)
├── database/
│   ├── surat_rt_rw.sql     (Base schema)
│   ├── upgrade_db.sql      (Old upgrade script)
│   └── fix_schema.sql      (New v2.0 schema improvements)
├── assets/
│   └── css/
│       └── style.css       (Global styling)
├── Approval & Admin Pages:
│   ├── approval_surat.php  (NEW - RT/RW approval workflow)
│   ├── admin_users.php     (NEW - User management)
│   ├── dashboard_admin.php (Admin dashboard)
│   ├── dashboard_rt.php    (RT dashboard)
│   ├── dashboard_rw.php    (RW dashboard)
│   └── dashboard_warga.php (Warga dashboard)
├── User Pages:
│   ├── login.php           (Generic login redirect)
│   ├── login_admin.php     (Admin login)
│   ├── login_rt.php        (RT login)
│   ├── login_rw.php        (RW login)
│   ├── login_warga.php     (Warga login)
│   ├── register.php        (Registration)
│   └── logout.php          (Logout)
├── Letter Pages:
│   ├── pengajuan.php       (Submit letter)
│   ├── surat_saya.php      (My letters)
│   ├── detail_surat.php    (Letter details)
│   └── cetak_surat.php     (Print letter)
└── Documentation:
    ├── README.md
    ├── QUICK_START.md
    ├── IMPROVEMENTS.md
    └── AUDIT_SYSTEM.md
```

---

### Database Schema

**Users Table:**
```
id, nik, username, nama, tempat_lahir, tanggal_lahir, jenis_kelamin, 
alamat, rt, rw, agama, pekerjaan, password, level, no_telp, status, 
created_at, updated_at
```

**Surat Table:**
```
id, no_surat, user_id, jenis_surat_id, keperluan, tanggal_pengajuan, 
status, tanggal_selesai, approval_date_rt, approval_date_rw, 
rejection_reason_rt, rejection_reason_rw, approved_by_rt, approved_by_rw, 
created_at, updated_at
```

**Audit_log Table:**
```
id, surat_id, action, action_by, role, details, created_at
```

**Status Values:**
```
pending → approved_rt → approved_rw → selesai
                ↓            ↓
         rejected_rt  rejected_rw
```

---

## 🔐 SECURITY FEATURES

### CSRF Protection
```php
// In forms:
<?php echo csrfInput(); ?>

// In handlers:
if (!verifyCSRFToken($_POST['csrf_token'])) {
    die("CSRF token tidak valid");
}
```

### Role Checking
```php
isAdmin()   // Check if admin
isRT()      // Check if RT
isRW()      // Check if RW
isWarga()   // Check if warga
isLoggedIn() // Check if logged in
```

### Input Validation
```php
// HTML escaping
echo e($user_input);

// Prepared statements (SQL safe)
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
```

### Audit Logging
```php
logAudit($db, $surat_id, $action, $details);

// Example:
logAudit($db, 5, 'RT APPROVED', 'Disetujui oleh RT 003');
```

---

## 📊 HELPER FUNCTIONS

**Location:** `config/config.php`

```php
// Role checking
isAdmin()
isRT()
isRW()
isWarga()
isLoggedIn()

// CSRF protection
generateCSRFToken()
verifyCSRFToken($token)
csrfInput()

// Audit & Notification
logAudit($db, $surat_id, $action, $details)
createNotification($db, $user_id, $surat_id, $title, $message, $type)
getUnreadNotifications($db, $user_id, $limit)

// Formatting
formatTanggalIndonesia($date)
getStatusBadge($status)
e($string)  // HTML escape
```

---

## 🧪 TESTING

### Test Letter Workflow

1. **Create Letter as Warga**
   - Register new warga account
   - Go to "Pengajuan Surat"
   - Select: "Surat Keterangan Domisili"
   - Fill keperluan: "Test workflow"
   - Submit → Status: pending

2. **Approve as RT**
   - Login as rt003
   - See pending letter
   - Click approval button
   - Approve with note
   - Status: approved_rt

3. **Approve as RW**
   - Login as rw013
   - See approved_rt letter
   - Click approval button
   - Final approve
   - Status: selesai

4. **Print as Warga**
   - Login back as warga
   - Go to "Surat Saya"
   - Find completed letter
   - Click "Cetak Surat Pengantar"
   - Print dialog opens

5. **Verify Audit Log**
   - Login as admin
   - Check audit_log table
   - Should show 3 entries:
     - Warga: CREATED
     - RT: APPROVED
     - RW: APPROVED

---

## ❌ TROUBLESHOOTING

### Problem: "CSRF token tidak valid"
**Solution:**
1. Clear browser cache
2. Start new session (logout/login)
3. Verify form has: `<?php echo csrfInput(); ?>`

### Problem: "Undefined function"
**Solution:**
1. Check `require_once 'config/config.php'` at top of file
2. Verify config.php was updated with v2.0 functions
3. Restart PHP server

### Problem: "Column not found" error
**Solution:**
1. Run fix_schema.sql script
2. Verify columns exist:
   ```sql
   DESC surat;
   DESC users;
   DESC audit_log;
   ```

### Problem: "No letters showing"
**Solution:**
1. Check status filter (should include all statuses)
2. Verify user RT/RW match letter RT/RW
3. Check surat table has data

---

## 🎯 NEXT STEPS (Roadmap)

### Phase 2 (Short-term)
- [ ] Create/Edit user pages (create_user.php, edit_user.php)
- [ ] Admin settings page (admin_settings.php)
- [ ] Email notifications
- [ ] Letter templates management
- [ ] Pagination untuk list views
- [ ] Search & filter functionality

### Phase 3 (Medium-term)
- [ ] Document upload support
- [ ] Recurring letter requests
- [ ] Monthly reports/recap
- [ ] Export to PDF/Excel
- [ ] Mobile app API

### Phase 4 (Long-term)
- [ ] Digital signature
- [ ] Advanced reporting
- [ ] SMS notifications
- [ ] Multi-location support
- [ ] Custom letter types

---

## 📞 SUPPORT

### Default Credentials
```
Username: admin | Password: admin123456
Username: rt003 | Password: rt123456
Username: rw013 | Password: rw123456
```

### Common Errors
- 404 - Page not found (check URL & file names)
- 500 - Server error (check config.php, database connection)
- CSRF - Invalid token (clear cache, new session)

### Database Backup
```bash
mysqldump -u root -p surat_rt_rw > backup.sql
```

### Database Restore
```bash
mysql -u root -p surat_rt_rw < backup.sql
```

---

## ✅ CHECKLIST

Before going to production:

- [ ] Database schema updated (fix_schema.sql)
- [ ] Default credentials changed
- [ ] Email notifications configured
- [ ] Backup system in place
- [ ] User documentation prepared
- [ ] Test workflow completed
- [ ] Performance tested
- [ ] Security audit done

---

**Version:** 2.0  
**Last Updated:** December 7, 2025  
**Status:** 🟡 Ready for Testing

---

For questions or issues, please refer to:
- IMPROVEMENTS.md - Detailed improvements
- AUDIT_SYSTEM.md - System audit report
- QUICK_START.md - Quick reference
