<?php /** views/admin/partials/topbar.php */
$titles = [
  'dashboard.php'   => ['Dashboard', 'Selamat datang di panel admin SIG Wisata Medan'],
  'wisata.php'      => ['Manajemen Data Wisata', 'Kelola semua data objek wisata'],
  'wisata_form.php' => ['Form Wisata', 'Tambah atau edit data wisata'],
];
$page = basename($_SERVER['PHP_SELF']);
[$h1, $sub] = $titles[$page] ?? ['Admin Panel', ''];
?>
<div class="topbar">
  <div class="topbar-title">
    <h1><?= $h1 ?></h1>
    <p><?= $sub ?></p>
  </div>
  <div style="display:flex;align-items:center;gap:1rem">
    <span style="font-size:.82rem;color:var(--text-muted)">
      <i class="fas fa-calendar"></i> <?= date('d M Y') ?>
    </span>
  </div>
</div>
