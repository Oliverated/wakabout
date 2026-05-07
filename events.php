<?php
require_once 'includes/db.php';
$pageTitle = 'Tourist Events';
$extraCss = 'pages.css';
$pageDescription = 'Discover upcoming tourist events, festivals, and attractions across Nigeria.';
require_once 'includes/header.php';
?>

      <section class="elephant-sector">
      <h2 class="elephant-title">Tour Events</h2>
      <div class="elephant-nav"
        ><a href="index.html">Home</a> | <span>Events</span></div
      >
    </section>

<?php
$eventsResult = $conn->query("SELECT * FROM events ORDER BY created_at DESC");
$events = $eventsResult ? $eventsResult->fetch_all(MYSQLI_ASSOC) : [];
?>
  <main class="event-main">
    <div class="event-grid">
      <?php if (!empty($events)): ?>
        <?php foreach ($events as $event): ?>
          <div class="event-card" >
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
              <h3 class="title" "><?= htmlspecialchars($event['title']) ?></h3>
              <p class="desc" "><?= htmlspecialchars($event['description'] ?? '') ?></p>
              <?php
              $ctaLink = !empty($event['cta_link']) ? htmlspecialchars($event['cta_link']) : '#';
              $ctaLabel = !empty($event['cta_label']) ? htmlspecialchars($event['cta_label']) : 'Learn More';
              ?>
              <a class="event-cta btn" href="<?= $ctaLink ?>" target="_blank" ><?= $ctaLabel ?></a>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p style="grid-column: 1 / -1; text-align: center; font-size: 18px; padding: 40px 0; color: #888;">No upcoming events at the moment. Check back later!</p>
      <?php endif; ?>
    </div>
      </main>

<?php require_once 'includes/footer.php'; ?>
