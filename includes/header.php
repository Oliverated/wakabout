<?php
require_once __DIR__ . '/session_config.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - Wakabout' : 'Wakabout' ?></title>
  <link rel="icon" href="assets/public/footerlogo.png">
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
  <?php if (isset($extraMeta)) echo $extraMeta; ?>
</head>

<body>
  <div class="cover-bg"></div>
  <header>
    <div class="menu">
      <div class="harm-block">
                <a class="" href="./">
        <img class="harm-block-logo" src="assets/public/footerlogo.png" alt="Wakabout Logo" />
        </a>
        <span class="harm">&#9776;</span>
      </div>
      <nav>
<div class="nav-logo">
     <a href="./">
          <img src="assets/public/footerlogo.png" alt="Wakabout Logo" />
        </a> 
</div>
    
        <ul class="nav-list" style="display:flex;align-items:center;">
          <li><a href="index.php">Home</a></li>
          <li><a href="blog.php">Blogs</a></li>
          <li><a href="books.php">Books</a></li>
          <li><a href="events.php">Events</a></li>
          <li><a href="contact.php">Contact</a></li>

        </ul>
    </div>
    </nav>
  </header>