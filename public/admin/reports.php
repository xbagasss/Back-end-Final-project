<?php
require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/ensure_admin.php';

use App\Config\Database;

$db = new Database();
$conn = $db->conn;

// 1. Most Searched Queries
$popular = $conn->query("
    SELECT query, COUNT(*) as count 
    FROM api_logs 
    WHERE status = 200 
    GROUP BY query 
    ORDER BY count DESC 
    LIMIT 10
");

// 2. API Calls last 7 days
$daily = $conn->query("
    SELECT DATE(created_at) as date, COUNT(*) as count 
    FROM api_logs 
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date ASC
");

// Prepare chart data
$chartLabels = [];
$chartData = [];
while ($row = $daily->fetch_assoc()) {
    $chartLabels[] = date('d M', strtotime($row['date']));
    $chartData[] = $row['count'];
}

$pageTitle = 'Reports';
require_once __DIR__ . '/layout_header.php';
?>

<h1>Reports & Analytics</h1>

<div class="grid-col-2">
    <!-- Chart -->
    <div class="card">
        <h3>API Usage (Last 7 Days)</h3>
        <canvas id="apiChart"></canvas>
    </div>

    <!-- Popular Searches -->
    <div class="card">
        <h3>Top Searched Foods</h3>
        <table>
            <thead>
                <tr>
                    <th>Query</th>
                    <th width="80">Count</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($popular->num_rows === 0): ?>
                    <tr><td colspan="2" class="muted text-center">No data yet.</td></tr>
                <?php else: ?>
                    <?php $i=1; while ($row = $popular->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <span style="color:var(--text-muted); margin-right:8px;">#<?= $i++ ?></span>
                            <strong><?= htmlspecialchars($row['query']) ?></strong>
                        </td>
                        <td><?= $row['count'] ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
const ctx = document.getElementById('apiChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($chartLabels) ?>,
        datasets: [{
            label: 'API Calls',
            data: <?= json_encode($chartData) ?>,
            backgroundColor: '#3b82f6',
            borderRadius: 4
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, grid: { display: true, drawBorder: false } },
            x: { grid: { display: false } }
        }
    }
});
</script>

<?php require_once __DIR__ . '/layout_footer.php'; ?>
