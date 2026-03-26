<?php
require_once __DIR__ . '/../includes/db.php';

$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title    = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? 'General');
    $author   = trim($_POST['author'] ?? 'Wakabout Team');
    $excerpt  = trim($_POST['excerpt'] ?? '');
    $body     = trim($_POST['body'] ?? '');
    $slug     = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $title));
    $slug     = trim($slug, '-');

    // Handle cover image upload
    $cover_image = '';
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if (in_array($ext, $allowed)) {
            $filename = $slug . '.' . $ext;
            $dest = UPLOAD_DIR . $filename;

            if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $dest)) {
                $cover_image = 'assets/post-img/' . $filename;
            } else {
                $error = 'Failed to upload image.';
            }
        } else {
            $error = 'Invalid image format. Allowed: jpg, jpeg, png, webp, gif';
        }
    }

    if (empty($title) || empty($body)) {
        $error = 'Title and Body are required.';
    }

    if (empty($error)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO posts (title, slug, category, author, excerpt, body, cover_image, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, datetime('now','localtime'))");
            $stmt->execute([$title, $slug, $category, $author, $excerpt, $body, $cover_image]);
            $success = 'Post created successfully! <a href="../post.php?slug=' . htmlspecialchars($slug) . '">View Post</a>';
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = 'A post with a similar title already exists. Please change the title.';
            } else {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Post - Wakabout Admin</title>
  <link rel="stylesheet" href="../assets/required.css">
  <style>
    .admin-container {
      max-width: 800px;
      margin: 15% auto 5%;
      padding: 40px;
      background: var(--primary);
      border: 1px solid rgba(0,0,0,0.05);
      border-radius: 16px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }

    .admin-container h1 {
      font-size: 32px;
      margin-bottom: 30px;
      color: var(--secondary);
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      font-weight: 600;
      margin-bottom: 8px;
      font-size: 15px;
      color: var(--secondary);
    }

    .form-group input,
    .form-group textarea,
    .form-group select {
      width: 100%;
      padding: 12px 16px;
      border: 1px solid rgba(0,0,0,0.1);
      border-radius: 8px;
      font-size: 15px;
      font-family: var(--ff);
      transition: border-color 0.3s ease;
      background-color: #fafafa;
    }

    .form-group input:focus,
    .form-group textarea:focus,
    .form-group select:focus {
      outline: none;
      border-color: var(--accent);
      box-shadow: 0 0 0 3px rgba(255, 0, 0, 0.1);
    }

    .form-group textarea {
      min-height: 200px;
      resize: vertical;
    }

    .form-row {
      display: flex;
      gap: 20px;
    }

    .form-row .form-group {
      flex: 1;
    }

    .submit-btn {
      background-color: var(--accent);
      color: var(--primary);
      border: none;
      padding: 14px 30px;
      font-size: 16px;
      font-weight: bold;
      border-radius: 8px;
      cursor: pointer;
      transition: all 0.3s ease;
      width: 100%;
    }

    .submit-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(255, 0, 0, 0.3);
    }

    .alert {
      padding: 15px 20px;
      border-radius: 8px;
      margin-bottom: 20px;
      font-size: 15px;
    }

    .alert-success {
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }

    .alert-error {
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }

    .alert a {
      color: inherit;
      font-weight: bold;
      text-decoration: underline;
    }

    .back-link {
      display: inline-block;
      margin-bottom: 20px;
      color: var(--accent);
      font-weight: bold;
      font-size: 15px;
    }

    .back-link:hover {
      transform: translateX(-5px);
    }
  </style>
</head>
<body>
  <div class="admin-container">
    <a href="../index.html" class="back-link">&larr; Back to Home</a>
    <h1>Create New Post</h1>

    <?php if ($success): ?>
      <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
      <div class="form-group">
        <label for="title">Post Title *</label>
        <input type="text" id="title" name="title" placeholder="Enter post title..." required
               value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="category">Category</label>
          <select id="category" name="category">
            <option value="General">General</option>
            <option value="Travel">Travel</option>
            <option value="Tourism">Tourism</option>
            <option value="Food">Food</option>
            <option value="Culture">Culture</option>
            <option value="Events">Events</option>
            <option value="News">News</option>
          </select>
        </div>
        <div class="form-group">
          <label for="author">Author</label>
          <input type="text" id="author" name="author" placeholder="Author name"
                 value="<?= htmlspecialchars($_POST['author'] ?? 'Wakabout Team') ?>">
        </div>
      </div>

      <div class="form-group">
        <label for="cover_image">Cover Image</label>
        <input type="file" id="cover_image" name="cover_image" accept="image/*">
      </div>

      <div class="form-group">
        <label for="excerpt">Excerpt (Short Preview)</label>
        <textarea id="excerpt" name="excerpt" rows="3" placeholder="Brief preview of the post..."><?= htmlspecialchars($_POST['excerpt'] ?? '') ?></textarea>
      </div>

      <div class="form-group">
        <label for="body">Post Body (HTML Supported) *</label>
        <textarea id="body" name="body" rows="10" placeholder="Write your full article here... HTML tags like <p>, <h2>, <ul> are supported." required><?= htmlspecialchars($_POST['body'] ?? '') ?></textarea>
      </div>

      <button type="submit" class="submit-btn">Publish Post</button>
    </form>
  </div>
</body>
</html>
