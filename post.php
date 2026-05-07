<?php
require_once __DIR__ . '/includes/db.php';

// Get the slug from URL
$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    header('Location: ./');
    exit;
}

// Fetch the post
$stmt = $conn->prepare("SELECT * FROM posts WHERE slug = ?");
$post = null;
if ($stmt) {
    $stmt->bind_param("s", $slug);
    $stmt->execute();
    $result = $stmt->get_result();
    $post = $result ? $result->fetch_assoc() : null;
}

if (!$post) {
    http_response_code(404);
    echo '<!DOCTYPE html><html><head><title>Post Not Found</title><link rel="stylesheet" href="assets/required.css"></head><body style="display:flex;align-items:center;justify-content:center;height:100vh;flex-direction:column;"><h1>404 - Post Not Found</h1><p>The post you are looking for does not exist.</p><a href="./" style="color:var(--accent);margin-top:20px;font-weight:bold;">&larr; Back to Home</a></body></html>';
    exit;
}

// Increment the view counter for this post
$conn->query("UPDATE posts SET views = views + 1 WHERE id = " . (int)$post['id']);

// Parse comma-separated categories
$postCategories = array_filter(array_map('trim', explode(',', $post['category'] ?? 'General')));
$primaryCategory = $postCategories[0] ?? 'General';

// Fetch related posts (same primary category, exclude current)
$relatedPosts = [];
$relatedStmt = $conn->prepare("SELECT id, title, slug, cover_image FROM posts WHERE category LIKE ? AND id != ? ORDER BY published_at DESC LIMIT 4");
if ($relatedStmt) {
    $likecat = '%' . $primaryCategory . '%';
    $relatedStmt->bind_param("si", $likecat, $post['id']);
    $relatedStmt->execute();
    $relatedPosts = $relatedStmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Fetch upcoming events from events table
$events = [];
$eventsResult = $conn->query("SELECT * FROM events ORDER BY created_at DESC LIMIT 3");
if ($eventsResult) {
    $events = $eventsResult->fetch_all(MYSQLI_ASSOC);
}

// Fetch all distinct categories from DB
$allCats = [];
$catResult = $conn->query("SELECT DISTINCT category FROM posts ORDER BY category");
if ($catResult) {
    foreach ($catResult->fetch_all(MYSQLI_ASSOC) as $row) {
        foreach (array_map('trim', explode(',', $row['category'])) as $c) {
            if ($c && !in_array($c, $allCats)) $allCats[] = $c;
        }
    }
}

// Check user like status and fetch count
$likeCount = 0;
$userLiked = false;
$likeStmt = $conn->prepare("SELECT COUNT(*) AS total FROM post_likes WHERE post_id = ?");
if ($likeStmt) {
    $likeStmt->bind_param("i", $post['id']);
    $likeStmt->execute();
    $likeCount = $likeStmt->get_result()->fetch_assoc()['total'];
}

if (isset($_SESSION['user_id'])) {
    $ulStmt = $conn->prepare("SELECT id FROM post_likes WHERE post_id = ? AND user_id = ?");
    if ($ulStmt) {
        $ulStmt->bind_param("ii", $post['id'], $_SESSION['user_id']);
        $ulStmt->execute();
        if ($ulStmt->get_result()->num_rows > 0) {
            $userLiked = true;
        }
    }
}

// Fetch comments
$comments = [];
$cStmt = $conn->prepare("SELECT c.id, c.comment, c.created_at, u.username FROM post_comments c JOIN users u ON c.user_id = u.id WHERE c.post_id = ? ORDER BY c.created_at DESC");
if ($cStmt) {
    $cStmt->bind_param("i", $post['id']);
    $cStmt->execute();
    $comments = $cStmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$publishedDate = date('F j, Y', strtotime($post['published_at']));

$pageTitle = $post['title'];
$extraCss = 'post.css';
$pageDescription = $post['excerpt'] ?? strip_tags(mb_strimwidth($post['body'], 0, 150, '...'));
require_once 'includes/header.php';
?>

<!-- BLOG POST -->
<section class="post-container">
  <div class="post-content">
    <a href="./" class="back-btn">&larr; Back to Home</a>

    <div class="post-header">
      <h1 class="post-title"><?= htmlspecialchars($post['title']) ?></h1>
      <div class="post-meta">
        <span class="post-author">By <?= htmlspecialchars($post['author'] ?? 'Wakabout Team') ?></span>
        <span class="post-date"><?= $publishedDate ?></span>
      </div>
      <!-- Category badges -->
      <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:10px;">
        <?php foreach ($postCategories as $cat): ?>
          <!-- <a href="blog.php?category=<?= urlencode($cat) ?>" style="display:inline-block;padding:4px 14px;border-radius:20px;background:var(--accent,#4f46e5);color:#fff;font-size:12px;font-weight:600;text-decoration:none;"><?= htmlspecialchars($cat) ?></a> -->
        <?php endforeach; ?>
      </div>
    </div>

    <?php if (!empty($post['cover_image'])): ?>
    <img src="<?= htmlspecialchars($post['cover_image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" class="post-image">
    <?php endif; ?>

    <div class="post-body">
      <?= $post['body'] ?>
    </div>
    
    <!-- ENGAGEMENT SECTION -->
    <div class="post-engagement">
      
      <!-- COMMENTS -->
      <div class="post-comments" id="commentsSection">
        <h3 style="margin-bottom:15px;">Comments (<span id="commentCountTotal"><?= count($comments) ?></span>)</h3>
        
        <?php if (isset($_SESSION['user_id'])): ?>
          <form id="commentForm" class="engage-form" style="margin-bottom:30px;">
            <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
            <textarea name="comment" rows="3" style="width:100%; font-family:inherit; font-size:15px; box-sizing:border-box;" placeholder="Add a comment..." required></textarea>

                         <!-- LIKES -->
      <div class="post-likes" style="display:flex; align-items:center; gap:10px;">
        <div id="likeBtn" data-post-id="<?= $post['id'] ?>" style=" border:none; cursor:pointer; display:flex; gap:5px; font-size:16px; color: <?= $userLiked ? '#e63946' : '#ff2b3dff' ?>;">
          <svg class="like-icon" fill="<?= $userLiked ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" /></svg>
          <span id="likeCount"><?= $likeCount ?></span>
        </div>
        <?php if (!isset($_SESSION['user_id'])): ?>
          <span style="font-size:12px;color:#888;">(<a href="auth/login.php" style="color:var(--accent);">Log in</a> to like)</span>
        <?php endif; ?>
      </div> 

            <button type="submit" style="background:transparent; border:none;" >
              <svg class="comment-icon" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
 <path d="M10 14 21 3"></path>
 <path d="m21 3-6.5 18a.55.55 0 0 1-1 0L10 14l-7-3.5a.55.55 0 0 1 0-1L21 3Z"></path>
</svg>
            </button>
          </form>
        <?php else: ?>
          <p style="margin-bottom:30px;font-size:14px;color:#555;background:#f5f5f5;padding:15px;border-radius:8px;">You must <a href="auth/login.php" style="color:var(--accent);font-weight:bold;text-decoration:none;">log in</a> to post a comment.</p>
        <?php endif; ?>



        <div id="commentsList" style="display:flex; flex-direction:column; gap:20px;">
          <?php foreach ($comments as $c): ?>
            <div class="comment" style="background:#f9f9fa; padding:15px; border-radius:8px; border-left: 3px solid #ddd;">
              <div style="display:flex; justify-content:space-between; margin-bottom:5px;">
                <strong style="color:var(--secondary);font-size:15px;"><?= htmlspecialchars($c['username']) ?></strong>
                <span style="font-size:12px; color:#888;"><?= date('M j, Y \a\t g:i A', strtotime($c['created_at'])) ?></span>
              </div>
              <p style="margin:0; font-size:14px; line-height:1.5; color:#333;"><?= nl2br(htmlspecialchars($c['comment'])) ?></p>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

    </div>

    <!-- AJAX SCRIPT -->
    <script>
      const likeBtn = document.getElementById('likeBtn');
      if(likeBtn) {
        likeBtn.addEventListener('click', async () => {
          const postId = likeBtn.getAttribute('data-post-id');
          const formData = new FormData();
          formData.append('post_id', postId);

          try {
            const req = await fetch('ajax/like_post.php', { method: 'POST', body: formData });
            const res = await req.json();
            if(res.success) {
               document.getElementById('likeCount').textContent = res.total_likes;
               const svg = likeBtn.querySelector('svg');
               if(res.action === 'liked') {
                 likeBtn.style.color = '#e63946';
                 svg.setAttribute('fill', 'currentColor');
               } else {
                 likeBtn.style.color = '#555';
                 svg.setAttribute('fill', 'none');
               }
            } else {
               alert(res.message);
            }
          } catch(e) { console.error('Like error', e); }
        });
      }

      const commentForm = document.getElementById('commentForm');
      if(commentForm) {
        commentForm.addEventListener('submit', async (e) => {
          e.preventDefault();
          const formData = new FormData(commentForm);
          const btn = commentForm.querySelector('button');
          btn.disabled = true;
          // btn.textContent = 'Posting...';

          try {
            const req = await fetch('ajax/add_comment.php', { method: 'POST', body: formData });
            const res = await req.json();
            if(res.success) {
              const c = res.comment;
              const html = `
                <div class="comment" style="background:#f9f9fa; padding:15px; border-radius:1px; border-left: 3px solid  #22890093;">
                  <div style="display:flex; justify-content:space-between; margin-bottom:5px;">
                    <strong style="color:var(--secondary);font-size:15px;">${c.username}</strong>
                    <span style="font-size:12px; color:#888;">${c.date}</span>
                  </div>
                  <p style="margin:0; font-size:14px; line-height:1.5; color:#333;">${c.text}</p>
                </div>
              `;
              const list = document.getElementById('commentsList');
              list.insertAdjacentHTML('afterbegin', html);
              commentForm.reset();
              const counter = document.getElementById('commentCountTotal');
              counter.textContent = list.children.length;
            } else {
              alert(res.message);
            }
          } catch(e) { console.error('Comment error', e); }
          btn.disabled = false;
          // btn.innerHTML = "<svg class=comment-icon fill=none stroke=currentColor stroke-linecap=round stroke-linejoin=round viewBox=0 0 24 24><path d=M10 14 21 3></path> <path d=m21 3-6.5 18a.55.55 0 0 1-1 0L10 14l-7-3.5a.55.55 0 0 1 0-1L21 3Z></path></svg>"
        });
      };
    </script>

  </div>

  <!-- SIDEBAR -->
  <aside class="post-sidebar">

    <!-- Related Posts -->
    <?php if (!empty($relatedPosts)): ?>
    <div class="post-block">
      <h3>Related Posts</h3>
      <?php foreach ($relatedPosts as $related): ?>
      <a href="post.php?slug=<?= htmlspecialchars($related['slug']) ?>" class="side-post">
        <?php if (!empty($related['cover_image'])): ?>
          <img src="<?= htmlspecialchars($related['cover_image']) ?>" alt="">
        <?php else: ?>
          <img src="assets/post-img/_uhdtexture596.jpg" alt="">
        <?php endif; ?>
        <p><?= htmlspecialchars($related['title']) ?></p>
      </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Post Categories -->
    <?php if (!empty($postCategories)): ?>
    <div class="post-block post-categ">
      <h3>Post Categories</h3>
      <div>
        <?php foreach ($postCategories as $cat): ?>
          <?php
            // Look up the group for this subcategory
            $gStmt = $conn->prepare("SELECT group_name FROM categories WHERE name = ? LIMIT 1");
            $gName = '';
            if ($gStmt) {
                $gStmt->bind_param("s", $cat);
                $gStmt->execute();
                $gRes = $gStmt->get_result();
                if ($gRow = $gRes->fetch_assoc()) $gName = $gRow['group_name'];
            }
          ?>
          <a href="blog.php?category=<?= urlencode($gName ?: 'All') ?>&sub=<?= urlencode($cat) ?>"><?= htmlspecialchars($cat) ?></a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Upcoming Events -->
    <div class="post-block">
      <h3>Upcoming Tourist Events</h3>
      <?php if (!empty($events)): ?>
        <?php foreach ($events as $event): ?>
        <a href="<?= htmlspecialchars($event['cta_link'] ?: '#') ?>" class="side-post" target="_blank">
          <?php if (!empty($event['cover_image'])): ?>
            <img src="<?= htmlspecialchars($event['cover_image']) ?>" alt="">
          <?php else: ?>
            <img src="assets/post-img/_uhdtexture596.jpg" alt="">
          <?php endif; ?>
          <p>
            <strong><?= htmlspecialchars($event['title']) ?></strong>
            <?php if (!empty($event['event_date'])): ?>
              <span style="display:block;font-size:12px;opacity:.6;margin-top:2px;"><?= htmlspecialchars($event['event_date']) ?></span>
            <?php endif; ?>
          </p>
        </a>
        <?php endforeach; ?>
      <?php else: ?>
        <p style="opacity:.5;font-size:14px;">No upcoming events at the moment.</p>
      <?php endif; ?>
    </div>

  </aside>
</section>

<?php require_once 'includes/footer.php'; ?>


