<?php
class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $nik;
    public $username;
    public $nama;
    public $tempat_lahir;
    public $tanggal_lahir;
    public $jenis_kelamin;
    public $alamat;
    public $rt;
    public $rw;
    public $agama;
    public $pekerjaan;
    public $password;
    public $level;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Register new user
    public function register() {
        $query = "INSERT INTO " . $this->table_name . "
            SET nik=:nik, username=:username, nama=:nama, tempat_lahir=:tempat_lahir,
            tanggal_lahir=:tanggal_lahir, jenis_kelamin=:jenis_kelamin,
            alamat=:alamat, rt=:rt, rw=:rw, agama=:agama,
            pekerjaan=:pekerjaan, password=:password";

        $stmt = $this->conn->prepare($query);

        // Sanitize input - handle potential null values to avoid deprecation warnings
        $this->nik = htmlspecialchars(strip_tags($this->nik ?? ''));
        if (!empty($this->username)) {
            $this->username = htmlspecialchars(strip_tags($this->username));
        } else {
            $this->username = null; // Set to NULL if not provided
        }
        $this->nama = htmlspecialchars(strip_tags($this->nama ?? ''));
        $this->tempat_lahir = htmlspecialchars(strip_tags($this->tempat_lahir ?? ''));
        $this->tanggal_lahir = htmlspecialchars(strip_tags($this->tanggal_lahir ?? ''));
        $this->jenis_kelamin = htmlspecialchars(strip_tags($this->jenis_kelamin ?? ''));
        $this->alamat = htmlspecialchars(strip_tags($this->alamat ?? ''));
        $this->rt = htmlspecialchars(strip_tags($this->rt ?? ''));
        $this->rw = htmlspecialchars(strip_tags($this->rw ?? ''));
        $this->agama = htmlspecialchars(strip_tags($this->agama ?? ''));
        $this->pekerjaan = htmlspecialchars(strip_tags($this->pekerjaan ?? ''));
        // Don't hash password for admin
        if($this->level !== 'admin') {
            $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        }

        // Bind parameters
        $stmt->bindParam(":nik", $this->nik);
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":nama", $this->nama);
        $stmt->bindParam(":tempat_lahir", $this->tempat_lahir);
        $stmt->bindParam(":tanggal_lahir", $this->tanggal_lahir);
        $stmt->bindParam(":jenis_kelamin", $this->jenis_kelamin);
        $stmt->bindParam(":alamat", $this->alamat);
        $stmt->bindParam(":rt", $this->rt);
        $stmt->bindParam(":rw", $this->rw);
        $stmt->bindParam(":agama", $this->agama);
        $stmt->bindParam(":pekerjaan", $this->pekerjaan);
        $stmt->bindParam(":password", $this->password);

        try {
            if($stmt->execute()) {
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            return false;
        }
    }

    // Login user
    public function login() {
        // Login admin by username, warga by NIK
        if (!empty($this->username)) {
            $query = "SELECT id, nik, username, nama, password, level 
                     FROM " . $this->table_name . " 
                     WHERE username = :username 
                     LIMIT 0,1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":username", $this->username);
        } else {
            $query = "SELECT id, nik, username, nama, password, level 
                     FROM " . $this->table_name . " 
                     WHERE nik = :nik 
                     LIMIT 0,1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":nik", $this->nik);
        }
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            // For admin, compare password directly (no hash)
            if($row['level'] === 'admin') {
                if($this->password === $row['password']) {
                    $this->id = $row['id'];
                    $this->nik = $row['nik'];
                    $this->username = $row['username'];
                    $this->nama = $row['nama'];
                    $this->level = $row['level'];
                    return true;
                }
            } else {
                // For regular users, use password verification
                if(password_verify($this->password, $row['password'])) {
                    $this->id = $row['id'];
                    $this->nik = $row['nik'];
                    $this->username = $row['username'];
                    $this->nama = $row['nama'];
                    $this->level = $row['level'];
                    return true;
                }
            }
        }
        return false;
    }

    // Check if NIK exists
    public function nikExists() {
        $query = "SELECT id FROM " . $this->table_name . " WHERE nik = :nik";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":nik", $this->nik);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    // Get user by ID
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->nik = $row['nik'];
            $this->nama = $row['nama'];
            $this->tempat_lahir = $row['tempat_lahir'];
            $this->tanggal_lahir = $row['tanggal_lahir'];
            $this->jenis_kelamin = $row['jenis_kelamin'];
            $this->alamat = $row['alamat'];
            $this->rt = $row['rt'];
            $this->rw = $row['rw'];
            $this->agama = $row['agama'];
            $this->pekerjaan = $row['pekerjaan'];
            $this->level = $row['level'];
            return true;
        }
        return false;
    }
}
?>