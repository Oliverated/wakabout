<?php
require_once __DIR__ . '/../includes/db.php';
define('UPLOAD_DIR', __DIR__ . '/../assets/post-img/');

$success = '';
$error   = '';

// Get existing post
$id = intval($_GET['id'] ?? 0);
$post = null;

if ($id > 0) {
    $stmt = $conn->prepare("SELECT * FROM posts WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $post = $result ? $result->fetch_assoc() : null;
    }
}

if (!$post) {
    header('Location: manage_posts.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title    = trim($_POST['title'] ?? '');
    // Handle multi-category
    $selectedCats = $_POST['categories'] ?? [];
    $category = !empty($selectedCats) ? implode(', ', array_map('trim', $selectedCats)) : 'General';
    $author   = trim($_POST['author'] ?? 'Wakabout Team');
    $excerpt  = trim($_POST['excerpt'] ?? '');
    $body     = trim($_POST['body'] ?? '');
    $slug     = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $title));
    $slug     = trim($slug, '-');

    // Handle cover image upload
    $cover_image = $post['cover_image']; // keep existing by default
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
            $stmt = $conn->prepare("UPDATE posts SET title = ?, slug = ?, category = ?, author = ?, excerpt = ?, body = ?, cover_image = ?, updated_at = NOW() WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("sssssssi", $title, $slug, $category, $author, $excerpt, $body, $cover_image, $id);
                $stmt->execute();
                if ($stmt->error) {
                    throw new Exception($stmt->error, $conn->errno);
                }
                $success = 'Post updated successfully! <a href="../post.php?slug=' . htmlspecialchars($slug) . '">View Post</a>';

                // Refresh post data
                $stmt2 = $conn->prepare("SELECT * FROM posts WHERE id = ?");
                if ($stmt2) {
                    $stmt2->bind_param("i", $id);
                    $stmt2->execute();
                    $result = $stmt2->get_result();
                    $post = $result ? $result->fetch_assoc() : $post;
                }
            } else {
                $error = 'Database error: ' . $conn->error;
            }
        } catch (Exception $e) {
            if ($conn->errno == 1062 || $e->getCode() == 1062) {
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
  <title>Edit Post — Wakabout Admin</title>
  <link rel="stylesheet" href="dashboard.css">
</head>
<body>

  <!-- Sidebar -->
  <aside class="dash-sidebar" id="sidebar">
    <div class="dash-sidebar-brand">
      <h2>Waka<span>bout</span></h2>
      <p>Admin Panel</p>
    </div>
    <ul class="dash-nav">
      <li><a href="dashboard.php">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25a2.25 2.25 0 0 1-2.25-2.25v-2.25Z"/></svg>
        Dashboard
      </a></li>
      <li><a href="create_post.php">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        Create Post
      </a></li>
      <li><a href="manage_posts.php">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg>
        Manage Posts
      </a></li>
    </ul>
    <div class="dash-nav-divider"></div>
    <div class="dash-sidebar-footer">
      <a href="../index.html">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3"/></svg>
        Back to Website
      </a>
    </div>
  </aside>

  <div class="dash-overlay" id="overlay"></div>

  <main class="dash-main">
    <div class="dash-topbar">
      <div style="display:flex;align-items:center;gap:16px;">
        <button class="dash-hamburger" id="hamburger">&#9776;</button>
        <h1>Edit Post</h1>
      </div>
      <a class="dash-btn dash-btn-ghost" href="manage_posts.php">← Back to Posts</a>
    </div>

    <?php if ($success): ?>
      <div class="dash-alert dash-alert-success"><?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="dash-alert dash-alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="dash-panel">
      <div class="dash-panel-header">
        <h2>Editing: <?= htmlspecialchars($post['title']) ?></h2>
      </div>
      <div style="padding: 24px;">
        <form method="POST" enctype="multipart/form-data" class="dash-form-container">
          <div class="dash-form-group">
            <label for="title">Post Title *</label>
            <input type="text" id="title" name="title" placeholder="Enter post title..." required
                   value="<?= htmlspecialchars($post['title']) ?>">
          </div>

          <div class="dash-form-row">
            <div class="dash-form-group" style="flex:1">
              <label>Categories <span style="font-size:12px;opacity:.6;">(select one or more)</span></label>
              <div class="cat-tag-group">
                <?php
                  $catRes = $conn->query("SELECT name, group_name FROM categories ORDER BY group_name, name");
                  $groupedCats = [];
                  if ($catRes) {
                      while ($row = $catRes->fetch_assoc()) {
                          $groupedCats[$row["group_name"]][] = $row["name"];
                      }
                  }
                  $prevCats = array_map('trim', explode(',', $post['category'] ?? 'General'));
                  if (isset($_POST['categories'])) {
                      $prevCats = $_POST['categories'];
                  }
                  foreach ($groupedCats as $gName => $cats):
                ?>
                <div style="width:100%;"><strong style="font-size:13px; color:var(--text);"><?= htmlspecialchars($gName) ?></strong></div>
                <?php foreach ($cats as $cat):
                        $checked = in_array($cat, $prevCats) ? 'checked' : '';
                ?>
                <label class="cat-tag">
                  <input type="checkbox" name="categories[]" value="<?= htmlspecialchars($cat) ?>" <?= $checked ?> hidden>
                  <span><?= htmlspecialchars($cat) ?></span>
                </label>
                <?php endforeach; ?>
                <?php endforeach; ?>
              </div>
            </div>
            <div class="dash-form-group">
              <label for="author">Author</label>
              <input type="text" id="author" name="author" placeholder="Author name"
                     value="<?= htmlspecialchars($post['author']) ?>">
            </div>
          </div>

          <div class="dash-form-group">
            <label for="cover_image">Cover Image <?= $post['cover_image'] ? '(current: ' . htmlspecialchars(basename($post['cover_image'])) . ')' : '' ?></label>
            <input type="file" id="cover_image" name="cover_image" accept="image/*">
          </div>

          <div class="dash-form-group">
            <label for="excerpt">Excerpt (Short Preview)</label>
            <textarea id="excerpt" name="excerpt" rows="3" placeholder="Brief preview of the post..."><?= htmlspecialchars($post['excerpt']) ?></textarea>
          </div>

          <div class="dash-form-group">
            <label>Post Body *</label>
            <!-- Hidden textarea synced on submit -->
            <textarea id="body" name="body" style="display:none" required><?= htmlspecialchars($post['body']) ?></textarea>
            <!-- Quill editor container -->
            <div id="quill-editor"></div>
          </div>

          <button type="submit" class="dash-submit-btn">Update Post</button>
        </form>
      </div>
    </div>
  </main>

  <style>
  #quill-editor {
    background: var(--input-bg, #1e1e2e);
    border: 1.5px solid var(--border, #333355);
    border-radius: 0 0 8px 8px;
    min-height: 380px;
    font-size: 15px;
    color: var(--text, #e0e0f0);
  }
  .cat-tag-group { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 6px; }
  .cat-tag span {
    display: inline-block;
    padding: 6px 16px;
    border-radius: 20px;
    border: 1.5px solid var(--border, #ddd);
    font-size: 13px;
    cursor: pointer;
    transition: background .15s, color .15s, border-color .15s;
    user-select: none;
  }
  .cat-tag input:checked + span {
    background: var(--accent);
    color: #fff;
    border-color: var(--accent, #4f46e5);
  }
  .cat-tag:hover span { border-color: var(--accent, #ff1f1fff); }
  .ql-toolbar {
    background: var(--card-bg, #16162a);
    border: 1.5px solid var(--border, #333355);
    border-radius: 8px 8px 0 0;
    border-bottom: none;
  }
  .ql-toolbar .ql-stroke { stroke: #a0a0c0; }
  .ql-toolbar .ql-fill   { fill:   #a0a0c0; }
  .ql-toolbar .ql-picker-label { color: #a0a0c0; }
  .ql-toolbar button:hover .ql-stroke,
  .ql-toolbar .ql-active  .ql-stroke { stroke: #fff; }
  .ql-toolbar button:hover .ql-fill,
  .ql-toolbar .ql-active  .ql-fill   { fill:   #fff; }
  .ql-toolbar .ql-picker-options { background: #1e1e2e; color: #e0e0f0; }
  .ql-editor.ql-blank::before { color: #666; font-style: italic; }
  </style>

  <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
  <script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
  <script>
    const hamburger = document.getElementById('hamburger');
    const sidebar   = document.getElementById('sidebar');
    const overlay   = document.getElementById('overlay');

    hamburger.addEventListener('click', () => {
      sidebar.classList.toggle('open');
      overlay.classList.toggle('active');
    });

    overlay.addEventListener('click', () => {
      sidebar.classList.remove('open');
      overlay.classList.remove('active');
    });

    // Toggle category tags
    document.querySelectorAll('.cat-tag input[type=checkbox]').forEach(cb => {
      cb.addEventListener('change', () => {
        cb.closest('.cat-tag').querySelector('span').style.fontWeight = cb.checked ? '600' : '';
      });
      if (cb.checked) cb.closest('.cat-tag').querySelector('span').style.fontWeight = '600';
    });

    // ── Quill WYSIWYG ──────────────────────────────────────────────
    const quill = new Quill('#quill-editor', {
      theme: 'snow',
      placeholder: 'Write your full article here…',
      modules: {
        toolbar: {
          container: [
            [{ header: [1, 2, 3, false] }],
            ['bold', 'italic', 'underline', 'strike'],
            [{ color: [] }, { background: [] }],
            [{ list: 'ordered' }, { list: 'bullet' }],
            [{ align: [] }],
            ['blockquote', 'code-block'],
            ['link', 'image'],
            ['clean']
          ],
          handlers: {
            image: function() {
              const input = document.createElement('input');
              input.setAttribute('type', 'file');
              input.setAttribute('accept', 'image/*');
              input.click();
              input.onchange = () => {
                const file = input.files[0];
                if (!file) return;
                const fd = new FormData();
                fd.append('file', file);
                fetch('upload_image.php', { method: 'POST', body: fd })
                  .then(r => r.json())
                  .then(data => {
                    if (data.location) {
                      const range = quill.getSelection(true);
                      quill.insertEmbed(range.index, 'image', data.location);
                    } else {
                      alert('Image upload failed: ' + (data.error || 'Unknown error'));
                    }
                  })
                  .catch(() => alert('Image upload request failed.'));
              };
            }
          }
        }
      }
    });

    // Pre-fill editor with existing post body
    const bodyTA = document.getElementById('body');
    if (bodyTA.value.trim()) {
      quill.root.innerHTML = bodyTA.value;
    }

    // Sync Quill HTML → hidden textarea on submit
    document.querySelector('form').addEventListener('submit', () => {
      bodyTA.value = quill.root.innerHTML;
    });
  </script>
</body>
</html>
