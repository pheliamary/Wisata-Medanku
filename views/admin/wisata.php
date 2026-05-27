<?php
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../models/Wisata.php';
requireLogin();

$wm   = new Wisata();
$search    = sanitize($_GET['search']    ?? '');
$kategori  = sanitize($_GET['kategori'] ?? '');
$kecamatan = sanitize($_GET['kecamatan']?? '');
$wisatas   = $wm->getAllAdmin($search, $kategori, $kecamatan);
$katList   = $wm->getDistinctKategori();
$kecList   = $wm->getDistinctKecamatan();
$flash     = $_SESSION['flash'] ?? null; unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Manajemen Wisata — Admin SIG</title>
<link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body>
<?php include __DIR__ . '/partials/sidebar.php'; ?>
<div class="main-content">
  <?php include __DIR__ . '/partials/topbar.php'; ?>
  <div class="page-body">
    <?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type']==='error'?'error':'success' ?>">
      <?= $flash['type']==='error'?'⚠️':'✅' ?> <?= htmlspecialchars($flash['msg']) ?>
    </div>
    <?php endif; ?>

    <div class="toolbar">
      <a href="wisata_form.php" class="btn btn-primary">+ Tambah Wisata</a>
      <form method="GET" style="display:flex;gap:.6rem;flex:1;flex-wrap:wrap">
        <input type="text" name="search" placeholder="Cari nama / alamat..." value="<?= htmlspecialchars($search) ?>">
        <select name="kategori">
          <option value="">Semua Kategori</option>
          <?php foreach ($katList as $k): ?>
          <option value="<?= $k ?>" <?= $kategori===$k?'selected':'' ?>><?= ucfirst($k) ?></option>
          <?php endforeach; ?>
        </select>
        <select name="kecamatan">
          <option value="">Semua Kecamatan</option>
          <?php foreach ($kecList as $k): ?>
          <option value="<?= $k ?>" <?= $kecamatan===$k?'selected':'' ?>><?= $k ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-light">Cari</button>
        <?php if ($search||$kategori||$kecamatan): ?><a href="wisata.php" class="btn btn-light">Reset</a><?php endif; ?>
      </form>
    </div>

    <div class="card">
      <div class="card-header">
        <h3>Data Wisata <span style="font-weight:400;color:var(--muted)">(<?= count($wisatas) ?> data)</span></h3>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr><th>#</th><th>Nama Wisata</th><th>Kategori</th><th>Kelurahan</th><th>Kecamatan</th><th>Tiket</th><th>Rating</th><th>Status</th><th>Aksi</th></tr>
          </thead>
          <tbody>
            <?php if (empty($wisatas)): ?>
            <tr><td colspan="9" style="text-align:center;padding:2.5rem;color:var(--muted)">Tidak ada data.</td></tr>
            <?php endif; ?>
            <?php foreach ($wisatas as $i => $w): ?>
            <tr>
              <td style="color:var(--muted)"><?= $i+1 ?></td>
              <td style="font-weight:600;max-width:180px"><?= htmlspecialchars($w['nama_wisata']) ?></td>
              <td><span class="cat-badge cat-<?= $w['kategori'] ?>"><?= ucfirst($w['kategori']) ?></span></td>
              <td style="color:var(--muted);font-size:.82rem"><?= htmlspecialchars($w['kelurahan']??'-') ?></td>
              <td style="color:var(--muted);font-size:.82rem"><?= htmlspecialchars($w['kecamatan']??'-') ?></td>
              <td><?= ($w['tiket_masuk']??0)>0 ? 'Rp '.number_format($w['tiket_masuk'],0,',','.') : '<span style="color:#28a745;font-weight:600">Gratis</span>' ?></td>
              <td>⭐ <?= number_format($w['rating']??0,1) ?></td>
              <td><span class="badge badge-<?= $w['status'] ?>"><?= $w['status'] ?></span></td>
              <td>
                <div class="action-btns">
                  <a href="wisata_form.php?id=<?= $w['id'] ?>" class="btn-icon btn-icon-edit" title="Edit">✏️</a>
                  <a href="../../controllers/wisata_controller.php?action=delete&id=<?= $w['id'] ?>"
                     class="btn-icon btn-icon-del" title="Hapus"
                     onclick="return confirm('Hapus wisata ini?')">🗑️</a>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
</body>
</html>
