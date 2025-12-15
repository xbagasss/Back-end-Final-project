<?php
require_once __DIR__ . '/../bootstrap.php';
use App\Config\Database;
header('Content-Type: application/json');
session_start();
if (!isset($_SESSION['user'])) { echo json_encode([]); exit; }
$user_id = $_SESSION['user']['id'];
$date = $_GET['date'] ?? date('Y-m-d');
$db = new Database();
$stmt = $db->conn->prepare("SELECT id, food_name, calories, protein, carbs, fat FROM nutrition_logs WHERE user_id = ? AND date = ? ORDER BY id DESC");
$stmt->bind_param("is", $user_id, $date);
$stmt->execute();
$res = $stmt->get_result();
$out = [];
while($r = $res->fetch_assoc()){
    $out[] = $r;
}
echo json_encode($out);
