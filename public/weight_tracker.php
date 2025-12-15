<?php
require_once __DIR__ . '/../bootstrap.php';
use App\Config\Database;

session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
$db = new Database();
$message = '';
$msgType = '';

// Handle Add Weight
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_weight'])) {
    $weight = $_POST['weight'];
    $date = $_POST['date'];
    $notes = $_POST['notes'] ?? '';

    if ($weight && $date) {
        $stmt = $db->conn->prepare("INSERT INTO weight_logs (user_id, weight, date, notes) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("idss", $user['id'], $weight, $date, $notes);
        
        if ($stmt->execute()) {
            $message = "Berat badan berhasil dicatat!";
            $msgType = 'success';
        } else {
            $message = "Gagal mencatat data.";
            $msgType = 'error';
        }
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $db->conn->prepare("DELETE FROM weight_logs WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $user['id']);
    if ($stmt->execute()) {
        header("Location: weight_tracker.php");
        exit;
    }
}

// Fetch Logs
$stmt = $db->conn->prepare("SELECT * FROM weight_logs WHERE user_id = ? ORDER BY date ASC");
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$result = $stmt->get_result();
$logs = [];
while ($row = $result->fetch_assoc()) {
    $logs[] = $row;
}

// Prepare Chart Data
$dates = array_column($logs, 'date');
$weights = array_column($logs, 'weight');

// Reverse logs for table display (newest first)
$displayLogs = array_reverse($logs);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Weight Tracker — SmartHealthy</title>
  <link rel="stylesheet" href="dashboard.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
    <section class="welcome card">
      <div>
        <h1>Weight Tracker ⚖️</h1>
        <p class="muted">Pantau progres berat badanmu menuju goal.</p>
      </div>
    </section>

    <?php if ($message): ?>
      <div class="card">
        <div class="alert <?= $msgType === 'success' ? 'success' : 'error' ?>" style="padding: 16px; border-radius: 12px; margin-bottom: 0; background: <?= $msgType === 'success' ? '#d1fae5' : '#fee2e2' ?>; color: <?= $msgType === 'success' ? '#065f46' : '#991b1b' ?>;">
          <?= htmlspecialchars($message) ?>
        </div>
      </div>
    <?php endif; ?>

    <div class="grid-col-2" style="margin-top: 20px;">
      <!-- Chart Section -->
      <div class="card">
        <h3>Progres Saya</h3>
        <div style="position: relative; height: 300px;">
            <canvas id="weightChart"></canvas>
        </div>
      </div>

      <!-- Input Form -->
      <div class="card">
        <h3>Catat Berat Badan</h3>
        <form method="post">
          <input type="hidden" name="add_weight" value="1">
          
          <div class="form-group">
            <label class="form-label">Tanggal</label>
            <input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>" required>
          </div>

          <div class="form-group">
            <label class="form-label">Berat (kg)</label>
            <input type="number" step="0.1" name="weight" class="form-control" placeholder="Contoh: 65.5" required>
          </div>

          <div class="form-group">
            <label class="form-label">Catatan (Opsional)</label>
            <textarea name="notes" class="form-control" style="min-height: 80px;" placeholder="Baru bangun tidur..."></textarea>
          </div>

          <button type="submit" class="btn btn-block">Simpan Data</button>
        </form>
      </div>
    </div>

    <!-- History Table -->
    <div class="card" style="margin-top: 20px;">
      <h3>Riwayat Pencatatan</h3>
      <?php if (empty($displayLogs)): ?>
        <p class="muted">Belum ada data berat badan.</p>
      <?php else: ?>
        <table style="width: 100%; border-collapse: collapse; margin-top: 16px;">
          <thead>
            <tr style="background: var(--soft);">
              <th style="padding: 12px; text-align: left; border-radius: 8px 0 0 8px;">Tanggal</th>
              <th style="padding: 12px; text-align: left;">Berat</th>
              <th style="padding: 12px; text-align: left;">Catatan</th>
              <th style="padding: 12px; text-align: right; border-radius: 0 8px 8px 0;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($displayLogs as $log): ?>
              <tr style="border-bottom: 1px solid #f0f2f6;">
                <td style="padding: 12px;"><?= date('d M Y', strtotime($log['date'])) ?></td>
                <td style="padding: 12px; font-weight: 600;"><?= $log['weight'] ?> kg</td>
                <td style="padding: 12px; color: var(--muted);"><?= htmlspecialchars($log['notes']) ?></td>
                <td style="padding: 12px; text-align: right;">
                  <a href="?delete=<?= $log['id'] ?>" onclick="return confirm('Hapus data ini?')" style="color: #ef4444; text-decoration: none;">Hapus</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </main>

  <script>
    const dates = <?= json_encode($dates) ?>;
    const weights = <?= json_encode($weights) ?>;

    new Chart(document.getElementById('weightChart'), {
        type: 'line',
        data: {
            labels: dates,
            datasets: [{
                label: 'Berat Badan (kg)',
                data: weights,
                borderColor: '#157AFE',
                backgroundColor: 'rgba(21, 122, 254, 0.1)',
                fill: true,
                tension: 0.3,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: false
                }
            }
        }
    });
  </script>
  <script src="theme_loader.js"></script>
</body>
</html>
