<?php
namespace App\Services;

use App\Config\Database;

class MealRecommendationService {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Calculate TDEE for a user
     */
    public function calculateDailyCalories($userId) {
        // Fetch user data
        $stmt = $this->db->conn->prepare("SELECT gender, age, height, activity_level, goal FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        
        // Fetch latest weight
        $wStmt = $this->db->conn->prepare("SELECT weight FROM weight_logs WHERE user_id = ? ORDER BY date DESC, created_at DESC LIMIT 1");
        $wStmt->bind_param("i", $userId);
        $wStmt->execute();
        $wRes = $wStmt->get_result();
        $weight = 60; // Default fallback
        if ($wRes->num_rows > 0) {
            $weight = $wRes->fetch_assoc()['weight'];
        }

        if (!$user) return 2000; // Fallback

        // Mifflin-St Jeor Equation
        // Men: 10W + 6.25H - 5A + 5
        // Women: 10W + 6.25H - 5A - 161
        $bmr = (10 * $weight) + (6.25 * $user['height']) - (5 * $user['age']);
        
        if ($user['gender'] === 'female') {
            $bmr -= 161;
        } else {
            $bmr += 5;
        }

        // Activity Multipliers
        $multipliers = [
            'sedentary' => 1.2,
            'light' => 1.375,
            'moderate' => 1.55,
            'active' => 1.725,
            'athlete' => 1.9
        ];

        $activity = $user['activity_level'] ?? 'moderate';
        $tdee = $bmr * ($multipliers[$activity] ?? 1.55);

        // Adjust based on Goal
        // Diet: -500 (approx 0.5kg loss/week)
        // Muscle: +300-500 (approx 0.25-0.5kg gain/week)
        $goal = $user['goal'] ?? 'maintain';
        if ($goal === 'diet') {
            $tdee -= 500;
        } elseif ($goal === 'muscle') {
            $tdee += 400; // Conservative surplus
        }

        // Safety floors
        if ($tdee < 1200) $tdee = 1200;

        return round($tdee);
    }

    /**
     * Generate plan for a specific date
     */
    public function generateDailyPlan($userId, $date) {
        $targetCalories = $this->calculateDailyCalories($userId);
        
        // Calorie Distribution
        // Breakfast 25%, Lunch 35%, Dinner 25%, Snack 15%
        $targets = [
            'breakfast' => $targetCalories * 0.25,
            'lunch' => $targetCalories * 0.35,
            'dinner' => $targetCalories * 0.25,
            'snack' => $targetCalories * 0.15
        ];

        // Clear existing plan for this date? (Optional, maybe just append or update. Let's clear to avoid dupes)
        $del = $this->db->conn->prepare("DELETE FROM meal_plans WHERE user_id = ? AND plan_date = ?");
        $del->bind_param("is", $userId, $date);
        $del->execute();

        foreach ($targets as $mealType => $calTarget) {
            $food = $this->getRandomFood($mealType);
            if ($food) {
                // Determine category mapping if needed, but we selecting by category directly
                // Logic: 
                // Breakfast -> category 'breakfast'
                // Lunch/Dinner -> category 'main'
                // Snack -> category 'snack'

                $servings = 1;
                if ($food['calories'] > 0) {
                    $servings = $calTarget / $food['calories'];
                    // Round to nearest 0.25
                    $servings = round($servings * 4) / 4;
                    if ($servings < 0.25) $servings = 0.5; // Minimum portion
                }

                // Insert into plan
                $ins = $this->db->conn->prepare("INSERT INTO meal_plans (user_id, plan_date, meal_type, food_id, servings, notes) VALUES (?, ?, ?, ?, ?, ?)");
                $notes = "Rekarendasi (" . round($calTarget) . " kcal)";
                $ins->bind_param("issids", $userId, $date, $mealType, $food['id'], $servings, $notes);
                $ins->execute();
            }
        }

        return true;
    }

    private function getRandomFood($mealType) {
        $category = 'main';
        if ($mealType === 'breakfast') $category = 'breakfast';
        if ($mealType === 'snack') $category = 'snack';
        
        // 1. Try to get a "Balanced" option first
        // Criteria: Protein > 15%, Fat < 40%, Carbs < 60% of total mass
        // We use a complex WHERE clause
        $sqlBalanced = "SELECT id, name, calories, protein, carbs, fat 
                        FROM foods 
                        WHERE category = ? 
                        AND (protein / (protein + carbs + fat)) > 0.15
                        AND (fat / (protein + carbs + fat)) < 0.40
                        AND (carbs / (protein + carbs + fat)) < 0.65
                        ORDER BY RAND() LIMIT 1";
                        
        $stmt = $this->db->conn->prepare($sqlBalanced);
        $stmt->bind_param("s", $category);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            return $res->fetch_assoc();
        }

        // 2. Fallback: If no balanced option (e.g. Snacks often fail), try 'High Protein' (>15%)
        $sqlProtein = "SELECT id, name, calories, protein, carbs, fat 
                       FROM foods 
                       WHERE category = ? 
                       AND (protein / (protein + carbs + fat)) > 0.15
                       ORDER BY RAND() LIMIT 1";
        $stmt = $this->db->conn->prepare($sqlProtein);
        $stmt->bind_param("s", $category);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            return $res->fetch_assoc();
        }
        
        // 3. Last Resort: Any food in category
        $sqlAny = "SELECT id, name, calories, protein, carbs, fat FROM foods WHERE category = ? ORDER BY RAND() LIMIT 1";
        $stmt = $this->db->conn->prepare($sqlAny);
        $stmt->bind_param("s", $category);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if ($res->num_rows > 0) {
            return $res->fetch_assoc();
        }

        // 4. Absolute Fallback
        return $this->db->conn->query("SELECT id, name, calories FROM foods ORDER BY RAND() LIMIT 1")->fetch_assoc();
    }
}
