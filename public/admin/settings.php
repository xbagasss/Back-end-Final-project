<?php
require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/ensure_admin.php';

use App\Config\Database;

$db = new Database();
$conn = $db->conn;

$message = '';

// Handle Save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appName = $_POST['app_name'] ?? '';
    // $apiKey = $_POST['api_key'] ?? ''; // Example secure field

    // Helper to save setting
    function saveSetting($conn, $key, $val) {
        $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        $stmt->bind_param("ss", $key, $val);
        $stmt->execute();
    }

    saveSetting($conn, 'app_name', $appName);
    // saveSetting($conn, 'api_key', $apiKey);

    $message = "Settings saved successfully.";
}

// Fetch current settings
$settings = [];
$res = $conn->query("SELECT * FROM settings");
while ($row = $res->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

$pageTitle = 'Settings';
require_once __DIR__ . '/layout_header.php';
?>

<div style="max-width: 600px; margin: 0 auto;">
    <h1>Settings</h1>
    <p class="muted">Configuration variables for the application.</p>

    <?php if ($message): ?>
        <div style="background: #dcfce7; color: #166534; padding: 12px; border-radius: 8px; margin-bottom: 20px;"><?= $message ?></div>
    <?php endif; ?>

    <div class="card">
        <form method="POST">
            <h3 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 12px; margin-bottom: 20px;">General Settings</h3>
            
            <div class="form-group">
                <label class="form-label">Application Name</label>
                <input type="text" name="app_name" class="form-control" value="<?= htmlspecialchars($settings['app_name'] ?? 'JawaHealthy') ?>">
            </div>

            <!-- Example for API Key (masked)
            <div class="form-group">
                <label class="form-label">Edamam API Key</label>
                <input type="password" name="api_key" class="form-control" value="<?= htmlspecialchars($settings['api_key'] ?? '') ?>" placeholder="Enter new key to update">
            </div>
            -->

            <button type="submit" class="btn btn-block">Save Settings</button>
        </form>

        <div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid #eee;">
            <h4 class="text-danger" style="margin-top:0">Danger Zone</h4>
            <p class="muted small">Clear all cached logs or reset data.</p>
            <button class="btn small danger" onclick="alert('Feature coming soon')">Clear API Logs</button>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/layout_footer.php'; ?>
