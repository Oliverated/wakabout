<?php
require_once 'includes/db.php';
$pageTitle = 'Books';
$extraCss = 'pages.css';
$pageDescription = 'Browse our collection of travel guides, tourism books, and cultural reads.';
require_once 'includes/header.php';
?>

      <section class="elephant-sector">
      <h2 class="elephant-title">Our Books</h2>
      <div class="elephant-nav"
        ><a href="index.html">Home</a> | <span>Books</span></div
      >
    </section>

<?php
$booksResult = $conn->query("SELECT * FROM books ORDER BY created_at DESC");
$books = $booksResult ? $booksResult->fetch_all(MYSQLI_ASSOC) : [];
?>
  <div class="page-container">
    <!-- Category Chips (Static placeholder for now, can be made dynamic later) -->
    <div class="category-chips">
      <a class="chip active" href="#">All</a>
      <a class="chip" href="#">Travel Guide</a>
      <a class="chip" href="#">Culture</a>
      <a class="chip" href="#">Food &amp; Drink</a>
      <a class="chip" href="#">Business</a>
      <a class="chip" href="#">Photography</a>
    </div>

    <div class="book-grid">
      <?php if (!empty($books)): ?>
        <?php foreach ($books as $book): ?>
          <div class="book-item">
            <?php if (!empty($book['cover_image'])): ?>
              <img src="<?= htmlspecialchars($book['cover_image']) ?>" alt="<?= htmlspecialchars($book['title']) ?>">
            <?php else: ?>
              <img src="assets/img/placeholder.png" alt="Book Cover Placeholder">
            <?php endif; ?>
            <div class="book-item-body">
              <div class="book-header-flex">
              <h3><?= htmlspecialchars($book['title']) ?></h3>                  
              <span class="book-category"><?= htmlspecialchars($book['category'] ?? 'General') ?></span>
              
              </div>

              <p class="book-author"><span class="book-atr">Author:</span> <?= htmlspecialchars($book['author'] ?? 'Wakabout Team') ?></p>
              <p class="book-desc"><?= htmlspecialchars($book['description'] ?? '') ?></p>
              <?php
                $buyLink = $book['buy_link'] ? htmlspecialchars($book['buy_link']) : '#';
                $priceLabel = $book['price'] ?  htmlspecialchars($book['price']) : '';
              ?>
              <a class="book-btn" href="<?= $buyLink ?>" target="_blank">Buy Now $<?= $priceLabel ?></a>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p style="grid-column: 1 / -1; text-align: center; font-size: 18px; padding: 40px 0; color: #888;">More books coming soon!</p>
      <?php endif; ?>
    </div>
  </div>

<?php require_once 'includes/footer.php'; ?>
