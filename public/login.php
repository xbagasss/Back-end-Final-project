<?php
require_once __DIR__ . '/../bootstrap.php';
use App\Config\Database;
use App\Services\AuthService;

session_start();
$db = new Database();
$auth = new AuthService($db);

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $user = $auth->login($email, $password);

    if ($user) {
        $_SESSION['user'] = $user;
        header('Location: dashboard.php');
        exit;
    } else {
        $message = 'Email atau password salah!';
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Login — SmartHealthy</title>
  <link rel="stylesheet" href="dashboard.css">
  <style>
    body { background: #f4f6f9; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
    .auth-card { width: 100%; max-width: 400px; background: white; padding: 32px; border-radius: 16px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
    .auth-title { font-size: 24px; font-weight: 700; color: #111; margin-bottom: 8px; text-align: center; }
    .auth-subtitle { color: var(--muted); text-align: center; margin-bottom: 24px; font-size: 14px; }
    .alert-error { background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 8px; margin-bottom: 16px; text-align: center; font-size: 14px; }
    .alert-success { background: #d1fae5; color: #065f46; padding: 12px; border-radius: 8px; margin-bottom: 16px; text-align: center; font-size: 14px; }
    .link { color: var(--blue); text-decoration: none; font-weight: 600; }
  </style>
</head>
<body>

  <div class="auth-card">
    <div class="auth-title">Welcome Back</div>
    <div class="auth-subtitle">Masuk untuk melanjutkan ke SmartHealthy</div>

    <?php if ($message): ?>
        <div class="alert-error"><?= $message ?></div>
    <?php endif; ?>

    <?php if (isset($_GET['registered'])): ?>
        <div class="alert-success">Registrasi berhasil, silakan login.</div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <input type="email" name="email" class="form-control" placeholder="Email Address" required>
        </div>
        <div class="form-group">
            <input type="password" name="password" class="form-control" placeholder="Password" required>
        </div>

        <button type="submit" class="btn btn-block">Masuk</button>
    </form>

    <p style="text-align:center; margin-top:24px; font-size:14px; color:#666;">
        Belum punya akun? <a href="register.php" class="link">Daftar sekarang</a>
    </p>
  </div>

</body>
</html>
