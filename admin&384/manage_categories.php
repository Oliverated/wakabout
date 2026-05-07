<?php
require_once __DIR__ . '/../includes/db.php';

$success = '';
$error = '';

// Handle Delete Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_category'])) {
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $success = "Category deleted successfully.";
    } else {
        $error = "Failed to delete category.";
    }
}

// Handle Add Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = trim($_POST['name']);
    $group_name = trim($_POST['group_name']);
    
    if (empty($name) || empty($group_name)) {
        $error = "Category Name and Group Name are required.";
    } else {
        try {
            $stmt = $conn->prepare("INSERT INTO categories (name, group_name) VALUES (?, ?)");
            if ($stmt) {
                $stmt->bind_param("ss", $name, $group_name);
                $stmt->execute();
                if ($stmt->error) {
                    throw new Exception($stmt->error, $conn->errno);
                }
                $success = "Category added successfully.";
            } else {
                $error = "Database error: " . $conn->error;
            }
        } catch (Exception $e) {
            if ($conn->errno == 1062 || $e->getCode() == 1062) {
                $error = "This category name already exists.";
            } else {
                $error = "Database error: " . $e->getMessage();
            }
        }
    }
}

// Fetch all categories
$categories = [];
$catRes = $conn->query("SELECT * FROM categories ORDER BY group_name, name");
if ($catRes) {
    $categories = $catRes->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Categories - Wakabout Admin</title>
  <link rel="stylesheet" href="dashboard.css">
  <style>
    .cat-table-wrapper { overflow-x: auto; margin-top: 20px; }
    .cat-table { width: 100%; border-collapse: collapse; min-width: 600px; }
    .cat-table th { background: var(--card-bg, #16162a); color: var(--text-mut, #a0a0c0); padding: 12px 16px; text-align: left; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; border-bottom: 2px solid var(--border, #333355); }
    .cat-table td { padding: 16px; border-bottom: 1px solid var(--border, #333355); font-size: 15px; color: var(--text, #e0e0f0); }
    .cat-table tr:hover { background: rgba(255,255,255,0.02); }
    .btn-delete { color: #f87171; background: none; border: none; cursor: pointer; font-size: 14px; text-decoration: underline; }
    .btn-delete:hover { color: #ef4444; }
  </style>
</head>
<body>

<?php $activePage = 'manage_categories'; include 'sidebar.php'; ?>

  <main class="dash-main">
    <div class="dash-topbar">
      <div style="display:flex;align-items:center;gap:16px;">
        <button class="dash-hamburger" id="hamburger">&#9776;</button>
        <h1>Manage Categories</h1>
      </div>
    </div>

    <?php if (isset($success) && $success): ?>
      <div class="dash-alert dash-alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if (isset($error) && $error): ?>
      <div class="dash-alert dash-alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="dash-grid" style="grid-template-columns: 1fr 2fr;">
      <!-- Add Category Form -->
      <div class="dash-panel" style="height:fit-content;">
        <div class="dash-panel-header">
          <h2>Add New Category</h2>
        </div>
        <div style="padding:24px;">
          <form method="POST" class="dash-form-container">
            <input type="hidden" name="add_category" value="1">
            <div class="dash-form-group">
              <label for="name">Category Name</label>
              <input type="text" id="name" name="name" placeholder="e.g. Surfing" required>
            </div>
            <div class="dash-form-group">
              <label for="group_name">Group / Parent</label>
              <input type="text" id="group_name" name="group_name" placeholder="e.g. Experiences & Lifestyle" required list="groups_list">
              <datalist id="groups_list">
                <option value="Destinations">
                <option value="Culture & Heritage">
                <option value="Experiences & Lifestyle">
                <option value="Reviews">
                <option value="Diaries & Columns">
              </datalist>
            </div>
            <button type="submit" class="dash-submit-btn">Add Category</button>
          </form>
        </div>
      </div>

      <!-- Categories List -->
      <div class="dash-panel">
        <div class="dash-panel-header">
          <h2>Existing Categories</h2>
        </div>
        <div class="cat-table-wrapper">
          <table class="cat-table">
            <thead>
              <tr>
                <th>Category Name</th>
                <th>Group Name</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($categories)): ?>
                <tr><td colspan="3" style="text-align:center;">No categories found.</td></tr>
              <?php else: ?>
                <?php foreach ($categories as $cat): ?>
                  <tr>
                    <td style="font-weight:600;"><?= htmlspecialchars($cat['name']) ?></td>
                    <td><?= htmlspecialchars($cat['group_name']) ?></td>
                    <td>
                      <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this category?');">
                        <input type="hidden" name="delete_category" value="1">
                        <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                        <button type="submit" class="btn-delete">Delete</button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </main>

  <div class="dash-overlay" id="overlay"></div>
  <script>
    const hamburger = document.getElementById('hamburger');
    const sidebar   = document.getElementById('sidebar');
    const overlay   = document.getElementById('overlay');
    hamburger.addEventListener('click', () => { sidebar.classList.toggle('open'); overlay.classList.toggle('active'); });
    overlay.addEventListener('click', () => { sidebar.classList.remove('open'); overlay.classList.remove('active'); });
  </script>
</body>
</html>
