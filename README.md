# 🗺️ SIG Wisata Kota Medan
### Sistem Informasi Geografis Pemetaan Objek Wisata Berbasis Web

> Dibuat oleh **Phelia Nathania** (NIM: 2305181048) — Politeknik Negeri Medan, TRPL-6D  
> Mata Kuliah: Sistem Informasi Geografis | Dosen: Donny Sanjaya, M.Kom

---

## 📁 Struktur Folder

```
sig_wisata_medan/
├── config/
│   └── koneksi.php          ← Koneksi PDO ke PostgreSQL + helpers
├── models/
│   ├── Wisata.php            ← Model CRUD tabel wisata
│   └── Admin.php             ← Model autentikasi admin
├── controllers/
│   ├── api_wisata.php        ← REST API (JSON untuk Leaflet)
│   ├── wisata_controller.php ← Handler CRUD (POST/GET)
│   └── auth_controller.php  ← Login / Logout
├── views/
│   ├── admin/
│   │   ├── login.php         ← Halaman login admin
│   │   ├── dashboard.php     ← Dashboard admin + statistik
│   │   ├── wisata.php        ← Daftar & manajemen wisata
│   │   ├── wisata_form.php   ← Form tambah/edit wisata
│   │   └── partials/
│   │       ├── sidebar.php   ← Sidebar navigasi
│   │       └── topbar.php    ← Header topbar
│   └── user/ (opsional: halaman user terpisah)
├── assets/
│   ├── css/
│   │   ├── style.css         ← CSS landing page publik
│   │   └── admin.css         ← CSS dashboard admin
│   ├── js/
│   │   └── map.js            ← Leaflet.js inisialisasi peta
│   └── images/               ← Gambar statis (banner, icon, dll)
├── uploads/
│   └── wisata/               ← Gambar upload objek wisata
├── index.php                 ← Landing page publik
└── database.sql              ← Query lengkap PostgreSQL + PostGIS
```

---



**Kredensial login admin:**
- Username: `admin`
- Password: `admin123`

---

## 🗄️ Penjelasan Database

### Tabel `wisata`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | SERIAL PK | Auto-increment primary key |
| `nama_wisata` | VARCHAR(150) | Nama objek wisata |
| `deskripsi` | TEXT | Deskripsi lengkap |
| `kategori` | VARCHAR(50) | alam/sejarah/religi/edukasi/kuliner/olahraga |
| `alamat` | TEXT | Alamat lengkap |
| `kecamatan` | VARCHAR(80) | Nama kecamatan |
| `latitude` | DECIMAL(10,7) | Koordinat lintang |
| `longitude` | DECIMAL(10,7) | Koordinat bujur |
| `tiket_masuk` | INTEGER | Harga tiket (0 = gratis) |
| `jam_operasional` | VARCHAR(100) | Jam buka |
| `fasilitas` | TEXT | Fasilitas tersedia |
| `gambar` | VARCHAR(255) | Nama file gambar |
| `rating` | DECIMAL(2,1) | Rating 0.0-5.0 |
| `status` | VARCHAR(20) | aktif/nonaktif |
| **`geom`** | **GEOMETRY(Point,4326)** | **Kolom spasial PostGIS** |

### Perbedaan Data Spasial vs Non-Spasial

| Aspek | Data Non-Spasial | Data Spasial |
|-------|-----------------|--------------|
| Contoh | nama, deskripsi, tiket | latitude, longitude, geom |
| Format | TEXT, INTEGER, VARCHAR | GEOMETRY, GEOGRAPHY |
| Query | `WHERE nama = 'X'` | `ST_DWithin(geom, point, radius)` |
| Analisis | Filter, sort biasa | Buffer, intersect, proximity |
| Library | Standard SQL | PostGIS extension |

### Fungsi PostGIS yang Digunakan
```sql
-- Membuat geometri dari koordinat
ST_MakePoint(longitude, latitude)

-- Set sistem koordinat (EPSG:4326 = WGS84)
ST_SetSRID(geom, 4326)

-- Konversi ke GeoJSON
ST_AsGeoJSON(geom)

-- Cari dalam radius (meter)
ST_DWithin(geom::geography, target::geography, 2000)
```

---

## 🗺️ Integrasi Leaflet.js

Alur data ke peta:
```
Database PostgreSQL → PHP API (api_wisata.php) → JSON GeoJSON → Leaflet.js → Marker di Peta
```

Endpoint API:
- `?action=map` → GeoJSON semua wisata aktif (untuk peta)
- `?action=list` → Array data (untuk daftar dengan filter)
- `?action=detail&id=1` → Data satu wisata (untuk modal popup)

---

## 🎨 Fitur Website

### Halaman Publik (`index.php`)
- Hero section dengan search bar
- Stats bar (total wisata per kategori)
- Filter wisata per kategori
- Grid kartu wisata
- Modal detail wisata (klik kartu)
- Peta interaktif Leaflet dengan marker warna per kategori
- Layer control (pilih basemap)

### Dashboard Admin
- Statistik ringkas (total, aktif, kategori)
- Bar chart distribusi kategori
- Mini peta persebaran
- Tabel data terbaru

### Manajemen CRUD
- Tabel searchable & filterable
- Form tambah/edit dengan map picker (klik lokasi di peta)
- Upload gambar dengan preview
- Validasi file (format, ukuran)
- Konfirmasi hapus

---
