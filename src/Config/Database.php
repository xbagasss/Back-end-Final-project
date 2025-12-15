<?php
namespace App\Config;

class Database {
    /**
     * @var \mysqli
     */
    public $conn;

    public function __construct(){

        // ENV fallback
        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASS') ?: '';
        $name = getenv('DB_NAME') ?: 'healthy_food_app';
        $port = getenv('DB_PORT') ?: 3306;

        // Koneksi MySQL yang benar
        $this->conn = new \mysqli($host, $user, $pass, $name, (int)$port);

        if ($this->conn->connect_error) {
            die("DB Error: " . $this->conn->connect_error);
        }

        $this->conn->set_charset("utf8mb4");
    }
}
