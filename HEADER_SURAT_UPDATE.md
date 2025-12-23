# 📄 Update Header Surat Pengantar - Pemerintah Kota Tangerang

## ✅ Perubahan yang Dilakukan

Header surat pengantar (cetak_surat.php) telah diupdate dengan informasi resmi dari Pemerintah Kota Tangerang.

---

## 📋 Struktur Header Baru

```
PEMERINTAH KOTA TANGERANG
KECAMATAN PINANG
KELURAHAN KUNCIRAN INDAH
RT 003 / RW 013
Jl. Sultan Ageng Tirtayas RT.003/RW.013, Kunciran Indah, Kec. Pinang, Kota Tangerang, Banten 15144
```

---

## 📂 File yang Diubah

### cetak_surat.php
- ✅ Update kop-surat HTML dengan header resmi
- ✅ Update styling CSS untuk tampilan lebih profesional
- ✅ Border bawah: 3px solid (lebih tegas)
- ✅ Text-align: center (format standar surat resmi)
- ✅ Font size sesuai standar: Title 14pt, Subtitle 12pt

### config/institusi.php
- ✅ Sudah berisi data lengkap
- ✅ Dapat dikustomisasi sesuai kebutuhan

---

## 📊 Detail Header

| Komponen | Isi | Font Size |
|----------|-----|-----------|
| Nama Pemerintah | PEMERINTAH KOTA TANGERANG | 14pt Bold |
| Kecamatan | KECAMATAN PINANG | 12pt Bold |
| Kelurahan | KELURAHAN KUNCIRAN INDAH | 12pt Bold |
| RT/RW | RT 003 / RW 013 | 11pt Bold |
| Alamat Lengkap | Jl. Sultan Ageng Tirtayas... | 10pt |

---

## 🎯 Hasil

### Saat Dicetak (Print)
Header akan tampil dengan format profesional sesuai standar surat resmi pemerintahan Indonesia.

### Di Browser (Preview)
Header sudah terlihat jelas dengan pemisah garis bawah yang tegas.

---

## 🔧 Jika Perlu Mengubah Data

### Edit di config/institusi.php:

```php
define('INSTITUSI_NAMA', 'PEMERINTAH KOTA TANGERANG');
define('INSTITUSI_UNIT1', 'KECAMATAN PINANG');
define('INSTITUSI_UNIT2', 'KELURAHAN KUNCIRAN INDAH');
define('INSTITUSI_RT_RW', 'RT 003 / RW 013');
define('INSTITUSI_ALAMAT', 'Jl. Sultan Ageng Tirtayas RT.003/RW.013, ...');
```

---

## 📸 Tampilan Header

```
═══════════════════════════════════════════════════════════
            PEMERINTAH KOTA TANGERANG
              KECAMATAN PINANG
            KELURAHAN KUNCIRAN INDAH
                RT 003 / RW 013
Jl. Sultan Ageng Tirtayas RT.003/RW.013, Kunciran Indah, 
     Kec. Pinang, Kota Tangerang, Banten 15144
═══════════════════════════════════════════════════════════
```

---

## ✨ Fitur Header

✅ Centered alignment (standar surat resmi)
✅ Bold typography untuk emphasis
✅ Proper spacing antar elemen
✅ Garis pemisah tebal (3px)
✅ Print-friendly formatting
✅ Responsive font sizes

---

## 🧪 Testing

1. Login sebagai warga
2. Buat surat pengajuan
3. Tunggu approval RT & RW
4. Buka "Surat Saya"
5. Klik "Cetak Surat Pengantar"
6. Lihat header dengan informasi Tangerang
7. Klik "Cetak Surat" untuk print

---

## 📝 Catatan

- Header sekarang resmi dari Pemerintah Kota Tangerang
- Data dapat diubah di `config/institusi.php`
- Semua surat yang dicetak akan menggunakan header ini
- Format sesuai standar penomoran surat pemerintahan

---

**Status**: ✅ **UPDATED & READY**

Header surat pengantar sudah diupdate dengan informasi Pemerintah Kota Tangerang yang lengkap dan profesional!
