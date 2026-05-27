<?php
/**
 * models/Admin.php
 * Model untuk autentikasi admin
 */

require_once __DIR__ . '/../config/koneksi.php';

class Admin {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function findByUsername(string $username): array|false {
        $stmt = $this->db->prepare("SELECT * FROM admin WHERE username = :username");
        $stmt->execute([':username' => $username]);
        return $stmt->fetch();
    }

    public function login(string $username, string $password): array|false {
        $admin = $this->findByUsername($username);
        if ($admin && password_verify($password, $admin['password'])) {
            return $admin;
        }
        return false;
    }

    public function changePassword(int $id, string $newPassword): bool {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("UPDATE admin SET password = :pass WHERE id = :id");
        return $stmt->execute([':pass' => $hash, ':id' => $id]);
    }
}
