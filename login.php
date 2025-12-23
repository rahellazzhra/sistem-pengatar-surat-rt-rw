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
$success = '';

// Process login form
if ($_POST) {
    $database = new Database();
    $db = $database->getConnection();
    
    $user = new User($db);
    
    $user->nik = $_POST['nik'];
    $user->password = $_POST['password'];
    
    if ($user->login()) {
        // Check if user is admin - if yes, reject
        $query = "SELECT level FROM users WHERE nik = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$user->nik]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row['level'] === 'admin') {
            $error = "Silakan gunakan halaman login admin!";
        } elseif ($row['level'] === 'rt') {
            $error = "Silakan gunakan halaman login khusus untuk RT!";
        } elseif ($row['level'] === 'rw') {
            $error = "Silakan gunakan halaman login khusus untuk RW!";
        } else {
            $_SESSION['user_id'] = $user->id;
            $_SESSION['nik'] = $user->nik;
            $_SESSION['nama'] = $user->nama;
            $_SESSION['level'] = $user->level;
            // Record successful login
            logLoginActivity($db, $user->id, $user->nik, $user->username, $user->level, true, 'Warga login successful');

            header("Location: dashboard_warga.php");
            exit();
        }
        } else {
            // Record failed login attempt for NIK
            logLoginActivity($db, null, $_POST['nik'], null, 'warga', false, 'Warga login failed - invalid credentials');

            $error = "NIK atau password salah!";
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
    <title>Login Warga - Sistem Surat RT/RW</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Login page - dark blue / electric blue theme */
        .login-wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: transparent; /* body already sets the page background */
        }

        .login-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: var(--white);
            padding: 2rem 0;
            text-align: center;
            box-shadow: 0 6px 24px rgba(2, 8, 30, 0.6);
        }

        .login-logo { font-size: 2rem; font-weight: 700; margin-bottom: 0.25rem; }
        .login-subtitle { font-size: 0.95rem; color: rgba(255,255,255,0.9); }

        .login-main { flex: 1; display:flex; align-items:center; justify-content:center; padding: 2.5rem 0; }
        .login-container { width:100%; max-width:480px; padding: 0 1rem; }

        .login-form {
            background: linear-gradient(180deg, rgba(255,255,255,0.03), rgba(255,255,255,0.02));
            border: 1px solid rgba(255,255,255,0.04);
            padding: 2.25rem;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(2,10,30,0.6);
            color: var(--app-text);
        }

        .login-form h2 { text-align:center; margin-bottom:1.5rem; color:var(--app-text); font-size:1.5rem; }

        .form-group { margin-bottom:1.25rem; }
        .form-label { display:block; margin-bottom:.5rem; color:var(--app-muted); font-weight:600; }

        .form-control {
            width:100%; padding:.75rem 1rem; border-radius:8px;
            background: rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.04); color:var(--app-text);
            transition: box-shadow .2s, border-color .2s;
        }

        .form-control:focus { outline:none; border-color: var(--secondary); box-shadow: 0 0 0 5px rgba(0,191,255,0.06); }

        .btn { width:100%; padding:.85rem 1rem; border-radius:8px; font-weight:700; cursor:pointer; }
        .btn-primary { background: linear-gradient(90deg, var(--secondary), var(--primary)); color: var(--white); border: none; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 12px 30px rgba(0,191,255,0.12); }

        .alert { padding: .9rem 1rem; margin-bottom:1rem; border-radius:8px; font-size:.95rem; }
        .alert-danger { background: rgba(255,77,109,0.08); color: var(--danger); border:1px solid rgba(255,77,109,0.12); }
        .alert-success { background: rgba(59,225,122,0.07); color: var(--success); border:1px solid rgba(59,225,122,0.12); }

        .login-footer { text-align:center; margin-top:1.25rem; padding-top:1rem; border-top:1px solid rgba(255,255,255,0.04); }
        .login-footer p { margin:.5rem 0; color:var(--app-muted); }
        .login-footer a { color: var(--secondary); font-weight:700; text-decoration:none; }
        .login-footer a:hover { text-decoration:underline; }

        .page-footer { background: transparent; color: rgba(255,255,255,0.6); text-align:center; padding:1rem 0; font-size:0.9rem; }

        @media (max-width:600px) { .login-form { padding:1.5rem; } .login-logo{ font-size:1.5rem;} }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-header">
            <div class="login-logo">📋 Surat RT/RW</div>
            <div class="login-subtitle">Sistem Manajemen Surat Menyurat</div>
        </div>

        <div class="login-main">
            <div class="login-container">
                <form method="POST" action="" class="login-form">
                    <h2>Login Warga</h2>
                    
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
                        <label for="nik" class="form-label">📱 NIK (Nomor Identitas)</label>
                        <input type="text" class="form-control" id="nik" name="nik" required 
                               placeholder="Masukkan 16 digit NIK Anda" maxlength="16" autofocus>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">🔒 Password</label>
                        <input type="password" class="form-control" id="password" name="password" required 
                               placeholder="Masukkan password Anda">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Login</button>
                    
                    <div class="login-footer">
                        <p>Belum punya akun?</p>
                        <p><a href="register.php">👤 Daftar sebagai Warga Baru</a></p>
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