<?php
namespace App\Services;

use App\Config\Database;

class RecommendationService {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getSmartRecommendations($userId, $goal = 'maintain') {
        // 1. Get today's nutrition totals
        $today = date('Y-m-d');
        $stmt = $this->db->conn->prepare("
            SELECT SUM(calories) as cal, SUM(protein) as protein, SUM(carbs) as carbs, SUM(fat) as fat 
            FROM nutrition_logs 
            WHERE user_id = ? AND date = ?
        ");
        $stmt->bind_param("is", $userId, $today);
        $stmt->execute();
        $current = $stmt->get_result()->fetch_assoc();

        // Default values if null
        $current['cal'] = $current['cal'] ?? 0;
        $current['protein'] = $current['protein'] ?? 0;
        $current['carbs'] = $current['carbs'] ?? 0;
        $current['fat'] = $current['fat'] ?? 0;

        // 2. Determine nutritional gaps/excesses based on GOAL
        $recommendations = [];
        $reason = "";

        if ($goal === 'bulking') {
            // BULKING LOGIC: Focus on high calorie, high protein, surplus
            if ($current['cal'] < 3000) {
                $reason = "Target Bulking: Anda butuh surplus kalori! Pilih makanan padat energi.";
                $recommendations = [
                    ['name' => 'Nasi Padang + Rendang', 'desc' => 'Kalori & protein tinggi untuk surplus'],
                    ['name' => 'Alpukat + Susu Kental Manis', 'desc' => 'Lemak sehat & kalori tinggi'],
                    ['name' => 'Steak Daging Sapi', 'desc' => 'Protein & lemak untuk massa otot'],
                    ['name' => 'Pasta Creamy', 'desc' => 'Karbohidrat padat energi']
                ];
            } else {
                $reason = "Target kalori bulking tercapai! Fokus protein untuk otot.";
                $recommendations = [
                    ['name' => 'Whey Protein / Susu', 'desc' => 'Protein cepat serap'],
                    ['name' => 'Dada Ayam Bakar', 'desc' => 'Protein murni tanpa lemak berlebih'],
                    ['name' => 'Telur Rebus (3-4 butir)', 'desc' => 'Protein & lemak sehat'],
                    ['name' => 'Kacang-kacangan', 'desc' => 'Camilan protein tinggi']
                ];
            }
        } elseif ($goal === 'muscle') {
            // LEAN MUSCLE LOGIC: High protein, moderate carb
             if ($current['protein'] < 100) {
                $reason = "Target Muscle: Protein adalah kunci! Tingkatkan asupan protein.";
                $recommendations = [
                    ['name' => 'Dada Ayam Rebus', 'desc' => 'Protein tinggi, rendah lemak'],
                    ['name' => 'Ikan Tuna/Salmon', 'desc' => 'Omega-3 & protein berkualitas'],
                    ['name' => 'Putih Telur', 'desc' => 'Protein murni rendah kalori'],
                    ['name' => 'Tempe Bakar', 'desc' => 'Protein nabati sehat']
                ];
            } else {
                $reason = "Protein cukup. Jaga karbohidrat untuk energi latihan.";
                $recommendations = [
                    ['name' => 'Ubi / Kentang Rebus', 'desc' => 'Karbohidrat kompleks'],
                    ['name' => 'Pisang', 'desc' => 'Energi cepat sebelum latihan'],
                    ['name' => 'Oatmeal', 'desc' => 'Energi tahan lama'],
                    ['name' => 'Roti Gandum', 'desc' => 'Serat & karbohidrat sehat']
                ];
            }
        } elseif ($goal === 'diet') {
            // DIET LOGIC: Low calorie, high volume, high protein
            if ($current['cal'] > 1600) {
                $reason = "Hati-hati, kalori sudah mendekati batas diet. Pilih makanan sangat rendah kalori.";
                $recommendations = [
                    ['name' => 'Salad Sayur Tanpa Dressing', 'desc' => 'Hampir 0 kalori, mengenyangkan'],
                    ['name' => 'Sup Bening', 'desc' => 'Volume besar, kalori kecil'],
                    ['name' => 'Putih Telur Rebus', 'desc' => 'Protein murni tanpa lemak'],
                    ['name' => 'Agar-agar Tawar', 'desc' => 'Camilan 0 kalori']
                ];
            } else {
                $reason = "Target Diet: Pilih makanan mengenyangkan tapi rendah kalori.";
                $recommendations = [
                    ['name' => 'Pepaya / Semangka', 'desc' => 'Buah berair yang mengenyangkan'],
                    ['name' => 'Tumis Sayuran (Sedikit Minyak)', 'desc' => 'Serat tinggi'],
                    ['name' => 'Ikan Pepes', 'desc' => 'Protein sehat tanpa goreng'],
                    ['name' => 'Tahu Rebus/Kukus', 'desc' => 'Protein nabati ringan']
                ];
            }
        } else {
            // MAINTAIN LOGIC (Default)
            // ... (Keep existing logic or simplify)
            if ($current['protein'] < 40) {
                 $reason = "Asupan protein Anda hari ini masih rendah (< 40g).";
                 $recommendations = [
                    ['name' => 'Dada Ayam Rebus', 'desc' => 'Tinggi protein, rendah lemak'],
                    ['name' => 'Telur Rebus', 'desc' => 'Sumber protein praktis & sehat'],
                    ['name' => 'Ikan Panggang', 'desc' => 'Kaya Omega-3 & protein'],
                    ['name' => 'Tempe/Tahu Bacem', 'desc' => 'Protein nabati serat tinggi']
                ];
            } elseif ($current['carbs'] > 250) {
                $reason = "Asupan karbohidrat sudah cukup tinggi. Coba alternatif rendah karbo.";
                $recommendations = [
                    ['name' => 'Salad Sayur', 'desc' => 'Kaya serat, rendah kalori'],
                    ['name' => 'Tumis Brokoli', 'desc' => 'Vitamin tinggi, rendah karbo'],
                    ['name' => 'Sup Ayam Bening', 'desc' => 'Mengenyangkan tanpa banyak karbo'],
                    ['name' => 'Alpukat', 'desc' => 'Lemak sehat, rendah gula']
                ];
            } elseif ($current['fat'] > 70) {
                $reason = "Asupan lemak sudah tinggi. Pilih makanan rendah lemak.";
                $recommendations = [
                    ['name' => 'Buah-buahan Segar', 'desc' => 'Apel, Pir, atau Pepaya'],
                    ['name' => 'Oatmeal', 'desc' => 'Serat tinggi untuk pencernaan'],
                    ['name' => 'Ubi Rebus', 'desc' => 'Karbohidrat kompleks sehat'],
                    ['name' => 'Yogurt Rendah Lemak', 'desc' => 'Probiotik untuk usus']
                ];
            } else {
                $reason = "Nutrisi Anda seimbang! Berikut beberapa pilihan sehat untuk menjaga energi.";
                $recommendations = [
                    ['name' => 'Smoothie Buah', 'desc' => 'Vitamin & energi instan'],
                    ['name' => 'Sandwich Gandum', 'desc' => 'Karbo kompleks & serat'],
                    ['name' => 'Kacang Almond', 'desc' => 'Lemak sehat untuk jantung'],
                    ['name' => 'Jus Wortel Jeruk', 'desc' => 'Vitamin C & A tinggi']
                ];
            }
        }

        return [
            'reason' => $reason,
            'foods' => $recommendations
        ];
    }

    // Helper methods removed as they are no longer needed for DB queries
}
