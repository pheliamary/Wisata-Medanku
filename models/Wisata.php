<?php
/**
 * models/Wisata.php — v2.0 (normalisasi)
 */
require_once __DIR__ . '/../config/koneksi.php';

class Wisata {
    private PDO $db;

    public function __construct() { $this->db = getDB(); }

    /** Filter publik: search, kategori, kecamatan, rating_min */
    public function getAll(array $f = []): array {
        $w = ["v.status = 'aktif'"]; $p = [];
        if (!empty($f['kategori']))   { $w[] = 'v.kategori = :kat';        $p[':kat']  = $f['kategori']; }
        if (!empty($f['kecamatan']))  { $w[] = 'v.kecamatan = :kec';       $p[':kec']  = $f['kecamatan']; }
        if (!empty($f['kelurahan']))  { $w[] = 'v.kelurahan = :kel';       $p[':kel']  = $f['kelurahan']; }
        if (!empty($f['rating_min'])) { $w[] = 'v.rating >= :rmin';        $p[':rmin'] = (float)$f['rating_min']; }
        if (!empty($f['tiket']))      {
            if ($f['tiket'] === 'gratis') { $w[] = 'v.tiket_masuk = 0'; }
            if ($f['tiket'] === 'berbayar') { $w[] = 'v.tiket_masuk > 0'; }
        }
        if (!empty($f['search'])) {
            $w[] = '(v.nama_wisata ILIKE :q OR v.deskripsi ILIKE :q OR v.alamat ILIKE :q OR v.kelurahan ILIKE :q OR v.kecamatan ILIKE :q)';
            $p[':q'] = '%' . $f['search'] . '%';
        }
        $order = match($f['sort'] ?? '') {
            'rating'  => 'v.rating DESC',
            'tiket'   => 'v.tiket_masuk ASC',
            'nama'    => 'v.nama_wisata ASC',
            default   => 'v.nama_wisata ASC'
        };
        $sql = "SELECT * FROM v_wisata v WHERE " . implode(' AND ', $w) . " ORDER BY $order";
        $stmt = $this->db->prepare($sql); $stmt->execute($p);
        return $stmt->fetchAll();
    }

    public function getAllAdmin(string $search='', string $kategori='', string $kecamatan=''): array {
        $w = ['1=1']; $p = [];
        if ($search)   { $w[] = '(v.nama_wisata ILIKE :q OR v.alamat ILIKE :q)'; $p[':q'] = "%$search%"; }
        if ($kategori) { $w[] = 'v.kategori = :kat'; $p[':kat'] = $kategori; }
        if ($kecamatan){ $w[] = 'v.kecamatan = :kec'; $p[':kec'] = $kecamatan; }
        $sql = "SELECT * FROM v_wisata v WHERE " . implode(' AND ', $w) . " ORDER BY v.id DESC";
        $stmt = $this->db->prepare($sql); $stmt->execute($p);
        return $stmt->fetchAll();
    }

    public function getById(int $id): array|false {
        $stmt = $this->db->prepare("SELECT * FROM v_wisata WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function getForMap(): array {
        $stmt = $this->db->query(
            "SELECT v.id, v.nama_wisata, v.kategori, v.kategori_ikon, v.kategori_warna,
                    v.alamat, v.kelurahan, v.kecamatan, v.latitude, v.longitude,
                    v.tiket_masuk, v.jam_operasional, v.fasilitas, v.rating
             FROM v_wisata v WHERE v.status = 'aktif' ORDER BY v.kategori, v.nama_wisata"
        );
        return $stmt->fetchAll();
    }

    public function getStats(): array {
        $s = [];
        $s['total']   = $this->db->query("SELECT COUNT(*) FROM wisata")->fetchColumn();
        $s['aktif']   = $this->db->query("SELECT COUNT(*) FROM wisata WHERE status='aktif'")->fetchColumn();
        $s['nonaktif']= $this->db->query("SELECT COUNT(*) FROM wisata WHERE status='nonaktif'")->fetchColumn();
        $s['per_kategori'] = $this->db->query(
            "SELECT k.nama AS kategori, COUNT(w.id) AS jumlah
             FROM kategori_wisata k LEFT JOIN wisata w ON w.id_kategori = k.id
             GROUP BY k.nama ORDER BY jumlah DESC"
        )->fetchAll();
        return $s;
    }

public function create(array $d): bool {
    $stmt = $this->db->prepare(
        "INSERT INTO wisata (nama_wisata,deskripsi,id_kategori,alamat,id_kelurahan,
          latitude,longitude,tiket_masuk,jam_operasional,fasilitas,rating,status,gambar)
         VALUES (:nm,:desc,:kat,:adr,:kel,:lat,:lng,:tkt,:jam,:fas,:rtg,:sts,:gmb)"
    );
    return $stmt->execute([
        ':nm'=>$d['nama_wisata'],':desc'=>$d['deskripsi'],':kat'=>$d['id_kategori'],
        ':adr'=>$d['alamat'],':kel'=>$d['id_kelurahan']?:null,':lat'=>$d['latitude'],
        ':lng'=>$d['longitude'],':tkt'=>$d['tiket_masuk']??0,':jam'=>$d['jam_operasional'],
        ':fas'=>$d['fasilitas'],':rtg'=>$d['rating']??0,':sts'=>$d['status']??'aktif',
        ':gmb'=>$d['gambar']??null,
    ]);
}

public function update(int $id, array $d): bool {
    // Jika ada gambar baru, update gambar; jika tidak, biarkan tetap
    if (!empty($d['gambar'])) {
        $sql = "UPDATE wisata SET nama_wisata=:nm, deskripsi=:desc, id_kategori=:kat,
                  alamat=:adr, id_kelurahan=:kel, latitude=:lat, longitude=:lng,
                  tiket_masuk=:tkt, jam_operasional=:jam, fasilitas=:fas,
                  rating=:rtg, status=:sts, gambar=:gmb
                WHERE id=:id";
        $params = [
            ':nm'=>$d['nama_wisata'],':desc'=>$d['deskripsi'],':kat'=>$d['id_kategori'],
            ':adr'=>$d['alamat'],':kel'=>$d['id_kelurahan']?:null,':lat'=>$d['latitude'],
            ':lng'=>$d['longitude'],':tkt'=>$d['tiket_masuk']??0,':jam'=>$d['jam_operasional'],
            ':fas'=>$d['fasilitas'],':rtg'=>$d['rating']??0,':sts'=>$d['status'],
            ':gmb'=>$d['gambar'],':id'=>$id,
        ];
    } else {
        $sql = "UPDATE wisata SET nama_wisata=:nm, deskripsi=:desc, id_kategori=:kat,
                  alamat=:adr, id_kelurahan=:kel, latitude=:lat, longitude=:lng,
                  tiket_masuk=:tkt, jam_operasional=:jam, fasilitas=:fas,
                  rating=:rtg, status=:sts
                WHERE id=:id";
        $params = [
            ':nm'=>$d['nama_wisata'],':desc'=>$d['deskripsi'],':kat'=>$d['id_kategori'],
            ':adr'=>$d['alamat'],':kel'=>$d['id_kelurahan']?:null,':lat'=>$d['latitude'],
            ':lng'=>$d['longitude'],':tkt'=>$d['tiket_masuk']??0,':jam'=>$d['jam_operasional'],
            ':fas'=>$d['fasilitas'],':rtg'=>$d['rating']??0,':sts'=>$d['status'],':id'=>$id,
        ];
    }
    return $this->db->prepare($sql)->execute($params);
}

    public function delete(int $id): bool {
        return $this->db->prepare("DELETE FROM wisata WHERE id=:id")->execute([':id'=>$id]);
    }

    // Lookup helpers untuk dropdown form
    public function getKategoriList(): array {
        return $this->db->query("SELECT id, nama, ikon FROM kategori_wisata ORDER BY nama")->fetchAll();
    }
    public function getKecamatanList(): array {
        return $this->db->query("SELECT id, nama FROM kecamatan ORDER BY nama")->fetchAll();
    }
    public function getKelurahanList(): array {
        return $this->db->query(
            "SELECT kel.id, kel.nama, kec.nama AS kecamatan
             FROM kelurahan kel JOIN kecamatan kec ON kec.id=kel.id_kecamatan ORDER BY kec.nama, kel.nama"
        )->fetchAll();
    }
    public function getDistinctKategori(): array {
        return $this->db->query("SELECT DISTINCT nama FROM kategori_wisata ORDER BY nama")->fetchAll(PDO::FETCH_COLUMN);
    }
    public function getDistinctKecamatan(): array {
        return $this->db->query(
            "SELECT DISTINCT kec.nama FROM kecamatan kec
             JOIN kelurahan kel ON kel.id_kecamatan=kec.id
             JOIN wisata w ON w.id_kelurahan=kel.id
             WHERE w.status='aktif' ORDER BY kec.nama"
        )->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getDistinctKelurahan(string $kecamatan = ''): array {
        $sql = "SELECT DISTINCT kel.nama, kec.nama AS kecamatan
                FROM kelurahan kel
                JOIN kecamatan kec ON kec.id = kel.id_kecamatan
                JOIN wisata w ON w.id_kelurahan = kel.id
                WHERE w.status = 'aktif'";
        $params = [];
        if ($kecamatan) {
            $sql .= " AND kec.nama = :kec";
            $params[':kec'] = $kecamatan;
        }
        $sql .= " ORDER BY kec.nama, kel.nama";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
