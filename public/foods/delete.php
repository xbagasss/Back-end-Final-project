<?php
require_once __DIR__ . '/../../bootstrap.php';

use App\Config\Database;

session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

$db = new Database();
$id = intval($_GET['id']);

$db->conn->query("DELETE FROM foods WHERE id = $id");

header("Location: index.php");
exit;
