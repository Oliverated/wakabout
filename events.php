<?php
require_once 'includes/db.php';

// ── Helper: format date range from start_date / end_date ────────────
function formatEventDates(?string $start, ?string $end, ?string $legacy = ''): string
{
  if (empty($start) && empty($end)) return htmlspecialchars($legacy ?? '');
  $fmt = function (string $d): string {
    $ts = strtotime($d);
    return $ts ? date('M j, Y', $ts) : $d;
  };
  if (!empty($start) && !empty($end) && $start !== $end) {
    return htmlspecialchars($fmt($start) . ' – ' . $fmt($end));
  }
  return htmlspecialchars($fmt($start ?: $end));
}

// ── Helper: format time (HH:MM) ──────────────────────────────────────
function formatTime(?string $t): string
{
  if (empty($t)) return '';
  $ts = strtotime($t);
  return $ts ? date('g:i A', $ts) : $t;
}

// ── Active location filter ──────────────────────────────────
$activeLocation = trim($_GET['location'] ?? 'All');

// ── Fetch all distinct locations from events table ──────────
$locResult = $conn->query("SELECT DISTINCT location FROM events WHERE location IS NOT NULL AND location != '' ORDER BY location ASC");
$eventLocations = ['All'];
if ($locResult) {
  while ($row = $locResult->fetch_assoc()) {
    $eventLocations[] = trim($row['location']);
  }
}

// ── Fetch featured/highlight events (top 3 for carousel & sidebar) ──
$featuredResult = $conn->query("SELECT * FROM events ORDER BY event_date ASC LIMIT 3");
$featuredEvents = $featuredResult ? $featuredResult->fetch_all(MYSQLI_ASSOC) : [];

// ── Fetch events (filter by location if set) ─────────────────
if ($activeLocation !== 'All') {
  $stmt = $conn->prepare("SELECT * FROM events WHERE location = ? ORDER BY event_date ASC, location ASC");
  if ($stmt) {
    $stmt->bind_param("s", $activeLocation);
    $stmt->execute();
    $eventsResult = $stmt->get_result();
  }
} else {
  $eventsResult = $conn->query("SELECT * FROM events ORDER BY event_date ASC, location ASC");
}
$events = $eventsResult ? $eventsResult->fetch_all(MYSQLI_ASSOC) : [];

$pageTitle = 'Tourist Events';
$extraCss = 'event.css';
$pageDescription = 'Discover upcoming tourist events, live theatre, cultural festivals, and travel experiences with Wakabout.';
$extraMeta = '<script type="module" src="./src/driftbox.js"></script>';
require_once 'includes/header.php';
?>

<!-- Page Title Banner -->
<section class="elephant-sector">
  <h2 class="elephant-title">Tour Events</h2>
  <div class="elephant-nav">
    <a href="index.php">Home</a> <b>|</b> <span>Events</span>
  </div>
</section>

<!-- Main Content Area -->
<main class="event-main">

  <?php if (!empty($featuredEvents)): ?>
    <!-- ── HERO FEATURED SHOWCASE (DRIFT-BOX & HIGHLIGHTS) ── -->
    <section class="event-home">

      <!-- Interactive Driftbox Carousel -->
      <drift-box autoplay interval="4500" pause-on-hover pagination rounded>
        <?php foreach ($featuredEvents as $featured): ?>
          <div class="drift-slide">
            <?php if (!empty($featured['cover_image'])): ?>
              <img src="<?= htmlspecialchars($featured['cover_image']) ?>" alt="<?= htmlspecialchars($featured['title']) ?>" />
            <?php else: ?>
              <img src="assets/public/event-elephant.png" alt="<?= htmlspecialchars($featured['title']) ?>" />
            <?php endif; ?>
            <div class="drift-slide-overlay">
              <div class="drift-slide-badges">
                <span class="slide-badge accent">Featured Event</span>
                <?php if (!empty($featured['location'])): ?>
                  <span class="slide-badge"><?= htmlspecialchars($featured['location']) ?></span>
                <?php endif; ?>
              </div>
              <h3 class="drift-slide-title"><?= htmlspecialchars($featured['title']) ?></h3>
              <?php if (!empty($featured['event_date'])): ?>
                <p class="drift-slide-desc"><strong>Date: <?= htmlspecialchars($featured['event_date']) ?></strong></p>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </drift-box>

      <!-- Sidebar Highlights List -->
      <aside class="event-sidebar">
        <div class="event-sidebar-header">
          <h4>Upcoming Events</h4>
          <span>Hot <svg class="event-icon" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path d="M5.25 15c0-4.36 5.813-7.734 4.5-12.75 3.094 0 9 4.5 9 12.75a6.75 6.75 0 0 1-13.5 0v0Z"></path>
              <path d="M15 17.25c0 2.705-1.5 3.75-3 3.75s-3-1.045-3-3.75 1.875-4.031 1.5-6c1.969 0 4.5 3.295 4.5 6Z"></path>
            </svg></span>
        </div>
        

        <ul class="event-list">
          <?php foreach ($featuredEvents as $highlight): ?>
            <li>
              <?php if (!empty($highlight['cover_image'])): ?>
                <img src="<?= htmlspecialchars($highlight['cover_image']) ?>" alt="<?= htmlspecialchars($highlight['title']) ?>" />
              <?php else: ?>
                <img src="assets/public/event-elephant.png" alt="<?= htmlspecialchars($highlight['title']) ?>" />
              <?php endif; ?>
              <div class="event-list-info">
                <h5><?= htmlspecialchars($highlight['title']) ?></h5>
                <div class="event-li-meta">
                  <?php if (!empty($highlight['event_date'])): ?>
                    <span>
                      <svg class="event-icons" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                      </svg>
                      <?= formatEventDates(htmlspecialchars($highlight['start_date']), "") ?>
                    </span>
                  <?php endif; ?>
                  <?php if (!empty($highlight['city'])): ?>
                    <span>📍 <?= htmlspecialchars($highlight['city']) ?></span>
                  <?php endif; ?>
                </div>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
      </aside>

    </section>
  <?php endif; ?>

  <!-- ── LOCATION FILTER CHIPS ── -->
  <?php if (count($eventLocations) > 1): ?>
    <section class="event-filter-section">
      <p class="filter-heading">Filter by Location</p>
      <div class="category-chips">
        <?php foreach ($eventLocations as $loc): ?>
          <a class="chip <?= ($activeLocation === $loc) ? 'active' : '' ?>"
            href="events.php<?= $loc !== 'All' ? '?location=' . urlencode($loc) : '' ?>">
            <?php if ($loc !== 'All'): ?>
              <svg style="width:13px;height:13px;margin-right:2px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
              </svg>
            <?php endif; ?>
            <?= htmlspecialchars($loc) ?>
          </a>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>

  <!-- ── Results count ── -->
  <p class="filter-results-count">
    <?php if ($activeLocation !== 'All'): ?>
      Showing <strong><?= count($events) ?></strong> event<?= count($events) !== 1 ? 's' : '' ?> in
      <strong><?= htmlspecialchars($activeLocation) ?></strong>
      &mdash; <a href="events.php">View all</a>
    <?php else: ?>
      <strong><?= count($events) ?></strong> upcoming event<?= count($events) !== 1 ? 's' : '' ?>
    <?php endif; ?>
  </p>

  <!-- ── MAIN EVENT CARDS GRID ── -->
  <section class="event-grid">
    <?php if (!empty($events)): ?>
      <?php foreach ($events as $event): ?>
        <article class="event-block">
          <div class="event-img-wrap">
            <?php if (!empty($event['cover_image'])): ?>
              <img src="<?= htmlspecialchars($event['cover_image']) ?>" alt="<?= htmlspecialchars($event['title']) ?>" />
            <?php else: ?>
              <img src="assets/public/event-elephant.png" alt="<?= htmlspecialchars($event['title']) ?>" />
            <?php endif; ?>
            <?php
            $tagText = !empty($event['category']) ? $event['category'] : (!empty($event['city']) ? $event['city'] : ($event['location'] ?? ''));
            ?>
            <?php if (!empty($tagText)): ?>
              <span class="event-category-tag"><?= htmlspecialchars($tagText) ?></span>
            <?php endif; ?>
          </div>
          <div class="event-txt-block">
            <h3 class="title"><?= htmlspecialchars($event['title']) ?></h3>
            <div class="event-info">
              <?php
              $dateStr = formatEventDates($event['start_date'], '');
              $timeStr = htmlspecialchars($event['event_time'] ?? '');
              ?>
              <?php if (!empty($dateStr) || !empty($timeStr)): ?>
                <div class="event-info-item">
                  <svg class="event-icons" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                  </svg>
                  <span><?= implode(' &bull; ', array_filter([$dateStr, $timeStr])) ?></span>
                </div>
              <?php endif; ?>
              <?php if (!empty($event['city']) || !empty($event['location'])): ?>
                <div class="event-info-item">
                  <svg class="event-icons" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                  </svg>
                  <span><?php
                        $parts = array_filter([htmlspecialchars($event['city'] ?? '')]);
                        echo implode(' &mdash; ', $parts);
                        ?></span>
                </div>
              <?php endif; ?>
            </div>
            <a class="event-btn" href="event_detail.php?id=<?= $event['id'] ?>">
              More Details
            </a>
          </div>
        </article>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="no-results" style="grid-column: 1 / -1;">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
        </svg>
        <p><?= $activeLocation !== 'All' ? 'No events found in <strong>' . htmlspecialchars($activeLocation) . '</strong>.' : 'No upcoming events at the moment. Check back later!' ?></p>
        <?php if ($activeLocation !== 'All'): ?>
          <a class="chip active" href="events.php">Browse all events</a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </section>

</main>

<?php require_once 'includes/footer.php'; ?>