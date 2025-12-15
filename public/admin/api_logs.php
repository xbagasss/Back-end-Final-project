<?php
require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/ensure_admin.php';

use App\Config\Database;

$db = new Database();
$conn = $db->conn;

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Count total
$totalLogs = $conn->query("SELECT COUNT(*) as c FROM api_logs")->fetch_assoc()['c'];
$totalPages = ceil($totalLogs / $perPage);

// Fetch logs
$sql = "SELECT l.*, u.name as user_name 
        FROM api_logs l 
        LEFT JOIN users u ON l.user_id = u.id 
        ORDER BY l.created_at DESC 
        LIMIT $perPage OFFSET $offset";
$result = $conn->query($sql);

$pageTitle = 'API Logs';
require_once __DIR__ . '/layout_header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <h1>API Logs</h1>
    <div class="muted">Total Requests: <strong><?= $totalLogs ?></strong></div>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
    <table>
        <thead>
            <tr>
                <th>Time</th>
                <th>User</th>
                <th>Endpoint</th>
                <th>Query</th>
                <th>Status</th>
                <th>Duration</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows === 0): ?>
                <tr><td colspan="6" style="text-align:center; padding: 24px;" class="muted">No logs recorded yet.</td></tr>
            <?php else: ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td class="small"><?= date('d M H:i:s', strtotime($row['created_at'])) ?></td>
                    <td><?= htmlspecialchars($row['user_name'] ?? 'Guest/Unknown') ?></td>
                    <td class="small muted"><?= htmlspecialchars($row['endpoint']) ?></td>
                    <td><code><?= htmlspecialchars($row['query']) ?></code></td>
                    <td>
                        <?php if ($row['status'] == 200): ?>
                            <span style="color: #16a34a; font-weight:600;">200 OK</span>
                        <?php elseif ($row['status'] == 404): ?>
                            <span style="color: #ca8a04; font-weight:600;">404</span>
                        <?php else: ?>
                            <span style="color: #dc2626; font-weight:600;"><?= $row['status'] ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="small"><?= number_format($row['response_time'], 3) ?>s</td>
                </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Simple Paginator -->
<?php if ($totalPages > 1): ?>
    <div style="margin-top: 16px; display: flex; center; gap: 8px; justify-content: center;">
        <?php for($i=1; $i<=$totalPages; $i++): ?>
            <a href="?page=<?= $i ?>" class="btn small <?= $i===$page ? '' : 'style="background:white; color:#333; border:1px solid #ddd;"' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/layout_footer.php'; ?>
