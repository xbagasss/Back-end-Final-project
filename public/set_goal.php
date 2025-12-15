<?php
require_once __DIR__ . '/../bootstrap.php';

use App\Config\Database;

session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $goal = $_POST['goal'] ?? 'maintain';
    $validGoals = ['diet', 'maintain', 'muscle', 'bulking'];

    if (in_array($goal, $validGoals)) {
        $db = new Database();
        $userId = $_SESSION['user']['id'];

        // Update goal in database
        $stmt = $db->conn->prepare("UPDATE users SET goal = ? WHERE id = ?");
        $stmt->bind_param("si", $goal, $userId);
        
        if ($stmt->execute()) {
            // Update session data to reflect change immediately
            $_SESSION['user']['goal'] = $goal;
        }
    }
}

header("Location: dashboard.php");
exit;
