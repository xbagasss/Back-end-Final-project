<?php
namespace App\Services;

use App\Config\Database;

class AuthService {

    private $db;

    public function __construct(Database $db){
        $this->db = $db;
    }

    public function register($name, $email, $password){
        $check = $this->db->conn->prepare("SELECT id FROM users WHERE email=?");
        $check->bind_param("s", $email);
        $check->execute();

        if ($check->get_result()->fetch_assoc()) {
            return false;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->db->conn->prepare(
            "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')"
        );
        $stmt->bind_param("sss", $name, $email, $hash);

        return $stmt->execute();
    }

    public function login($email, $password){
        $stmt = $this->db->conn->prepare("SELECT * FROM users WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();

        $user = $stmt->get_result()->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            unset($user['password']); // keamanan
            return $user;
        }

        return false;
    }
}
