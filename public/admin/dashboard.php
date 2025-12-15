<?php
require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/ensure_admin.php';

use App\Config\Database;

$db = new Database();
$conn = $db->conn;

// 1. Total Users
$userCount = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];

// 2. Total Foods
$foodCount = $conn->query("SELECT COUNT(*) as c FROM foods")->fetch_assoc()['c'];

// 3. API Calls Today
$today = date('Y-m-d');
$apiCount = $conn->query("SELECT COUNT(*) as c FROM api_logs WHERE DATE(created_at) = '$today'")->fetch_assoc()['c'];

// 4. Most Searched Today
$trend = $conn->query("SELECT query, COUNT(*) as c FROM api_logs WHERE DATE(created_at) = '$today' GROUP BY query ORDER BY c DESC LIMIT 1")->fetch_assoc();
$topSearch = $trend ? $trend['query'] : '-';

$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/layout_header.php';
?>

    <div class="card">
        <h1>Admin Overview</h1>
        <p class="muted">Welcome back, <strong><?= htmlspecialchars($_SESSION['user']['name']) ?></strong></p>
    </div>

    <!-- Stats Grid -->
    <div class="cards-row" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
        <div class="stat card">
            <div class="stat-title">Total Users</div>
            <div class="stat-value"><?= $userCount ?></div>
            <div class="muted">Registered members</div>
        </div>
        <div class="stat card">
            <div class="stat-title">Foods Database</div>
            <div class="stat-value"><?= $foodCount ?></div>
            <div class="muted">Items available</div>
        </div>
        <div class="stat card">
            <div class="stat-title">API Requests</div>
            <div class="stat-value"><?= $apiCount ?></div>
            <div class="muted">Calls made today</div>
        </div>
        <div class="stat card">
            <div class="stat-title">Trending Now</div>
            <div class="stat-value" style="font-size: 24px; text-transform:capitalize;"><?= htmlspecialchars($topSearch) ?></div>
            <div class="muted">Top search today</div>
        </div>
    </div>

    <div class="grid-col-2">
        <div class="card">
            <h3>Quick Actions</h3>
            <div style="display: flex; flex-wrap: wrap; gap: 12px; margin-top: 16px;">
                <a href="users.php" class="btn">Manage Users</a>
                <a href="foods.php" class="btn">Manage Foods</a>
                <a href="reports.php" class="btn" style="background: var(--soft-blue); color: var(--blue);">View Reports</a>
            </div>
            <div style="margin-top: 16px;">
                <a href="add_user.php" class="btn small" style="background: white; color: var(--text-muted); border: 1px solid var(--border);">+ Add User</a>
                <a href="food_form.php" class="btn small" style="background: white; color: var(--text-muted); border: 1px solid var(--border);">+ Add Food</a>
            </div>
        </div>

        <div class="card">
            <h3>System Status</h3>
            <ul style="list-style: none; padding: 0; margin: 16px 0 0 0;">
                <li style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f1f5f9;">
                    <span>Database Connection</span>
                    <span style="color: var(--success); font-weight: 600;">OK</span>
                </li>
                <li style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f1f5f9;">
                    <span>Wait Duration (API)</span>
                    <span>~0.8s</span>
                </li>
                <li style="display: flex; justify-content: space-between; padding: 8px 0;">
                    <span>PHP Version</span>
                    <span><?= phpversion() ?></span>
                </li>
            </ul>
            <div style="margin-top: 12px; text-align: right;">
                <a href="settings.php" class="small muted" style="text-decoration: none;">Configure System →</a>
            </div>
        </div>
    </div>

<?php require_once __DIR__ . '/layout_footer.php'; ?>
