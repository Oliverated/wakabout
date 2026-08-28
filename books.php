<?php
require_once 'includes/db.php';

// ── Active category filter ──────────────────────────────────
$activeCategory = trim($_GET['category'] ?? 'All');

// ── Fetch all distinct categories from books table ──────────
$catResult = $conn->query("SELECT DISTINCT category FROM books WHERE category IS NOT NULL AND category != '' ORDER BY category ASC");
$bookCategories = ['All'];
if ($catResult) {
  while ($row = $catResult->fetch_assoc()) {
    $bookCategories[] = trim($row['category']);
  }
}

// ── Fetch books (filter by category if not "All") ───────────
if ($activeCategory !== 'All') {
  $stmt = $conn->prepare("SELECT * FROM books WHERE category = ? ORDER BY created_at DESC");
  if ($stmt) {
    $stmt->bind_param("s", $activeCategory);
    $stmt->execute();
    $booksResult = $stmt->get_result();
  }
} else {
  $booksResult = $conn->query("SELECT * FROM books ORDER BY created_at DESC");
}
$books = $booksResult ? $booksResult->fetch_all(MYSQLI_ASSOC) : [];

$pageTitle = 'Books';
$extraCss = 'pages.css';
$pageDescription = 'Browse our collection of travel guides, tourism books, and cultural reads.';
require_once 'includes/header.php';
?>

<section class="elephant-sector">
  <h2 class="elephant-title">Our Books</h2>
  <div class="elephant-nav"><a href="index.php">Home</a> | <span>Books</span></div>
</section>

<main class="event-main">

  <!-- ── Category Filter Chips ── -->
  <div class="category-chips">
    <?php foreach ($bookCategories as $cat): ?>
      <a class="chip <?= ($activeCategory === $cat) ? 'active' : '' ?>"
        href="books.php<?= $cat !== 'All' ? '?category=' . urlencode($cat) : '' ?>">
        <?= htmlspecialchars($cat) ?>
      </a>
    <?php endforeach; ?>
  </div>

  <!-- ── Results count ── -->
  <p class="filter-results-count">
    <?php if ($activeCategory !== 'All'): ?>
      Showing <strong><?= count($books) ?></strong> book<?= count($books) !== 1 ? 's' : '' ?> in
      <strong><?= htmlspecialchars($activeCategory) ?></strong>
      &mdash; <a href="books.php">View all</a>
    <?php else: ?>
      <strong><?= count($books) ?></strong> book<?= count($books) !== 1 ? 's' : '' ?> available
    <?php endif; ?>
  </p>

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
              <p class="book-author"><span class="book-atr">Author:</span> <?= htmlspecialchars($book['author'] ?? 'Wakabout Team') ?></p>
              <span class="book-category"><?= htmlspecialchars($book['category'] ?? 'General') ?></span>
            </div>
            <h3><?= htmlspecialchars($book['title']) ?></h3>

            <p class="book-desc"><?= htmlspecialchars($book['description'] ?? '') ?></p>
            <?php
            $buyLink = $book['buy_link'] ? htmlspecialchars($book['buy_link']) : '#';
            $priceLabel = $book['price'] ?  htmlspecialchars($book['price']) : '';
            ?>
            <a class="book-btn" href="<?= $buyLink ?>" target="_blank">Buy Now <?= $priceLabel ? '$' . $priceLabel : '' ?></a>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="no-results">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
        </svg>
        <p><?= $activeCategory !== 'All' ? 'No books found in <strong>' . htmlspecialchars($activeCategory) . '</strong>.' : 'More books coming soon!' ?></p>
        <?php if ($activeCategory !== 'All'): ?>
          <a class="chip active" href="books.php">Browse all books</a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
</main>

<?php require_once 'includes/footer.php'; ?>