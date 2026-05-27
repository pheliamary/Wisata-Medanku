<?php
/**
 * views/admin/login.php
 */
require_once __DIR__ . '/../../config/koneksi.php';
startSession();
if (isLoggedIn()) redirect(BASE_URL . 'views/admin/dashboard.php');
$flash = $_SESSION['flash'] ?? null; unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login Admin — SIG Wisata Medan</title>
<link rel="stylesheet" href="../../assets/css/admin.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
  body { display: flex; align-items: center; justify-content: center; min-height: 100vh;
         background: linear-gradient(160deg,#0f4525 0%,#1a6b3c 60%,#2d9b57 100%); }
  .login-box { background: #fff; border-radius: 20px; padding: 3rem 2.5rem; width: 100%; max-width: 420px; box-shadow: 0 24px 80px rgba(0,0,0,.25); }
  .login-icon { width: 68px; height: 68px; background: linear-gradient(135deg,#1a6b3c,#2d9b57); border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto 1.5rem; box-shadow: 0 8px 24px rgba(26,107,60,.35); }
  .login-title { font-family: 'Playfair Display',serif; font-size: 1.7rem; text-align: center; margin-bottom: .4rem; }
  .login-sub { text-align: center; color: var(--muted); font-size: .85rem; margin-bottom: 2rem; }
  .input-group { position: relative; margin-bottom: 1.1rem; }
  .input-group i { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--muted); }
  .input-group input { width: 100%; padding: .75rem 1rem .75rem 2.75rem; border: 1.5px solid var(--border); border-radius: 10px; font-family: var(--fb); font-size: .9rem; outline: none; transition: border-color .2s; }
  .input-group input:focus { border-color: var(--green); box-shadow: 0 0 0 3px rgba(26,107,60,.1); }
  .login-btn { width: 100%; padding: .85rem; background: linear-gradient(135deg,#1a6b3c,#2d9b57); color: #fff; border: none; border-radius: 10px; font-family: var(--fb); font-size: 1rem; font-weight: 700; cursor: pointer; transition: opacity .2s; margin-top: .5rem; }
  .login-btn:hover { opacity: .92; }
  .back-link { display: block; text-align: center; margin-top: 1.25rem; font-size: .82rem; color: var(--muted); }
</style>
</head>
<body>
<div class="login-box">
  <div class="login-icon">🗺️</div>
  <h1 class="login-title">Masuk Admin</h1>
  <p class="login-sub">SIG Wisata Kota Medan — Panel Pengelola</p>

  <?php if ($flash): ?>
  <div class="alert alert-<?= $flash['type'] === 'error' ? 'error' : 'success' ?>">
    <?= $flash['type'] === 'error' ? '⚠️' : '✅' ?> <?= htmlspecialchars($flash['msg']) ?>
  </div>
  <?php endif; ?>

  <form action="<?= BASE_URL ?>controllers/auth_controller.php" method="POST">
    <input type="hidden" name="action" value="login">
    <div class="input-group">
      <i class="fas fa-user"></i>
      <input type="text" name="username" placeholder="Username" required autocomplete="username">
    </div>
    <div class="input-group">
      <i class="fas fa-lock"></i>
      <input type="password" name="password" placeholder="Password" required autocomplete="current-password">
    </div>
    <button type="submit" class="login-btn">Masuk ke Dashboard</button>
  </form>
  <a href="<?= BASE_URL ?>" class="back-link">← Kembali ke halaman publik</a>
</div>
</body>
</html>
