<?php
require_once __DIR__ . '/../includes/db.php';
define('UPLOAD_DIR', __DIR__ . '/../assets/post-img/');

$success = '';
$error   = '';

// Load book by ID
$id   = intval($_GET['id'] ?? $_POST['id'] ?? 0);
$book = null;

if ($id > 0) {
    $stmt = $conn->prepare("SELECT * FROM books WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $book   = $result ? $result->fetch_assoc() : null;
    }
}

if (!$book) {
    header('Location: manage_books.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title']       ?? '');
    $author      = trim($_POST['author']      ?? 'Wakabout Team');
    $category    = trim($_POST['category']    ?? 'General');
    $description = trim($_POST['description'] ?? '');
    $price       = trim($_POST['price']       ?? '');
    $buy_link    = trim($_POST['buy_link']    ?? '');
    $cover_image = $book['cover_image']; // keep existing by default

    if (empty($title)) {
        $error = 'Title is required.';
    }

    // Handle optional new cover image upload
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
            $filename = 'book-' . $id . '-' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['cover_image']['tmp_name'], UPLOAD_DIR . $filename)) {
                $cover_image = 'assets/post-img/' . $filename;
            } else {
                $error = 'Failed to upload image.';
            }
        } else {
            $error = 'Invalid file type. Use jpg, png, webp, or gif.';
        }
    }

    if (empty($error)) {
        $stmt = $conn->prepare("UPDATE books SET title=?, author=?, category=?, description=?, cover_image=?, price=?, buy_link=? WHERE id=?");
        if ($stmt) {
            $stmt->bind_param("sssssssi", $title, $author, $category, $description, $cover_image, $price, $buy_link, $id);
            if ($stmt->execute()) {
                $success = 'Book updated successfully! <a href="manage_books.php">View All Books</a>';
                // Refresh data
                $stmt2 = $conn->prepare("SELECT * FROM books WHERE id = ?");
                if ($stmt2) {
                    $stmt2->bind_param("i", $id);
                    $stmt2->execute();
                    $result2 = $stmt2->get_result();
                    $book    = $result2 ? $result2->fetch_assoc() : $book;
                }
            } else {
                $error = 'Database error: ' . $stmt->error;
            }
        } else {
            $error = 'Prepare failed: ' . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Book — Wakabout Admin</title>
  <link rel="stylesheet" href="dashboard.css">
</head>
<body>

<?php $activePage = 'books'; include 'sidebar.php'; ?>

  <main class="dash-main">
    <div class="dash-topbar">
      <div style="display:flex;align-items:center;gap:16px;">
        <button class="dash-hamburger" id="hamburger">&#9776;</button>
        <h1>Edit Book</h1>
      </div>
      <a class="dash-btn dash-btn-ghost" href="manage_books.php">← Manage Books</a>
    </div>

    <?php if ($success): ?>
      <div class="dash-alert dash-alert-success"><?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="dash-alert dash-alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="dash-panel">
      <div class="dash-panel-header">
        <h2>Editing: <?= htmlspecialchars($book['title']) ?></h2>
      </div>
      <div style="padding:24px;">
        <form method="POST" action="edit_book.php?id=<?= $id ?>" enctype="multipart/form-data" class="dash-form-container">
          <input type="hidden" name="id" value="<?= $id ?>">

          <div class="dash-form-group">
            <label for="title">Book Title *</label>
            <input type="text" id="title" name="title" placeholder="Enter book title..." required
                   value="<?= htmlspecialchars($book['title']) ?>">
          </div>

          <div class="dash-form-row">
            <div class="dash-form-group">
              <label for="author">Author</label>
              <input type="text" id="author" name="author" placeholder="Author name"
                     value="<?= htmlspecialchars($book['author'] ?? 'Wakabout Team') ?>">
            </div>
            <div class="dash-form-group">
              <label for="category">Category</label>
              <select id="category" name="category">
                <?php foreach (['General','Travel Guide','Culture','Food & Drink','Business','Photography'] as $cat): ?>
                  <option value="<?= $cat ?>" <?= ($book['category'] ?? '') === $cat ? 'selected' : '' ?>><?= $cat ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="dash-form-row">
            <div class="dash-form-group">
              <label for="price">Price (e.g. $5)</label>
              <input type="text" id="price" name="price" placeholder="e.g. $5"
                     value="<?= htmlspecialchars($book['price'] ?? '') ?>">
            </div>
            <div class="dash-form-group">
              <label for="buy_link">Buy Link</label>
              <input type="url" id="buy_link" name="buy_link" placeholder="https://wa.me/..."
                     value="<?= htmlspecialchars($book['buy_link'] ?? '') ?>">
            </div>
          </div>

          <div class="dash-form-group">
            <label for="cover_image">Cover Image</label>
            <?php if (!empty($book['cover_image'])): ?>
              <div style="margin-bottom:10px;">
                <img src="../<?= htmlspecialchars($book['cover_image']) ?>" alt="Current cover"
                     style="max-height:140px;border-radius:8px;border:1px solid var(--border,#333);object-fit:cover;">
                <p style="font-size:12px;opacity:.6;margin-top:4px;">
                  Current: <?= htmlspecialchars(basename($book['cover_image'])) ?> — Upload a new file to replace it.
                </p>
              </div>
            <?php else: ?>
              <p style="font-size:12px;opacity:.6;margin-bottom:8px;">No cover image yet — upload one below.</p>
            <?php endif; ?>
            <input type="file" id="cover_image" name="cover_image" accept="image/*">
          </div>

          <div class="dash-form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4" placeholder="Brief description of the book..."><?= htmlspecialchars($book['description'] ?? '') ?></textarea>
          </div>

          <button type="submit" class="dash-submit-btn">Update Book</button>
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
