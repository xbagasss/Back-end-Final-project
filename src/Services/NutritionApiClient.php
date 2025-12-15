<?php
namespace App\Services;

class NutritionApiClient {

    private $appId;
    private $appKey;

    public function __construct(){
        $this->appId = getenv("EDAMAM_ID");
        $this->appKey = getenv("EDAMAM_KEY");
    }

    // ============================================
    //  AUTO FORMAT INGREDIENT
    // ============================================
    private function autoFormat($input){
        $input = trim(strtolower($input));

        // 1. Jika user hanya mengetik "apple" → jadikan "1 apple"
        if (!preg_match('/\d/', $input)) {
            $input = "1 " . $input;
        }

        // 2. Mapping makanan Indonesia → list bahan internasional
        $indonesiaMap = [
            "nasi goreng" => ["1 cup cooked rice", "1 egg", "1 tbsp oil"],
            "mie goreng" => ["1 cup cooked noodles", "1 tbsp oil", "1 egg"],
            "bakso" => ["100g beef", "1 cup broth"],
            "ayam goreng" => ["100g chicken", "1 tbsp oil"],
            "soto ayam" => ["50g chicken", "1 cup broth", "1 potato"],
            "rendang" => ["100g beef", "1 tbsp coconut milk"]
        ];

        if (isset($indonesiaMap[$input])) {
            return $indonesiaMap[$input]; // return multiple items → ok untuk Edamam
        }

        // 3. Return single ingredient in array
        return [$input];
    }


    // ============================================
    //   FETCH NUTRITION (POST JSON)
    // ============================================
    public function fetchNutrition($input){

        $url = "https://api.edamam.com/api/nutrition-details"
             . "?app_id={$this->appId}&app_key={$this->appKey}";

        $ingredients = [];

        // 1. Handle Array Input
        if (is_array($input)) {
            $ingredients = $input;
        } 
        // 2. Handle Multi-line String
        elseif (strpos($input, "\n") !== false) {
            $lines = explode("\n", $input);
            $ingredients = array_map('trim', $lines);
            $ingredients = array_filter($ingredients); // Remove empty lines
        } 
        // 3. Handle Single Line (Auto Format)
        else {
            $ingredients = $this->autoFormat($input);
        }

        // Body JSON
        $body = [
            "title" => is_array($input) ? "Recipe Analysis" : $input,
            "ingr" => array_values($ingredients) // Ensure indexed array
        ];

        // Send POST JSON
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        // Handle error
        if ($response === false) {
            error_log("Edamam API Error (cURL): " . $error);
            return null;
        }

        if ($httpCode !== 200) {
            error_log("Edamam API Error (HTTP $httpCode): " . $response);
            return null;
        }

        $data = json_decode($response, true);

        // Manual Aggregation if totalNutrients is missing
        if ($data && !isset($data['totalNutrients']) && isset($data['ingredients'])) {
            $totalNutrients = [];
            $totalCalories = 0;
            $totalWeight = 0;

            foreach ($data['ingredients'] as $ing) {
                if (isset($ing['parsed'][0])) {
                    $parsed = $ing['parsed'][0];
                    $totalWeight += $parsed['weight'] ?? 0;
                    $totalCalories += $parsed['nutrients']['ENERC_KCAL']['quantity'] ?? 0;

                    foreach ($parsed['nutrients'] as $code => $nutrient) {
                        if (!isset($totalNutrients[$code])) {
                            $totalNutrients[$code] = $nutrient;
                        } else {
                            $totalNutrients[$code]['quantity'] += $nutrient['quantity'];
                        }
                    }
                }
            }

            $data['totalNutrients'] = $totalNutrients;
            $data['calories'] = $totalCalories;
            $data['totalWeight'] = $totalWeight;
        }

        return $data;
    }
}
