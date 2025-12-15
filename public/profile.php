<?php
require_once __DIR__ . '/../bootstrap.php';
use App\Config\Database;

session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$db = new Database();
$user_id = $_SESSION['user']['id'];
$message = '';
$msgType = '';

if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'reset_success') {
        $message = "Semua data tracking berhasil direset.";
        $msgType = 'success';
    } elseif ($_GET['msg'] === 'reset_error') {
        $message = "Terjadi kesalahan saat mereset data.";
        $msgType = 'error';
    }
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['change_password'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $goal = $_POST['goal'] ?? 'maintain';
    
    // Check if email is already used by another user
    $checkEmail = $db->conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $checkEmail->bind_param("si", $email, $user_id);
    $checkEmail->execute();
    $result = $checkEmail->get_result();
    
    if ($result->num_rows > 0) {
        $message = "Email sudah digunakan oleh pengguna lain.";
        $msgType = 'error';
    } else {
        // Update user profile
        $stmt = $db->conn->prepare("UPDATE users SET name = ?, email = ?, goal = ?, gender = ?, age = ?, height = ?, activity_level = ? WHERE id = ?");
        $gender = $_POST['gender'] ?? 'male';
        $age = (int)($_POST['age'] ?? 25);
        $height = (int)($_POST['height'] ?? 170);
        $activity = $_POST['activity_level'] ?? 'moderate';
        
        $stmt->bind_param("ssssiisi", $name, $email, $goal, $gender, $age, $height, $activity, $user_id);
        
        if ($stmt->execute()) {
            // Update session data
            $_SESSION['user']['name'] = $name;
            $_SESSION['user']['email'] = $email;
            $_SESSION['user']['goal'] = $goal;
            // No need to store everything in session unless needed often
            
            $message = "Profil berhasil diperbarui!";
            $msgType = 'success';
        } else {
            $message = "Gagal memperbarui profil: " . $db->conn->error;
            $msgType = 'error';
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Get current password from database
    $stmt = $db->conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!password_verify($current_password, $user['password'])) {
        $message = "Password lama tidak sesuai.";
        $msgType = 'error';
    } elseif ($new_password !== $confirm_password) {
        $message = "Password baru tidak cocok.";
        $msgType = 'error';
    } elseif (strlen($new_password) < 6) {
        $message = "Password minimal 6 karakter.";
        $msgType = 'error';
    } else {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $db->conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed, $user_id);
        
        if ($stmt->execute()) {
            $message = "Password berhasil diubah!";
            $msgType = 'success';
        } else {
            $message = "Gagal mengubah password.";
            $msgType = 'error';
        }
    }
}

// Fetch current user data
$stmt = $db->conn->prepare("SELECT name, email, goal, gender, age, height, activity_level, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$userData = $stmt->get_result()->fetch_assoc();

// Sync session with database to prevent undefined key warnings
$_SESSION['user']['name'] = $userData['name'];
$_SESSION['user']['email'] = $userData['email'];
$_SESSION['user']['goal'] = $userData['goal'];

// Get user statistics
$stmt = $db->conn->prepare("SELECT COUNT(*) as total_logs FROM nutrition_logs WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Profil — JawaHealthy</title>
  <link rel="stylesheet" href="dashboard.css">
</head>
<body>
  <header class="topbar">
    <div class="brand">JawaHealthy</div>
    <nav>
      <a href="dashboard.php" class="nav-link">Dashboard</a>
      <a href="search_nutrition.php" class="nav-link">Search</a>
      <a href="analytics.php" class="nav-link">Analytics</a>
      <a href="profile.php" class="nav-link" style="color:var(--blue); font-weight:600; background: var(--soft-blue);">Profile</a>
      <a href="logout.php" class="nav-link logout">Logout</a>
    </nav>
  </header>

  <main class="container">
    <section class="welcome card">
      <div>
        <h1>Profil Pengguna</h1>
        <p class="muted">Kelola informasi akun dan preferensi Anda.</p>
      </div>
    </section>

    <?php if ($message): ?>
      <div class="card" style="background: <?= $msgType === 'success' ? '#ECFDF5' : '#FEF2F2' ?>; border-color: <?= $msgType === 'success' ? '#10B981' : '#EF4444' ?>;">
        <div style="color: <?= $msgType === 'success' ? '#065F46' : '#991B1B' ?>; font-weight: 500;">
          <?= htmlspecialchars($message) ?>
        </div>
      </div>
    <?php endif; ?>

    <div class="grid-col-2" style="margin-top: 20px;">
      <!-- Profile Information -->
      <div class="card">
        <h3>Informasi Profil</h3>
        <form method="post">
          <div class="form-group">
            <label class="form-label">Nama Lengkap</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($userData['name']) ?>" required>
          </div>

          <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($userData['email']) ?>" required>
          </div>

          <div class="form-group">
            <label class="form-label">Tujuan Fitness</label>
            <select name="goal" class="form-control">
              <option value="diet" <?= $userData['goal'] === 'diet' ? 'selected' : '' ?>>Diet (Fat Loss)</option>
              <option value="maintain" <?= $userData['goal'] === 'maintain' ? 'selected' : '' ?>>Maintain</option>
              <option value="muscle" <?= $userData['goal'] === 'muscle' ? 'selected' : '' ?>>Build Muscle</option>
            </select>
          </div>

          <div class="grid-col-2" style="margin-top:0; gap: 16px;">
            <div class="form-group">
                <label class="form-label">Jenis Kelamin</label>
                <select name="gender" class="form-control">
                    <option value="male" <?= ($userData['gender'] ?? 'male') === 'male' ? 'selected' : '' ?>>Laki-laki</option>
                    <option value="female" <?= ($userData['gender'] ?? 'male') === 'female' ? 'selected' : '' ?>>Perempuan</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Usia (Tahun)</label>
                <input type="number" name="age" class="form-control" value="<?= htmlspecialchars($userData['age'] ?? 25) ?>" required>
            </div>
          </div>

          <div class="grid-col-2" style="margin-top:0; gap: 16px;">
            <div class="form-group">
                <label class="form-label">Tinggi Badan (cm)</label>
                <input type="number" name="height" class="form-control" value="<?= htmlspecialchars($userData['height'] ?? 170) ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Aktivitas Fisik</label>
                <select name="activity_level" class="form-control">
                    <option value="sedentary" <?= ($userData['activity_level'] ?? 'moderate') === 'sedentary' ? 'selected' : '' ?>>Sedentary (Jarang Olahraga)</option>
                    <option value="light" <?= ($userData['activity_level'] ?? 'moderate') === 'light' ? 'selected' : '' ?>>Light (1-3x seminggu)</option>
                    <option value="moderate" <?= ($userData['activity_level'] ?? 'moderate') === 'moderate' ? 'selected' : '' ?>>Moderate (3-5x seminggu)</option>
                    <option value="active" <?= ($userData['activity_level'] ?? 'moderate') === 'active' ? 'selected' : '' ?>>Active (6-7x seminggu)</option>
                    <option value="athlete" <?= ($userData['activity_level'] ?? 'moderate') === 'athlete' ? 'selected' : '' ?>>Athlete (2x sehari)</option>
                </select>
            </div>
          </div>

          <button type="submit" class="btn btn-block" style="margin-top: 10px;">Simpan Perubahan</button>
        </form>
      </div>

      <!-- Account Statistics & Password -->
      <div>
        <div class="card">
          <h3>Statistik Akun</h3>
          <div style="padding: 16px 0;">
            <div style="margin-bottom: 20px;">
              <div class="muted" style="font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Bergabung sejak</div>
              <div style="font-weight: 700; font-size: 18px; margin-top: 4px; color: var(--text-main);">
                <?= date('d M Y', strtotime($userData['created_at'])) ?>
              </div>
            </div>
            <div style="margin-bottom: 20px;">
              <div class="muted" style="font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Total Log Makanan</div>
              <div style="font-weight: 700; font-size: 18px; margin-top: 4px; color: var(--text-main);">
                <?= number_format($stats['total_logs']) ?> entries
              </div>
            </div>
            <div>
              <div class="muted" style="font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Tujuan Saat Ini</div>
              <div style="font-weight: 700; font-size: 18px; margin-top: 4px; color: var(--blue);">
                <?= ucfirst($userData['goal']) ?>
              </div>
            </div>
          </div>
        </div>

        <div class="card" style="margin-top: 24px;">
          <h3>Ubah Password</h3>
          <form method="post">
            <input type="hidden" name="change_password" value="1">
            
            <div class="form-group">
              <label class="form-label">Password Lama</label>
              <input type="password" name="current_password" class="form-control" required>
            </div>

            <div class="form-group">
              <label class="form-label">Password Baru</label>
              <input type="password" name="new_password" class="form-control" minlength="6" required>
            </div>

            <div class="form-group">
              <label class="form-label">Konfirmasi Password Baru</label>
              <input type="password" name="confirm_password" class="form-control" minlength="6" required>
            </div>

            <button type="submit" class="btn btn-block" style="background: var(--warn);">Ubah Password</button>
          </form>
        </div>

        <!-- Reset Data Zone -->
        <div class="card" style="margin-top: 24px; border: 1px solid #fee2e2; background: #fff5f5;">
          <h3 style="color: #ef4444;">⚠️ Danger Zone</h3>
          <p class="muted" style="margin-bottom: 16px; font-size: 14px;">Hapus semua riwayat makanan & berat badan. Data yang dihapus tidak dapat dikembalikan.</p>
          
          <form method="post" action="reset_data.php" onsubmit="return confirm('YAKIN ingin menghapus SEMUA data tracking? Data akan hilang permanen!');">
            <button type="submit" name="reset_data" class="btn btn-block danger">Reset Semua Data</button>
          </form>
        </div>
      </div>
    </div>
  </main>
<script src="theme_loader.js"></script>
</body>
</html>
