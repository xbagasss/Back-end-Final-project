<?php
require_once __DIR__ . '/../bootstrap.php';
use App\Config\Database;

session_start();
if (!isset($_SESSION['user'])) { exit; }

$db = new Database();
$user_id = $_SESSION['user']['id'];

$stmt = $db->conn->prepare("
    SELECT date,
           SUM(calories) AS cal,
           SUM(protein) AS p,
           SUM(carbs) AS c,
           SUM(fat) AS f
    FROM nutrition_logs
    WHERE user_id = ?
    GROUP BY date
    ORDER BY date ASC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

$out = [
    'dates' => [],
    'calories' => [],
    'proteins' => [],
    'carbs' => [],
    'fats' => []
];

while ($r = $res->fetch_assoc()) {
    $out['dates'][] = $r['date'];
    $out['calories'][] = (int)$r['cal'];
    $out['proteins'][] = (float)$r['p'];
    $out['carbs'][] = (float)$r['c'];
    $out['fats'][] = (float)$r['f'];
}

header('Content-Type: application/json');
echo json_encode($out);
