<?php
require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/ensure_admin.php';

use App\Config\Database;
use App\Services\AuthService;

$db = new Database();
$auth = new AuthService($db);

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    // Optional role selection if you implemented it in previous step
    $role = $_POST['role'] ?? 'user';

    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Semua field wajib diisi!';
    } else {
        $check = $db->conn->prepare("SELECT id FROM users WHERE email=?");
        $check->bind_param("s", $email);
        $check->execute();
        if ($check->get_result()->fetch_assoc()) {
            $error = 'Email sudah terdaftar!';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $hash, $role);
            
            if ($stmt->execute()) {
                $message = 'User berhasil ditambahkan!';
            } else {
                $error = 'Gagal menambahkan user: ' . $db->conn->error;
            }
        }
    }
}

$pageTitle = 'Add New Member';
require_once __DIR__ . '/layout_header.php';
?>

    <div style="max-width: 600px; margin: 0 auto;">
        <a href="users.php" style="text-decoration: none; color: var(--text-muted); display: flex; align-items: center; gap: 8px; margin-bottom: 16px;">
            ← Back to Users
        </a>

        <div class="card">
            <h1>Add New Member</h1>
            <p class="muted" style="margin-bottom: 24px;">Create a new account for a user or admin.</p>

            <?php if ($message): ?>
                <div style="background: #dcfce7; color: #166534; padding: 12px; border-radius: 8px; margin-bottom: 20px;"><?= $message ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div style="background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 8px; margin-bottom: 20px;"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control" required placeholder="Ex: John Doe">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" required placeholder="user@example.com">
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required placeholder="Min 6 characters">
                </div>

                <div class="form-group">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-control">
                        <option value="user">User (Standard)</option>
                        <option value="admin">Administrator</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-block">Create Member</button>
            </form>
        </div>
    </div>

<?php require_once __DIR__ . '/layout_footer.php'; ?>
