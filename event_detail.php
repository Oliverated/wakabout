<?php
require_once 'includes/db.php';

// ── Helper: format date range from start_date / end_date ────────────
function formatEventDates(?string $start, ?string $end, ?string $legacy = ''): string {
    if (empty($start) && empty($end)) return htmlspecialchars($legacy ?? '');
    $fmt = function(string $d): string {
        $ts = strtotime($d);
        return $ts ? date('M j, Y', $ts) : $d;
    };
    if (!empty($start) && !empty($end) && $start !== $end) {
        return htmlspecialchars($fmt($start) . ' – ' . $fmt($end));
    }
    return htmlspecialchars($fmt($start ?: $end));
}

// ── Helper: format time (HH:MM) ──────────────────────────────────────
function formatTime(?string $t): string {
    if (empty($t)) return '';
    $ts = strtotime($t);
    return $ts ? date('g:i A', $ts) : $t;
}

// Get event ID from query string
$eventId = $_GET['id'] ?? null;
if (!$eventId) {
    header('Location: events.php');
    exit;
}

// Fetch the single event
$stmt = $conn->prepare('SELECT * FROM events WHERE id = ?');
if ($stmt) {
    $stmt->bind_param('i', $eventId);
    $stmt->execute();
    $result = $stmt->get_result();
    $event = $result->fetch_assoc();
} else {
    $event = null;
}

if (!$event) {
    echo '<p>Event not found.</p>';
    echo '<a href="events.php">Back to events</a>';
    exit;
}

// Fetch other upcoming events for the sidebar (exclude current)
$sidebarStmt = $conn->prepare('SELECT * FROM events WHERE id != ? ORDER BY event_date ASC LIMIT 3');
if ($sidebarStmt) {
    $sidebarStmt->bind_param('i', $eventId);
    $sidebarStmt->execute();
    $sidebarResult = $sidebarStmt->get_result();
    $sidebarEvents = $sidebarResult->fetch_all(MYSQLI_ASSOC);
} else {
    $sidebarEvents = [];
}

// Fetch recent blog posts for the sidebar
$blogResult = $conn->query('SELECT id, title, cover_image, category FROM posts ORDER BY created_at DESC LIMIT 3');
$recentPosts = $blogResult ? $blogResult->fetch_all(MYSQLI_ASSOC) : [];

$pageTitle = htmlspecialchars($event['title']) . ' - Event Details';
$extraCss = 'event.css';
$pageDescription = !empty($event['description']) ? htmlspecialchars(substr(strip_tags($event['description']), 0, 160)) : 'Details for the selected event.';
require_once 'includes/header.php';
?>

    <!-- Page Title Banner -->
    <section class="elephant-sector">
      <h2 class="elephant-title"><?= htmlspecialchars($event['title']) ?></h2>
      <div class="elephant-nav">
        <a href="index.php">Home</a> <b>|</b> <a href="events.php">Events</a> <b>|</b> <span>Details</span>
      </div>
    </section>

    <!-- Main Content Area with Side-by-Side Sidebar -->
    <main class="event-det-main">
      <div class="event-det-layout">

        <!-- Primary Left Column: Hero Cover & Story Details -->
        <div class="event-det-primary">

          <!-- Event Cover Image Banner -->
          <div class="event-det-hero">
            <?php if (!empty($event['cover_image'])): ?>
              <img class="event-det-img" src="<?= htmlspecialchars($event['cover_image']) ?>" alt="<?= htmlspecialchars($event['title']) ?>" />
            <?php else: ?>
              <img class="event-det-img" src="assets/public/event-elephant.png" alt="<?= htmlspecialchars($event['title']) ?>" />
            <?php endif; ?>
            <?php 
              $locationTag = htmlspecialchars($event['city']);
            ?>
            <?php if (!empty($locationTag)): ?>
              <span class="event-det-tag">
                <?= $locationTag ?></span>
            <?php endif; ?>
          </div>

          <!-- Event Content Card Block -->
          <section class="event-det-block">
            <h1 class="event-det-title"><?= htmlspecialchars($event['title']) ?></h1>

            <?php if (!empty($event['description'])): ?>
            <article class="event-det-body">
              <?= nl2br(htmlspecialchars($event['description'])) ?>
            </article>
            <?php endif; ?>

            <!-- Event Metadata Grid -->
            <div class="event-det-info">
        
              <div class="event-det-info-item">
                <label>Start Date</label>
 <span><?= formatEventDates($event['start_date'], "")?></span>
              </div>
        
              <div class="event-det-info-item">
                <label>End Date</label>
            <span><?= formatEventDates($event['end_date'], "")?></span>
              </div>

              <?php if (!empty($event['event_time'])): ?>
              <div class="event-det-info-item">
                <label>Time</label>
                <span><?= htmlspecialchars($event['event_time']) ?></span>
              </div>
              <?php endif; ?>

              <?php if (!empty($event['category'])): ?>
              <div class="event-det-info-item">
                <label>Category</label>
                <span><?= htmlspecialchars($event['category']) ?></span>
              </div>
              <?php endif; ?>

              <?php if (!empty($event['location'])): ?>
              <div class="event-det-info-item">
                <label>Venue</label>
                <span><?= htmlspecialchars($event['location']) ?></span>
              </div>
              <?php endif; ?>
              
              <?php if (!empty($event['city'])): ?>
              <div class="event-det-info-item">
                <label>City</label>
                <span><?= htmlspecialchars($event['city']) ?></span>
              </div>
              <?php endif; ?>
            </div>

            <!-- Action Links & Back Button -->
            <div class="event-det-actions">
              <?php if (!empty($event['cta_link'])): ?>
              <a class="event-det-btn" href="<?= htmlspecialchars($event['cta_link']) ?>" target="_blank" rel="noopener">
                <?= htmlspecialchars($event['cta_label'] ?? 'Get Tickets / Reserve Seat') ?>
              </a>
              <?php endif; ?>

              <a href="events.php" class="back-link">
                &larr; Back to All Events
              </a>
            </div>
          </section>

        </div>

        <!-- Sidebar Right Column: Other Events & Blog Posts -->
        <aside class="event-sidebar event-det-sidebar">

          <!-- Upcoming Events Widget -->
          <?php if (!empty($sidebarEvents)): ?>
          <div class="event-sidebar-header">
            <h4>Upcoming Events</h4>
            <span>Hot<svg fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
  <path d="M12 23a7.5 7.5 0 0 0 7.5-7.5c0-.866-.23-1.697-.5-2.47-1.667 1.647-2.933 2.47-3.8 2.47 3.995-7 1.8-10-4.2-14 .5 5-2.796 7.274-4.138 8.537A7.5 7.5 0 0 0 12 23Zm.71-17.765c3.241 2.75 3.257 4.887.753 9.274-.76 1.333.202 2.991 1.737 2.991.688 0 1.384-.2 2.12-.595a5.5 5.5 0 1 1-9.088-5.412c.126-.118.765-.685.793-.71.424-.38.773-.717 1.118-1.086 1.23-1.318 2.114-2.78 2.566-4.462h.001Z"></path>
</svg></span>
          </div>

          <ul class="event-list">
            <?php foreach ($sidebarEvents as $side): ?>
            <li onclick="window.location='event_detail.php?id=<?= $side['id'] ?>'">
              <?php if (!empty($side['cover_image'])): ?>
                <img src="<?= htmlspecialchars($side['cover_image']) ?>" alt="<?= htmlspecialchars($side['title']) ?>" />
              <?php else: ?>
                <img src="assets/public/event-elephant.png" alt="<?= htmlspecialchars($side['title']) ?>" />
              <?php endif; ?>
              <div class="event-list-info">
                <h5><?= htmlspecialchars($side['title']) ?></h5>
                <div class="event-li-meta">
                  <?php if (!empty($side['event_date'])): ?>
                  <span>
                    <svg class="event-icons" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                    <?= htmlspecialchars($side['event_date']) ?>
                  </span>
                  <?php endif; ?>
                  <?php if (!empty($side['location'])): ?>
                  <span>📍 <?= htmlspecialchars($side['location']) ?></span>
                  <?php endif; ?>
                </div>
              </div>
            </li>
            <?php endforeach; ?>
          </ul>
          <?php endif; ?>

          <!-- Related Blog Posts Widget -->
          <?php if (!empty($recentPosts)): ?>
          <div class="sidebar-blog-widget">
            <div class="event-sidebar-header" style="margin-bottom: 12px;">
              <h4>Recommended Reads</h4>
              <span>Travel Blog</span>
            </div>

            <?php foreach ($recentPosts as $post): ?>
            <a href="post.php?id=<?= $post['id'] ?>" class="blog-mini-item">
              <?php if (!empty($post['cover_image'])): ?>
                <img src="<?= htmlspecialchars($post['cover_image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" />
              <?php else: ?>
                <img src="assets/public/event-elephant.png" alt="<?= htmlspecialchars($post['title']) ?>" />
              <?php endif; ?>
              <div class="blog-mini-content">
                <h6><?= htmlspecialchars($post['title']) ?></h6>
                <span>Read Story &rarr;</span>
              </div>
            </a>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>

        </aside>

      </div>
    </main>

<?php require_once 'includes/footer.php'; ?>
