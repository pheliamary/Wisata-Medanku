<?php
/**
 * views/admin/dashboard.php
 */
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../models/Wisata.php';
requireLogin();
$wisataModel = new Wisata();
$stats = $wisataModel->getStats();
$recent = $wisataModel->getAllAdmin();
$recent = array_slice($recent, 0, 5);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard — Admin SIG Wisata Medan</title>
<link rel="stylesheet" href="../../assets/css/admin.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
</head>
<body>
<?php include __DIR__ . '/partials/sidebar.php'; ?>
<div class="main-content">
  <?php include __DIR__ . '/partials/topbar.php'; ?>
  <div class="page-body">

    <!-- Stats Cards -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon">🏛️</div>
        <div><div class="stat-label">Total Wisata</div><div class="stat-value"><?= $stats['total'] ?></div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">✅</div>
        <div><div class="stat-label">Wisata Aktif</div><div class="stat-value"><?= $stats['aktif'] ?></div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">📂</div>
        <div><div class="stat-label">Kategori</div><div class="stat-value"><?= count($stats['per_kategori']) ?></div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">🚫</div>
        <div><div class="stat-label">Non-Aktif</div><div class="stat-value"><?= $stats['nonaktif'] ?></div></div>
      </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem">

      <!-- Tabel Data Terbaru -->
      <div class="card">
        <div class="card-header">
          <h3>Data Wisata Terbaru</h3>
          <a href="wisata.php" class="btn btn-primary btn-sm"><i class="fas fa-arrow-right"></i> Lihat Semua</a>
        </div>
        <div class="table-wrap">
          <table>
            <thead><tr><th>Nama Wisata</th><th>Kategori</th><th>Status</th></tr></thead>
            <tbody>
              <?php foreach ($recent as $w): ?>
              <tr>
                <td><?= htmlspecialchars($w['nama_wisata']) ?></td>
                <td><span class="cat-badge cat-<?= $w['kategori'] ?>"><?= ucfirst($w['kategori']) ?></span></td>
                <td><span class="badge badge-<?= $w['status'] ?>"><?= $w['status'] ?></span></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Distribusi Kategori -->
      <div class="card">
        <div class="card-header"><h3>Distribusi Kategori</h3></div>
        <div style="padding:1.5rem">
          <?php foreach ($stats['per_kategori'] as $cat): ?>
          <?php $pct = $stats['total'] > 0 ? round($cat['jumlah'] / $stats['total'] * 100) : 0; ?>
          <div style="margin-bottom:1rem">
            <div style="display:flex;justify-content:space-between;font-size:.83rem;margin-bottom:.3rem">
              <span style="font-weight:600"><?= ucfirst($cat['kategori']) ?></span>
              <span style="color:var(--muted)"><?= $cat['jumlah'] ?> lokasi (<?= $pct ?>%)</span>
            </div>
            <div class="dist-bar-track"><div class="dist-bar-fill" style="width:<?= $pct ?>%"></div></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- Mini Map Dashboard -->
    <div class="card" style="margin-top:1.5rem">
      <div class="card-header">
        <h3>Peta Persebaran Wisata</h3>
        <a href="<?= BASE_URL ?>#peta" target="_blank" class="btn btn-light btn-sm">Buka Peta Publik</a>
      </div>
      <div id="map" style="height:400px;border-radius:0 0 12px 12px"></div>
    </div>

  </div>
</div>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// Inisialisasi peta dashboard langsung
const map = L.map('map').setView([3.5952, 98.6722], 13);
L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png').addTo(map);
const colors = {sejarah:'#3b82f6',religi:'#8b5cf6',edukasi:'#ef4444',alam:'#2d9b57',kuliner:'#f59e0b',olahraga:'#ec4899'};
fetch('../../controllers/api_wisata.php?action=map')
  .then(r=>r.json())
  .then(data=>{
    data.features.forEach(f=>{
      const c=f.geometry.coordinates;
      const p=f.properties;
      const color=colors[p.kategori]||'#6b7280';
      const icon=L.divIcon({html:`<svg xmlns="http://www.w3.org/2000/svg" width="24" height="32" viewBox="0 0 32 42"><path d="M16 0C7.16 0 0 7.16 0 16c0 10 16 26 16 26S32 26 32 16C32 7.16 24.84 0 16 0z" fill="${color}"/><circle cx="16" cy="16" r="7" fill="white" opacity="0.9"/></svg>`,className:'',iconSize:[24,32],iconAnchor:[12,32]});
      L.marker([c[1],c[0]],{icon}).bindPopup(`<b>${p.nama}</b><br><small>${p.kategori}</small>`).addTo(map);
    });
  });
</script>
</body>
</html>
