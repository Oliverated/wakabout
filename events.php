<?php
require_once 'includes/db.php';

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

// ── Fetch events (filter by location if set) ─────────────────
if ($activeLocation !== 'All') {
    $stmt = $conn->prepare("SELECT * FROM events WHERE location = ? ORDER BY created_at DESC");
    if ($stmt) {
        $stmt->bind_param("s", $activeLocation);
        $stmt->execute();
        $eventsResult = $stmt->get_result();
    }
} else {
    $eventsResult = $conn->query("SELECT * FROM events ORDER BY created_at DESC");
}
$events = $eventsResult ? $eventsResult->fetch_all(MYSQLI_ASSOC) : [];

$pageTitle = 'Tourist Events';
$extraCss = 'pages.css';
$pageDescription = 'Discover upcoming tourist events, festivals, and attractions across Nigeria.';
require_once 'includes/header.php';
?>

      <section class="elephant-sector">
      <h2 class="elephant-title">Tour Events</h2>
      <div class="elephant-nav"
        ><a href="index.php">Home</a> | <span>Events</span></div
      >
    </section>

  <main class="event-main">

    <!-- ── Location Filter Chips ── -->
    <?php if (count($eventLocations) > 1): ?>
    <div class="category-chips" style="margin-bottom: 28px;">
      <?php foreach ($eventLocations as $loc): ?>
        <a class="chip <?= ($activeLocation === $loc) ? 'active' : '' ?>"
           href="events.php<?= $loc !== 'All' ? '?location=' . urlencode($loc) : '' ?>">
          <?php if ($loc !== 'All'): ?>
            <svg style="width:13px;height:13px;margin-right:4px;vertical-align:middle;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
          <?php endif; ?>
          <?= htmlspecialchars($loc) ?>
        </a>
      <?php endforeach; ?>
    </div>
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

    <div class="event-grid">
      <?php if (!empty($events)): ?>
        <?php foreach ($events as $event): ?>
          <div class="event-card">
            <?php if (!empty($event['cover_image'])): ?>
              <img src="<?= htmlspecialchars($event['cover_image']) ?>" alt="<?= htmlspecialchars($event['title']) ?>" >
            <?php else: ?>
              <img src="assets/img/placeholder.png" alt="Event Placeholder" >
            <?php endif; ?>
            <div class="event-card-body">
              <?php
              // Format event date/location
              $dateLabel = !empty($event['event_date']) ? '<b><svg class="event-icons" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 22" xmlns="http://www.w3.org/2000/svg"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2Z"></path></svg></b><span>' . htmlspecialchars($event['event_date']) . '</span>' : '';
              $locLabel = !empty($event['location']) ? '<b><svg class="event-icons" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 22" xmlns="http://www.w3.org/2000/svg"><path d="M12 2.25c-3.727 0-6.75 2.878-6.75 6.422 0 4.078 4.5 10.54 6.152 12.773a.739.739 0 0 0 1.196 0c1.652-2.231 6.152-8.692 6.152-12.773 0-3.544-3.023-6.422-6.75-6.422Z"></path><path d="M12 11.25a2.25 2.25 0 1 0 0-4.5 2.25 2.25 0 0 0 0 4.5Z"></path></svg></b><span>' . htmlspecialchars($event['location']) . '</span>' : '';
              ?>
              <div class="event-flex" >
                <?php if (!empty($dateLabel) || !empty($locLabel)): ?>
                  <span ><?= $dateLabel ?></span>
                  <span ><?= $locLabel ?></span>
                <?php endif; ?>
              </div>
              <h3 class="title"><?= htmlspecialchars($event['title']) ?></h3>
              <p class="desc"><?= htmlspecialchars($event['description'] ?? '') ?></p>
              <?php
              $ctaLink = !empty($event['cta_link']) ? htmlspecialchars($event['cta_link']) : '#';
              $ctaLabel = !empty($event['cta_label']) ? htmlspecialchars($event['cta_label']) : 'Learn More';
              ?>

            </div>
              <a class="event-cta btn" href="<?= $ctaLink ?>" target="_blank"><?= $ctaLabel ?></a>            
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="no-results" style="grid-column: 1 / -1;">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
          <p><?= $activeLocation !== 'All' ? 'No events found in <strong>' . htmlspecialchars($activeLocation) . '</strong>.' : 'No upcoming events at the moment. Check back later!' ?></p>
          <?php if ($activeLocation !== 'All'): ?>
            <a class="chip active" href="events.php">Browse all events</a>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
  </main>

<?php require_once 'includes/footer.php'; ?>
