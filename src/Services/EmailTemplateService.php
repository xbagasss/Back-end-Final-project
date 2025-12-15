<?php
namespace App\Services;

class EmailTemplateService {
    
    public function generateAnalysisReport($userEmail, $weeklyData, $topFoods, $insights) {
        $avgCal = 0;
        if (!empty($weeklyData)) {
            $avgCal = array_sum(array_column($weeklyData, 'cal')) / count($weeklyData);
        }
        
        $topFoodHtml = '';
        foreach ($topFoods as $food) {
            $topFoodHtml .= "<li><strong>" . htmlspecialchars($food['food_name']) . "</strong>: " . $food['total'] . "x</li>";
        }

        $insightHtml = '';
        if (empty($insights)) {
            $insightHtml = '<div style="background-color: #d1fae5; color: #065f46; padding: 15px; border-radius: 8px; margin-bottom: 20px;">✅ Pola makan Anda stabil minggu ini. Pertahankan!</div>';
        } else {
            $insightHtml = '<div style="background-color: #fff1f2; color: #9f1239; padding: 15px; border-radius: 8px; margin-bottom: 20px;">';
            foreach ($insights as $insight) {
                $insightHtml .= "<p style='margin: 5px 0;'>$insight</p>";
            }
            $insightHtml .= '</div>';
        }

        // Calculate total macros for the week to show distribution
        $totalP = array_sum(array_column($weeklyData, 'p'));
        $totalC = array_sum(array_column($weeklyData, 'c'));
        $totalF = array_sum(array_column($weeklyData, 'f'));

        // Pre-format numbers
        $avgCalFormatted = number_format($avgCal);
        $totalPFormatted = number_format($totalP);

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Analisis Mingguan</title>
</head>
<body style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; line-height: 1.6; color: #334155; background-color: #f1f5f9; margin: 0; padding: 0; -webkit-text-size-adjust: 100%;">
    <div style="max-width: 600px; margin: 40px auto; background-color: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);">
        
        <!-- Header -->
        <div style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); padding: 40px 30px; text-align: center; color: white;">
            <div style="font-size: 28px; font-weight: 800; margin-bottom: 8px;">JawaHealthy</div>
            <p style="margin: 0; opacity: 0.9; font-size: 16px; font-weight: 500;">Weekly Nutrition Report</p>
        </div>

        <!-- Content -->
        <div style="padding: 40px 30px;">
            <p style="font-size: 16px; margin-bottom: 24px;">Hi <strong>$userEmail</strong>,</p>
            <p style="color: #64748b; margin-bottom: 32px;">Ini adalah ringkasan progres nutrisi dan kesehatanmu minggu ini. Yuk lihat pencapaianmu!</p>

            <!-- Stats Grid -->
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-bottom: 32px;">
                <div style="background: #f8fafc; padding: 20px; border-radius: 12px; text-align: center; border: 1px solid #e2e8f0;">
                    <div style="font-size: 12px; color: #64748b; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 8px;">Avg Calories</div>
                    <div style="font-size: 24px; font-weight: 800; color: #2563eb;">
                        $avgCalFormatted<span style="font-size: 14px; font-weight: 600; color: #94a3b8; margin-left: 2px;">kcal</span>
                    </div>
                </div>
                <div style="background: #f8fafc; padding: 20px; border-radius: 12px; text-align: center; border: 1px solid #e2e8f0;">
                    <div style="font-size: 12px; color: #64748b; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 8px;">Total Protein</div>
                    <div style="font-size: 24px; font-weight: 800; color: #059669;">
                        $totalPFormatted<span style="font-size: 14px; font-weight: 600; color: #94a3b8; margin-left: 2px;">g</span>
                    </div>
                </div>
            </div>

            <!-- Insights -->
            <div style="margin-bottom: 32px;">
                <h3 style="font-size: 18px; font-weight: 700; color: #1e293b; margin-bottom: 16px; display: flex; align-items: center;">
                    <span style="font-size: 20px; margin-right: 8px;">💡</span> Insight Mingguan
                </h3>
                $insightHtml
            </div>

            <!-- Top Foods -->
            <div style="margin-bottom: 40px;">
                <h3 style="font-size: 18px; font-weight: 700; color: #1e293b; margin-bottom: 16px; display: flex; align-items: center;">
                    <span style="font-size: 20px; margin-right: 8px;">🏆</span> Makanan Favoritmu
                </h3>
                <ul style="list-style: none; padding: 0; margin: 0; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;">
                    $topFoodHtml
                </ul>
            </div>

            <div style="text-align: center;">
                <a href="http://localhost/yourproject/public/dashboard.php" style="
                    background-color: #2563eb; 
                    color: white; 
                    padding: 16px 32px; 
                    text-decoration: none; 
                    border-radius: 99px; 
                    font-weight: 700; 
                    font-size: 16px;
                    display: inline-block;
                    box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2);
                    transition: transform 0.2s;
                ">Buka Dashboard Saya &rarr;</a>
            </div>
        </div>

        <!-- Footer -->
        <div style="background-color: #f8fafc; padding: 30px; text-align: center; color: #94a3b8; font-size: 12px; border-top: 1px solid #e2e8f0;">
            <p style="margin: 0 0 8px;">&copy; " . date('Y') . " JawaHealthy App. Dibuat dengan ❤️ untuk hidup sehat.</p>
            <p style="margin: 0;">Jangan lupa minum air putih hari ini!</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
}
