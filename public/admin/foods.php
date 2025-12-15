<?php
require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/ensure_admin.php'; // Ensure admin access

use App\Config\Database;

$db = new Database();
$conn = $db->conn;

// Handle Delete Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = (int)$_POST['delete_id'];
    $stmt = $conn->prepare("DELETE FROM foods WHERE id = ?");
    $stmt->bind_param("i", $deleteId);
    if ($stmt->execute()) {
        $success = "Food item deleted successfully.";
    } else {
        $error = "Failed to delete food item.";
    }
}

// Fetch foods with category name if available (Left join in case no category)
$sql = "SELECT f.*, c.name as category_name 
        FROM foods f 
        LEFT JOIN food_categories c ON f.category_id = c.id 
        ORDER BY f.id DESC";
$result = $conn->query($sql);

$pageTitle = 'Manage Foods';
require_once __DIR__ . '/layout_header.php';
?>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <h1>Manage Foods</h1>
        <a href="food_form.php" class="btn">+ Add Food</a>
    </div>

    <?php if (isset($success)): ?>
        <div style="background: #dcfce7; color: #166534; padding: 12px; border-radius: 8px; margin-bottom: 16px;"><?= $success ?></div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <div style="background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 8px; margin-bottom: 16px;"><?= $error ?></div>
    <?php endif; ?>

    <div class="card" style="padding: 0; overflow: hidden;">
        <table>
            <thead>
                <tr>
                    <th width="60">Img</th>
                    <th>Name</th>
                    <th>Calories</th>
                    <th>Macros (P/C/F)</th>
                    <!-- <th>Category</th> -->
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($food = $result->fetch_assoc()): ?>
                <tr>
                    <td>
                        <?php if (!empty($food['image_path'])): ?>
                            <img src="../../uploads/foods/<?= htmlspecialchars($food['image_path']) ?>" alt="" style="width: 40px; height: 40px; object-fit: cover; border-radius: 8px;">
                        <?php else: ?>
                            <div style="width: 40px; height: 40px; background: #f1f5f9; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #94a3b8; font-size: 10px;">No Img</div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <strong><?= htmlspecialchars($food['name']) ?></strong>
                        <?php if ($food['is_verified']): ?>
                            <span title="Verified" style="color: #0ea5e9;">✓</span>
                        <?php endif; ?>
                    </td>
                    <td><?= $food['calories'] ?> kcal</td>
                    <td>
                        <span style="color: #0ea5e9;"><?= (float)$food['protein'] ?>g</span> / 
                        <span style="color: #eab308;"><?= (float)$food['carbs'] ?>g</span> / 
                        <span style="color: #ef4444;"><?= (float)$food['fat'] ?>g</span>
                    </td>
                    <!-- <td><?= htmlspecialchars($food['category_name'] ?? '-') ?></td> -->
                    <td>
                        <a href="food_form.php?id=<?= $food['id'] ?>" class="btn small" style="background: #fff; color: #64748b; border: 1px solid #e2e8f0; margin-right: 4px;">Edit</a>
                        
                        <form method="POST" onsubmit="return confirm('Delete this food item?');" style="display:inline;">
                            <input type="hidden" name="delete_id" value="<?= $food['id'] ?>">
                            <button type="submit" class="btn small danger">Del</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

<?php require_once __DIR__ . '/layout_footer.php'; ?>
