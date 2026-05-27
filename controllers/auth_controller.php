<?php
/**
 * controllers/auth_controller.php
 */

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../models/Admin.php';

startSession();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Username dan password wajib diisi.'];
        redirect(BASE_URL . 'views/admin/login.php');
    }

    $adminModel = new Admin();
    $admin = $adminModel->login($username, $password);

    if ($admin) {
        $_SESSION['admin_id']   = $admin['id'];
        $_SESSION['admin_name'] = $admin['nama_lengkap'];
        $_SESSION['admin_user'] = $admin['username'];
        session_regenerate_id(true);
        redirect(BASE_URL . 'views/admin/dashboard.php');
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Username atau password salah.'];
        redirect(BASE_URL . 'views/admin/login.php');
    }
}

if ($action === 'logout') {
    session_destroy();
    redirect(BASE_URL . 'views/admin/login.php');
}
