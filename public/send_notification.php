<?php
require_once __DIR__ . '/../bootstrap.php';

use App\Config\Database;
use App\Services\AnalyticsService;
use App\Services\NotificationService;
use App\Services\EmailTemplateService;

session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$db = new Database();
$analytics = new AnalyticsService();
$notify = new NotificationService();
$template = new EmailTemplateService();

$user = $_SESSION['user'];
$user_id = $user['id'];
$email = $user['email'];

// Ambil data analisis
$weeklyData = $analytics->getWeeklyTotals($user_id);
$topFoods = $analytics->getTopFoods($user_id);

// Calculate insights
$insights = [];
if (!empty($weeklyData)) {
    $avgCal = array_sum(array_column($weeklyData, 'cal')) / count($weeklyData);
    if ($avgCal > 2300) $insights[] = "⚠ Kalori harian tinggi. Coba kurangi gorengan & minuman manis.";
    if ($avgCal < 1500) $insights[] = "⚠ Kalori terlalu rendah. Tambahkan makanan bernutrisi.";
}

$htmlBody = $template->generateAnalysisReport($email, $weeklyData, $topFoods, $insights);
$subject = "Laporan Analisis Nutrisi Mingguan Anda";

if ($notify->sendEmail($email, $subject, $htmlBody, false, true)) {
    echo "<p style='color: green;'>✅ Email berhasil dikirim ke {$email}</p>";
} else {
    $error = $notify->getLastError();
    echo "<p style='color: red;'>❌ Email gagal dikirim: {$error}</p>";
}

echo "<br><a href='dashboard.php'>Kembali ke Dashboard</a>";
