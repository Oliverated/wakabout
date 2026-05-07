<?php
require_once __DIR__ . '/../includes/db.php';

$success = '';
$error = '';

// Handle Delete Subscriber
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_subscriber'])) {
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("DELETE FROM subscribers WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $success = "Subscriber removed successfully.";
    } else {
        $error = "Failed to remove subscriber.";
    }
}

// Fetch all subscribers
$subscribers = [];
$subTableExists = $conn->query("SHOW TABLES LIKE 'subscribers'")->num_rows > 0;
if ($subTableExists) {
    $subRes = $conn->query("SELECT * FROM subscribers ORDER BY created_at DESC");
    if ($subRes) {
        $subscribers = $subRes->fetch_all(MYSQLI_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Subscribers - Wakabout Admin</title>
  <link rel="stylesheet" href="dashboard.css">
  <style>
    .sub-table-wrapper { overflow-x: auto; margin-top: 20px; }
    .sub-table { width: 100%; border-collapse: collapse; min-width: 600px; }
    .sub-table th { background: var(--card-bg, #16162a); color: var(--text-mut, #a0a0c0); padding: 12px 16px; text-align: left; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; border-bottom: 2px solid var(--border, #333355); }
    .sub-table td { padding: 16px; border-bottom: 1px solid var(--border, #333355); font-size: 15px; color: var(--text, #e0e0f0); }
    .sub-table tr:hover { background: rgba(255,255,255,0.02); }
    .btn-delete { color: #f87171; background: none; border: none; cursor: pointer; font-size: 14px; text-decoration: underline; }
    .btn-delete:hover { color: #ef4444; }
  </style>
</head>
<body>

<?php $activePage = 'subscribers'; include 'sidebar.php'; ?>

  <main class="dash-main">
    <div class="dash-topbar">
      <div style="display:flex;align-items:center;gap:16px;">
        <button class="dash-hamburger" id="hamburger">&#9776;</button>
        <h1>Manage Subscribers</h1>
      </div>
    </div>

    <?php if ($success): ?>
      <div class="dash-alert dash-alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="dash-alert dash-alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="dash-panel">
      <div class="dash-panel-header">
        <h2>All Subscribers (<?= count($subscribers) ?>)</h2>
      </div>
      <div class="sub-table-wrapper">
        <table class="sub-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Email Address</th>
              <th>Subscribed On</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($subscribers)): ?>
              <tr><td colspan="4" style="text-align:center;">No subscribers yet.</td></tr>
            <?php else: ?>
              <?php foreach ($subscribers as $sub): ?>
                <tr>
                  <td><?= $sub['id'] ?></td>
                  <td style="font-weight:600;"><?= htmlspecialchars($sub['email']) ?></td>
                  <td><?= date('M j, Y g:i A', strtotime($sub['created_at'])) ?></td>
                  <td>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to remove this subscriber?');">
                      <input type="hidden" name="delete_subscriber" value="1">
                      <input type="hidden" name="id" value="<?= $sub['id'] ?>">
                      <button type="submit" class="btn-delete">Remove</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </main>

  <div class="dash-overlay" id="overlay"></div>
  <script>
    const hamburger = document.getElementById('hamburger');
    const sidebar   = document.getElementById('sidebar');
    const overlay   = document.getElementById('overlay');
    hamburger.addEventListener('click', () => { sidebar.classList.toggle('open'); overlay.classList.toggle('active'); });
    overlay.addEventListener('click', () => { sidebar.classList.remove('open'); overlay.classList.remove('active'); });
  </script>
</body>
</html>
