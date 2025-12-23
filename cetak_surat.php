<?php
require_once 'config/config.php';
require_once 'config/institusi.php';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

require_once 'classes/Letter.php';
$letter = new Letter($db);

$letter->id = $_GET['id'];
if (!$letter->readOne()) {
    header("Location: index.php");
    exit();
}

// Get user details
require_once 'classes/User.php';
$user = new User($db);
$user->id = $letter->user_id;
$user->readOne();

// Check if user has permission to print this letter
if (!isAdmin() && $letter->user_id != $_SESSION['user_id']) {
    header("Location: index.php");
    exit();
}

// Check if letter is completed
if ($letter->status != 'selesai') {
    header("Location: detail_surat.php?id=" . $letter->id);
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Surat - <?php echo e($letter->nama_surat); ?></title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 0;
            padding: 2cm;
            line-height: 1.6;
            font-size: 12pt;
        }
        .header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .kop-surat {
            border-bottom: 3px solid #000;
            padding-bottom: 1.5rem;
            margin-bottom: 2rem;
            text-align: center;
        }
        .kop-surat h1 {
            margin: 0;
            font-size: 14pt;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        .kop-surat h2 {
            margin: 5px 0;
            font-size: 12pt;
            font-weight: bold;
        }
        .kop-surat p {
            margin: 5px 0;
            font-size: 11pt;
        }
        .surat-content {
            margin: 2rem 0;
        }
        .data-pemohon {
            margin: 2rem 0;
        }
        .footer {
            margin-top: 3rem;
            text-align: right;
        }
        .ttd {
            margin-top: 3rem;
        }
        .ttd-nama {
            margin-top: 4rem;
        }
        .page-break {
            page-break-before: always;
        }
        @media print {
            body {
                padding: 1cm;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin-bottom: 2rem;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">
            Cetak Surat
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 5px; cursor: pointer; margin-left: 10px;">
            Tutup
        </button>
    </div>

    <div class="kop-surat">
        <div style="text-align: center;">
            <h1 style="margin: 0; font-size: 14pt; font-weight: bold;">PEMERINTAH KOTA TANGERANG</h1>
            <h2 style="margin: 5px 0; font-size: 12pt; font-weight: bold;">KECAMATAN PINANG</h2>
            <h2 style="margin: 5px 0; font-size: 12pt; font-weight: bold;">KELURAHAN KUNCIRAN INDAH</h2>
            <p style="margin: 5px 0; font-size: 11pt; font-weight: bold;">RT 003 / RW 013</p>
            <p style="margin: 5px 0; font-size: 10pt;">Jl. Sultan Ageng Tirtayas RT.003/RW.013, Kunciran Indah, Kec. Pinang, Kota Tangerang, Banten 15144</p>
        </div>
    </div>

    <div style="text-align: center;">
        <h2 style="text-decoration: underline; margin-bottom: 0.5rem;">
            <?php echo strtoupper(e($letter->nama_surat)); ?>
        </h2>
        <p style="margin: 0;">Nomor: <?php echo e($letter->no_surat); ?></p>
    </div>

    <div class="surat-content">
        <p>Yang bertanda tangan di bawah ini Ketua RT / RW menerangkan dengan sebenarnya bahwa:</p>
        
        <div class="data-pemohon">
            <table style="margin-left: 2rem;">
                <tr>
                    <td style="padding-right: 1rem;">Nama</td>
                    <td>: <?php echo e($letter->nama); ?></td>
                </tr>
                <tr>
                    <td>NIK</td>
                    <td>: <?php echo e($letter->nik); ?></td>
                </tr>
                <tr>
                    <td>Tempat/Tgl. Lahir</td>
                    <td>: <?php echo e($user->tempat_lahir); ?>, <?php echo date('d-m-Y', strtotime($user->tanggal_lahir)); ?></td>
                </tr>
                <tr>
                    <td>Jenis Kelamin</td>
                    <td>: <?php echo $user->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan'; ?></td>
                </tr>
                <tr>
                    <td>Alamat</td>
                    <td>: <?php echo e($user->alamat); ?></td>
                </tr>
                <tr>
                    <td>Agama</td>
                    <td>: <?php echo e($user->agama); ?></td>
                </tr>
                <tr>
                    <td>Pekerjaan</td>
                    <td>: <?php echo e($user->pekerjaan); ?></td>
                </tr>
            </table>
        </div>

        <p>Yang bersangkutan benar-benar membutuhkan <?php echo e($letter->nama_surat); ?> untuk:</p>
        <p style="margin-left: 2rem;"><?php echo nl2br(e($letter->keperluan)); ?></p>
        
        <p>Demikian surat keterangan ini dibuat dengan sebenarnya untuk dapat dipergunakan sebagaimana mestinya.</p>
    </div>

    <div class="footer">
        <div class="ttd">
            <p><?php echo e(date('d F Y', strtotime($letter->tanggal_selesai))); ?></p>
            <p>Ketua RT <?php echo e($letter->rt); ?> / RW <?php echo e($letter->rw); ?></p>
            <div class="ttd-nama">
                <p>_________________________</p>
            </div>
        </div>
    </div>
</body>
</html>