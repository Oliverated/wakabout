<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - Wakabout' : 'Wakabout' ?></title>
    <!-- CSS and Scripts -->
    <link rel="stylesheet" href="assets/required.css" />
    <?php if (isset($extraCss)): ?>
      <link rel="stylesheet" href="assets/<?= htmlspecialchars($extraCss) ?>" />
    <?php else: ?>
      <link rel="stylesheet" href="assets/styles.css" />
    <?php endif; ?>
    <script defer src="assets/script.js"></script>
    <?php if (isset($pageDescription)): ?>
      <meta name="description" content="<?= htmlspecialchars($pageDescription) ?>">
    <?php endif; ?>
</head>
<body>
    <div class="cover-bg"></div>
    <header>
      <span class="harm">&#9776;</span>
      <nav>
        <div class="menu">
          <a class="nav-logo" href="/">
            <img src="assets/public/white-logo.png" alt="Wakabout Logo" />
          </a>
          <ul class="nav-list" style="display:flex;align-items:center;">
            <li><a href="index.php">Home</a></li>
            <li><a href="blog.php">Blogs</a></li>
            <li><a href="books.php">Books</a></li>
            <li><a href="events.php">Events</a></li>
            <li><a href="about.php">About</a></li>
            <li><a href="contact.php">Contact</a></li>
            
            <!-- <li style="margin-left:auto;display:flex;align-items:center;gap:15px;border-left:1px solid #ffffff40;padding-left:15px;"> -->
              <?php if (isset($_SESSION['user_id'])): ?>
                <!-- <span style="color:#ddd;font-size:14px;">Hi, <?= htmlspecialchars($_SESSION['username']) ?></span> -->
                <!-- <a href="auth/logout.php" style="background:var(--accent,#ff141493);padding:6px 14px;border-radius:20px;font-size:13px;font-weight:bold;">Logout</a> -->
              <?php else: ?>
                <!-- <a href="auth/login.php" style="background:var(--accent,#ff141493);padding:6px 14px;border-radius:20px;font-size:13px;font-weight:bold;">Login / Sign Up</a> -->
              <?php endif; ?>
            <!-- </li> -->
          </ul>
        </div>
      </nav>
    </header>
