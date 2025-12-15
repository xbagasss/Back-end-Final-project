<?php
require_once __DIR__ . '/../bootstrap.php';
use App\Config\Database;

session_start();
if (!isset($_SESSION['user'])) { exit; }

$db = new Database();
$user_id = $_SESSION['user']['id'];

$date = $_GET['date'] ?? date('Y-m-d');

$stmt = $db->conn->prepare("
    SELECT food_name, calories, protein, carbs, fat
    FROM nutrition_logs
    WHERE user_id = ? AND date = ?
");
$stmt->bind_param("is", $user_id, $date);
$stmt->execute();
$res = $stmt->get_result();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Food Diary <?= $date ?> — SmartHealthy</title>
  <link rel="stylesheet" href="dashboard.css">
  <style>
    .food-list-item { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px dashed #f0f2f6; }
    .food-list-item:last-child { border-bottom: none; }
  </style>
</head>
<body>
  <header class="topbar">
    <div class="brand">SmartHealthy</div>
    <nav>
      <a href="dashboard.php" class="nav-link" style="color:var(--blue); font-weight:700;">Dashboard</a>
      <a href="search_nutrition.php" class="nav-link">Search</a>
      <a href="analytics.php" class="nav-link">Analytics</a>
      <a href="profile.php" class="nav-link">Profile</a>
      <a href="logout.php" class="nav-link logout">Logout</a>
    </nav>
  </header>

  <main class="container">
    <div style="margin-bottom: 16px;">
        <a href="calendar.php" class="link">← Kembali ke Kalender</a>
    </div>

    <section class="card">
      <div class="welcome" style="margin-bottom: 16px;">
        <div>
          <h1>Makanan — <?= date('d M Y', strtotime($date)) ?></h1>
          <p class="muted">Detail asupan nutrisi pada tanggal ini.</p>
        </div>
      </div>

      <?php if ($res->num_rows === 0): ?>
        <p class="muted">Tidak ada makanan tercatat di tanggal ini.</p>
      <?php else: ?>
        <div class="food-list">
            <?php 
            $totalCal = 0;
            while ($f = $res->fetch_assoc()): 
                $totalCal += $f['calories'];
            ?>
            <div class="food-list-item">
                <div>
                    <strong><?= htmlspecialchars($f['food_name']) ?></strong>
                    <div class="muted small">
                        P: <?= $f['protein'] ?>g • C: <?= $f['carbs'] ?>g • F: <?= $f['fat'] ?>g
                    </div>
                </div>
                <div style="font-weight: 700; color: var(--blue);">
                    <?= $f['calories'] ?> kcal
                </div>
            </div>
            <?php endwhile; ?>
            
            <div style="margin-top: 20px; padding-top: 16px; border-top: 2px solid #f0f2f6; display: flex; justify-content: space-between; align-items: center;">
                <strong>Total Kalori</strong>
                <strong style="font-size: 18px;"><?= $totalCal ?> kcal</strong>
            </div>
        </div>
      <?php endif; ?>
    </section>
  </main>
</body>
</html>
