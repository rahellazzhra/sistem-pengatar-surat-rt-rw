# ✨ AUTO-GENERATE NOMOR SURAT - FITUR BARU

## 🎯 Ringkasan Singkat

Fitur **Auto-Generate Nomor Surat** telah berhasil diimplementasikan. Sistem sekarang **otomatis menghasilkan nomor surat unik** untuk setiap pengajuan baru tanpa perlu input manual.

---

## 📝 Format Nomor Surat

```
[KODE_JENIS]/[NOMOR_URUT]/[BULAN]/[TAHUN]
```

### Contoh:
| Jenis Surat | Contoh Nomor |
|------------|--------------|
| Surat Keterangan Domisili | **DOM/001/12/2024** |
| Surat Keterangan Usaha | **SKU/002/12/2024** |
| Surat Pengantar | **SPN/003/12/2024** |
| Surat Keterangan Kelahiran | **SKK/001/01/2025** |

---

## 🔧 Cara Kerja

```
Warga buat surat
        ↓
generateNoSurat() dipanggil otomatis
        ↓
Sistem generate: KODE/URUT/BULAN/TAHUN
        ↓
Nomor surat disimpan di database
        ↓
Ditampilkan di "Surat Saya"
```

---

## 📂 File yang Diubah/Ditambah

### Modified Files
- ✅ `classes/Letter.php` - Ditambah function `generateNoSurat()`
- ✅ `classes/Letter.php` - Update `create()` untuk auto-generate nomor

### New Files
- ✅ `AUTO_GENERATE_NOMOR_SURAT.md` - Dokumentasi lengkap
- ✅ `test_nomor_surat.php` - Testing & preview tool

---

## 🚀 Cara Testing

### Test 1: Preview Nomor Surat
```
Buka: http://localhost/cbaaa/test_nomor_surat.php
```
Halaman ini menunjukkan preview nomor untuk setiap jenis surat.

### Test 2: Create Surat dan Lihat Nomor
1. Login sebagai warga → `login.php`
2. Klik "Pengajuan Surat"
3. Isi form dan submit
4. Klik "Surat Saya" → lihat nomor surat di kolom "No. Surat"

### Test 3: Nomor Urut Increment
1. Buat surat jenis sama beberapa kali
2. Lihat nomor urut bertambah: 001 → 002 → 003, dst

### Test 4: Reset Nomor Setiap Bulan
1. Tunggu bulan berubah (atau ubah tanggal sistem)
2. Buat surat baru
3. Nomor urut akan reset ke 001

---

## 💻 Code Implementation

### Function di classes/Letter.php

```php
public function generateNoSurat() {
    // 1. Get kode dari nama jenis surat (3 huruf)
    //    "Surat Keterangan Domisili" → DOM
    
    // 2. Get nomor urut untuk bulan/tahun ini
    //    Cek berapa banyak surat bulan ini
    //    Nomor urut = count + 1 (format 3 digit)
    
    // 3. Return: KODE/URUT/BULAN/TAHUN
    //    Contoh: DOM/001/12/2024
}
```

### Saat Create Surat

```php
public function create() {
    // 1. Auto-call generateNoSurat()
    $this->no_surat = $this->generateNoSurat();
    
    // 2. Insert ke database dengan nomor otomatis
    // 3. Return success/fail
}
```

---

## ✨ Keuntungan

| Keuntungan | Penjelasan |
|-----------|-----------|
| **Otomatis** | Tidak perlu input manual nomor surat |
| **Unik** | Setiap surat memiliki nomor yang unik |
| **Terstruktur** | Format terstandar untuk semua surat |
| **Mudah Tracking** | Bisa filter berdasarkan jenis dari kode |
| **Reset Bulanan** | Nomor urut reset setiap bulan |

---

## 🔍 Dimana Nomor Surat Ditampilkan

1. **Surat Saya** (`surat_saya.php`)
   - Tabel list surat
   - Kolom "No. Surat"

2. **Detail Surat** (`detail_surat.php`)
   - Di bagian atas halaman detail

3. **Print Surat** (`cetak_surat.php`)
   - Di header/atas surat yang dicetak

4. **Dashboard** (untuk admin/RT/RW)
   - Di list surat yang perlu approval

---

## 🛠️ Customization (Optional)

### Jika ingin mengubah format:

**Current**: `KODE/URUT/BULAN/TAHUN`
```
DOM/001/12/2024
```

**Alternative 1**: `KODE/TAHUN/BULAN/URUT`
```
DOM/2024/12/001
```

**Alternative 2**: `KODE-URUT-BULAN-TAHUN`
```
DOM-001-12-2024
```

Edit di `classes/Letter.php` function `generateNoSurat()`:
```php
// Ubah baris terakhir function
return $code . '/' . $nomor_urut . '/' . $bulan . '/' . $tahun;
// Ganti dengan format pilihan
```

---

## 📊 Contoh Data di Database

```sql
SELECT no_surat, nama_surat, created_at FROM surat ORDER BY created_at DESC;

no_surat      | nama_surat                    | created_at
--------------|-------------------------------|-------------------
DOM/003/12/24 | Surat Keterangan Domisili     | 2024-12-05 14:30:00
SKU/002/12/24 | Surat Keterangan Usaha        | 2024-12-04 10:15:00
DOM/001/12/24 | Surat Keterangan Domisili     | 2024-12-01 09:00:00
SPN/001/11/24 | Surat Pengantar                | 2024-11-30 15:45:00
```

---

## ⚠️ Catatan Penting

1. **Nomor tidak bisa diedit** setelah dibuat (by design)
2. **Auto-generate terjadi** saat form submit, bukan preview
3. **Jika error saat generate**, surat tidak akan dibuat
4. **Setiap bulan** nomor urut reset ke 001
5. **Untuk setiap jenis surat** nomor urut terpisah per bulan

---

## 🧪 Verification

Untuk verify fitur bekerja dengan baik:

✅ Nomor surat ter-generate otomatis
✅ Format sesuai: KODE/URUT/BULAN/TAHUN
✅ Nomor unik setiap surat
✅ Nomor urut increment
✅ Reset setiap bulan
✅ Ditampilkan di "Surat Saya"
✅ Ditampilkan di "Detail Surat"

---

## 📞 Support

Jika ada masalah:
1. Buka `test_nomor_surat.php` untuk melihat preview
2. Check database kolom `no_surat` apakah ada nilai
3. Baca `AUTO_GENERATE_NOMOR_SURAT.md` untuk detail lengkap

---

**Status**: ✅ **IMPLEMENTED & READY**

Fitur auto-generate nomor surat sudah siap digunakan!
