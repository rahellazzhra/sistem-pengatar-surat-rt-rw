<?php
require_once 'config/config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Ensure `login_history` table exists (safe idempotent create)
try {
    $createSql = <<<'SQL'
CREATE TABLE IF NOT EXISTS login_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    nik VARCHAR(32) NULL,
    username VARCHAR(64) NULL,
    role VARCHAR(20) NULL,
    success TINYINT(1) NOT NULL DEFAULT 0,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX (user_id),
    INDEX (nik),
    INDEX (username),
    INDEX (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
    $db->exec($createSql);
} catch (PDOException $e) {
    // If table creation fails, surface a friendly message instead of raw exception
    echo "Database error: " . htmlspecialchars($e->getMessage());
    exit;
}

// Filters
$limit = 100;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$where = [];
$params = [];
if ($search !== '') {
    $where[] = '(nik LIKE ? OR username LIKE ? OR role LIKE ? OR ip_address LIKE ? or user_agent LIKE ?)';
    $like = "%{$search}%";
    $params = array_fill(0,5,$like);
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "SELECT * FROM login_history " . $whereSql . " ORDER BY created_at DESC LIMIT ? OFFSET ?";
$stmt = $db->prepare($sql);
// bind params
$bindIndex = 1;
foreach ($params as $p) {
    $stmt->bindValue($bindIndex++, $p);
}
$stmt->bindValue($bindIndex++, $limit, PDO::PARAM_INT);
$stmt->bindValue($bindIndex++, $offset, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count total for pagination
$countSql = "SELECT COUNT(*) as cnt FROM login_history " . $whereSql;
$countStmt = $db->prepare($countSql);
if ($params) {
    foreach ($params as $i => $p) {
        $countStmt->bindValue($i+1, $p);
    }
}
$countStmt->execute();
$total = $countStmt->fetchColumn();

?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Riwayat Login - Admin</title>
<link rel="stylesheet" href="assets/css/style.css">
<style>
.container { max-width:1100px; margin: 2rem auto; padding: 0 1rem; }
.table { width: 100%; border-collapse: collapse; }
.table th, .table td { padding: 0.75rem; border-bottom: 1px solid #eee; text-align: left; }
.header { background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); color: white; padding: 1rem 0; }
</style>
</head>
<body>
<div class="header"><div class="container"><h2>Riwayat Login</h2></div></div>
<div class="container">
    <form method="get" style="margin: 1rem 0; display:flex; gap:.5rem;">
        <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="Cari nik / username / ip" style="flex:1;padding:.5rem;border-radius:6px;border:1px solid #ddd;">
        <button class="btn btn-primary">Cari</button>
    </form>

    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Waktu</th>
                <th>User</th>
                <th>Role</th>
                <th>IP</th>
                <th>Hasil</th>
                <th>User Agent</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?php echo htmlspecialchars($r['id']); ?></td>
                    <td><?php echo htmlspecialchars($r['created_at']); ?></td>
                    <td><?php echo htmlspecialchars($r['username'] ?: $r['nik'] ?: '-'); ?></td>
                    <td><?php echo htmlspecialchars($r['role']); ?></td>
                    <td><?php echo htmlspecialchars($r['ip_address']); ?></td>
                    <td><?php echo $r['success'] ? '<span style="color:green;">Sukses</span>' : '<span style="color:red;">Gagal</span>'; ?></td>
                    <td><?php
                        $user_agent = $r['user_agent'] ?? '';
                        echo htmlspecialchars($user_agent ? mb_strimwidth($user_agent, 0, 120, '...') : '-');
                    ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div style="margin-top:1rem;display:flex;justify-content:space-between;align-items:center;">
        <div>Menampilkan <?php echo count($rows); ?> dari <?php echo (int)$total; ?> entri</div>
        <div>
            <?php if ($page > 1): ?>
                <a class="btn btn-secondary" href="?q=<?php echo urlencode($search); ?>&page=<?php echo $page-1; ?>">Prev</a>
            <?php endif; ?>
            <?php if ($page * $limit < $total): ?>
                <a class="btn btn-primary" href="?q=<?php echo urlencode($search); ?>&page=<?php echo $page+1; ?>">Next</a>
            <?php endif; ?>
        </div>
    </div>

</div>
</body>
</html>
