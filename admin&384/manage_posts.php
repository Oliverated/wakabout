<?php
require_once __DIR__ . '/../includes/db.php';

// Fetch all posts
$postsRes = $conn->query("SELECT id, title, slug, category, author, published_at, COALESCE(views, 0) AS views FROM posts ORDER BY published_at DESC");
$posts = $postsRes ? $postsRes->fetch_all(MYSQLI_ASSOC) : [];

// Flash message from delete
$flash = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Posts — Wakabout Admin</title>
  <link rel="stylesheet" href="dashboard.css">
</head>
<body>


<?php $activePage = 'posts'; include 'sidebar.php'; ?>


  <main class="dash-main">
    <div class="dash-topbar">
      <div style="display:flex;align-items:center;gap:16px;">
        <button class="dash-hamburger" id="hamburger">&#9776;</button>
        <h1>Manage Posts</h1>
      </div>
      <a class="dash-btn dash-btn-primary" href="create_post.php">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        New Post
      </a>
    </div>

    <?php if ($flash === 'deleted'): ?>
      <div class="dash-alert dash-alert-success">✓ Post has been deleted successfully.</div>
    <?php endif; ?>

    <div class="dash-panel">
      <div class="dash-panel-header">
        <h2>All Posts (<?= count($posts) ?>)</h2>
      </div>
      <?php if (count($posts) > 0): ?>
      <div class="table-scroll-wrap">
      <table class="dash-table posts-table">
        <thead>
          <tr>
            <th class="col-num">#</th>
            <th class="col-title">Title</th>
            <th class="col-cat">Category</th>
            <th class="col-author">Author</th>
            <th class="col-date">Published</th>
            <th class="col-views">Views</th>
            <th class="col-actions">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($posts as $i => $post): ?>
          <?php
            $views = (int)$post['views'];
            $viewClass = $views >= 500 ? 'views-hot' : ($views >= 100 ? 'views-warm' : 'views-cool');
          ?>
          <tr>
            <td class="col-num" data-label="#"><?= $i + 1 ?></td>
            <td class="col-title" data-label="Title">
              <span class="post-title" title="<?= htmlspecialchars($post['title']) ?>"><?= htmlspecialchars($post['title']) ?></span>
            </td>
            <td class="col-cat" data-label="Category">
              <span class="category-badge"><?= htmlspecialchars($post['category']) ?></span>
            </td>
            <td class="col-author" data-label="Author">
              <span class="author-text"><?= htmlspecialchars($post['author']) ?></span>
            </td>
            <td class="col-date" data-label="Published">
              <span class="date-text"><?= $post['published_at'] ? date('M j, Y', strtotime($post['published_at'])) : '—' ?></span>
            </td>
            <td class="col-views" data-label="Views">
              <span class="views-badge <?= $viewClass ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                <span class="views-num"><?= number_format($views) ?></span>
              </span>
            </td>
            <td class="col-actions" data-label="Actions">
              <div class="dash-actions">
                <a class="dash-action-icon" href="./../post.php?slug=<?= htmlspecialchars($post['slug']) ?>" target="_blank" title="View post">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                </a>
                <a class="dash-action-icon icon-edit" href="edit_post.php?id=<?= $post['id'] ?>" title="Edit post">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125"/></svg>
                </a>
                <a class="dash-action-icon icon-delete" href="delete_post.php?id=<?= $post['id'] ?>" onclick="return confirm('Are you sure you want to delete this post?')" title="Delete post">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                </a>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      </div>
      <?php else: ?>
      <div class="dash-empty">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
        <p>No posts found. Create your first post!</p>
        <a class="dash-btn dash-btn-primary" href="create_post.php">Create Post</a>
      </div>
      <?php endif; ?>
    </div>
  </main>

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
  </script>
</body>
</html>
