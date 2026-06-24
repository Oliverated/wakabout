<?php
require_once '../includes/db.php';
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

define('UPLOAD_DIR', __DIR__ . '/../assets/post-img/');

$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title    = trim($_POST['title'] ?? '');
    $author   = trim($_POST['author'] ?? 'Wakabout Team');
    $excerpt  = trim($_POST['excerpt'] ?? '');
    $body     = trim($_POST['body'] ?? '');
    $slug     = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $title));
    $slug     = trim($slug, '-');

    // Handle multi-category
    $selectedCats = $_POST['categories'] ?? [];
    $category = !empty($selectedCats) ? implode(', ', array_map('trim', $selectedCats)) : 'General';

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
            $stmt = $conn->prepare("INSERT INTO posts (title, slug, category, author, excerpt, body, cover_image, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            if ($stmt) {
                $stmt->bind_param("sssssss", $title, $slug, $category, $author, $excerpt, $body, $cover_image);
                $stmt->execute();
                if ($stmt->error) {
                    throw new Exception($stmt->error, $conn->errno);
                }
                $success = 'Post created successfully! <a href="../post.php?slug=' . htmlspecialchars($slug) . '">View Post</a>';
                
                // ── Send Email to Subscribers ──
                $subRes = $conn->query("SELECT email FROM subscribers");
                if ($subRes && $subRes->num_rows > 0) {
                    $mail = new PHPMailer(true);
                    try {
              $mail->isSMTP();
$mail->Host       = 'smtp.hostinger.com';
$mail->SMTPAuth   = true;
$mail->Username   = 'paw@wakaabout.net';
$mail->Password   = 'Pelu@952';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL
$mail->Port       = 465;

$mail->setFrom('paw@wakaabout.net', 'WakaAbout Blog');
                        while ($row = $subRes->fetch_assoc()) {
                            $mail->addBCC($row['email']);
                        }

                        $mail->isHTML(true);
                        $mail->Subject = 'New Post: ' . $title;
                        
                        $postLink = 'https://www.wakaabout.net/post.php?slug=' . urlencode($slug);
                        $emailBody = '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: auto;">';
                        $emailBody .= '<h2 style="color: #333;">' . htmlspecialchars($title) . '</h2>';
                        if (!empty($cover_image)) {
                            $emailBody .= '<img src="https://www.wakaabout.net/' . htmlspecialchars($cover_image) . '" style="width: 100%; max-height: 300px; object-fit: cover; border-radius: 8px;" alt="Cover Image">';
                        }
                        if (!empty($excerpt)) {
                            $emailBody .= '<p style="color: #555; font-size: 16px;">' . nl2br(htmlspecialchars($excerpt)) . '</p>';
                        } else {
                            $emailBody .= '<p style="color: #555; font-size: 16px;">A new post has been published on WakaAbout Blog. Read it now!</p>';
                        }
                        $emailBody .= '<a href="' . $postLink . '" style="display: inline-block; padding: 10px 20px; background-color: #e60000ff; color: #fff; text-decoration: none; border-radius: 10px; margin-top: 15px;">Read Full Article</a>';
                        $emailBody .= '<p style="margin-top: 30px; font-size: 12px; color: #888;">You are receiving this email because you subscribed to WakaAbout Blog
<div>  <a href="https://www.wakaabout.net/ajax/unsubscribe.php" style="color: #888; text-decoration: underline;">unsubscribe</a> </div> </p>';                      $emailBody .= '</div>';

                        $mail->Body = $emailBody;
                        $mail->send();
                    } catch (Exception $e) {
                        // Silently fail if email doesn't send, so it doesn't break post creation
                        error_log("Failed to send subscriber emails: " . $mail->ErrorInfo);
                    }
                }
                
                $_POST = []; // Clear form data on success
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
    <title>Create Post - Wakabout Admin</title>
    <link rel="stylesheet" href="dashboard.css">
    <!-- <link rel="stylesheet" href="/assets/required.css"> -->
</head>

<body>

    <?php $activePage = 'create_post'; include 'sidebar.php'; ?>


    <main class="dash-main">
        <div class="dash-topbar">
            <div style="display:flex;align-items:center;gap:16px;">
                <button class="dash-hamburger" id="hamburger">&#9776;</button>
                <h1>Create Post</h1>
            </div>
            <a class="dash-btn dash-btn-ghost" href="manage_posts.php">← Manage Posts</a>
        </div>

        <?php if ($success): ?>
        <div class="dash-alert dash-alert-success"><?= $success ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
        <div class="dash-alert dash-alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="dash-panel">
            <div class="dash-panel-header">
                <h2>Write a New Article</h2>
            </div>
            <div style="padding: 24px;">
                <form method="POST" enctype="multipart/form-data" class="dash-form-container">
                    <div class="dash-form-group">
                        <label for="title">Post Title *</label>
                        <input type="text" id="title" name="title" placeholder="Enter post title..." required
                            value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
                    </div>

                    <div class="dash-form-row">
                        <div class="dash-form-group" style="flex:1">
                            <label>Categories <span style="font-size:12px;opacity:.6;">(select one or
                                    more)</span></label>
                            <div class="cat-tag-group">
                                <?php
                  $catRes = $conn->query("SELECT name, group_name FROM categories ORDER BY group_name, name");
                  $groupedCats = [];
                  if ($catRes) {
                      while ($row = $catRes->fetch_assoc()) {
                          $groupedCats[$row["group_name"]][] = $row["name"];
                      }
                  }
                  $prevCats = $_POST['categories'] ?? [];
                  foreach ($groupedCats as $gName => $cats):
                ?>
                                <div style="width:100%;"><strong
                                        style="font-size:13px; color:var(--text);"><?= htmlspecialchars($gName) ?></strong>
                                </div>
                                <?php foreach ($cats as $cat):
                        $checked = in_array($cat, $prevCats) ? 'checked' : '';
                ?>
                                <label class="cat-tag">
                                    <input type="checkbox" name="categories[]" value="<?= htmlspecialchars($cat) ?>"
                                        <?= $checked ?> hidden>
                                    <span><?= htmlspecialchars($cat) ?></span>
                                </label>
                                <?php endforeach; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>

                    </div>

                    <div class="dash-form-group-flex">
                        <div class="dash-form-group">
                            <label for="author">Author</label>
                            <input type="text" id="author" name="author" placeholder="Author name"
                                value="<?= htmlspecialchars($_POST['author'] ?? 'Wakabout Team') ?>">
                        </div>

                        <div class="dash-form-group">
                            <label for="cover_image">Cover Image</label>
                            <input type="file" id="cover_image" name="cover_image" accept="image/*">
                        </div>
                    </div>



                    <div class="dash-form-group">
                        <label for="excerpt">Excerpt (Short Preview)</label>
                        <textarea id="excerpt" name="excerpt" rows="3"
                            placeholder="Brief preview of the post..."><?= htmlspecialchars($_POST['excerpt'] ?? '') ?></textarea>
                    </div>

                    <div class="dash-form-group">
                        <label>Post Body *</label>
                        <!-- Hidden textarea synced on submit (no 'required' — JS validates) -->
                        <textarea id="body" name="body" style="display:none"></textarea>
                        <!-- Quill editor container -->
                        <div id="quill-editor"></div>
                        <span id="body-error" style="display:none;color:#f87171;font-size:13px;margin-top:4px;">Post
                            body cannot be empty.</span>
                    </div>

                    <button type="submit" class="dash-submit-btn">Publish Post</button>
                </form>
            </div>
        </div>
    </main>

    <style>
    .cat-tag-group {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 6px;
    }

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

    .cat-tag input:checked+span {
        background: var(--accent);
        color: #fff;
        border-color: var(--accent);
    }

    .cat-tag:hover span {
        border-color: var(--accent, #ff1f1fff);
    }

    /* Quill editor styling */
    #quill-editor {
        background: var(--input-bg, #1e1e2e);
        border: 1.5px solid var(--border, #333355);
        border-radius: 0 0 8px 8px;
        min-height: 380px;
        font-size: 15px;
        color: var(--text, #e0e0f0);
    }

    .ql-toolbar {
        background: var(--card-bg, #16162a);
        border: 1.5px solid var(--border, #333355);
        border-radius: 8px 8px 0 0;
        border-bottom: none;
    }

    .ql-toolbar .ql-stroke {
        stroke: #a0a0c0;
    }

    .ql-toolbar .ql-fill {
        fill: #a0a0c0;
    }

    .ql-toolbar .ql-picker-label {
        color: #a0a0c0;
    }

    .ql-toolbar button:hover .ql-stroke,
    .ql-toolbar .ql-active .ql-stroke {
        stroke: #fff;
    }

    .ql-toolbar button:hover .ql-fill,
    .ql-toolbar .ql-active .ql-fill {
        fill: #fff;
    }

    .ql-toolbar .ql-picker-options {
        background: #1e1e2e;
        color: #e0e0f0;
    }

    .ql-editor.ql-blank::before {
        color: #666;
        font-style: italic;
    }
    </style>

    <!-- Quill JS -->
    <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
    <script>
    const hamburger = document.getElementById('hamburger');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
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
    });

    // ── Quill WYSIWYG ──────────────────────────────────────────────
    const quill = new Quill('#quill-editor', {
        theme: 'snow',
        placeholder: 'Write your full article here…',
        modules: {
            toolbar: {
                container: [
                    [{
                        header: [1, 2, 3, false]
                    }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{
                        color: []
                    }, {
                        background: []
                    }],
                    [{
                        list: 'ordered'
                    }, {
                        list: 'bullet'
                    }],
                    [{
                        align: []
                    }],
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
                            fetch('upload_image.php', {
                                    method: 'POST',
                                    body: fd
                                })
                                .then(r => r.json())
                                .then(data => {
                                    if (data.location) {
                                        const range = quill.getSelection(true);
                                        quill.insertEmbed(range.index, 'image', data.location);
                                    } else {
                                        alert('Image upload failed: ' + (data.error ||
                                            'Unknown error'));
                                    }
                                })
                                .catch(() => alert('Image upload request failed.'));
                        };
                    }
                }
            }
        }
    });

    // Pre-fill editor with existing body content (on validation error)
    const bodyTA = document.getElementById('body');
    const bodyError = document.getElementById('body-error');

    // Pre-fill the editor if PHP returned body content (e.g. after a server-side error)
    <?php if (!empty($_POST['body'])): ?>
    quill.root.innerHTML = <?= json_encode($_POST['body']) ?>;
    <?php endif; ?>

    // Fix Quill link sanitizer so http/https links are preserved correctly
    const Link = Quill.import('formats/link');
    Link.sanitize = function(url) {
        if (!url.startsWith('http://') && !url.startsWith('https://') &&
            !url.startsWith('mailto:') && !url.startsWith('/')) {
            return 'https://' + url;
        }
        return url;
    };

    // Sync Quill HTML → hidden textarea; validate before submit
    document.querySelector('form').addEventListener('submit', function(e) {
        const html = quill.root.innerHTML;
        const text = quill.getText().trim();
        if (!text) {
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