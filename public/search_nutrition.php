<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../src/Services/NutritionApiClient.php';
use App\Services\NutritionApiClient;
use App\Config\Database;

session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$db = new Database();
$client = new NutritionApiClient();
$results = null;
$error = null;
$success = null;
$user_id = $_SESSION['user']['id'];

// Handle Add to Log
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_log') {
    $name = trim($_POST['food_name']);
    $cals = (int)$_POST['calories'];
    $prot = (float)$_POST['protein'];
    $carbs = (float)$_POST['carbs'];
    $fat = (float)$_POST['fat'];
    $date = date('Y-m-d');
    $time = date('H:i:s');
    
    // Insert into nutrition_logs
    $stmt = $db->conn->prepare("INSERT INTO nutrition_logs (user_id, food_name, calories, protein, carbs, fat, date) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isdddds", $user_id, $name, $cals, $prot, $carbs, $fat, $date);
    
    if ($stmt->execute()) {
        $success = "Berhasil menambahkan <strong>" . htmlspecialchars($name) . "</strong> ke log harian!";
    } else {
        $error = "Gagal menyimpan data.";
    }
}

// Handle Search
$query = $_GET['q'] ?? '';
if ($query) {
    $startTime = microtime(true);
    $status = 200; // Assume success initially
    
    try {
        $apiData = $client->fetchNutrition($query);
        if ($apiData && isset($apiData['calories'])) {
            $results = $apiData;
        } else {
            $error = "Makanan tidak ditemukan. Coba gunakan bahasa Inggris (contoh: '1 cup rice').";
            $status = 404;
        }
    } catch (Exception $e) {
        $error = "Terjadi kesalahan koneksi ke API.";
        $status = 500;
    }

    $duration = microtime(true) - $startTime;
    
    $duration = microtime(true) - $startTime;
    
    // Check for recent duplicate (debounce 10s)
    $stmt = $db->conn->prepare("SELECT id FROM api_logs WHERE user_id = ? AND query = ? AND created_at >= NOW() - INTERVAL 10 SECOND");
    $stmt->bind_param("is", $user_id, $query);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        // Log API Call
        $stmt = $db->conn->prepare("INSERT INTO api_logs (user_id, endpoint, query, status, response_time) VALUES (?, 'nutrition-details', ?, ?, ?)");
        $stmt->bind_param("isid", $user_id, $query, $status, $duration);
        $stmt->execute();
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Search Nutrition — SmartHealthy</title>
  <link rel="stylesheet" href="dashboard.css">
  <style>
    .search-hero {
        background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
        padding: 40px 20px;
        border-radius: 20px;
        color: white;
        text-align: center;
        margin-bottom: 30px;
        box-shadow: 0 10px 20px rgba(99, 102, 241, 0.2);
    }
    .search-input-group {
        display: flex;
        max-width: 500px;
        margin: 20px auto 0;
        background: white;
        padding: 6px;
        border-radius: 99px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .search-input {
        flex: 1;
        border: none;
        padding: 12px 20px;
        font-size: 16px;
        outline: none;
        border-radius: 99px;
    }
    .search-btn {
        background: #111;
        color: white;
        border: none;
        padding: 10px 24px;
        border-radius: 99px;
        font-weight: 600;
        cursor: pointer;
        transition: transform 0.2s;
    }
    .search-btn:hover { transform: scale(1.05); }

    .result-card {
        background: white;
        border-radius: 24px;
        padding: 30px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05);
        max-width: 600px;
        margin: 0 auto;
        text-align: center;
    }
    .macro-row {
        display: flex;
        justify-content: center;
        gap: 24px;
        margin: 24px 0;
    }
    .macro-item {
        background: #f8fafc;
        padding: 15px 20px;
        border-radius: 16px;
        min-width: 80px;
    }
    .macro-val { font-size: 18px; font-weight: 700; color: #1e293b; }
    .macro-lbl { font-size: 12px; color: #64748b; text-transform: uppercase; margin-top: 4px; }
  </style>
</head>
<body>
<header class="topbar">
    <div class="brand">SmartHealthy</div>
    <nav>
      <a href="dashboard.php" class="nav-link">Dashboard</a>
      <a href="search_nutrition.php" class="nav-link active" style="font-weight:700;">Search</a>
      <a href="analytics.php" class="nav-link">Analytics</a>
      <a href="profile.php" class="nav-link">Profile</a>
      <a href="logout.php" class="nav-link logout">Logout</a>
    </nav>
</header>

<main class="container">
    
    <div class="search-hero">
        <h1 style="color:white; margin-bottom: 8px;">Cari Nutrisi Makanan</h1>
        <p style="opacity: 0.9;">Analisis kalori dan makronutrisi makanan apapun secara instan.</p>
        
        <form method="get" class="search-input-group">
            <input type="text" name="q" class="search-input" placeholder="Contoh: 1 cup rice, 2 eggs..." value="<?= htmlspecialchars($query) ?>" required>
            <button type="submit" class="search-btn">Analisis</button>
        </form>
    </div>

    <?php if ($success): ?>
        <div class="card" style="background: #ecfdf5; border-color: #10b981; color: #065f46; text-align: center;">
            <div style="font-size: 24px; margin-bottom:8px;">✅</div>
            <?= $success ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="card" style="background: #fef2f2; border-color: #ef4444; color: #991b1b; text-align: center;">
            <div style="font-size: 24px; margin-bottom:8px;">❌</div>
            <?= $error ?>
        </div>
    <?php endif; ?>

    <?php if ($results): ?>
        <?php 
            // Extract macros safely
            $nutrients = $results['totalNutrients'] ?? [];
            $calories = $results['calories'] ?? 0;
            $protein = $nutrients['PROCNT']['quantity'] ?? 0;
            $carbs = $nutrients['CHOCDF']['quantity'] ?? 0;
            $fat = $nutrients['FAT']['quantity'] ?? 0;
            $weight = $results['totalWeight'] ?? 0;
        ?>

        <div class="result-card">
            <div style="color: #64748b; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px;">Hasil Analisis</div>
            <h2 style="font-size: 32px; margin-bottom: 24px; color: #3b82f6; text-transform: capitalize;">
                <?= htmlspecialchars($query) ?>
            </h2>

            <div style="font-size: 48px; font-weight: 800; color: #1e293b; margin-bottom: 4px;">
                <?= (int)$calories ?> <span style="font-size: 20px; color: #94a3b8; font-weight: 500;">kcal</span>
            </div>
            <div style="color: #94a3b8; font-size: 14px;">per est. <?= (int)$weight ?>g serving</div>

            <div class="macro-row">
                <div class="macro-item">
                    <div class="macro-val" style="color: #3b82f6;"><?= round($protein, 1) ?>g</div>
                    <div class="macro-lbl">Protein</div>
                </div>
                <div class="macro-item">
                    <div class="macro-val" style="color: #10b981;"><?= round($carbs, 1) ?>g</div>
                    <div class="macro-lbl">Carbs</div>
                </div>
                <div class="macro-item">
                    <div class="macro-val" style="color: #f59e0b;"><?= round($fat, 1) ?>g</div>
                    <div class="macro-lbl">Fat</div>
                </div>
            </div>

            <?php
                // Health Analysis Logic
                $sugar = $nutrients['SUGAR']['quantity'] ?? 0;
                $sodium = $nutrients['NA']['quantity'] ?? 0;
                $satFat = $nutrients['FASAT']['quantity'] ?? 0;
                
                $warnings = [];
                $recommendations = [];

                // 1. Sugar Check (> 20g)
                if ($sugar > 20) {
                    $warnings[] = "⚠️ <strong>Tinggi Gula (" . round($sugar) . "g)</strong>: Dapat menyebabkan lonjakan energi sesaat diikuti sugar crash.";
                    if (!in_array("Coba Fresh Fruit sebagai pengganti manis alami.", $recommendations)) {
                        $recommendations[] = "🍎 Coba <strong>Fresh Fruit</strong> (Apple/Orange) untuk rasa manis alami & serat.";
                    }
                }

                // 2. Sodium Check (> 800mg)
                if ($sodium > 800) {
                    $warnings[] = "⚠️ <strong>Tinggi Garam (" . round($sodium) . "mg)</strong>: Berisiko meningkatkan tekanan darah.";
                    $recommendations[] = "💧 Perbanyak minum water (air putih) untuk membuang kelebihan sodium.";
                }

                // 3. Saturated Fat Check (> 10g)
                if ($satFat > 10) {
                    $warnings[] = "⚠️ <strong>Tinggi Lemak Jenuh (" . round($satFat) . "g)</strong>: Konsumsi berlebih tidak baik untuk jantung.";
                    $recommendations[] = "🐟 Pilih sumber healthy fats seperti <strong>Fish, Avocado, atau Nuts</strong>.";
                }

                // 4. Calorie Check (> 800kcal)
                if ($calories > 800) {
                    $warnings[] = "⚠️ <strong>Kalori Sangat Tinggi</strong>: Satu porsi ini memenuhi hampir setengah kebutuhan harian!";
                    $recommendations[] = "🥗 Pertimbangkan untuk membagi porsi atau menambah Side Salad.";
                }
            ?>

            <?php if (!empty($warnings)): ?>
                <div style="text-align: left; background: #fff1f2; border: 1px solid #fda4af; border-radius: 16px; padding: 20px; margin-bottom: 20px;">
                    <h4 style="color: #be123c; margin: 0 0 12px 0; font-size: 15px; text-transform: uppercase; letter-spacing: 0.5px;">🚨 Peringatan Kesehatan</h4>
                    <ul style="margin: 0; padding-left: 20px; color: #9f1239; font-size: 14px; line-height: 1.6;">
                        <?php foreach ($warnings as $w): ?>
                            <li style="margin-bottom: 8px;"><?= $w ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!empty($recommendations)): ?>
                <div style="text-align: left; background: #f0fdf4; border: 1px solid #86efac; border-radius: 16px; padding: 20px; margin-bottom: 24px;">
                    <h4 style="color: #15803d; margin: 0 0 12px 0; font-size: 15px; text-transform: uppercase; letter-spacing: 0.5px;">💡 Rekomendasi (Healthier Options)</h4>
                    <ul style="margin: 0; padding-left: 20px; color: #166534; font-size: 14px; line-height: 1.6;">
                        <?php foreach ($recommendations as $r): ?>
                            <li style="margin-bottom: 8px;"><?= $r ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <?php if (empty($warnings)): ?>
                <div style="background: #e0f2fe; color: #0369a1; padding: 12px; border-radius: 12px; margin-bottom: 24px; font-weight: 600; font-size: 14px;">
                    ✨ Makanan ini adalah pilihan yang cukup healthy!
                </div>
            <?php endif; ?>

            <form method="post">
                <input type="hidden" name="action" value="add_log">
                <input type="hidden" name="food_name" value="<?= htmlspecialchars($query) ?>">
                <input type="hidden" name="calories" value="<?= $calories ?>">
                <input type="hidden" name="protein" value="<?= $protein ?>">
                <input type="hidden" name="carbs" value="<?= $carbs ?>">
                <input type="hidden" name="fat" value="<?= $fat ?>">
                
                <button type="submit" class="btn btn-block" style="font-size: 16px; padding: 14px;">
                    + Tambahkan ke Log Harian
                </button>
            </form>
        </div>
    <?php elseif (!$query && !$error && !$success): ?>
        <div style="text-align: center; color: #94a3b8; margin-top: 40px;">
            <p>Masukkan kata kunci makanan dalam bahasa Inggris untuk hasil terbaik.<br>Contoh: <em>Fried Rice, 100g Chicken, 1 Apple</em></p>
        </div>
    <?php endif; ?>

</main>
</body>
</html>
