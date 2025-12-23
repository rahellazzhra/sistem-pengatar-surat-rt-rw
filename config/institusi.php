<?php
/**
 * Institutional Configuration
 * Informasi resmi institusi pemerintah untuk keperluan surat
 */

// Informasi Pemerintah/Institusi
define('INSTITUSI_NAMA', 'PEMERINTAH KOTA TANGERANG');
define('INSTITUSI_UNIT1', 'KECAMATAN PINANG');
define('INSTITUSI_UNIT2', 'KELURAHAN KUNCIRAN INDAH');
define('INSTITUSI_RT_RW', 'RT 003 / RW 013');

// Alamat Lengkap
define('INSTITUSI_ALAMAT', 'Jl. Sultan Ageng Tirtayas RT.003/RW.013, Kunciran Indah, Kec. Pinang, Kota Tangerang, Banten 15144');

// Informasi Kontak (opsional)
define('INSTITUSI_TELEPON', '+62-21-XXXXXXX');
define('INSTITUSI_EMAIL', 'kelurahan.kunciranindah@tangerangkota.go.id');

// Informasi Pejabat (bisa diisi atau dikosongkan)
define('PEJABAT_LURAH_NAMA', ''); // Nama Lurah (bisa diisi di database)
define('PEJABAT_LURAH_NIP', ''); // NIP Lurah

// Format Penomoran Surat
define('FORMAT_NOMOR_SURAT', 'KODE/URUT/BULAN/TAHUN'); // KODE/001/12/2024

// Helper Functions
function getInstitusiHeader() {
    return [
        'nama' => INSTITUSI_NAMA,
        'unit1' => INSTITUSI_UNIT1,
        'unit2' => INSTITUSI_UNIT2,
        'rt_rw' => INSTITUSI_RT_RW,
        'alamat' => INSTITUSI_ALAMAT,
        'telepon' => INSTITUSI_TELEPON,
        'email' => INSTITUSI_EMAIL
    ];
}

function displayInstitusiHeader() {
    $header = getInstitusiHeader();
    echo '<div class="institusi-header">';
    echo '<div class="institusi-nama">' . $header['nama'] . '</div>';
    echo '<div class="institusi-unit1">' . $header['unit1'] . '</div>';
    echo '<div class="institusi-unit2">' . $header['unit2'] . '</div>';
    echo '<div class="institusi-rt-rw">' . $header['rt_rw'] . '</div>';
    echo '<div class="institusi-alamat">' . $header['alamat'] . '</div>';
    echo '</div>';
}

function displayInstitusiHeaderForPrint() {
    $header = getInstitusiHeader();
    $html = '<div class="print-header">';
    $html .= '<div style="text-align: center; margin-bottom: 2rem;">';
    $html .= '<div style="font-size: 11pt; font-weight: bold;">' . $header['nama'] . '</div>';
    $html .= '<div style="font-size: 10pt; font-weight: bold;">' . $header['unit1'] . '</div>';
    $html .= '<div style="font-size: 10pt; font-weight: bold;">' . $header['unit2'] . '</div>';
    $html .= '<div style="font-size: 10pt; font-weight: bold;">' . $header['rt_rw'] . '</div>';
    $html .= '<div style="font-size: 9pt;">' . $header['alamat'] . '</div>';
    $html .= '<hr style="margin: 0.5rem 0; border: none; border-top: 2px solid #000;">';
    $html .= '</div>';
    return $html;
}

?>
