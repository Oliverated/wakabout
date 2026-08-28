<?php
require_once __DIR__ . '/../includes/db.php';
define('UPLOAD_DIR', __DIR__ . '/../assets/post-img/');

$success = '';
$error = '';

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
    $cover_image = '';

    

    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','webp','gif'])) {
            $filename = 'event-' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['cover_image']['tmp_name'], UPLOAD_DIR . $filename)) {
                $cover_image = 'assets/post-img/' . $filename;
            } else { $error = 'Failed to upload image.'; }
        } else { $error = 'Invalid file type. Use jpg, png, webp, or gif.'; }
    }

    if (empty($title)) { $error = 'Title is required.'; }

    if (empty($error)) {
        $stmt = $conn->prepare(
            "INSERT INTO events (title, category, start_date, end_date, event_time, city, location, description, cover_image, cta_label, cta_link)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        if ($stmt) {
            $stmt->bind_param(
                "sssssssssss",
                $title, $category, $start_date, $end_date, $event_time,
                $city, $location, $description, $cover_image,
                $cta_label, $cta_link
            );
            if ($stmt->execute()) {
                $success = 'Event added successfully! <a href="manage_events.php">View All Events</a>';
                $_POST = [];
            } else { $error = 'Database error: ' . $stmt->error; }
        } else { $error = 'Prepare failed: ' . $conn->error; }
    }
}

// Fetch categories for dropdown
$categoriesResult = $conn->query("SELECT name FROM categories ORDER BY name ASC");
$dbCategories = ['General', 'Festivals', 'Culture & Heritage', 'Music & Concerts', 'Exhibitions', 'Tours & Road Trips', 'Recreation', "Tech Event"];
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
  <title>Add Event — Wakabout Admin</title>
  <link rel="stylesheet" href="dashboard.css">
</head>
<body>

<?php $activePage = 'create_event'; include 'sidebar.php'; ?>

  <main class="dash-main">
    <div class="dash-topbar">
      <div style="display:flex;align-items:center;gap:16px;">
        <button class="dash-hamburger" id="hamburger">&#9776;</button>
        <h1>Add Event</h1>
      </div>
      <a class="dash-btn dash-btn-ghost" href="manage_events.php">← Manage Events</a>
    </div>

    <?php if ($success): ?><div class="dash-alert dash-alert-success"><?= $success ?></div><?php endif; ?>
    <?php if ($error): ?><div class="dash-alert dash-alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="dash-panel">
      <div class="dash-panel-header"><h2>New Event</h2></div>
      <div style="padding:24px;">
        <form method="POST" enctype="multipart/form-data" class="dash-form-container">

          <!-- Event Title & Category -->
          <div class="dash-form-row">
            <div class="dash-form-group" style="flex:2;">
              <label>Event Title *</label>
              <input type="text" name="title" placeholder="Enter event title..." required value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
            </div>
            <div class="dash-form-group" style="flex:1;">
              <label>Event Category</label>
              <select name="category" style="width:100%;padding:10px;border-radius:8px;border:1px solid var(--border,#333);background:var(--bg,#fff);color:var(--text,#111);">
                <?php 
                  $selCat = $_POST['category'] ?? 'General';
                  foreach ($dbCategories as $catOption): 
                ?>
                  <option value="<?= htmlspecialchars($catOption) ?>" <?= ($selCat === $catOption) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($catOption) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <!-- Start Date & End Date -->
          <div class="dash-form-row">
            <div class="dash-form-group">
              <label>Start Date</label>
              <input type="date" name="start_date" value="<?= htmlspecialchars($_POST['start_date'] ?? '') ?>">
            </div>
            <div class="dash-form-group">
              <label>End Date</label>
              <input type="date" name="end_date" value="<?= htmlspecialchars($_POST['end_date'] ?? '') ?>">
            </div>
          </div>

          <!-- Time, City & Venue/Location -->
          <div class="dash-form-row">
            <div class="dash-form-group" style="flex:1;">
              <label>Time</label>
              <input type="text" name="event_time" placeholder="e.g. 10:00 AM" value="<?= htmlspecialchars($_POST['event_time'] ?? '') ?>">
            </div>
            <div class="dash-form-group" style="flex:1;">
              <label>City</label>
              <input type="text" name="city" placeholder="e.g. Lagos" value="<?= htmlspecialchars($_POST['city'] ?? '') ?>">
            </div>
            <div class="dash-form-group" style="flex:1;">
              <label>Venue / Location</label>
              <input type="text" name="location" placeholder="e.g. Eko Hotel" value="<?= htmlspecialchars($_POST['location'] ?? '') ?>">
            </div>
          </div>

          <!-- Cover Image -->
          <div class="dash-form-group">
            <label>Cover Image</label>
            <input type="file" name="cover_image" accept="image/*">
          </div>

          <!-- Description -->
          <div class="dash-form-group">
            <label>Description</label>
            <textarea name="description" rows="5" placeholder="Brief description of the event..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
          </div>

          <!-- CTA Label + CTA Link -->
          <div class="dash-form-row">
            <div class="dash-form-group">
              <label>CTA Button Label</label>
              <input type="text" name="cta_label" placeholder="e.g. Book Now" value="<?= htmlspecialchars($_POST['cta_label'] ?? 'Learn More') ?>">
            </div>
            <div class="dash-form-group">
              <label>CTA Link</label>
              <input type="url" name="cta_link" placeholder="https://..." value="<?= htmlspecialchars($_POST['cta_link'] ?? '') ?>">
            </div>
          </div>

          <button type="submit" class="dash-submit-btn">Add Event</button>
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
</html>
