<?php
/**
 * views/admin/wisata_form.php v2.0
 */
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../models/Wisata.php';
requireLogin();

$wm   = new Wisata();
$id   = (int)($_GET['id'] ?? 0);
$w    = $id ? $wm->getById($id) : null;
$isEdit = (bool)$w;

$kategoriList  = $wm->getKategoriList();
$kelurahanList = $wm->getKelurahanList();

$flash = $_SESSION['flash'] ?? null; unset($_SESSION['flash']);

$v = $w ?: ['nama_wisata'=>'','deskripsi'=>'','id_kategori'=>'','alamat'=>'','id_kelurahan'=>'',
            'latitude'=>3.5952,'longitude'=>98.6722,'tiket_masuk'=>0,'jam_operasional'=>'',
            'fasilitas'=>'','rating'=>0,'status'=>'aktif','gambar' => ''];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= $isEdit?'Edit':'Tambah' ?> Wisata — Admin SIG</title>
<link rel="stylesheet" href="../../assets/css/admin.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
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

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem">
      <h2 style="font-family:var(--fd)"><?= $isEdit?'Edit':'Tambah' ?> Data Wisata</h2>
      <a href="wisata.php" class="btn btn-light">← Kembali</a>
    </div>

    <div class="card">
      <form action="../../controllers/wisata_controller.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="<?= $isEdit?'update':'create' ?>">
        <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= $id ?>"><?php endif; ?>

        <div style="padding:1.5rem">
          <div class="form-grid">

            <div class="form-group form-full">
              <label>Nama Wisata *</label>
              <div style="display:flex;gap:0.5rem">
                <input type="text" name="nama_wisata" id="nama_wisata" required value="<?= htmlspecialchars($v['nama_wisata']) ?>" placeholder="Contoh: Istana Maimun" style="flex:1">
                <button type="button" class="btn btn-light" onclick="geocodeNamaWisata()" title="Cari Koordinat Otomatis">📍 Cari Lat/Lng</button>
              </div>
            </div>

            <div class="form-group">
              <label>Kategori *</label>
              <select name="id_kategori" required>
                <option value="">-- Pilih Kategori --</option>
                <?php foreach ($kategoriList as $k): ?>
                <option value="<?= $k['id'] ?>" <?= ($v['id_kategori']==$k['id'])?'selected':'' ?>><?= $k['ikon'] ?> <?= ucfirst($k['nama']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group">
              <label>Kelurahan</label>
              <select name="id_kelurahan">
                <option value="">-- Pilih Kelurahan --</option>
                <?php foreach ($kelurahanList as $kel): ?>
                <option value="<?= $kel['id'] ?>" <?= ($v['id_kelurahan']==$kel['id'])?'selected':'' ?>>
                  <?= htmlspecialchars($kel['nama']) ?> (<?= htmlspecialchars($kel['kecamatan']) ?>)
                </option>
                <?php endforeach; ?>
              </select>
              <span class="form-hint">Kelurahan otomatis menentukan Kecamatan</span>
            </div>

            <div class="form-group form-full">
              <label>Alamat Lengkap</label>
              <input type="text" name="alamat" value="<?= htmlspecialchars($v['alamat']) ?>" placeholder="Jl. Ahmad Yani No.1">
            </div>

            <div class="form-group form-full">
              <label>Deskripsi</label>
              <textarea name="deskripsi" rows="4"><?= htmlspecialchars($v['deskripsi']) ?></textarea>
            </div>

            <div class="form-group">
              <label>Harga Tiket (Rp, 0 = Gratis)</label>
              <input type="number" name="tiket_masuk" min="0" value="<?= (int)$v['tiket_masuk'] ?>">
            </div>

            <div class="form-group">
              <label>Jam Operasional</label>
              <input type="text" name="jam_operasional" value="<?= htmlspecialchars($v['jam_operasional']) ?>" placeholder="08:00 - 17:00 WIB">
            </div>

            <div class="form-group form-full">
              <label>Fasilitas <span class="form-hint" style="display:inline">(pisahkan koma)</span></label>
              <input type="text" name="fasilitas" value="<?= htmlspecialchars($v['fasilitas']) ?>" placeholder="Parkir, Toilet, WiFi, Kantin">
            </div>

            <div class="form-group">
              <label>Rating (0.0–5.0)</label>
              <input type="number" name="rating" min="0" max="5" step="0.1" value="<?= $v['rating'] ?>">
            </div>

            <div class="form-group">
              <label>Status</label>
              <select name="status">
                <option value="aktif"    <?= $v['status']==='aktif'?'selected':'' ?>>Aktif</option>
                <option value="nonaktif" <?= $v['status']==='nonaktif'?'selected':'' ?>>Non-Aktif</option>
              </select>
            </div>
                        <!-- Gambar Upload -->
            <div class="form-group form-full">
              <label>Gambar Wisata <?= $isEdit ? '(biarkan kosong jika tidak diubah)' : '' ?></label>
              <input type="file" name="gambar" accept="image/jpeg,image/png,image/webp" onchange="previewImg(this)">
              <span class="form-hint">Format: JPG, PNG, WEBP. Maks: 5MB. Ukuran disarankan: 800×600px</span>
              <div class="img-preview" id="imgPreview">
                <?php if (!empty($v['gambar'])): ?>
                <img src="../../uploads/wisata/<?= htmlspecialchars($v['gambar']) ?>" style="width:100%;height:100%;object-fit:cover">
                <?php else: ?>
                <span style="color:var(--muted);font-size:.85rem">Preview gambar</span>
                <?php endif; ?>
              </div>
            </div>

            <div class="form-group">
              <label>Latitude *</label>
              <input type="number" name="latitude" id="latitude" step="any" required value="<?= $v['latitude'] ?>">
            </div>
            <div class="form-group">
              <label>Longitude *</label>
              <input type="number" name="longitude" id="longitude" step="any" required value="<?= $v['longitude'] ?>">
            </div>

            <div class="form-group form-full">
              <label>📍 Pilih Lokasi di Peta (klik atau seret marker)</label>
              <div id="map-picker"></div>
              <span class="form-hint">Klik pada peta untuk menentukan koordinat secara visual</span>
            </div>

          </div>
        </div>

        <div style="padding:1.25rem 1.5rem;border-top:1px solid var(--border);display:flex;gap:.75rem">
          <button type="submit" class="btn btn-primary">💾 <?= $isEdit?'Simpan Perubahan':'Tambah Data Wisata' ?></button>
          <a href="wisata.php" class="btn btn-light">Batal</a>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="../../assets/js/map.js"></script>
<script>
initMapPicker(
  document.getElementById('latitude').value,
  document.getElementById('longitude').value,
  'latitude', 'longitude'
);

function geocodeNamaWisata() {
  const nama = document.getElementById('nama_wisata').value;
  if (!nama) {
    alert('Masukkan nama wisata terlebih dahulu');
    return;
  }
  const btn = event.currentTarget;
  const originalText = btn.innerHTML;
  btn.innerHTML = '⏳...';
  btn.disabled = true;

  const query = encodeURIComponent(nama + ' Medan');
  fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + query)
    .then(res => res.json())
    .then(data => {
      if (data && data.length > 0) {
        const lat = parseFloat(data[0].lat);
        const lon = parseFloat(data[0].lon);
        document.getElementById('latitude').value = lat.toFixed(7);
        document.getElementById('longitude').value = lon.toFixed(7);
        
        if (window.SIG && window.SIG.mapPickerMarker && window.SIG.mapPickerMap) {
          const latlng = L.latLng(lat, lon);
          window.SIG.mapPickerMarker.setLatLng(latlng);
          window.SIG.mapPickerMap.setView(latlng, 16);
        }
        alert('Koordinat berhasil ditemukan!');
      } else {
        alert('Koordinat tidak ditemukan untuk: ' + nama);
      }
    })
    .catch(err => {
      console.error(err);
      alert('Terjadi kesalahan saat mencari koordinat');
    })
    .finally(() => {
      btn.innerHTML = originalText;
      btn.disabled = false;
    });
}
</script>
</body>
</html>
