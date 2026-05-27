<?php
/**
 * controllers/api_wisata.php v2.0
 */
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../models/Wisata.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$action = $_GET['action'] ?? 'map';
$wm = new Wisata();

switch ($action) {
    case 'map':
        $rows = $wm->getForMap();
        $features = array_map(fn($r) => [
            'type' => 'Feature',
            'geometry' => ['type'=>'Point','coordinates'=>[(float)$r['longitude'],(float)$r['latitude']]],
            'properties' => [
                'id'       => (int)$r['id'],
                'nama'     => $r['nama_wisata'],
                'kategori' => $r['kategori'],
                'ikon'     => $r['kategori_ikon'],
                'warna'    => $r['kategori_warna'],
                'alamat'   => $r['alamat'],
                'kelurahan'=> $r['kelurahan'],
                'kecamatan'=> $r['kecamatan'],
                'tiket'    => (int)$r['tiket_masuk'],
                'jam'      => $r['jam_operasional'],
                'fasilitas'=> $r['fasilitas'],
                'rating'   => (float)$r['rating'],
            ],
        ], $rows);
        echo json_encode(['type'=>'FeatureCollection','features'=>$features], JSON_UNESCAPED_UNICODE);
        break;

    case 'detail':
        $id = (int)($_GET['id'] ?? 0);
        $row = $wm->getById($id);
        echo json_encode($row ?: ['error'=>'Tidak ditemukan'], JSON_UNESCAPED_UNICODE);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error'=>'Action tidak dikenali']);
}

/**Contoh output yang dikirim ke browser:
/**
 * {
 *   "type": "FeatureCollection",
 *   "features": [
 *     {
 *       "type": "Feature",
 *       "geometry": {
 *         "type": "Point",
 *         "coordinates": [98.6760, 3.5922]
 *       },
 *       "properties": {
 *         "nama": "Tjong A Fie Mansion",
 *         "kategori": "sejarah",
 *         "tiket": 35000
 *       }
 *     }
 *   ]
 * }
 */

/**
 * ALUR DATA SISTEM INFORMASI GEOGRAFIS:
 * 
 * 1. Postgres    : "Saya punya data koordinat mentah 0101000020E6..."
 * 2. ST_AsGeoJSON : "Oke, saya ubah jadi {"type": "Point", "coordinates": [98.67, 3.59]}"
 * 3. PHP         : "Saya bungkus data itu dengan nama 'Tjong A Fie' lalu saya kirim ke web."
 * 4. Fetch       : "Saya terima paketnya!"
 * 5. Leaflet     : "Sip! Saya gambar markernya di peta sekarang."
 */

