<?php
require_once __DIR__ . '/../includes/db.php';

$flash = $_GET['msg'] ?? '';
$books = $conn->query("SELECT * FROM books ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);

// Handle inline delete
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    $conn->query("DELETE FROM books WHERE id = $delId");
    header('Location: manage_books.php?msg=deleted');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Books — Wakabout Admin</title>
  <link rel="stylesheet" href="dashboard.css">
</head>
<body>

<?php $activePage = 'books'; include 'sidebar.php'; ?>

  <main class="dash-main">
    <div class="dash-topbar">
      <div style="display:flex;align-items:center;gap:16px;">
        <button class="dash-hamburger" id="hamburger">&#9776;</button>
        <h1>Manage Books</h1>
      </div>
      <a class="dash-btn dash-btn-primary" href="create_book.php">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        Add Book
      </a>
    </div>

    <?php if ($flash === 'deleted'): ?><div class="dash-alert dash-alert-success">✓ Book deleted.</div><?php endif; ?>

    <div class="dash-panel">
      <div class="dash-panel-header">
        <h2>All Books (<?= count($books) ?>)</h2>
      </div>
      <?php if ($books): ?>
      <table class="dash-table">
        <thead>
          <tr>
            <th>#</th><th>Cover</th><th>Title</th><th>Author</th><th>Category</th><th>Price</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($books as $i => $book): ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td>
              <?php if (!empty($book['cover_image'])): ?>
                <img src="../<?= htmlspecialchars($book['cover_image']) ?>" alt="" style="width:50px;height:60px;object-fit:cover;border-radius:4px;">
              <?php else: ?><span style="opacity:.4;">—</span><?php endif; ?>
            </td>
            <td><span class="post-title"><?= htmlspecialchars($book['title']) ?></span></td>
            <td><?= htmlspecialchars($book['author']) ?></td>
            <td><span class="category-badge"><?= htmlspecialchars($book['category']) ?></span></td>
            <td><?= htmlspecialchars($book['price'] ?: '—') ?></td>
            <td>
              <div class="dash-actions">
                <a class="dash-btn dash-btn-edit" href="edit_book.php?id=<?= $book['id'] ?>">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125"/></svg>
                  Edit
                </a>
                <a class="dash-btn dash-btn-delete" href="manage_books.php?delete=<?= $book['id'] ?>" onclick="return confirm('Delete this book?')">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                  Delete
                </a>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php else: ?>
      <div class="dash-empty">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/></svg>
        <p>No books yet. Add your first book!</p>
        <a class="dash-btn dash-btn-primary" href="create_book.php">Add Book</a>
      </div>
      <?php endif; ?>
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
