<?php
require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/ensure_admin.php';

use App\Config\Database;

$db = new Database();
$conn = $db->conn;

$id = $_GET['id'] ?? null;
$item = [
    'name' => '',
    'calories' => '',
    'protein' => 0,
    'carbs' => 0,
    'fat' => 0,
    'category_id' => '',
    'description' => '',
    'image_path' => ''
];

if ($id) {
    $stmt = $conn->prepare("SELECT * FROM foods WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $item = $res->fetch_assoc();
    } else {
        die("Food not found");
    }
}

$message = '';
$error = '';

// Handle Form Submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $calories = (int)($_POST['calories'] ?? 0);
    $protein = (float)($_POST['protein'] ?? 0);
    $carbs = (float)($_POST['carbs'] ?? 0);
    $fat = (float)($_POST['fat'] ?? 0);
    $description = $_POST['description'] ?? '';
    $category_id = !empty($_POST['category_id']) ? $_POST['category_id'] : null;
    $is_verified = 1; // Admin created/edited is always verified

    if (empty($name)) {
        $error = "Name is required";
    } else {
        // Handle File Upload
        $imagePath = $item['image_path']; // Keep old image by default
        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../uploads/foods/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                
                $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (in_array($ext, $allowed)) {
                    $filename = uniqid('food_') . '.' . $ext;
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename)) {
                        $imagePath = $filename;
                    } else {
                        $error = "Failed to move uploaded file. Check folder permissions.";
                    }
                } else {
                    $error = "Invalid file type. Only JPG, PNG, GIF, and WEBP are allowed.";
                }
            } else {
                $uploadErrors = [
                    UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize.',
                    UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE.',
                    UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
                    UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder.',
                    UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
                    UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.'
                ];
                $error = "Upload Error: " . ($uploadErrors[$_FILES['image']['error']] ?? 'Unknown error');
            }
        }
        
        // If error occurred during upload, don't proceed to DB update
        if (!empty($error)) {
            // Fall through to show error
        } else {

        if ($id) {
            // Update
            $sql = "UPDATE foods SET name=?, calories=?, protein=?, carbs=?, fat=?, description=?, category_id=?, image_path=?, is_verified=1 WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sidddsssi", $name, $calories, $protein, $carbs, $fat, $description, $category_id, $imagePath, $id);
        } else {
            // Insert
            $sql = "INSERT INTO foods (name, calories, protein, carbs, fat, description, category_id, image_path, is_verified, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, ?)";
            $stmt = $conn->prepare($sql);
            $userId = $_SESSION['user']['id'];
            $stmt->bind_param("sidddsssi", $name, $calories, $protein, $carbs, $fat, $description, $category_id, $imagePath, $userId);
        }

        if ($stmt->execute()) {
            if (!$id) { // Redirect to list if new
                header("Location: foods.php");
                exit;
            } else {
                $message = "Food updated successfully!";
                $item['image_path'] = $imagePath; // Update current view
            }
        } else {
            $error = "Database Error: " . $conn->error;
        }
    }
}
}

// Fetch categories for dropdown
$categories = $conn->query("SELECT * FROM food_categories ORDER BY name ASC");

$pageTitle = $id ? 'Edit Food' : 'Add Food';
require_once __DIR__ . '/layout_header.php';
?>

<div style="max-width: 800px; margin: 0 auto;">
    <a href="foods.php" style="text-decoration: none; color: var(--text-muted); display: flex; align-items: center; gap: 8px; margin-bottom: 16px;">
        ← Back to Foods
    </a>

    <div class="card">
        <h1><?= $id ? 'Edit Food' : 'Add New Food' ?></h1>

        <?php if ($message): ?>
            <div style="background: #dcfce7; color: #166534; padding: 12px; border-radius: 8px; margin-bottom: 20px;"><?= $message ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div style="background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 8px; margin-bottom: 20px;"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label class="form-label">Food Name</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($item['name']) ?>" required>
            </div>

            <div class="grid-col-2">
                <div class="form-group">
                    <label class="form-label">Calories (kcal)</label>
                    <input type="number" name="calories" class="form-control" value="<?= $item['calories'] ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-control">
                        <option value="">-- No Category --</option>
                        <?php while ($cat = $categories->fetch_assoc()): ?>
                            <option value="<?= $cat['id'] ?>" <?= $item['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Protein (g)</label>
                    <input type="number" step="0.1" name="protein" class="form-control" value="<?= $item['protein'] ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Carbs (g)</label>
                    <input type="number" step="0.1" name="carbs" class="form-control" value="<?= $item['carbs'] ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Fat (g)</label>
                    <input type="number" step="0.1" name="fat" class="form-control" value="<?= $item['fat'] ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Image</label>
                <?php if (!empty($item['image_path'])): ?>
                    <div style="margin-bottom: 8px;">
                        <img src="../../uploads/foods/<?= $item['image_path'] ?>" width="100" style="border-radius: 8px;">
                    </div>
                <?php endif; ?>
                <input type="file" name="image" class="form-control" accept="image/*">
                <div class="muted small" style="margin-top: 4px;">Recommended: Square image, max 2MB.</div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Description (Optional)</label>
                <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($item['description']) ?></textarea>
            </div>

            <button type="submit" class="btn btn-block">Save Food Item</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/layout_footer.php'; ?>
