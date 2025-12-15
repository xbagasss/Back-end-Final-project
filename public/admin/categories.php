<?php
require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/ensure_admin.php';

use App\Config\Database;

$db = new Database();
$conn = $db->conn;

// Handle Add Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = trim($_POST['name']);
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    
    if (!empty($name)) {
        $stmt = $conn->prepare("INSERT INTO food_categories (name, slug) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $slug);
        if ($stmt->execute()) {
            $success = "Category added successfully.";
        } else {
            $error = "Failed to add category (might be duplicate).";
        }
    }
}

// Handle Delete Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = (int)$_POST['id'];
    $stmt = $conn->prepare("DELETE FROM food_categories WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $success = "Category deleted.";
    } else {
        $error = "Failed to delete category.";
    }
}

$categories = $conn->query("SELECT * FROM food_categories ORDER BY name ASC");

$pageTitle = 'Manage Categories';
require_once __DIR__ . '/layout_header.php';
?>

<div style="max-width: 800px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <h1>Manage Categories</h1>
    </div>

    <?php if (isset($success)): ?>
        <div style="background: #dcfce7; color: #166534; padding: 12px; border-radius: 8px; margin-bottom: 16px;"><?= $success ?></div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <div style="background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 8px; margin-bottom: 16px;"><?= $error ?></div>
    <?php endif; ?>

    <div class="grid-col-2">
        <!-- Add Form -->
        <div class="card" style="height: fit-content;">
            <h3>Add New Category</h3>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label class="form-label">Category Name</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Protein Source" required>
                </div>
                <button type="submit" class="btn btn-block">Add Category</button>
            </form>
        </div>

        <!-- List -->
        <div class="card" style="padding: 0; overflow: hidden;">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th width="80">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($categories->num_rows === 0): ?>
                        <tr><td colspan="3" style="text-align:center; padding: 24px;" class="muted">No categories yet.</td></tr>
                    <?php else: ?>
                        <?php while ($cat = $categories->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($cat['name']) ?></strong></td>
                            <td class="muted small"><?= htmlspecialchars($cat['slug']) ?></td>
                            <td>
                                <form method="POST" onsubmit="return confirm('Delete this category?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                    <button type="submit" class="btn small danger">Del</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/layout_footer.php'; ?>
