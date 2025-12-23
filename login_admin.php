<?php
require_once 'config/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    if (isAdmin()) {
        header("Location: dashboard_admin.php");
    } else {
        header("Location: dashboard_warga.php");
    }
    exit();
}

$error = '';
$success = '';

// Process login form
if ($_POST && isset($_POST['username']) && isset($_POST['password'])) {
    $database = new Database();
    $db = $database->getConnection();
    
    $user = new User($db);
    $user->username = $_POST['username'];
    $user->password = $_POST['password'];
    if ($user->login()) {
        // Check if user is admin - if not, show error
        $query = "SELECT level FROM users WHERE username = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$user->username]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row['level'] !== 'admin') {
            $error = "Akses hanya untuk admin!";
        } else {
            $_SESSION['user_id'] = $user->id;
            $_SESSION['nik'] = $user->nik;
            $_SESSION['username'] = $user->username;
            $_SESSION['nama'] = $user->nama;
            $_SESSION['level'] = $user->level;
            // Record successful admin login
            logLoginActivity($db, $user->id, $user->nik, $user->username, $user->level, true, 'Admin login successful');

            header("Location: dashboard_admin.php");
            exit();
        }
    } else {
        // Record failed admin login attempt
        try {
            $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
            $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
            $insert = $db->prepare("INSERT INTO login_history (user_id, nik, username, role, success, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            $insert->execute([null, null, $_POST['username'], 'admin', 0, $ip, $ua]);
        } catch (Exception $e) {
            error_log('Login history insert failed: ' . $e->getMessage());
        }

        $error = "Username atau password salah!";
    }
}

// Check for registration success message
if (isset($_SESSION['message'])) {
    $success = $_SESSION['message'];
    unset($_SESSION['message']);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Sistem Surat RT/RW</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .login-wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: var(--light);
        }
        
        .login-header {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            padding: 2rem 0;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .login-logo {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .login-subtitle {
            font-size: 0.95rem;
            opacity: 0.9;
        }
        
        .login-main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 0;
        }
        
        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 0 1rem;
        }
        
        .login-form {
            background: white;
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }
        
        .login-form h2 {
            text-align: center;
            margin-bottom: 2rem;
            color: #333;
            font-size: 1.75rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: #555;
            font-weight: 600;
            font-size: 0.95rem;
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #e74c3c;
            box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
        }
        
        .btn {
            width: 100%;
            padding: 0.85rem 1rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(231, 76, 60, 0.3);
        }
        
        .alert {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 8px;
            font-size: 0.95rem;
        }
        
        .alert-danger {
            background-color: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }
        
        .alert-success {
            background-color: #efe;
            color: #3c3;
            border: 1px solid #cfc;
        }
        
        .login-footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e0e0e0;
        }
        
        .login-footer p {
            margin: 0.5rem 0;
            font-size: 0.9rem;
            color: #666;
        }
        
        .login-footer a {
            color: #e74c3c;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }
        
        .login-footer a:hover {
            color: #c0392b;
            text-decoration: underline;
        }
        
        .login-links {
            display: flex;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
            font-size: 0.85rem;
            margin-top: 1rem;
        }
        
        .login-links a {
            color: #e74c3c;
            text-decoration: none;
            padding: 0.3rem 0.6rem;
        }
        
        .login-links a:hover {
            text-decoration: underline;
        }
        
        .admin-badge {
            display: inline-block;
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-top: 1rem;
        }
        
        .page-footer {
            background: rgba(0, 0, 0, 0.1);
            color: white;
            text-align: center;
            padding: 1rem 0;
            font-size: 0.9rem;
        }
        
        @media (max-width: 600px) {
            .login-form {
                padding: 2rem;
            }
            
            .login-logo {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-header">
            <div class="login-logo">🔐 Admin Panel</div>
            <div class="login-subtitle">Login Khusus Administrator</div>
        </div>

        <div class="login-main">
            <div class="login-container">
                <form method="POST" action="" class="login-form">
                    <h2>Login Admin</h2>
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <strong>Error:</strong> <?php echo e($error); ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <strong>Berhasil:</strong> <?php echo e($success); ?>
                        </div>
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="username" class="form-label">👤 Username Admin</label>
                        <input type="text" class="form-control" id="username" name="username" required 
                               placeholder="Masukkan username admin" autofocus>
                    </div>
                    <div class="form-group">
                        <label for="password" class="form-label">🔒 Password</label>
                        <input type="password" class="form-control" id="password" name="password" required 
                               placeholder="Masukkan password Anda">
                    </div>
                    <button type="submit" class="btn btn-primary">Login sebagai Admin</button>
                    <div class="admin-badge">
                        ⚠️ Halaman untuk Administrator saja
                    </div>
                    <div class="login-footer">
                        <p>Bukan admin?</p>
                        <p><a href="login.php">👤 Login sebagai Warga</a></p>
                        <div class="login-links">
                            <a href="create_admin.php">⚙️ Buat Admin</a>
                            <span style="color: #ccc;">|</span>
                            <a href="test_admin_login.php">🧪 Test Login</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="page-footer">
            <p>&copy; 2024 Sistem Surat RT/RW. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
