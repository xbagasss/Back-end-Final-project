<?php
require_once __DIR__ . '/../bootstrap.php';
use App\Config\Database;
use App\Services\AuthService;

$db = new Database();
$auth = new AuthService($db);
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($auth->register($_POST['name'], $_POST['email'], $_POST['password'])) {
        header('Location: login.php?registered=1');
        exit;
    } else {
        $message = 'Email sudah digunakan!';
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Register — JawaHealthy</title>
  <link rel="stylesheet" href="dashboard.css">
  <style>
    body { background: #f4f6f9; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
    .auth-card { width: 100%; max-width: 400px; background: white; padding: 32px; border-radius: 16px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
    .auth-title { font-size: 24px; font-weight: 700; color: #111; margin-bottom: 8px; text-align: center; }
    .auth-subtitle { color: var(--muted); text-align: center; margin-bottom: 24px; font-size: 14px; }
    .alert-error { background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 8px; margin-bottom: 16px; text-align: center; font-size: 14px; }
    .link { color: var(--blue); text-decoration: none; font-weight: 600; }
  </style>
</head>
<body>

  <div class="auth-card">
    <div class="auth-title">Create Account</div>
    <div class="auth-subtitle">Daftar untuk memulai hidup sehat</div>

    <?php if ($message): ?>
        <div class="alert-error"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <input type="text" name="name" class="form-control" placeholder="Nama Lengkap" required>
        </div>
        <div class="form-group">
            <input type="email" name="email" class="form-control" placeholder="Email Address" required>
        </div>
        <div class="form-group">
            <input type="password" name="password" class="form-control" placeholder="Password" required>
        </div>

        <button type="submit" class="btn btn-block">Daftar</button>
    </form>

    <p style="text-align:center; margin-top:24px; font-size:14px; color:#666;">
        Sudah punya akun? <a href="login.php" class="link">Login</a>
    </p>
  </div>

</body>
</html>
