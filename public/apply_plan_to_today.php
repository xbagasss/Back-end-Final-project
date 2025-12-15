<?php
require_once __DIR__ . '/../bootstrap.php';
use App\Config\Database;

session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: meal_plan.php");
    exit;
}

$db = new Database();
$user_id = $_SESSION['user']['id'];
$date = $_POST['date'] ?? date('Y-m-d');

// Fetch plans for the date
$stmt = $db->conn->prepare("
    SELECT mp.food_id, mp.servings, f.name, f.calories, f.protein, f.carbs, f.fat
    FROM meal_plans mp
    JOIN foods f ON f.id = mp.food_id
    WHERE mp.user_id = ? AND mp.plan_date = ?
");
$stmt->bind_param("is", $user_id, $date);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    $ins = $db->conn->prepare("
        INSERT INTO nutrition_logs (user_id, food_id, food_name, calories, protein, carbs, fat, date)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    while ($row = $res->fetch_assoc()) {
        $cal = $row['calories'] * $row['servings'];
        $pro = $row['protein'] * $row['servings'];
        $car = $row['carbs'] * $row['servings'];
        $fat = $row['fat'] * $row['servings'];

        $ins->bind_param("iisiddds", 
            $user_id, 
            $row['food_id'], 
            $row['name'], 
            $cal, 
            $pro, 
            $car, 
            $fat, 
            $date
        );
        $ins->execute();
    }
}

header("Location: dashboard.php"); // Redirect to dashboard after applying
exit;
