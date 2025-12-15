<?php
require_once __DIR__ . '/../../bootstrap.php';
use App\Config\Database;

session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

$db = new Database();

$food_id = intval($_GET['id']);
$user_id = $_SESSION['user']['id'];

// Ambil data makanan
$food = $db->conn->query("SELECT * FROM foods WHERE id = $food_id")->fetch_assoc();

if (!$food) exit("Food not found");

// Insert ke logs
$stmt = $db->conn->prepare("
    INSERT INTO nutrition_logs 
    (user_id, food_id, food_name, calories, protein, carbs, fat, date)
    VALUES (?, ?, ?, ?, ?, ?, ?, CURDATE())
");

$stmt->bind_param(
    "iisiddd",
    $user_id,
    $food_id,
    $food['name'],
    $food['calories'],
    $food['protein'],
    $food['carbs'],
    $food['fat']
);

$stmt->execute();

header("Location: ../dashboard.php");
exit;
