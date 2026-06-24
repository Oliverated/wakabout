<?php
require_once __DIR__ . '/../includes/db.php';
define('UPLOAD_DIR', __DIR__ . '/../assets/post-img/');

$success = '';
$error   = '';

// Get existing post
// Get post id — from URL (?id=N) on GET, or hidden field on POST
$id = intval($_GET['id'] ?? $_POST['id'] ?? 0);
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

    // Quill blank state is '<p><br></p>' — strip tags to check actual content
    $bodyStripped = trim(strip_tags($body));
    if (empty($title) || empty($bodyStripped)) {
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
  <link rel="stylesheet" href="/assets/required.css">
</head>
<body>

<?php $activePage = 'manage_posts'; include 'sidebar.php'; ?>

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
        <form method="POST" action="edit_post.php?id=<?= $id ?>" enctype="multipart/form-data" class="dash-form-container">
          <!-- Keep id on POST in case action URL is stripped -->
          <input type="hidden" name="id" value="<?= $id ?>">
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
            <label for="cover_image">Cover Image</label>
            <?php if (!empty($post['cover_image'])): ?>
              <div style="margin-bottom:10px;">
                <img src="../<?= htmlspecialchars($post['cover_image']) ?>" alt="Current cover"
                     style="max-height:140px;border-radius:8px;border:1px solid var(--border,#333);object-fit:cover;">
                <p style="font-size:12px;opacity:.6;margin-top:4px;">Current: <?= htmlspecialchars(basename($post['cover_image'])) ?> — Upload a new file to replace it.</p>
              </div>
            <?php else: ?>
              <p style="font-size:12px;opacity:.6;margin-bottom:8px;">No cover image — upload one below.</p>
            <?php endif; ?>
            <input type="file" id="cover_image" name="cover_image" accept="image/*">
          </div>

          <div class="dash-form-group">
            <label for="excerpt">Excerpt (Short Preview)</label>
            <textarea id="excerpt" name="excerpt" rows="3" placeholder="Brief preview of the post..."><?= htmlspecialchars($post['excerpt']) ?></textarea>
          </div>

          <div class="dash-form-group">
            <label>Post Body *</label>
            <!-- Hidden textarea — value set by JS before submit, NOT pre-filled via PHP to avoid double-encoding -->
            <textarea id="body" name="body" style="display:none"></textarea>
            <!-- Quill editor container -->
            <div id="quill-editor"></div>
            <span id="body-error" style="display:none;color:#f87171;font-size:13px;margin-top:4px;">Post body cannot be empty.</span>
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

    // ── Fix Quill link sanitizer (must be BEFORE Quill init) ──
    const Link = Quill.import('formats/link');
    Link.sanitize = function(url) {
      if (!url.startsWith('http://') && !url.startsWith('https://') &&
          !url.startsWith('mailto:') && !url.startsWith('/')) {
        return 'https://' + url;
      }
      return url;
    };
    Quill.register(Link, true);

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

    // ── Pre-fill Quill with existing post body ─────────────────
    const existingBody = <?= json_encode($post['body'] ?? '') ?>;
    if (existingBody && existingBody.replace(/<[^>]*>/g, '').trim()) {
      quill.root.innerHTML = existingBody;
    }

    const bodyTA    = document.getElementById('body');
    const bodyError = document.getElementById('body-error');

    // Removed Link.sanitize here — moved before Quill init above

    // Sync Quill HTML → hidden textarea; validate before submit
    document.querySelector('form').addEventListener('submit', function(e) {
      const html  = quill.root.innerHTML;
      const text  = quill.getText().trim();
      const blank = html.trim() === '<p><br></p>';
      if (!text || blank) {
        e.preventDefault();
        bodyError.style.display = 'block';
        quill.focus();
        return;
      }
      bodyError.style.display = 'none';
      bodyTA.value = html;
    });

    quill.on('text-change', () => {
      if (quill.getText().trim()) bodyError.style.display = 'none';
    });
  </script>
</body>
</html>
