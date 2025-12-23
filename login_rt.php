<?php
require_once 'config/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    if (isAdmin()) {
        header("Location: dashboard_admin.php");
    } elseif ($_SESSION['level'] === 'rt') {
        header("Location: dashboard_rt.php");
    } elseif ($_SESSION['level'] === 'rw') {
        header("Location: dashboard_rw.php");
    } else {
        header("Location: dashboard_warga.php");
    }
    exit();
}

$error = '';

// Process login form
if ($_POST) {
    $database = new Database();
    $db = $database->getConnection();
    
    $user = new User($db);
    
    $user->username = $_POST['username'] ?? '';
    $user->password = $_POST['password'] ?? '';
    
    if ($user->username && $user->password) {
        // Check for RT user with username
        $query = "SELECT id, username, nama, nik, password, level FROM users WHERE username = ? AND level = 'rt'";
        $stmt = $db->prepare($query);
        $stmt->execute([$user->username]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row && $row['password'] === $user->password) { // Plain text comparison for admin/rt/rw
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['nik'] = $row['nik'];
            $_SESSION['nama'] = $row['nama'];
            $_SESSION['level'] = 'rt';
            $_SESSION['username'] = $row['username'];
            
            // Record successful login
            logLoginActivity($db, $row['id'], $row['nik'], $row['username'], 'rt', true, 'RT login successful');
            
            header("Location: dashboard_rt.php");
            exit();
        } else {
            // Record failed login attempt
            logLoginActivity($db, null, null, $_POST['username'], 'rt', false, 'RT login failed - invalid credentials');
            
            $error = "Username atau password salah!";
        }
    } else {
        $error = "Username dan password harus diisi!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login RT - Sistem Surat RT/RW</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .login-wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
        }
        
        .login-header {
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
            color: white;
            padding: 2rem 0;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .login-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            margin-bottom: 1rem;
            font-weight: 600;
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
            margin-bottom: 0.5rem;
            color: #333;
            font-size: 1.75rem;
        }
        
        .login-form-subtitle {
            text-align: center;
            margin-bottom: 2rem;
            color: #999;
            font-size: 0.9rem;
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
            border-color: #f39c12;
            box-shadow: 0 0 0 3px rgba(243, 156, 18, 0.1);
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
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(243, 156, 18, 0.3);
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
        
        .login-links {
            display: flex;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
            font-size: 0.85rem;
            margin-top: 1rem;
        }
        
        .login-links a {
            color: #f39c12;
            text-decoration: none;
            padding: 0.3rem 0.6rem;
        }
        
        .login-links a:hover {
            text-decoration: underline;
        }
        
        .page-footer {
            background: rgba(0, 0, 0, 0.05);
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
            <div class="login-badge">🏘️ KETUA RT</div>
            <div class="login-logo">Surat RT/RW</div>
            <div class="login-subtitle">Portal Ketua Rukun Tetangga</div>
        </div>

        <div class="login-main">
            <div class="login-container">
                <form method="POST" action="" class="login-form">
                    <h2>Login RT</h2>
                    <p class="login-form-subtitle">Masuk sebagai Ketua Rukun Tetangga</p>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <strong>Error:</strong> <?php echo e($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="username" class="form-label">👤 Username</label>
                        <input type="text" class="form-control" id="username" name="username" required 
                               placeholder="Masukkan username RT Anda" autofocus>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">🔒 Password</label>
                        <input type="password" class="form-control" id="password" name="password" required 
                               placeholder="Masukkan password Anda">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Login</button>
                    
                    <div class="login-footer">
                        <p>Akses lainnya:</p>
                        
                        <div class="login-links">
                            <a href="login.php">👥 Warga</a>
                            <span style="color: #ccc;">|</span>
                            <a href="login_rw.php">🏗️ RW</a>
                            <span style="color: #ccc;">|</span>
                            <a href="login_admin.php">⚙️ Admin</a>
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
