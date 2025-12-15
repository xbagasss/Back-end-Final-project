<?php
require_once __DIR__ . '/../bootstrap.php';

// Load .env manual
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '=') !== false) {
            putenv(trim($line));
        }
    }
}

use App\Config\Database;

// Cek koneksi awal
$db = new Database();

header("Location: login.php");
exit;
