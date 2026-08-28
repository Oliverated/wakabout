<?php
require_once 'includes/db.php';

// ── Extract first <img src> from post body HTML ─────────────
function getFirstBodyImage(string $html): string
{
  if (empty($html)) return '';
  if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $html, $m)) {
    return $m[1];
  }
  return '';
}

// Fetch the Hero post
$stmt = $conn->prepare("SELECT * FROM posts ORDER BY published_at DESC LIMIT 1");
$post = null;
if ($stmt) {
  $stmt->execute();
  $result = $stmt->get_result();
  $post = $result ? $result->fetch_assoc() : null;
}

// Fetch related posts (same category, exclude current) for sidebar
$relatedPosts = [];
if ($post) {
  $relatedStmt = $conn->prepare("SELECT id, title, slug, cover_image, category, body FROM posts WHERE id != ? ORDER BY published_at DESC LIMIT 5");
  if ($relatedStmt) {
    $relatedStmt->bind_param("i", $post['id']);
    $relatedStmt->execute();
    $result = $relatedStmt->get_result();
    $relatedPosts = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
  }
} else {
  // Fallback if no posts
  $relatedStmt = $conn->prepare("SELECT id, title, slug, cover_image, category, body FROM posts ORDER BY published_at DESC LIMIT 5");
  if ($relatedStmt) {
    $relatedStmt->execute();
    $result = $relatedStmt->get_result();
    $relatedPosts = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
  }
}

// Fetch Other Stories (exclude the hero post)
$otherStories = [];
$excludeId = $post['id'] ?? 0;
$storiesStmt = $conn->prepare("SELECT id, title, slug, cover_image, excerpt, body, published_at FROM posts WHERE id != ? ORDER BY published_at DESC LIMIT 6");
if ($storiesStmt) {
  $storiesStmt->bind_param("i", $excludeId);
  $storiesStmt->execute();
  $result = $storiesStmt->get_result();
  $otherStories = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

// Fetch Latest Books
$books = [];
$booksStmt = $conn->prepare("SELECT id, title, description, cover_image, buy_link, price FROM books ORDER BY created_at DESC LIMIT 4");
if ($booksStmt) {
  $booksStmt->execute();
  $result = $booksStmt->get_result();
  $books = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

// Fetch Upcoming Events
$events = [];
$eventsStmt = $conn->prepare("SELECT id, title, description, cover_image, event_date, location, cta_link, cta_label FROM events ORDER BY event_date DESC LIMIT 3");
if ($eventsStmt) {
  $eventsStmt->execute();
  $result = $eventsStmt->get_result();
  $events = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

// Format published date for hero
$publishedDate = '';
if ($post && !empty($post['published_at'])) {
  $publishedDate = date('F j, Y', strtotime($post['published_at']));
}
?>
<?php require_once 'includes/header.php'; ?>

<body>

  <!-- <div class="loader-block">
  <div class="loader-ctn" >
  <span class="loader">Wakaabout Online...</span>
  <span class="loader-masthead" >Nigeria's original travel voice <em class="since">since 2010.</em> </span>
  </div>
</div> -->
  <main>
    <!-- HERO SECTION -->
    <section class="hero-sector">
      <!-- <div class="hero-main"> -->
      <?php if ($post): ?>
        <?php
        $heroImg = !empty($post['cover_image'])
          ? $post['cover_image']
          : getFirstBodyImage($post['body'] ?? '');
        ?>
        <div class="hero-block" <?php if ($heroImg): ?>style=" background-image:linear-gradient(to top, #000000, #000000e4, #00000000), url('<?= htmlspecialchars($heroImg) ?>')" <?php endif; ?>>
          <div class="hero-txt">
            <p class="hero-date"><?= $publishedDate ?></p>
            <a href="post.php?slug=<?= htmlspecialchars($post['slug'] ?? '') ?>" class="hero-title">
              <?= htmlspecialchars($post['title']) ?>
              <p class="hero-post-desp">
                <?= htmlspecialchars($post['excerpt'] ?? '') ?>
              </p>
            </a>
          </div>
        </div>
      <?php else: ?>
        <div class="hero-block">
          <div class="hero-txt">
            <h2>Welcome to Wakabout</h2>
            <p>Check back later for exciting travel insights and updates!</p>
          </div>
        </div>
      <?php endif; ?>
      <!-- </div> -->

      <!-- SIDEBAR headline sidebar -->
      <div class="hero-sidebar">
        <h2>Recent Posts</h2>
        <div class="hero-sidebar-block">
          <?php foreach ($relatedPosts as $rPost): ?>
            <div class="hero-sidebar-li">
              <?php
              $rImg = !empty($rPost['cover_image'])
                ? $rPost['cover_image']
                : getFirstBodyImage($rPost['body'] ?? '');
              $rImg = $rImg ?: 'assets/public/placeholder.jpg';
              ?>
              <img src="<?= htmlspecialchars($rImg) ?>" alt="<?= htmlspecialchars($rPost['title']) ?>" />
              <div class="hero-sidebar-links">
                <!-- <a class="categ" href="#"><?= htmlspecialchars($rPost['category'] ?? 'General') ?></a> -->
                <a class="link" href="post.php?slug=<?= htmlspecialchars($rPost['slug'] ?? '') ?>">
                  <?= htmlspecialchars($rPost['title']) ?>
                </a>
              </div>
            </div>
          <?php endforeach; ?>
          <?php if (empty($relatedPosts)): ?>
            <p>No recent posts found.</p>
          <?php endif; ?>
        </div>
        <a class="hero-sidebar-btn btn" href="blog.php">View More</a>
      </div>
    </section>

    <!-- lastest book -->
    <section class="book-sector">
      <fieldset>
        <legend>Latest Books</legend>
      </fieldset>

      <div class="books-block">

        <?php if (!empty($books)): ?>
          <?php foreach ($books as $book): ?>
            <div class="book-card" style="background-image: url(<?= htmlspecialchars($book['cover_image']) ?>)">
              <span class="book-category"><?= htmlspecialchars($book['category'] ?? 'General') ?></span>
              <div class="book-content">

                <h3><?= htmlspecialchars($book['title']) ?></h3>
                <p class="book-desc"><?= htmlspecialchars($book['description'] ?? '') ?></p>
                <?php
                $buyLink = $book['buy_link'] ? htmlspecialchars($book['buy_link']) : '#';
                $priceLabel = $book['price'] ?  htmlspecialchars($book['price']) : '';
                ?>
                <a class="book-btn btn" href="<?= $buyLink ?>" target="_blank">Buy Now ₦<?= $priceLabel ?></a>

              </div>

            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

    </section>

    <!-- upcoming events -->
    <section class="event-sector">
      <fieldset>
        <legend>Upcoming Tourist Events</legend>
      </fieldset>
      <div class="event-block">
        <?php if (!empty($events)): ?>
          <?php foreach ($events as $event): ?>
            <div class="event-li">
              <?php if (!empty($event['cover_image'])): ?>
                <img src="<?= htmlspecialchars($event['cover_image']) ?>" alt="<?= htmlspecialchars($event['title']) ?>">
              <?php else: ?>
                <img src="assets/img/placeholder.png" alt="Event Placeholder">
              <?php endif; ?>
              <div class="event-txt-block">

                <?php

                // Format event date/location
                $dateLabel = !empty($event['event_date']) ? '<b><svg class="event-icons" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 22" xmlns="http://www.w3.org/2000/svg">
              <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2Z"></path>
              </svg></b><span>' . htmlspecialchars($event['event_date']) . '</span>' : '';
                $locLabel = !empty($event['location']) ? '<b><svg class="event-icons" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 22" xmlns="http://www.w3.org/2000/svg">
              <path d="M12 2.25c-3.727 0-6.75 2.878-6.75 6.422 0 4.078 4.5 10.54 6.152 12.773a.739.739 0 0 0 1.196 0c1.652-2.231 6.152-8.692 6.152-12.773 0-3.544-3.023-6.422-6.75-6.422Z"></path>
              <path d="M12 11.25a2.25 2.25 0 1 0 0-4.5 2.25 2.25 0 0 0 0 4.5Z"></path>
              </svg></b><span>' . htmlspecialchars($event['location']) . '</span>' : '';
                ?>
                <div class="event-flex">
                  <?php if (!empty($dateLabel) || !empty($locLabel)): ?>
                    <span><?= $dateLabel ?> </span> <span> <?= $locLabel ?></span>
                  <?php endif; ?>
                </div>
                <h5 class="title"><?= htmlspecialchars($event['title']) ?></h5>
                <p class="desc"><?= htmlspecialchars($event['description'] ?? '') ?></p>
                <?php
                $ctaLink = !empty($event['cta_link']) ? htmlspecialchars($event['cta_link']) : 'events.php';
                $ctaLabel = !empty($event['cta_label']) ? htmlspecialchars($event['cta_label']) : 'Learn More';
                ?>
                <a class="event-btn btn" href="<?= $ctaLink ?>" target="_blank"><?= $ctaLabel ?></a>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p style="grid-column: 1 / -1; margin:auto; text-align: center; font-size: 18px; padding: 40px 0; color: #888;">No upcoming
            events at the moment. Check back later!</p>
        <?php endif; ?>
      </div>
    </section>

    <!-- main block -->
    <section class="blog-sector">
      <fieldset>
        <legend>More Stories</legend>
      </fieldset>

      <div class="blog-block">
        <?php foreach ($otherStories as $story): ?>
          <div class="blog-list">
            <?php
            $storyImg = !empty($story['cover_image'])
              ? $story['cover_image']
              : getFirstBodyImage($story['body'] ?? '');
            ?>
            <img src="<?= htmlspecialchars($storyImg ?: 'assets/public/placeholder.jpg') ?>" alt="<?= htmlspecialchars($story['title']) ?>" />
            <div class="blog-flex-top">
              <p class="date"><?= date('M d, Y', strtotime($story['published_at'])) ?></p>
              <!-- <p class="category"><?= htmlspecialchars($story['category'] ?? 'General') ?></p> -->
            </div>
            <h3 class="title"><?= htmlspecialchars($story['title']) ?></h3>
            <!-- <p > -->
            <a class="desc" href="post.php?slug=<?= htmlspecialchars($story['slug'] ?? '') ?>">
              <?= htmlspecialchars(mb_strimwidth($story['excerpt'] ?? strip_tags($story['body']), 0, 80, "...")) ?>
              <span class="read-more">Read More</span></a>
            <!-- </p> -->
          </div>
        <?php endforeach; ?>

        <?php if (empty($otherStories)): ?>
          <p style="grid-column: 1/-1;text-align: center;">More stories coming soon!</p>
        <?php endif; ?>
      </div>

      <a class="home-blog-btn btn" href="blog.php">More Stories</a>
    </section>

  </main>
  <!-- <script>
document.body.style.overflow = 'hidden';

setTimeout(() => {
    document.querySelector('.loader-block').classList.add('hidden');
    document.body.style.overflow = 'auto';
}, 4000);
</script>  -->
</body>
<?php require_once 'includes/footer.php'; ?>