<?php
require_once __DIR__ . '/../bootstrap.php';

use App\Config\Database;
use App\Services\NotificationService;
use App\Services\AnalyticsService;
use App\Services\EmailTemplateService;

session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$db = new Database();
$mailer = new NotificationService();
$analytics = new AnalyticsService();
$template = new EmailTemplateService();

$message = '';
$msgType = ''; // success or error

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_SESSION['user'];
    $email = $user['email'];
    $userId = $user['id'];

    if (isset($_POST['action']) && $_POST['action'] === 'send_report') {
        // Generate Analysis Report
        $weeklyData = $analytics->getWeeklyTotals($userId);
        $topFoods = $analytics->getTopFoods($userId);
        
        // Calculate insights
        $insights = [];
        if (!empty($weeklyData)) {
            $avgCal = array_sum(array_column($weeklyData, 'cal')) / count($weeklyData);
            if ($avgCal > 2300) $insights[] = "⚠ Kalori harian tinggi. Coba kurangi gorengan & minuman manis.";
            if ($avgCal < 1500) $insights[] = "⚠ Kalori terlalu rendah. Tambahkan makanan bernutrisi.";
        }

        $htmlBody = $template->generateAnalysisReport($email, $weeklyData, $topFoods, $insights);
        $subject = "Laporan Analisis Nutrisi Mingguan Anda";

        // Send HTML email
        if ($mailer->sendEmail($email, $subject, $htmlBody, false, true)) {
             $message = "✅ Laporan analisis berhasil dikirim ke $email";
             $msgType = 'success';
        } else {
             $errorDetail = $mailer->getLastError();
             $message = "❌ Gagal mengirim laporan: " . ($errorDetail ?: "Cek konfigurasi SMTP");
             $msgType = 'error';
        }

    } else {
        // Manual Notification
        $subject = $_POST['subject'] ?? 'Notification';
        $body    = $_POST['body'] ?? '';

        if ($mailer->sendEmail($email, $subject, $body)) {
            $message = "✅ Notifikasi berhasil dikirim ke $email";
            $msgType = 'success';
        } else {
            $errorDetail = $mailer->getLastError();
            if ($errorDetail) {
                $message = "❌ Gagal mengirim notifikasi: " . $errorDetail;
            } else {
                $message = "❌ Gagal mengirim notifikasi. Cek SMTP atau app password Gmail.";
            }
            $msgType = 'error';
        }
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Notifications — SmartHealthy</title>
  <link rel="stylesheet" href="dashboard.css">
  <style>
    .form-group { margin-bottom: 16px; }
    .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #333; }
    .form-control { width: 100%; padding: 12px; border: 1px solid #dbeafe; border-radius: var(--radius); font-size: 15px; font-family: inherit; }
    .form-control:focus { outline: none; border-color: var(--blue); box-shadow: 0 0 0 3px var(--soft); }
    .alert { padding: 16px; border-radius: var(--radius); margin-bottom: 24px; }
    .alert.success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
    .alert.error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
  </style>
</head>
<body>
  <header class="topbar">
    <div class="brand">SmartHealthy</div>
    <nav>
      <a href="dashboard.php" class="nav-link">Dashboard</a>
      <a href="search_nutrition.php" class="nav-link">Search</a>
      <a href="analytics.php" class="nav-link">Analytics</a>
      <a href="profile.php" class="nav-link">Profile</a>
      <a href="logout.php" class="nav-link logout">Logout</a>
    </nav>
  </header>

  <main class="container">
    <section class="card" style="max-width: 600px; margin: 0 auto;">
      <div class="welcome" style="margin-bottom: 24px;">
        <div>
          <h1>Kirim Notifikasi</h1>
          <p class="muted">Kirim pesan atau laporan analisis ke email Anda.</p>
        </div>
      </div>

      <?php if ($message): ?>
        <div class="alert <?= $msgType ?>">
            <?= htmlspecialchars($message) ?>
        </div>
      <?php endif; ?>

      <!-- Section: Weekly Report -->
      <div style="background: #f8fafc; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 30px;">
          <h3 style="margin-top: 0; color: #1e293b;">📊 Laporan Analisis Mingguan</h3>
          <p class="muted" style="font-size: 14px; margin-bottom: 16px;">
              Dapatkan ringkasan lengkap tentang asupan kalori, makronutrisi, dan insight kesehatan Anda minggu ini langsung di inbox email Anda.
          </p>
          <form method="post">
              <input type="hidden" name="action" value="send_report">
              <button type="submit" class="btn" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); width: 100%;">
                  📩 Kirim Laporan Analisis ke Email Saya
              </button>
          </form>
      </div>

      <hr style="border: 0; border-top: 1px solid #eee; margin: 30px 0;">

      <!-- Section: Manual Notification -->
      <h3 style="margin-top: 0; color: #1e293b; margin-bottom: 15px;">📝 Kirim Pesan Manual</h3>
      <form method="post">
        <div class="form-group">
            <label>Judul Email</label>
            <input type="text" name="subject" class="form-control" placeholder="Contoh: Reminder Diet" required>
        </div>

        <div class="form-group">
            <label>Isi Pesan</label>
            <textarea name="body" rows="6" class="form-control" placeholder="Tulis pesan Anda di sini..." required></textarea>
        </div>

        <div style="text-align: right;">
            <button type="submit" class="btn" style="padding: 12px 24px; font-size: 16px;">Kirim Pesan</button>
        </div>
      </form>
    </section>
  </main>
<script src="theme_loader.js"></script>
</body>
</html>
