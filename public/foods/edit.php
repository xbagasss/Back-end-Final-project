<?php
require_once __DIR__ . '/../../bootstrap.php';

use App\Config\Database;

session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

$db = new Database();
$id = intval($_GET['id']);

$data = $db->conn->query("SELECT * FROM foods WHERE id = $id")->fetch_assoc();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = $_POST['name'];
    $desc = $_POST['description'];
    $cal = intval($_POST['calories']);
    $protein = floatval($_POST['protein']);
    $carbs = floatval($_POST['carbs']);
    $fat = floatval($_POST['fat']);

    $stmt = $db->conn->prepare("
        UPDATE foods SET name=?, description=?, calories=?, protein=?, carbs=?, fat=? 
        WHERE id=?
    ");

    $stmt->bind_param("ssiddii", $name, $desc, $cal, $protein, $carbs, $fat, $id);

    if ($stmt->execute()) {
        header("Location: index.php");
        exit;
    } else {
        $msg = "Gagal mengupdate data.";
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Edit Food — SmartHealthy</title>
    <link rel="stylesheet" href="../dashboard.css">
</head>
<body>

  <header class="topbar">
    <div class="brand">SmartHealthy</div>
    <nav>
      <a href="../dashboard.php" class="nav-link">Dashboard</a>
      <a href="../foods/index.php" class="nav-link" style="color:var(--blue); font-weight:700;">Foods</a>
      <a href="../logout.php" class="nav-link logout">Logout</a>
    </nav>
  </header>

  <main class="container">
    <div class="card" style="max-width: 600px; margin: 0 auto;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h2>Edit Food</h2>
            <a href="index.php" class="link">Kembali</a>
        </div>

        <?php if ($msg): ?>
            <div class="alert" style="background:#fef2f2; color:#ef4444; padding:12px; border-radius:8px; margin-bottom:16px;">
                <?= $msg ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label class="form-label">Nama Makanan</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($data['name']) ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Deskripsi</label>
                <textarea name="description" class="form-control" style="min-height:80px;"><?= htmlspecialchars($data['description']) ?></textarea>
            </div>

            <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Kalori (kcal)</label>
                    <input type="number" name="calories" class="form-control" value="<?= $data['calories'] ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Protein (g)</label>
                    <input type="number" step="0.1" name="protein" class="form-control" value="<?= $data['protein'] ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Karbohidrat (g)</label>
                    <input type="number" step="0.1" name="carbs" class="form-control" value="<?= $data['carbs'] ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Lemak (g)</label>
                    <input type="number" step="0.1" name="fat" class="form-control" value="<?= $data['fat'] ?>">
                </div>
            </div>

            <button type="submit" class="btn btn-block">Update</button>
        </form>
    </div>
  </main>

</body>
</html>
