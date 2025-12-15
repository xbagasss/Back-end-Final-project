<?php
namespace App\Services;

use App\Config\Database;

class AnalyticsService {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getWeeklyTotals($userId) {
        $stmt = $this->db->conn->prepare("
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
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $res = $stmt->get_result();

        $data = [];
        while ($row = $res->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }

    public function getTopFoods($userId, $limit = 5) {
        $stmt = $this->db->conn->prepare("
            SELECT food_name, COUNT(*) AS total
            FROM nutrition_logs
            WHERE user_id = ?
            GROUP BY food_name
            ORDER BY total DESC
            LIMIT ?
        ");
        $stmt->bind_param("ii", $userId, $limit);
        $stmt->execute();
        $res = $stmt->get_result();

        $data = [];
        while ($row = $res->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }

    public function getHistory($userId, $limit = 20) {
        $stmt = $this->db->conn->prepare("
            SELECT date, food_name, calories, protein, carbs, fat
            FROM nutrition_logs
            WHERE user_id = ?
            ORDER BY date DESC, id DESC
            LIMIT ?
        ");
        $stmt->bind_param("ii", $userId, $limit);
        $stmt->execute();
        $res = $stmt->get_result();

        $data = [];
        while ($row = $res->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }
}
