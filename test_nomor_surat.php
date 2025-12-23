<?php
/**
 * Test Auto-Generate Nomor Surat
 * Script untuk testing dan preview nomor surat otomatis
 */

require_once 'config/config.php';

$database = new Database();
$db = $database->getConnection();

// Get semua jenis surat
$query = "SELECT * FROM jenis_surat ORDER BY id";
$stmt = $db->prepare($query);
$stmt->execute();
$jenis_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Generate preview nomor untuk setiap jenis surat
$preview_data = [];

foreach ($jenis_list as $jenis) {
    // Simulate nomor generation
    $nama_surat = $jenis['nama_surat'];
    $words = explode(' ', $nama_surat);
    $code = '';
    foreach ($words as $word) {
        $code .= strtoupper($word[0]);
    }
    if (strlen($code) < 3) {
        $code = strtoupper(substr($nama_surat, 0, 3));
    } else {
        $code = substr($code, 0, 3);
    }
    
    // Current month/year
    $bulan = date('m');
    $tahun = date('Y');
    
    // Count surat untuk jenis ini di bulan ini
    $count_query = "SELECT COUNT(*) as count FROM surat 
                   WHERE jenis_surat_id = ? AND MONTH(created_at) = ? AND YEAR(created_at) = ?";
    $count_stmt = $db->prepare($count_query);
    $count_stmt->execute([$jenis['id'], $bulan, $tahun]);
    $count_result = $count_stmt->fetch(PDO::FETCH_ASSOC);
    
    $next_nomor = str_pad($count_result['count'] + 1, 3, '0', STR_PAD_LEFT);
    $preview_no_surat = $code . '/' . $next_nomor . '/' . $bulan . '/' . $tahun;
    
    $preview_data[] = [
        'id' => $jenis['id'],
        'nama' => $nama_surat,
        'kode' => $code,
        'next_nomor' => $next_nomor,
        'current_count' => $count_result['count'],
        'preview_no_surat' => $preview_no_surat
    ];
}

// Get existing surat untuk bulan ini
$existing_query = "SELECT s.id, s.no_surat, j.nama_surat, s.created_at, u.nama as nama_warga
                   FROM surat s
                   JOIN jenis_surat j ON s.jenis_surat_id = j.id
                   JOIN users u ON s.user_id = u.id
                   WHERE MONTH(s.created_at) = MONTH(NOW()) AND YEAR(s.created_at) = YEAR(NOW())
                   ORDER BY s.created_at DESC";
$existing_stmt = $db->prepare($existing_query);
$existing_stmt->execute();
$existing_surat = $existing_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Auto-Generate Nomor Surat</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; padding: 2rem; }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 { color: #333; margin-bottom: 2rem; text-align: center; }
        .section { background: white; padding: 2rem; border-radius: 8px; margin-bottom: 2rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .section h2 { color: var(--secondary); margin-bottom: 1rem; border-bottom: 2px solid var(--secondary); padding-bottom: 0.5rem; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th { background: #f5f5f5; padding: 1rem; text-align: left; font-weight: 600; border-bottom: 2px solid #ddd; }
        td { padding: 1rem; border-bottom: 1px solid #ddd; }
        tr:hover { background: #fafafa; }
        .code-box { background: #f5f5f5; padding: 0.75rem; border-radius: 4px; font-family: 'Courier New', monospace; font-weight: bold; }
        .preview { background: #e8f4f8; padding: 1rem; border-left: 4px solid #3498db; border-radius: 4px; margin-top: 1rem; }
        .status-ok { color: #27ae60; font-weight: bold; }
        .info-box { background: #fef3cd; padding: 1rem; border-left: 4px solid var(--accent); border-radius: 4px; margin-bottom: 1rem; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
        .stat-card { background: linear-gradient(135deg, var(--secondary) 0%, var(--secondary-dark) 100%); color: white; padding: 1.5rem; border-radius: 8px; }
        .stat-value { font-size: 2rem; font-weight: bold; }
        .stat-label { font-size: 0.9rem; opacity: 0.9; margin-top: 0.5rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔢 Test Auto-Generate Nomor Surat</h1>
        
        <div class="info-box">
            <strong>ℹ️ Informasi:</strong><br>
            Halaman ini menunjukkan preview nomor surat yang akan di-generate otomatis ketika warga membuat pengajuan surat baru.
        </div>

        <div class="stats">
            <div class="stat-card">
                <div class="stat-value"><?php echo count($preview_data); ?></div>
                <div class="stat-label">Jenis Surat</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo count($existing_surat); ?></div>
                <div class="stat-label">Surat Bulan Ini</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo date('m/Y'); ?></div>
                <div class="stat-label">Bulan/Tahun</div>
            </div>
        </div>

        <!-- Preview Nomor Surat -->
        <div class="section">
            <h2>📋 Preview Nomor Surat Otomatis</h2>
            
            <p style="margin-bottom: 1rem; color: #666;">
                Format: <span class="code-box">KODE/URUT/BULAN/TAHUN</span>
            </p>
            
            <table>
                <thead>
                    <tr>
                        <th>Jenis Surat</th>
                        <th>Kode</th>
                        <th>Count Bulan Ini</th>
                        <th>Nomor Urut Berikutnya</th>
                        <th>Preview Nomor Surat</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($preview_data as $preview): ?>
                    <tr>
                        <td><?php echo e($preview['nama']); ?></td>
                        <td><span class="code-box"><?php echo $preview['kode']; ?></span></td>
                        <td style="text-align: center;">
                            <?php echo $preview['current_count']; ?>
                        </td>
                        <td style="text-align: center;">
                            <span class="code-box"><?php echo $preview['next_nomor']; ?></span>
                        </td>
                        <td>
                            <span class="preview">
                                <span class="status-ok"><?php echo $preview['preview_no_surat']; ?></span>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Existing Surat Bulan Ini -->
        <div class="section">
            <h2>✅ Surat yang Sudah Dibuat Bulan Ini</h2>
            
            <?php if (count($existing_surat) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>No. Surat</th>
                            <th>Jenis Surat</th>
                            <th>Nama Warga</th>
                            <th>Tanggal Dibuat</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($existing_surat as $surat): ?>
                        <tr>
                            <td><span class="code-box"><?php echo e($surat['no_surat']); ?></span></td>
                            <td><?php echo e($surat['nama_surat']); ?></td>
                            <td><?php echo e($surat['nama_warga']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($surat['created_at'])); ?></td>
                            <td><?php echo date('H:i', strtotime($surat['created_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="preview">
                    <p style="color: #666;">📭 Belum ada surat yang dibuat bulan ini.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Penjelasan Format -->
        <div class="section">
            <h2>📖 Penjelasan Format Nomor Surat</h2>
            
            <div style="margin-bottom: 1.5rem;">
                <h3 style="color: #333; margin-bottom: 0.5rem;">Format: KODE/URUT/BULAN/TAHUN</h3>
                
                <table style="margin-top: 1rem;">
                    <tr style="background: #f5f5f5;">
                        <td style="width: 30%; font-weight: bold;">Komponen</td>
                        <td style="width: 25%; font-weight: bold;">Penjelasan</td>
                        <td style="width: 20%; font-weight: bold;">Contoh</td>
                        <td style="width: 25%; font-weight: bold;">Catatan</td>
                    </tr>
                    <tr>
                        <td><strong>KODE</strong></td>
                        <td>3 huruf dari nama jenis surat</td>
                        <td><span class="code-box">DOM</span></td>
                        <td>Surat Keterangan Domisili → DOM</td>
                    </tr>
                    <tr>
                        <td><strong>URUT</strong></td>
                        <td>Nomor urut bulanan (3 digit)</td>
                        <td><span class="code-box">001</span></td>
                        <td>Reset ke 001 setiap bulan baru</td>
                    </tr>
                    <tr>
                        <td><strong>BULAN</strong></td>
                        <td>Bulan pembuatan surat (2 digit)</td>
                        <td><span class="code-box">12</span></td>
                        <td>01=Jan, 02=Feb, ... 12=Des</td>
                    </tr>
                    <tr>
                        <td><strong>TAHUN</strong></td>
                        <td>Tahun pembuatan surat (4 digit)</td>
                        <td><span class="code-box">2024</span></td>
                        <td>Tahun penuh (2024, 2025, dst)</td>
                    </tr>
                </table>
            </div>

            <h3 style="color: #333; margin: 1.5rem 0 0.5rem;">Contoh Nomor Surat:</h3>
            <div style="background: #e8f4f8; padding: 1rem; border-radius: 4px;">
                <p><span class="code-box">DOM/001/12/2024</span> = Surat Domisili ke-1, Desember 2024</p>
                <p><span class="code-box">SKU/002/12/2024</span> = Surat Usaha ke-2, Desember 2024</p>
                <p><span class="code-box">DOM/001/01/2025</span> = Surat Domisili ke-1, Januari 2025 (nomor reset)</p>
            </div>
        </div>

        <!-- Proses Generasi -->
        <div class="section">
            <h2>⚙️ Proses Generasi Nomor Surat</h2>
            
            <ol style="line-height: 2; color: #666;">
                <li>Warga mengisi form pengajuan surat dan submit</li>
                <li>Sistem memanggil function <code>generateNoSurat()</code> di class Letter</li>
                <li>Function mencari kode jenis surat dari nama jenis</li>
                <li>Function menghitung jumlah surat jenis ini di bulan/tahun sekarang</li>
                <li>Nomor urut = count + 1 (dengan padding 0 menjadi 3 digit)</li>
                <li>Format nomor surat: KODE/URUT/BULAN/TAHUN</li>
                <li>Nomor surat di-insert ke database saat surat dibuat</li>
                <li>Nomor surat ditampilkan di surat_saya.php dan detail_surat.php</li>
            </ol>
        </div>

        <!-- Testing -->
        <div class="section">
            <h2>🧪 Cara Testing</h2>
            
            <h3 style="color: #333; margin: 1rem 0 0.5rem;">Test Auto-Generate Nomor Surat:</h3>
            <ol style="line-height: 2; color: #666;">
                <li>Login sebagai warga di <code>login.php</code></li>
                <li>Klik menu "Pengajuan Surat"</li>
                <li>Isi form pengajuan surat</li>
                <li>Submit form (jangan edit nomor surat karena otomatis)</li>
                <li>Lihat di "Surat Saya" - nomor surat sudah otomatis tergenerate</li>
                <li>Buat surat lagi dengan jenis yang sama - nomor urut bertambah</li>
                <li>Berikutnya bulan - nomor urut kembali ke 001</li>
            </ol>
        </div>

        <div style="text-align: center; padding: 2rem; color: #999;">
            <p>&copy; 2024 Sistem Surat RT/RW - Auto-Generate Nomor Surat</p>
            <p><a href="pengajuan.php" style="color: var(--secondary); text-decoration: none;">← Kembali ke Pengajuan Surat</a></p>
        </div>
    </div>
</body>
</html>
