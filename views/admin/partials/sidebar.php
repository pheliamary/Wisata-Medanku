<?php /** views/admin/partials/sidebar.php v3.0 */ ?>
<aside class="sidebar">
  <div class="sidebar-header">
    <div class="sidebar-logo-icon">🗺️</div>
    <div class="sidebar-logo-text">
      <div class="brand-name">SIG Wisata</div>
      <div class="brand-role">Panel Administrator</div>
    </div>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-section-label">Menu Utama</div>
    <a href="dashboard.php" class="<?= basename($_SERVER['PHP_SELF'])==='dashboard.php'?'active':'' ?>">
      <span class="nav-icon">📊</span> Dashboard
    </a>
    <a href="wisata.php" class="<?= basename($_SERVER['PHP_SELF'])==='wisata.php'?'active':'' ?>">
      <span class="nav-icon">🏛️</span> Data Wisata
      <span class="nav-badge"><?php
        require_once __DIR__.'/../../../config/koneksi.php';
        echo getDB()->query("SELECT COUNT(*) FROM wisata WHERE status='aktif'")->fetchColumn();
      ?></span>
    </a>
    <a href="wisata_form.php" class="<?= basename($_SERVER['PHP_SELF'])==='wisata_form.php'?'active':'' ?>">
      <span class="nav-icon">➕</span> Tambah Wisata
    </a>

    <div class="nav-section-label" style="margin-top:.5rem">Lainnya</div>
    <a href="<?= BASE_URL ?>" target="_blank">
      <span class="nav-icon">🌐</span> Halaman Publik
    </a>
  </nav>

  <div class="sidebar-footer">
    <div class="admin-card">
      <div class="admin-avatar"><?= strtoupper(substr($_SESSION['admin_user']??'A',0,1)) ?></div>
      <div>
        <div class="admin-name"><?= htmlspecialchars($_SESSION['admin_name']??'Admin') ?></div>
        <div class="admin-role">Administrator</div>
      </div>
    </div>
    <a href="<?= BASE_URL ?>controllers/auth_controller.php?action=logout" class="logout-link">
      🚪 Keluar
    </a>
  </div>
</aside>
