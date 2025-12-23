<?php
require_once 'config/config.php';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Redirect to appropriate dashboard based on role
if (isAdmin()) {
    header("Location: dashboard_admin.php");
} elseif ($_SESSION['level'] === 'rt') {
    header("Location: dashboard_rt.php");
} elseif ($_SESSION['level'] === 'rw') {
    header("Location: dashboard_rw.php");
} else {
    header("Location: dashboard_warga.php");
}
?>
