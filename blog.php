<?php
require_once 'includes/db.php';

// Fetch all categories from DB
$catRes = $conn->query("SELECT name, group_name FROM categories ORDER BY group_name, name");
$allGroupedCats = [];
if ($catRes) {
  while ($row = $catRes->fetch_assoc()) {
    $allGroupedCats[$row['group_name']][] = $row['name'];
  }
}

// Fetch categories actually used in posts
$usedCats = [];
$usedRes = $conn->query("SELECT DISTINCT category FROM posts");
if ($usedRes) {
  while ($row = $usedRes->fetch_assoc()) {
    foreach (array_map('trim', explode(',', $row['category'])) as $c) {
      if ($c && !in_array($c, $usedCats))
        $usedCats[] = $c;
    }
  }
}

// Filter groups to only show those that have at least one used subcategory
$groupedCats = [];
foreach ($allGroupedCats as $gName => $subs) {
  $usedSubs = array_filter($subs, fn($s) => in_array($s, $usedCats));
  if (!empty($usedSubs)) {
    $groupedCats[$gName] = array_values($usedSubs);
  }
}
$groupNames = array_keys($groupedCats);

$activeGroup = $_GET['category'] ?? 'All';      // main group filter
$activeSub = $_GET['sub'] ?? '';               // subcategory filter
$searchQuery = trim($_GET['q'] ?? '');

// Pagination setup
$perPage = 9;
$page = max(1, (int) ($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

// Build WHERE clause based on category + search
$whereParts = [];
$bindTypes = '';
$bindValues = [];

if ($activeGroup !== 'All' && isset($groupedCats[$activeGroup])) {
  if (!empty($activeSub) && in_array($activeSub, $groupedCats[$activeGroup])) {
    // Filter by specific subcategory
    $whereParts[] = "category LIKE ?";
    $bindTypes .= 's';
    $bindValues[] = '%' . $activeSub . '%';
  } else {
    // Filter by any subcategory in this group
    $subs = $groupedCats[$activeGroup];
    $likeParts = array_fill(0, count($subs), "category LIKE ?");
    $whereParts[] = '(' . implode(' OR ', $likeParts) . ')';
    foreach ($subs as $s) {
      $bindTypes .= 's';
      $bindValues[] = '%' . $s . '%';
    }
  }
}

if (!empty($searchQuery)) {
  $like = '%' . $searchQuery . '%';
  $whereParts[] = "(title LIKE ? OR excerpt LIKE ? OR body LIKE ?)";
  $bindTypes .= 'sss';
  $bindValues[] = $like;
  $bindValues[] = $like;
  $bindValues[] = $like;
}

$whereSQL = count($whereParts) ? 'WHERE ' . implode(' AND ', $whereParts) : '';

// Count total posts for pagination
$countStmt = $conn->prepare("SELECT COUNT(*) AS total FROM posts $whereSQL");
if (!empty($bindTypes)) {
  $countStmt->bind_param($bindTypes, ...$bindValues);
}
$countStmt->execute();
$totalPosts = (int) $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = max(1, (int) ceil($totalPosts / $perPage));

// Fetch posts
$postBindTypes = $bindTypes . 'ii';
$postBindValues = array_merge($bindValues, [$perPage, $offset]);
$postsStmt = $conn->prepare("SELECT * FROM posts $whereSQL ORDER BY published_at DESC LIMIT ? OFFSET ?");
if (!empty($postBindTypes)) {
  $postsStmt->bind_param($postBindTypes, ...$postBindValues);
}
$postsStmt->execute();
$posts = $postsStmt->get_result()->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Blog';
$extraCss = 'pages.css';
$pageDescription = 'Explore the latest travel, tourism, and lifestyle stories from across Nigeria and beyond.';
require_once 'includes/header.php';
?>


<section class="elephant-sector">
  <h2 class="elephant-title">Our Blog</h2>
  <div class="elephant-nav"><a href="index.html">Home</a> | <span>Blog</span></div>
</section>
<!-- <div class="page-hero" style="background-image: url(assets/post-img/kitchen-sink.jpg);">
    <div class="page-hero-content">
      <h1>Our Blog</h1>
      <p>Stories, insights, and travel guides from across Nigeria and beyond</p>
    </div>
  </div> -->

<!-- Main Content -->
<div class="page-container">

  <!-- Search -->
  <form method="GET" action="blog.php" class="blog-search">
    <?php if ($activeGroup !== 'All'): ?>
      <input type="hidden" name="category" value="<?= htmlspecialchars($activeGroup) ?>">
    <?php endif; ?>
    <input type="text" name="q" placeholder="Search articles..." value="<?= htmlspecialchars($searchQuery) ?>">
    <button type="submit">Search</button>
  </form>

  <!-- Category Group Chips -->
  <div class="category-chips">
    <a class="chip <?= $activeGroup === 'All' ? 'active' : '' ?>" href="blog.php">All</a>

    <?php foreach ($groupNames as $gName): ?>
      <a class="chip <?= $activeGroup === $gName ? 'active' : '' ?>"
        href="blog.php?category=<?= urlencode($gName) ?>"><?= htmlspecialchars($gName) ?></a>

      <?php if ($activeGroup === $gName && isset($groupedCats[$gName])): ?>
        <?php foreach ($groupedCats[$gName] as $sub): ?>
          <a class="chip <?= $activeSub === $sub ? 'active' : '' ?>"
            style="<?= $activeSub === $sub ? 'background:var(--accent-lit);color:var(--secondary);border-color:var(--accent-lit);' : 'opacity:0.8; font-size:13px;' ?>"
            href="blog.php?category=<?= urlencode($gName) ?>&sub=<?= urlencode($sub) ?>">↳ <?= htmlspecialchars($sub) ?></a>
        <?php endforeach; ?>
      <?php endif; ?>

    <?php endforeach; ?>
  </div>

  <!-- Blog Grid -->
  <div class="blog-grid">
    <?php foreach ($posts as $blogPost):
        $cardImg = !empty($blogPost['cover_image'])
            ? $blogPost['cover_image']
            : (function($html) {
                  if (!empty($html) && preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $html, $m)) return $m[1];
                  return '';
              })($blogPost['body'] ?? '');
      ?>
      <div class="blog-card">
        <img src="<?= htmlspecialchars($cardImg ?: 'assets/public/placeholder.jpg') ?>" alt="<?= htmlspecialchars($blogPost['title']) ?>">
        <div class="blog-card-body">
          <div class="blog-card-meta">
            <span class="date"><?= date('M j, Y', strtotime($blogPost['published_at'])) ?></span>
            <!-- <span class="category"><?= htmlspecialchars($blogPost['category'] ?? 'General') ?></span> -->
          </div>
          <h3><?= htmlspecialchars($blogPost['title']) ?></h3>
          <!-- <p > -->
          <a class="excerpt" href="post.php?slug=<?= htmlspecialchars($blogPost['slug']) ?>">
            <?= htmlspecialchars(mb_strimwidth($blogPost['excerpt'] ?? strip_tags($blogPost['body']), 0, 100, '...')) ?>
            <span class="read-more">Read more →</span>
            <!-- </p> -->
          </a>
        </div>
      </div>
    <?php endforeach; ?>

    <?php if (empty($posts)): ?>
      <p style="grid-column:1/-1;text-align:center;padding:40px 0;">No posts
        found<?= $activeGroup !== 'All' ? ' in the "' . htmlspecialchars($activeSub ?: $activeGroup) . '" category' : '' ?>.
      </p>
    <?php endif; ?>
  </div>

  <!-- Pagination -->
  <?php if ($totalPages > 1): ?>
    <?php
    $paginationParams = '';
    if ($activeGroup !== 'All')
      $paginationParams .= 'category=' . urlencode($activeGroup) . '&';
    if (!empty($activeSub))
      $paginationParams .= 'sub=' . urlencode($activeSub) . '&';
    ?>
    <div class="pagination" style="margin-top:40px;display:flex;justify-content:center;gap:10px;">
      <?php if ($page > 1): ?>
        <a href="blog.php?<?= $paginationParams ?>page=<?= $page - 1 ?>" class="chip">&laquo; Prev</a>
      <?php endif; ?>
      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="blog.php?<?= $paginationParams ?>page=<?= $i ?>"
          class="chip <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
      <?php endfor; ?>
      <?php if ($page < $totalPages): ?>
        <a href="blog.php?<?= $paginationParams ?>page=<?= $page + 1 ?>" class="chip">Next &raquo;</a>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>