<?php
class Letter {
    private $conn;
    private $table_name = "surat";

    public $id;
    public $no_surat;
    public $user_id;
    public $jenis_surat_id;
    public $keperluan;
    public $tanggal_pengajuan;
    public $status;
    public $tanggal_selesai;
    public $catatan_admin;
    public $nama;
    public $nik;
    public $alamat;
    public $rt;
    public $rw;
    public $nama_surat;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Generate nomor surat otomatis
    // Format: [KODE_JENIS]/[NOMOR_URUT]/[BULAN]/[TAHUN]
    // Contoh: DOM/001/12/2024
    public function generateNoSurat() {
        try {
            // Get jenis surat code (menggunakan 3 huruf pertama dari nama jenis surat)
            $query_jenis = "SELECT nama_surat FROM jenis_surat WHERE id = ?";
            $stmt_jenis = $this->conn->prepare($query_jenis);
            $stmt_jenis->execute([$this->jenis_surat_id]);
            $result_jenis = $stmt_jenis->fetch(PDO::FETCH_ASSOC);
            
            if (!$result_jenis) {
                return null;
            }
            
            // Get code dari jenis surat (3 huruf pertama, uppercase)
            $nama_surat = $result_jenis['nama_surat'];
            $words = explode(' ', $nama_surat);
            $code = '';
            foreach ($words as $word) {
                $code .= strtoupper($word[0]);
            }
            // Jika kurang dari 3 karakter, ambil 3 huruf pertama dari nama
            if (strlen($code) < 3) {
                $code = strtoupper(substr($nama_surat, 0, 3));
            } else {
                $code = substr($code, 0, 3);
            }
            
            // Get current bulan dan tahun
            $bulan = date('m');
            $tahun = date('Y');
            
            // Get nomor urut untuk bulan dan tahun ini
            $query_count = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                           WHERE MONTH(created_at) = ? AND YEAR(created_at) = ?";
            $stmt_count = $this->conn->prepare($query_count);
            $stmt_count->execute([$bulan, $tahun]);
            $result_count = $stmt_count->fetch(PDO::FETCH_ASSOC);
            
            // Nomor urut: count + 1, padded dengan 0 (3 digit)
            $nomor_urut = str_pad($result_count['count'] + 1, 3, '0', STR_PAD_LEFT);
            
            // Generate nomor surat
            $no_surat = $code . '/' . $nomor_urut . '/' . $bulan . '/' . $tahun;
            
            return $no_surat;
        } catch (Exception $e) {
            error_log("Error generating nomor surat: " . $e->getMessage());
            return null;
        }
    }

    // Create new letter request
    public function create() {
        // Generate nomor surat otomatis
        $this->no_surat = $this->generateNoSurat();
        
        if (!$this->no_surat) {
            error_log("Failed to generate nomor surat");
            return false;
        }
        
        $query = "INSERT INTO " . $this->table_name . "
                SET no_surat=:no_surat, user_id=:user_id, jenis_surat_id=:jenis_surat_id,
                keperluan=:keperluan, tanggal_pengajuan=:tanggal_pengajuan,
                status='pending'";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->no_surat = htmlspecialchars(strip_tags($this->no_surat));
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->jenis_surat_id = htmlspecialchars(strip_tags($this->jenis_surat_id));
        $this->keperluan = htmlspecialchars(strip_tags($this->keperluan));
        $this->tanggal_pengajuan = htmlspecialchars(strip_tags($this->tanggal_pengajuan));

        // Bind parameters
        $stmt->bindParam(":no_surat", $this->no_surat);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":jenis_surat_id", $this->jenis_surat_id);
        $stmt->bindParam(":keperluan", $this->keperluan);
        $stmt->bindParam(":tanggal_pengajuan", $this->tanggal_pengajuan);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Read all letters
    public function readAll($user_id = null) {
        $query = "SELECT s.*, u.nama, u.nik, j.nama_surat
                 FROM " . $this->table_name . " s
                 LEFT JOIN users u ON s.user_id = u.id
                 LEFT JOIN jenis_surat j ON s.jenis_surat_id = j.id";
        
        if($user_id) {
            $query .= " WHERE s.user_id = :user_id";
        }
        
        $query .= " ORDER BY s.tanggal_pengajuan DESC";

        $stmt = $this->conn->prepare($query);
        
        if($user_id) {
            $stmt->bindParam(":user_id", $user_id);
        }

        $stmt->execute();
        return $stmt;
    }

    // Read one letter
    public function readOne() {
        $query = "SELECT s.*, u.nama, u.nik, u.alamat, u.rt, u.rw, j.nama_surat 
                 FROM " . $this->table_name . " s
                 LEFT JOIN users u ON s.user_id = u.id
                 LEFT JOIN jenis_surat j ON s.jenis_surat_id = j.id
                 WHERE s.id = ? LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->no_surat = $row['no_surat'];
            $this->user_id = $row['user_id'];
            $this->jenis_surat_id = $row['jenis_surat_id'];
            $this->keperluan = $row['keperluan'];
            $this->tanggal_pengajuan = $row['tanggal_pengajuan'];
            $this->status = $row['status'];
            $this->tanggal_selesai = $row['tanggal_selesai'];
            $this->catatan_admin = $row['catatan_admin'];
            $this->nama = $row['nama'];
            $this->nik = $row['nik'];
            $this->alamat = $row['alamat'];
            $this->rt = $row['rt'];
            $this->rw = $row['rw'];
            $this->nama_surat = $row['nama_surat'];
            return true;
        }
        return false;
    }

    // Update letter status
    public function updateStatus() {
        $query = "UPDATE " . $this->table_name . "
                SET status=:status, no_surat=:no_surat, 
                tanggal_selesai=:tanggal_selesai, catatan_admin=:catatan_admin
                WHERE id=:id";

        try {
            $stmt = $this->conn->prepare($query);

            // Sanitize input
            $this->status = htmlspecialchars(strip_tags($this->status));
            $this->no_surat = htmlspecialchars(strip_tags($this->no_surat));
            $this->tanggal_selesai = htmlspecialchars(strip_tags($this->tanggal_selesai));
            $this->catatan_admin = htmlspecialchars(strip_tags($this->catatan_admin));
            $this->id = intval($this->id);

            // Bind parameters
            $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
            $stmt->bindParam(":status", $this->status);
            $stmt->bindParam(":no_surat", $this->no_surat);
            $stmt->bindParam(":tanggal_selesai", $this->tanggal_selesai);
            $stmt->bindParam(":catatan_admin", $this->catatan_admin);

            if($stmt->execute()) {
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Update status error: " . $e->getMessage());
            return false;
        }
    }

    // Get statistics
    public function getStats() {
        $query = "SELECT
                 COUNT(*) as total,
                 COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending,
                 COUNT(CASE WHEN status = 'diproses' THEN 1 END) as diproses,
                 COUNT(CASE WHEN status = 'selesai' THEN 1 END) as selesai,
                 COUNT(CASE WHEN status = 'ditolak' THEN 1 END) as ditolak
                 FROM " . $this->table_name;

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>