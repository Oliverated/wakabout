<?php
require_once __DIR__ . '/includes/db.php';

// Get the slug from URL
$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    header('Location: index.html');
    exit;
}

// Fetch the post
$stmt = $pdo->prepare("SELECT * FROM posts WHERE slug = ?");
$stmt->execute([$slug]);
$post = $stmt->fetch();

if (!$post) {
    http_response_code(404);
    echo '<!DOCTYPE html><html><head><title>Post Not Found</title><link rel="stylesheet" href="assets/required.css"></head><body style="display:flex;align-items:center;justify-content:center;height:100vh;flex-direction:column;"><h1>404 - Post Not Found</h1><p>The post you are looking for does not exist.</p><a href="index.html" style="color:var(--accent);margin-top:20px;font-weight:bold;">&larr; Back to Home</a></body></html>';
    exit;
}

// Fetch related posts (same category, exclude current)
$relatedStmt = $pdo->prepare("SELECT id, title, slug, cover_image FROM posts WHERE category = ? AND id != ? ORDER BY published_at DESC LIMIT 4");
$relatedStmt->execute([$post['category'], $post['id']]);
$relatedPosts = $relatedStmt->fetchAll();

// Format date
$publishedDate = date('F j, Y', strtotime($post['published_at']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="assets/required.css" />
  <link rel="stylesheet" href="assets/post.css" />
  <script defer src="assets/script.js"></script>
  <title><?= htmlspecialchars($post['title']) ?> - Wakabout</title>
  <meta name="description" content="<?= htmlspecialchars($post['excerpt']) ?>">
</head>
<body>
  <!-- NAVBAR -->
  <!-- nav > start -->
  <div class="cover-bg"></div>
  <header>
    <span class="harm">&#9776;</span>
    <nav>
      <div class="menu">
        <a class="nav-logo" href="/">
          <span>Wakabout.</span>
        </a>

        <ul class="nav-list">
          <li><a href="index.html">Blogs</a></li>
          <li><a href="#">Partners</a></li>
          <li><a href="#">Rankings</a></li>
          <li><a href="#">Lists</a></li>
          <li><a href="#">Specials</a></li>
          <li><a href="#">Contact</a></li>
        </ul>
      </div>
    </nav>
  </header>
  <!-- nav > end -->

<!-- BLOG POST -->
<section class="post-container">
  <div class="post-content">
    <a href="index.html" class="back-btn">&larr; Back to Home</a>
    
    <div class="post-header">
      <h1 class="post-title"><?= htmlspecialchars($post['title']) ?></h1>
      <div class="post-meta">
        <span class="post-author">By <?= htmlspecialchars($post['author']) ?></span>
        <span class="post-date"><?= $publishedDate ?></span>
      </div>
    </div>

    <?php if (!empty($post['cover_image'])): ?>
    <img src="<?= htmlspecialchars($post['cover_image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" class="post-image">
    <?php endif; ?>

    <div class="post-body">
      <?= $post['body'] ?>
    </div>
  </div>

  <!-- SIDEBAR -->
  <aside class="post-sidebar">
    <?php if (!empty($relatedPosts)): ?>
    <div class="post-block">
      <h3>Related Posts</h3>
      <?php foreach ($relatedPosts as $related): ?>
      <a href="post.php?slug=<?= htmlspecialchars($related['slug']) ?>" class="side-post">
        <?php if (!empty($related['cover_image'])): ?>
        <img src="<?= htmlspecialchars($related['cover_image']) ?>" alt="">
        <?php endif; ?>
        <p><?= htmlspecialchars($related['title']) ?></p>
      </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="post-block">
      <h3>Categories</h3>
      <?php
        $catStmt = $pdo->query("SELECT DISTINCT category FROM posts ORDER BY category");
        $categories = $catStmt->fetchAll();
      ?>
      <div style="display:flex;flex-wrap:wrap;gap:10px;">
        <?php foreach ($categories as $cat): ?>
        <span style="background:rgba(0,0,0,0.03);padding:6px 14px;border-radius:20px;font-size:14px;font-weight:500;color:var(--secondary);"><?= htmlspecialchars($cat['category']) ?></span>
        <?php endforeach; ?>
      </div>
    </div>
  </aside>
</section>

<!-- footer sector -->
<footer>
  <div class="footer-content">
    <div class="footer-section about">
      <p>
        Wakabout is a travel and tourism blog dedicated to providing readers
        with the latest news, insights, and tips on travel destinations,
        experiences, and trends. Our mission is to inspire and empower
        travelers to explore the world and create unforgettable memories.
      </p>
      <a class="footer-btn" href="https://wa.me/12345678910">
        Contact Us Now
      </a>
    </div>
    <div class="footer-section links">
      <h3>Quick Links</h3>
      <ul>
        <li><a href="#">Blogs</a></li>
        <li><a href="#">Partners</a></li>
        <li><a href="#">Rankings</a></li>
        <li><a href="#">Lists</a></li>
        <li><a href="#">Specials</a></li>
      </ul>
    </div>
    <div class="footer-section contact">
      <h3>Contact Us</h3>
      <p>
        <svg
          class="tel-icon"
          fill="currentColor"
          viewBox="0 0 24 24"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            d="M6.492 2.25c-.393 0-.78.14-1.102.398l-.047.024-.023.023L2.976 5.11 3 5.133a2.35 2.35 0 0 0-.633 2.531c.003.006-.003.018 0 .024.636 1.819 2.262 5.332 5.437 8.507 3.188 3.188 6.747 4.75 8.508 5.438h.024a2.692 2.692 0 0 0 2.601-.516l2.367-2.367c.622-.621.622-1.7 0-2.32l-3.046-3.047-.024-.047c-.621-.621-1.723-.621-2.344 0l-1.5 1.5a12.131 12.131 0 0 1-3.07-2.11c-1.228-1.171-1.854-2.519-2.086-3.046l1.5-1.5c.63-.63.642-1.679-.023-2.297l.023-.024-.07-.07-3-3.094-.024-.023-.047-.024a1.767 1.767 0 0 0-1.101-.398Zm0 1.5c.056 0 .111.026.164.07l3 3.07.07.07c-.006-.005.044.074-.047.165L7.804 9l-.351.328.164.469s.861 2.305 2.672 4.031l.164.14c1.743 1.592 3.797 2.462 3.797 2.462l.468.21 2.227-2.226c.129-.129.105-.129.234 0l3.07 3.07c.13.13.13.082 0 .211l-2.296 2.297c-.346.296-.712.358-1.149.211-1.699-.668-5-2.118-7.945-5.062-2.968-2.968-4.518-6.334-5.086-7.97-.114-.304-.032-.755.235-.984l.046-.046 2.274-2.32a.263.263 0 0 1 .164-.071Z"
          ></path>
        </svg>
        <a class="footer-tel" href="tel:+2347066071996">+2347066071996</a>
      </p>
      <div class="socials">
        <a href="#"
          ><svg
            class="icons"
            fill="currentColor"
            viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              d="M19.321 5.562a5.124 5.124 0 0 1-.443-.258 6.229 6.229 0 0 1-1.137-.966c-.849-.971-1.166-1.956-1.282-2.645h.004c-.097-.573-.057-.943-.05-.943h-3.865v14.943c0 .2 0 .399-.008.595 0 .024-.003.046-.004.073 0 .01 0 .022-.003.033v.009a3.28 3.28 0 0 1-1.65 2.604 3.226 3.226 0 0 1-1.6.422c-1.8 0-3.26-1.468-3.26-3.281 0-1.814 1.46-3.282 3.26-3.282.341 0 .68.054 1.004.16l.005-3.936a7.178 7.178 0 0 0-5.532 1.62 7.583 7.583 0 0 0-1.655 2.04c-.163.281-.779 1.412-.853 3.246-.047 1.04.266 2.12.415 2.565v.01c.093.262.457 1.158 1.049 1.913a7.855 7.855 0 0 0 1.674 1.58v-.01l.009.01c1.87 1.27 3.945 1.187 3.945 1.187.359-.015 1.562 0 2.928-.647 1.515-.718 2.377-1.787 2.377-1.787a7.43 7.43 0 0 0 1.296-2.153c.35-.92.466-2.022.466-2.462V8.273c.047.028.672.441.672.441s.9.577 2.303.952c1.006.267 2.363.324 2.363.324V6.153c-.475.052-1.44-.098-2.429-.59Z"
            ></path></svg
        ></a>
        <a href="#"
          ><svg
            class="icons"
            fill="currentColor"
            viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              d="M16.375 3.25a4.388 4.388 0 0 1 4.375 4.375v8.75a4.388 4.388 0 0 1-4.375 4.375h-8.75a4.389 4.389 0 0 1-4.375-4.375v-8.75A4.388 4.388 0 0 1 7.625 3.25h8.75Zm0-1.75h-8.75C4.256 1.5 1.5 4.256 1.5 7.625v8.75c0 3.369 2.756 6.125 6.125 6.125h8.75c3.369 0 6.125-2.756 6.125-6.125v-8.75c0-3.369-2.756-6.125-6.125-6.125Z"
            ></path>
            <path
              d="M17.688 7.625a1.313 1.313 0 1 1 0-2.625 1.313 1.313 0 0 1 0 2.625Z"
            ></path>
            <path
              d="M12 8.5a3.5 3.5 0 1 1 0 7 3.5 3.5 0 0 1 0-7Zm0-1.75a5.25 5.25 0 1 0 0 10.5 5.25 5.25 0 0 0 0-10.5Z"
            ></path></svg
        ></a>
        <a href="#">
          <svg
            class="icons"
            fill="currentColor"
            viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              fill-rule="evenodd"
              d="M3.567 2.033c-.847 0-1.534.687-1.534 1.534v16.866c0 .847.687 1.534 1.534 1.534h16.866c.847 0 1.534-.687 1.534-1.534V3.567c0-.847-.687-1.534-1.534-1.534H3.567ZM5.177 9.7H8.09v9.2H5.177V9.7Zm3.105-3.059a1.648 1.648 0 1 1-3.297 0 1.648 1.648 0 0 1 3.297 0ZM18.9 13.314c0-2.767-1.789-3.843-3.566-3.843a3.376 3.376 0 0 0-1.68.353c-.394.198-.807.65-1.125 1.438h-.082V9.7H9.7v9.206h2.923V14.01c-.042-.502.118-1.15.446-1.536.329-.386.798-.478 1.155-.524h.11c.93 0 1.62.575 1.62 2.025v4.931h2.922l.024-5.593Z"
              clip-rule="evenodd"
            ></path>
          </svg>
        </a>
      </div>
    </div>
  </div>
  <div class="footer-bottom">
    <p>&copy; 2026 Wakabout. All rights reserved.</p>
  </div>
</footer>
</body>
</html>
