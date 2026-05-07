<?php
require_once __DIR__ . '/../includes/db.php';
define('UPLOAD_DIR', __DIR__ . '/../assets/post-img/');

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $author      = trim($_POST['author'] ?? 'Wakabout Team');
    $category    = trim($_POST['category'] ?? 'General');
    $description = trim($_POST['description'] ?? '');
    $price       = trim($_POST['price'] ?? '');
    $buy_link    = trim($_POST['buy_link'] ?? '');
    $cover_image = '';

    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','webp','gif'])) {
            $filename = 'book-' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['cover_image']['tmp_name'], UPLOAD_DIR . $filename)) {
                $cover_image = 'assets/post-img/' . $filename;
            } else { $error = 'Failed to upload image.'; }
        } else { $error = 'Invalid file type. Use jpg, png, webp, or gif.'; }
    }

    if (empty($title)) { $error = 'Title is required.'; }

    if (empty($error)) {
        $stmt = $conn->prepare("INSERT INTO books (title, author, category, description, cover_image, price, buy_link) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sssssss", $title, $author, $category, $description, $cover_image, $price, $buy_link);
            if ($stmt->execute()) {
                $success = 'Book added successfully! <a href="manage_books.php">View All Books</a>';
                $_POST = [];
            } else { $error = 'Database error: ' . $stmt->error; }
        } else { $error = 'Prepare failed: ' . $conn->error; }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Book — Wakabout Admin</title>
  <link rel="stylesheet" href="dashboard.css">
</head>
<body>

<?php $activePage = 'create_book'; include 'sidebar.php'; ?>

  <main class="dash-main">
    <div class="dash-topbar">
      <div style="display:flex;align-items:center;gap:16px;">
        <button class="dash-hamburger" id="hamburger">&#9776;</button>
        <h1>Add Book</h1>
      </div>
      <a class="dash-btn dash-btn-ghost" href="manage_books.php">← Manage Books</a>
    </div>

    <?php if ($success): ?><div class="dash-alert dash-alert-success"><?= $success ?></div><?php endif; ?>
    <?php if ($error): ?><div class="dash-alert dash-alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="dash-panel">
      <div class="dash-panel-header"><h2>New Book</h2></div>
      <div style="padding:24px;">
        <form method="POST" enctype="multipart/form-data" class="dash-form-container">
          <div class="dash-form-group">
            <label>Book Title *</label>
            <input type="text" name="title" placeholder="Enter book title..." required value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
          </div>
          <div class="dash-form-row">
            <div class="dash-form-group">
              <label>Author</label>
              <input type="text" name="author" placeholder="Author name" value="<?= htmlspecialchars($_POST['author'] ?? 'Wakabout Team') ?>">
            </div>
            <div class="dash-form-group">
              <label>Category</label>
              <select name="category">
                <?php foreach (['General','Travel Guide','Culture','Food & Drink','Business','Photography'] as $cat): ?>
                  <option value="<?= $cat ?>" <?= ($_POST['category'] ?? '') === $cat ? 'selected' : '' ?>><?= $cat ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="dash-form-row">
            <div class="dash-form-group">
              <label>Price (e.g. $5)</label>
              <input type="text" name="price" placeholder="e.g. $5" value="<?= htmlspecialchars($_POST['price'] ?? '') ?>">
            </div>
            <div class="dash-form-group">
              <label>Buy Link</label>
              <input type="url" name="buy_link" placeholder="https://wa.me/..." value="<?= htmlspecialchars($_POST['buy_link'] ?? '') ?>">
            </div>
          </div>
          <div class="dash-form-group">
            <label>Cover Image</label>
            <input type="file" name="cover_image" accept="image/*">
          </div>
          <div class="dash-form-group">
            <label>Description</label>
            <textarea name="description" rows="4" placeholder="Brief description of the book..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
          </div>
          <button type="submit" class="dash-submit-btn">Add Book</button>
        </form>
      </div>
    </div>
  </main>

  <script>
    const hamburger = document.getElementById('hamburger');
    const sidebar   = document.getElementById('sidebar');
    const overlay   = document.getElementById('overlay');
    hamburger.addEventListener('click', () => { sidebar.classList.toggle('open'); overlay.classList.toggle('active'); });
    overlay.addEventListener('click', () => { sidebar.classList.remove('open'); overlay.classList.remove('active'); });
  </script>
</body>
</html>
