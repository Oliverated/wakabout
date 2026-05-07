<?php
require_once '../includes/db.php';

// Fetch stats
$totalPosts     = $conn->query("SELECT COUNT(*) FROM posts")->fetch_row()[0];
$totalCategories = $conn->query("SELECT COUNT(DISTINCT category) FROM posts")->fetch_row()[0];
$latestPostRes  = $conn->query("SELECT published_at FROM posts ORDER BY published_at DESC LIMIT 1")->fetch_row();
$latestPost     = $latestPostRes ? $latestPostRes[0] : null;
$postsThisMonth = $conn->query("SELECT COUNT(*) FROM posts WHERE DATE_FORMAT(published_at, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m')")->fetch_row()[0];

// New Stats for Users and Comments
$totalUsers = $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0];
$totalComments = $conn->query("SELECT COUNT(*) FROM post_comments")->fetch_row()[0];

// Subscribers
$subTableExists = $conn->query("SHOW TABLES LIKE 'subscribers'")->num_rows > 0;
$totalSubscribers = $subTableExists ? $conn->query("SELECT COUNT(*) FROM subscribers")->fetch_row()[0] : 0;

// Recent posts
$recentPostsRes = $conn->query("SELECT id, title, slug, category, author, published_at FROM posts ORDER BY published_at DESC LIMIT 5");
$recentPosts = $recentPostsRes ? $recentPostsRes->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard — Wakabout Admin</title>
  <link rel="stylesheet" href="dashboard.css">
</head>
<body>


<?php $activePage = 'dashboard'; include 'sidebar.php'; ?>


  <!-- Main -->
  <main class="dash-main">
    <div class="dash-topbar">
      <div style="display:flex;align-items:center;gap:16px;">
        <button class="dash-hamburger" id="hamburger">&#9776;</button>
        <h1>Dashboard</h1>
      </div>
      <span class="dash-date"><?= date('l, F j, Y') ?></span>
    </div>

    <!-- Stat Cards -->
    <div class="dash-stats">
      <div class="dash-stat-card">
        <div class="stat-icon">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
        </div>
        <div class="stat-number"><?= $totalPosts ?></div>
        <div class="stat-label">Total Posts</div>
      </div>
      <!-- <div class="dash-stat-card">
        <div class="stat-icon">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z"/></svg>
        </div>
        <div class="stat-number"><?= $totalCategories ?></div>
        <div class="stat-label">Categories</div>
      </div> -->
      <!-- <div class="dash-stat-card">
        <div class="stat-icon">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
        </div>
        <div class="stat-number"><?= $latestPost ? date('M j', strtotime($latestPost)) : '—' ?></div>
        <div class="stat-label">Latest Post</div>
      </div> -->
      <!-- <div class="dash-stat-card">
        <div class="stat-icon">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>
        </div>
        <div class="stat-number"><?= $postsThisMonth ?></div>
        <div class="stat-label">Posts This Month</div>
      </div> -->
      <div class="dash-stat-card">
        <div class="stat-icon">
          <!-- Users icon -->
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
        </div>
        <div class="stat-number"><?= $totalUsers ?></div>
        <div class="stat-label">Total Users</div>
      </div>
      <div class="dash-stat-card">
        <div class="stat-icon">
          <!-- Comment icon -->
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.95 5.95 0 01-4.74-1.951c2.04-.72 3.06-2.024 3.06-2.024C1.928 15.688 1.5 13.91 1.5 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" /></svg>
        </div>
        <div class="stat-number"><?= $totalComments ?></div>
        <div class="stat-label">Total Comments</div>
      </div>
      <div class="dash-stat-card">
        <div class="stat-icon">
          <!-- Subscribers icon -->
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>
        </div>
        <div class="stat-number"><?= $totalSubscribers ?></div>
        <div class="stat-label">Subscribers</div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="dash-quick-actions">
      <a class="dash-quick-card" href="create_post.php">
        <div class="quick-icon">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
        </div>
        <div class="quick-text">
          <h4>Write a Post</h4>
          <p>Create a new blog article</p>
        </div>
      </a>
      <a class="dash-quick-card" href="manage_posts.php">
        <div class="quick-icon">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75"/></svg>
        </div>
        <div class="quick-text">
          <h4>Manage Posts</h4>
          <p>Edit or delete existing posts</p>
        </div>
      </a>
      <a class="dash-quick-card" href="manage_users.php">
        <div class="quick-icon">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
        </div>
        <div class="quick-text">
          <h4>Manage Users</h4>
          <p>View and manage accounts</p>
        </div>
      </a>
      <a class="dash-quick-card" href="manage_subscribers.php">
        <div class="quick-icon">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 9v.906a2.25 2.25 0 0 1-1.183 1.981l-6.478 3.488M2.25 9v.906a2.25 2.25 0 0 0 1.183 1.981l6.478 3.488m8.839 2.51-4.66-2.51m0 0-1.023-.55a2.25 2.25 0 0 0-2.134 0l-1.022.55m0 0-4.661 2.51m16.5 1.615a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V8.844a2.25 2.25 0 0 1 1.183-1.981l7.5-4.039a2.25 2.25 0 0 1 2.134 0l7.5 4.039a2.25 2.25 0 0 1 1.183 1.98V19.5Z" /></svg>
        </div>
        <div class="quick-text">
          <h4>Subscribers</h4>
          <p>Review email newsletter list</p>
        </div>
      </a>
      <!-- <a class="dash-quick-card" href="../index.php">
        <div class="quick-icon">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
        </div>
        <div class="quick-text">
          <h4>View Website</h4>
          <p>See your live blog</p>
        </div>
      </a> -->
    </div>

    <!-- Recent Posts Table -->
    <div class="dash-panel">
      <div class="dash-panel-header">
        <h2>Recent Posts</h2>
        <a class="panel-action" href="manage_posts.php">View All →</a>
      </div>
      <?php if (count($recentPosts) > 0): ?>
      <table class="dash-table">
        <thead>
          <tr>
            <th>Title</th>
            <th>Category</th>
            <!-- <th>Author</th> -->
            <th>Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recentPosts as $post): ?>
          <tr>
            <td><span class="post-title"><?= htmlspecialchars($post['title']) ?></span></td>
            <td><span class="category-badge"><?= htmlspecialchars($post['category']) ?></span></td>
            <!-- <td><?= htmlspecialchars($post['author']) ?></td> -->
            <td><?= $post['published_at'] ? date('M j, Y', strtotime($post['published_at'])) : '—' ?></td>
            <td>
              <div class="dash-actions">
                <a class="dash-btn dash-btn-edit" href="edit_post.php?id=<?= $post['id'] ?>">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125"/></svg>
                  <!-- Edit -->
                </a>
                <a class="dash-btn dash-btn-delete" href="delete_post.php?id=<?= $post['id'] ?>" onclick="return confirm('Are you sure you want to delete this post?')">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                  <!-- Delete -->
                </a>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php else: ?>
      <div class="dash-empty">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
        <p>No posts yet. Start by creating your first post!</p>
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
      hamburger.classList.toggle('active');
    });

    overlay.addEventListener('click', () => {
      sidebar.classList.remove('open');
      overlay.classList.remove('active');
          hamburger.classList.remove('active');
    });
  </script>
</body>
</html>
