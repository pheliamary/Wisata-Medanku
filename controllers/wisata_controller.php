<?php
/**
 * controllers/wisata_controller.php v2.0 (normalisasi)
 */
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../models/Wisata.php';
requireLogin();

$wm     = new Wisata();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

function uploadGambar(array $file): string|false {
    if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > MAX_FILE_SIZE) return false;
    if (!in_array($file['type'], ALLOWED_TYPES)) return false;
    $ext  = pathinfo($file['name'], PATHINFO_EXTENSION);
    $nama = uniqid('wisata_', true) . '.' . $ext;
    if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
    return move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $nama) ? $nama : false;
}

switch ($action) {
    case 'create':
        $gambar = null;
        if (!empty($_FILES['gambar']['name'])) {
            $gambar = uploadGambar($_FILES['gambar']);
            if (!$gambar) { $_SESSION['flash']=['type'=>'error','msg'=>'Upload gambar gagal.']; redirect(BASE_URL.'views/admin/wisata_form.php'); }
        }
        $data = [
            'nama_wisata'     => sanitize($_POST['nama_wisata']),
            'deskripsi'       => sanitize($_POST['deskripsi']),
            'id_kategori'     => (int)$_POST['id_kategori'],
            'alamat'          => sanitize($_POST['alamat']),
            'id_kelurahan'    => (int)$_POST['id_kelurahan'] ?: null,
            'latitude'        => (float)$_POST['latitude'],
            'longitude'       => (float)$_POST['longitude'],
            'tiket_masuk'     => (int)$_POST['tiket_masuk'],
            'jam_operasional' => sanitize($_POST['jam_operasional']),
            'fasilitas'       => sanitize($_POST['fasilitas']),
            'rating'          => (float)$_POST['rating'],
            'status'          => sanitize($_POST['status'] ?? 'aktif'),
            'gambar'          => $gambar,   // ← TAMBAHKAN INI
        ];
        $_SESSION['flash'] = $wm->create($data)
            ? ['type'=>'success','msg'=>'Data wisata berhasil ditambahkan!']
            : ['type'=>'error',  'msg'=>'Gagal menyimpan data.'];
        redirect(BASE_URL.'views/admin/wisata.php');

case 'update':
    $id = (int)$_POST['id'];
    $gambar = null;                                      // ← ini yang kurang
    if (!empty($_FILES['gambar']['name'])) {
        $gambar = uploadGambar($_FILES['gambar']);
        if (!$gambar) {
            $_SESSION['flash'] = ['type'=>'error','msg'=>'Upload gambar gagal.'];
            redirect(BASE_URL.'views/admin/wisata_form.php?id='.$id);
        }
    }
    $data = [
        'nama_wisata'     => sanitize($_POST['nama_wisata']),
        'deskripsi'       => sanitize($_POST['deskripsi']),
        'id_kategori'     => (int)$_POST['id_kategori'],
        'alamat'          => sanitize($_POST['alamat']),
        'id_kelurahan'    => (int)$_POST['id_kelurahan'] ?: null,
        'latitude'        => (float)$_POST['latitude'],
        'longitude'       => (float)$_POST['longitude'],
        'tiket_masuk'     => (int)$_POST['tiket_masuk'],
        'jam_operasional' => sanitize($_POST['jam_operasional']),
        'fasilitas'       => sanitize($_POST['fasilitas']),
        'rating'          => (float)$_POST['rating'],
        'status'          => sanitize($_POST['status']),
        'gambar'          => $gambar,
    ];
    $_SESSION['flash'] = $wm->update($id, $data)
        ? ['type'=>'success','msg'=>'Data berhasil diperbarui!']
        : ['type'=>'error',  'msg'=>'Gagal memperbarui data.'];
    redirect(BASE_URL.'views/admin/wisata.php');    

    case 'delete':
        $id = (int)($_GET['id'] ?? 0);
        if ($wm->delete($id)) { $_SESSION['flash']=['type'=>'success','msg'=>'Data berhasil dihapus.']; }
        else                  { $_SESSION['flash']=['type'=>'error',  'msg'=>'Data tidak ditemukan.']; }
        redirect(BASE_URL.'views/admin/wisata.php');

    default:
        redirect(BASE_URL.'views/admin/wisata.php');
}
