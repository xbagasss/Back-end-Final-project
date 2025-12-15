<?php
namespace App\Models;

use App\Config\Database;

class NutritionLog
{
    private $db;

    public function __construct(Database $db)
    {
        $this->db = $db->conn;
    }

    public function addLog($user_id, $food, $calories)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO nutrition_logs (user_id, food_name, calories, date)
             VALUES (?, ?, ?, CURDATE())"
        );
        $stmt->bind_param("isi", $user_id, $food, $calories);
        return $stmt->execute();
    }

    public function getWeekly($user_id)
    {
        $stmt = $this->db->prepare(
            "SELECT date, SUM(calories) as total
             FROM nutrition_logs
             WHERE user_id = ?
             GROUP BY date
             ORDER BY date ASC"
        );
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result();
    }
}
