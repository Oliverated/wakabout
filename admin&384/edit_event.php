<?php
require_once __DIR__ . '/../includes/db.php';
define('UPLOAD_DIR', __DIR__ . '/../assets/post-img/');

$success = '';
$error   = '';

// Load event by ID
$id    = intval($_GET['id'] ?? $_POST['id'] ?? 0);
$event = null;

if ($id > 0) {
    $stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $event  = $result ? $result->fetch_assoc() : null;
    }
}

if (!$event) {
    header('Location: manage_events.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title']       ?? '');
    $category    = trim($_POST['category']    ?? 'General');
    $start_date  = trim($_POST['start_date']  ?? '') ?: null;
    $end_date    = trim($_POST['end_date']    ?? '') ?: null;
    $event_time  = trim($_POST['event_time']  ?? '');
    $city        = trim($_POST['city']        ?? '');
    $location    = trim($_POST['location']    ?? '');
    $description = trim($_POST['description'] ?? '');
    $cta_label   = trim($_POST['cta_label']   ?? 'Learn More');
    $cta_link    = trim($_POST['cta_link']    ?? '');
    $cover_image = $event['cover_image']; // keep existing by default

    if (empty($title)) {
        $error = 'Title is required.';
    }

    // Handle optional new cover image upload
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
            $filename = 'event-' . $id . '-' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['cover_image']['tmp_name'], UPLOAD_DIR . $filename)) {
                $cover_image = 'assets/post-img/' . $filename;
            } else {
                $error = 'Failed to upload image.';
            }
        } else {
            $error = 'Invalid file type. Use jpg, png, webp, or gif.';
        }
    }

    if (empty($error)) {
        $stmt = $conn->prepare(
            "UPDATE events SET title=?, category=?, start_date=?, end_date=?, event_time=?,
             city=?, location=?, description=?, cover_image=?, cta_label=?, cta_link=? WHERE id=?"
        );
        if ($stmt) {
            $stmt->bind_param(
                "sssssssssssi",
                $title, $category, $start_date, $end_date, $event_time,
                $city, $location, $description, $cover_image,
                $cta_label, $cta_link, $id
            );
            if ($stmt->execute()) {
                $success = 'Event updated successfully! <a href="manage_events.php">View All Events</a>';
                // Refresh data
                $stmt2 = $conn->prepare("SELECT * FROM events WHERE id = ?");
                if ($stmt2) {
                    $stmt2->bind_param("i", $id);
                    $stmt2->execute();
                    $result2 = $stmt2->get_result();
                    $event   = $result2 ? $result2->fetch_assoc() : $event;
                }
            } else {
                $error = 'Database error: ' . $stmt->error;
            }
        } else {
            $error = 'Prepare failed: ' . $conn->error;
        }
    }
}

// Format stored start/end dates for date inputs
$fmt_date = fn($d) => !empty($d) ? date('Y-m-d', strtotime($d)) : '';

// Fetch categories for dropdown
$categoriesResult = $conn->query("SELECT name FROM categories ORDER BY name ASC");
$dbCategories = ['General', 'Festivals', 'Culture & Heritage', 'Music & Concerts', 'Exhibitions', 'Tours & Road Trips', 'Recreation'];
if ($categoriesResult && $categoriesResult->num_rows > 0) {
    while ($row = $categoriesResult->fetch_assoc()) {
        if (!in_array($row['name'], $dbCategories)) {
            $dbCategories[] = $row['name'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Event — Wakabout Admin</title>
  <link rel="stylesheet" href="dashboard.css">
</head>
<body>

<?php $activePage = 'events'; include 'sidebar.php'; ?>

  <main class="dash-main">
    <div class="dash-topbar">
      <div style="display:flex;align-items:center;gap:16px;">
        <button class="dash-hamburger" id="hamburger">&#9776;</button>
        <h1>Edit Event</h1>
      </div>
      <a class="dash-btn dash-btn-ghost" href="manage_events.php">← Manage Events</a>
    </div>

    <?php if ($success): ?>
      <div class="dash-alert dash-alert-success"><?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="dash-alert dash-alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="dash-panel">
      <div class="dash-panel-header">
        <h2>Editing: <?= htmlspecialchars($event['title']) ?></h2>
      </div>
      <div style="padding:24px;">
        <form method="POST" action="edit_event.php?id=<?= $id ?>" enctype="multipart/form-data" class="dash-form-container">
          <input type="hidden" name="id" value="<?= $id ?>">

          <!-- Event Title & Category -->
          <div class="dash-form-row">
            <div class="dash-form-group" style="flex:2;">
              <label for="title">Event Title *</label>
              <input type="text" id="title" name="title" placeholder="Enter event title..." required
                     value="<?= htmlspecialchars($event['title']) ?>">
            </div>
            <div class="dash-form-group" style="flex:1;">
              <label for="category">Event Category</label>
              <select id="category" name="category" style="width:100%;padding:10px;border-radius:8px;border:1px solid var(--border,#333);background:var(--bg,#fff);color:var(--text,#111);">
                <?php 
                  $currentCat = $event['category'] ?? 'General';
                  foreach ($dbCategories as $catOption): 
                ?>
                  <option value="<?= htmlspecialchars($catOption) ?>" <?= ($currentCat === $catOption) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($catOption) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <!-- Start Date & End Date -->
          <div class="dash-form-row">
            <div class="dash-form-group">
              <label for="start_date">Start Date</label>
              <input type="date" id="start_date" name="start_date"
                     value="<?= htmlspecialchars($fmt_date($event['start_date'] ?? '')) ?>">
            </div>
            <div class="dash-form-group">
              <label for="end_date">End Date</label>
              <input type="date" id="end_date" name="end_date"
                     value="<?= htmlspecialchars($fmt_date($event['end_date'] ?? '')) ?>">
            </div>
          </div>

          <!-- Time, City & Venue/Location -->
          <div class="dash-form-row">
            <div class="dash-form-group" style="flex:1;">
              <label for="event_time">Time</label>
              <input type="text" id="event_time" name="event_time" placeholder="e.g. 10:00 AM"
                     value="<?= htmlspecialchars($event['event_time'] ?? '') ?>">
            </div>
            <div class="dash-form-group" style="flex:1;">
              <label for="city">City</label>
              <input type="text" id="city" name="city" placeholder="e.g. Lagos"
                     value="<?= htmlspecialchars($event['city'] ?? '') ?>">
            </div>
            <div class="dash-form-group" style="flex:1;">
              <label for="location">Venue / Location</label>
              <input type="text" id="location" name="location" placeholder="e.g. Eko Hotel"
                     value="<?= htmlspecialchars($event['location'] ?? '') ?>">
            </div>
          </div>

          <!-- Cover Image -->
          <div class="dash-form-group">
            <label for="cover_image">Cover Image</label>
            <?php if (!empty($event['cover_image'])): ?>
              <div style="margin-bottom:10px;">
                <img src="../<?= htmlspecialchars($event['cover_image']) ?>" alt="Current cover"
                     style="max-height:140px;border-radius:8px;border:1px solid var(--border,#333);object-fit:cover;">
                <p style="font-size:12px;opacity:.6;margin-top:4px;">
                  Current: <?= htmlspecialchars(basename($event['cover_image'])) ?> — Upload a new file to replace it.
                </p>
              </div>
            <?php else: ?>
              <p style="font-size:12px;opacity:.6;margin-bottom:8px;">No cover image yet — upload one below.</p>
            <?php endif; ?>
            <input type="file" id="cover_image" name="cover_image" accept="image/*">
          </div>

          <!-- Description -->
          <div class="dash-form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="5" placeholder="Brief description of the event..."><?= htmlspecialchars($event['description'] ?? '') ?></textarea>
          </div>

          <!-- CTA Label + CTA Link -->
          <div class="dash-form-row">
            <div class="dash-form-group">
              <label for="cta_label">CTA Button Label</label>
              <input type="text" id="cta_label" name="cta_label" placeholder="e.g. Book Now"
                     value="<?= htmlspecialchars($event['cta_label'] ?? 'Learn More') ?>">
            </div>
            <div class="dash-form-group">
              <label for="cta_link">CTA Link</label>
              <input type="url" id="cta_link" name="cta_link" placeholder="https://..."
                     value="<?= htmlspecialchars($event['cta_link'] ?? '') ?>">
            </div>
          </div>

          <button type="submit" class="dash-submit-btn">Update Event</button>
        </form>
      </div>
    </div>
  </main>

  <script>
    const hamburger = document.getElementById('hamburger');
    const sidebar   = document.getElementById('sidebar');
    const overlay   = document.getElementById('overlay');
    hamburger.addEventListener('click', () => { sidebar.classList.toggle('open'); overlay.classList.toggle('active'); });
    overlay.addEventListener('click', () => { sidebar.classList.remove('open'); overlay.classList.remove('active'); });
  </script>
</body>
</html>
