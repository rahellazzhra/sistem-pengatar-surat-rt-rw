# Fitur Auto-Generate Nomor Surat

## 📋 Deskripsi

Sistem sekarang secara otomatis menghasilkan nomor surat dengan format yang terstruktur dan unik.

## 🔢 Format Nomor Surat

```
[KODE_JENIS]/[NOMOR_URUT]/[BULAN]/[TAHUN]
```

### Penjelasan:
- **KODE_JENIS**: 3 huruf kode dari jenis surat (diambil dari 3 huruf pertama nama jenis surat)
  - Surat Keterangan Domisili → DOM
  - Surat Keterangan Usaha → SKU
  - Surat Pengantar → SPN
  - dll

- **NOMOR_URUT**: Nomor urut bulanan (3 digit, dimulai dari 001)
  - Surat pertama di bulan Januari → 001
  - Surat ke-15 di bulan Januari → 015
  - Reset ke 001 setiap bulan baru

- **BULAN**: Bulan saat surat dibuat (2 digit)
  - 01 = Januari, 02 = Februari, dst

- **TAHUN**: Tahun saat surat dibuat (4 digit)
  - 2024, 2025, dst

### Contoh Nomor Surat:
```
DOM/001/12/2024  ← Surat Domisili ke-1 bulan Desember 2024
SKU/002/12/2024  ← Surat Usaha ke-2 bulan Desember 2024
SPN/003/12/2024  ← Surat Pengantar ke-3 bulan Desember 2024
DOM/001/01/2025  ← Surat Domisili ke-1 bulan Januari 2025 (reset nomor)
```

## 🔧 Implementasi

### Function di classes/Letter.php

```php
public function generateNoSurat() {
    // Get kode jenis surat dari nama
    // Get nomor urut berdasarkan bulan/tahun
    // Return format: KODE/URUT/BULAN/TAHUN
}
```

### Proses di pengajuan.php

1. User submit form pengajuan surat
2. Sistem otomatis call `generateNoSurat()`
3. Nomor surat di-insert ke database
4. Surat dibuat dengan nomor yang unik

### Display di surat_saya.php

Nomor surat ditampilkan di tabel list surat warga dengan format yang sudah di-generate.

## 📊 Contoh Data di Database

```sql
SELECT id, no_surat, nama_surat, created_at FROM surat;

id | no_surat      | nama_surat          | created_at
---|---------------|---------------------|-------------------
1  | DOM/001/12/24 | Surat Keterangan    | 2024-12-01 10:30:00
2  | SKU/002/12/24 | Surat Usaha         | 2024-12-02 14:15:00
3  | DOM/003/12/24 | Surat Keterangan    | 2024-12-05 09:45:00
```

## 🎯 Keuntungan

1. **Unik & Terstruktur**: Setiap surat punya nomor unik yang terstruktur
2. **Otomatis**: Tidak perlu input manual nomor surat
3. **Reset Bulanan**: Nomor urut reset setiap bulan untuk kemudahan tracking
4. **Mudah Difilter**: Bisa filter surat berdasarkan jenis dari kode nomor
5. **Standar**: Mengikuti standar penomoran surat di pemerintahan

## 🔍 Cara Melihat Nomor Surat

### Di Dashboard Warga
1. Login sebagai warga
2. Klik "Surat Saya"
3. Lihat tabel, nomor surat ada di kolom "No. Surat"

### Di Detail Surat
1. Klik tombol "Detail" pada surat yang ingin dilihat
2. Nomor surat ditampilkan di bagian atas

### Di Print/Cetak
1. Nomor surat akan muncul di surat yang dicetak

## 🛠️ Customization

### Mengubah Format Kode Jenis

Edit `classes/Letter.php` function `generateNoSurat()`:

```php
// Saat ini: mengambil 3 huruf pertama dari setiap kata di nama jenis
// Contoh: "Surat Keterangan Domisili" → S.K.D → SKD

// Alternatif: Bisa didefinisikan manual per jenis di database
// Tambah column "kode" di tabel jenis_surat
$query_jenis = "SELECT kode FROM jenis_surat WHERE id = ?";
```

### Mengubah Format Penomoran

Sekarang: `KODE/URUT/BULAN/TAHUN`

Alternatif: `KODE/TAHUN/BULAN/URUT`
```php
$no_surat = $code . '/' . $tahun . '/' . $bulan . '/' . $nomor_urut;
```

### Reset Penomoran per Tahun (Bukan Bulanan)

Ubah query di `generateNoSurat()`:
```php
// From:
WHERE MONTH(created_at) = ? AND YEAR(created_at) = ?

// To:
WHERE YEAR(created_at) = ?
```

## 📝 Catatan

1. Nomor surat di-generate saat form submission
2. Jika ada error saat generate, surat tidak akan dibuat
3. Nomor surat tidak bisa di-edit setelah dibuat
4. Jika perlu ubah nomor, lakukan delete dan buat ulang surat

## 🐛 Troubleshooting

### Problem: Nomor Surat Tidak Jelas/Tidak Muncul
**Solution**: Check kolom `no_surat` di database
```sql
SELECT id, no_surat FROM surat WHERE no_surat IS NULL;
```

### Problem: Nomor Urut Tidak Reset Setiap Bulan
**Solution**: Check query di `generateNoSurat()` - pastikan MONTH() dan YEAR() condition

### Problem: Kode Jenis Salah
**Solution**: Check nama jenis surat di database, pastikan minimal 3 huruf

---

**Feature Status**: ✅ **IMPLEMENTED & ACTIVE**

Nomor surat sekarang di-generate otomatis untuk setiap surat baru yang dibuat!
