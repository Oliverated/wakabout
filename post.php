<?php
require_once __DIR__ . '/includes/db.php';

// ── Extract first <img src> from post body HTML ─────────────
function getFirstBodyImage(string $html): string
{
  if (empty($html))
    return '';
  if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $html, $m)) {
    return $m[1];
  }
  return '';
}

// Get the slug from URL
$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
  header('Location: ./');
  exit;
}

// Fetch the post
$stmt = $conn->prepare('SELECT * FROM posts WHERE slug = ?');
$post = null;
if ($stmt) {
  $stmt->bind_param('s', $slug);
  $stmt->execute();
  $result = $stmt->get_result();
  $post = $result ? $result->fetch_assoc() : null;
}

if (!$post) {
  http_response_code(404);
  echo '<!DOCTYPE html><html><head><title>Post Not Found</title><link rel="stylesheet" href="assets/required.css"></head><body style="display:flex;align-items:center;justify-content:center;height:100vh;flex-direction:column;"><h1>404 - Post Not Found</h1><p>The post you are looking for does not exist.</p><a href="./" style="color:var(--accent);margin-top:20px;font-weight:bold;">&larr; Back to Home</a></body></html>';
  exit;
}

// Increment the view counter — once per session per post
if (session_status() === PHP_SESSION_NONE)
  require_once __DIR__ . '/includes/session_config.php';
$sessionKey = 'viewed_post_' . (int) $post['id'];
if (empty($_SESSION[$sessionKey])) {
  $conn->query('UPDATE posts SET views = views + 1 WHERE id = ' . (int) $post['id']);
  $_SESSION[$sessionKey] = true;
  // Refetch to get updated view count
  $stmt = $conn->prepare('SELECT * FROM posts WHERE slug = ?');
  if ($stmt) {
    $stmt->bind_param('s', $slug);
    $stmt->execute();
    $result = $stmt->get_result();
    $post = $result ? $result->fetch_assoc() : $post;
  }
}

// Parse comma-separated categories
$postCategories = array_filter(array_map('trim', explode(',', $post['category'] ?? 'General')));
$primaryCategory = $postCategories[0] ?? 'General';

// Fetch related posts (same primary category, exclude current)
$relatedPosts = [];
$relatedStmt = $conn->prepare('SELECT id, title, slug, cover_image, body FROM posts WHERE category LIKE ? AND id != ? ORDER BY published_at DESC LIMIT 4');
if ($relatedStmt) {
  $likecat = '%' . $primaryCategory . '%';
  $relatedStmt->bind_param('si', $likecat, $post['id']);
  $relatedStmt->execute();
  $relatedPosts = $relatedStmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Fetch upcoming events from events table
$events = [];
$eventsResult = $conn->query('SELECT * FROM events ORDER BY created_at DESC LIMIT 3');
if ($eventsResult) {
  $events = $eventsResult->fetch_all(MYSQLI_ASSOC);
}

// Fetch all distinct categories from DB
$allCats = [];
$catResult = $conn->query('SELECT DISTINCT category FROM posts ORDER BY category');
if ($catResult) {
  foreach ($catResult->fetch_all(MYSQLI_ASSOC) as $row) {
    foreach (array_map('trim', explode(',', $row['category'])) as $c) {
      if ($c && !in_array($c, $allCats))
        $allCats[] = $c;
    }
  }
}

// Check user like status and fetch count
$likeCount = 0;
$userLiked = false;
$likeStmt = $conn->prepare('SELECT COUNT(*) AS total FROM post_likes WHERE post_id = ?');
if ($likeStmt) {
  $likeStmt->bind_param('i', $post['id']);
  $likeStmt->execute();
  $likeCount = $likeStmt->get_result()->fetch_assoc()['total'];
}

if (isset($_SESSION['user_id'])) {
  $ulStmt = $conn->prepare('SELECT id FROM post_likes WHERE post_id = ? AND user_id = ?');
  if ($ulStmt) {
    $ulStmt->bind_param('ii', $post['id'], $_SESSION['user_id']);
    $ulStmt->execute();
    if ($ulStmt->get_result()->num_rows > 0) {
      $userLiked = true;
    }
  }
}

// Fetch comments
$comments = [];
$cStmt = $conn->prepare('SELECT c.id, c.comment, c.created_at, u.username FROM post_comments c JOIN users u ON c.user_id = u.id WHERE c.post_id = ? ORDER BY c.created_at DESC');
if ($cStmt) {
  $cStmt->bind_param('i', $post['id']);
  $cStmt->execute();
  $comments = $cStmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$publishedDate = date('F j, Y', strtotime($post['published_at']));

$pageTitle = $post['title'];
$extraCss = 'post.css';
$pageDescription = $post['excerpt'] ?? strip_tags(mb_strimwidth($post['body'], 0, 150, '...'));
require_once 'includes/header.php';
?>
<head>
  <!-- Required for all platforms -->
  <meta property="og:title"       content="Your Post Title Here" />
  <meta property="og:description" content="A short summary of the post..." />
  <meta property="og:image"       content="<?= htmlspecialchars($post['cover_image']) ?>" />
  <meta property="og:url"         content="https://yourdomain.com/post/your-post-slug" />
  <meta property="og:type"        content="article" />

  <!-- Twitter/X specific -->
  <meta name="twitter:card"        content="summary_large_image" />
  <meta name="twitter:title"       content="Your Post Title Here" />
  <meta name="twitter:description" content="A short summary of the post..." />
  <meta name="twitter:image"       content="https://yourdomain.com/images/post-thumbnail.jpg" />
</head>
<body>
<!-- BLOG POST -->
<main class="post-main">
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
      <img src="<?= htmlspecialchars($post['cover_image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>"
        class="post-image">
    <?php endif; ?>
    <article class="post-body">
      <?= $post['body'] ?>
    </article>

    <!-- SHARE SECTION -->
    <section class="post-share-section">
      <span class="share-title">Share this post:</span>
      <div class="share-buttons">
        <!-- Facebook -->
        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode((empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]") ?>" 
           target="_blank" rel="noopener" class="share-btn share-facebook" title="Share on Facebook">
          <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor">
            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
          </svg>
        </a>

        <!-- Twitter / X -->
        <a href="https://twitter.com/intent/tweet?text=<?= urlencode($post['title']) ?>&url=<?= urlencode((empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]") ?>" 
           target="_blank" rel="noopener" class="share-btn share-twitter" title="Share on X">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
            <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
          </svg>
        </a>

        <!-- WhatsApp -->
        <a href="https://api.whatsapp.com/send?text=<?= urlencode($post['title'] . ' - ' . (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]") ?>" 
           target="_blank" rel="noopener" class="share-btn share-whatsapp" title="Share on WhatsApp">
          <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L0 24l6.335-1.662c1.746.953 3.71 1.458 5.704 1.459h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
          </svg>
        </a>

        <!-- LinkedIn -->
        <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode((empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]") ?>" 
           target="_blank" rel="noopener" class="share-btn share-linkedin" title="Share on LinkedIn">
          <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor">
            <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
          </svg>
        </a>

        <!-- Copy Link Button -->
        <button id="copyShareLink" class="share-btn share-copy" title="Copy Link">
          <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
            <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
          </svg>
          <span class="tooltip" id="copyTooltip">Copy Link</span>
        </button>
      </div>
    </section>

    <!-- ENGAGEMENT SECTION -->
    <section class="comment-section">

      <!-- COMMENTS -->
      <!-- <div class="" id="commentsSection"> -->
        <!-- <h3 style="margin-bottom:15px;">Comments (<span id="commentCountTotal"><?= count($comments) ?></span>)</h3> -->

        <?php if (isset($_SESSION['user_id'])): ?>
          <form id="commentForm" class="engage-form">
            <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
            <textarea name="comment" rows="3"
              placeholder="Add a comment..." required></textarea>

            <!-- LIKES -->
            <div class="post-likes" style="display:flex; align-items:center; gap:10px;">
              <div id="likeBtn" data-post-id="<?= $post['id'] ?>"
                style=" color: <?= $userLiked ? '#e63946' : '#ff2b3dff' ?>;">
                <svg class="like-icon" fill="<?= $userLiked ? 'currentColor' : 'none' ?>" stroke="currentColor"
                  stroke-width="1" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                </svg>
                <span id="likeCount"><?= $likeCount ?></span>
              </div>
            </div>

            <button type="submit" style="background:transparent; border:none;">
              <svg class="comment-icon" fill="red" stroke="" stroke-linecap="round" stroke-linejoin="round"
                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M10 14 21 3"></path>
                <path d="m21 3-6.5 18a.55.55 0 0 1-1 0L10 14l-7-3.5a.55.55 0 0 1 0-1L21 3Z"></path>
              </svg>
            </button>
          </form>
         <?php else: ?>
          <a href="auth/login.php">
         <form id="commentForm" class="engage-form">
            <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
            <textarea name="comment" rows="3"
              placeholder="Add a comment..." required></textarea>

            <!-- LIKES -->
            <div class="post-likes" style="display:flex; align-items:center; gap:10px;">
              <div id="likeBtn" data-post-id="<?= $post['id'] ?>"
                style=" color: <?= $userLiked ? '#e63946' : '#ff2b3dff' ?>;">
                <svg class="like-icon" fill="<?= $userLiked ? 'currentColor' : 'none' ?>" stroke="currentColor"
                  stroke-width="1" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                </svg>
                <span id="likeCount"><?= $likeCount ?></span>
              </div>
            </div>

            <button type="submit" style="background:transparent; border:none;">
              <svg class="comment-icon" fill="red" stroke="" stroke-linecap="round" stroke-linejoin="round"
                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M10 14 21 3"></path>
                <path d="m21 3-6.5 18a.55.55 0 0 1-1 0L10 14l-7-3.5a.55.55 0 0 1 0-1L21 3Z"></path>
              </svg>
            </button>
          </form>
          </a>
        <?php endif; ?> 



        <div id="commentsList" style="display:flex; flex-direction:column; gap:20px;">
          <?php foreach ($comments as $c): ?>
            <div class="comment"
              style="background:#f9f9fa; padding:15px; border-radius:8px; border-left: 3px solid #ddd;">
              <div style="display:flex; justify-content:space-between; margin-bottom:5px;">
                <strong style="color:var(--secondary);font-size:15px;"><?= htmlspecialchars($c['username']) ?></strong>
                <span
                  style="font-size:12px; color:#888;"><?= date('M j, Y \a\t g:i A', strtotime($c['created_at'])) ?></span>
              </div>
              <p style="margin:0; font-size:14px; line-height:1.5; color:#333;">
                <?= nl2br(htmlspecialchars($c['comment'])) ?></p>
            </div>
          <?php endforeach; ?>
        </div>
      <!-- </div> -->

    </section>


    <!-- AJAX SCRIPT -->
    <script>
      const likeBtn = document.getElementById('likeBtn');
      if (likeBtn) {
        likeBtn.addEventListener('click', async () => {
          const postId = likeBtn.getAttribute('data-post-id');
          // Switch to URLSearchParams to send as x-www-form-urlencoded
          const formData = new URLSearchParams();
          formData.append('post_id', postId);

          try {
            const req = await fetch('ajax/like_post.php', {
              method: 'POST',
              body: formData
            });
            const res = await req.json();
            if (res.success) {
              document.getElementById('likeCount').textContent = res.total_likes;
              const svg = likeBtn.querySelector('svg');
              if (res.action === 'liked') {
                likeBtn.style.color = '#e63946';
                svg.setAttribute('fill', 'currentColor');
              } else {
                likeBtn.style.color = '#555';
                svg.setAttribute('fill', 'none');
              }
            } else {
              alert(res.message);
            }
          } catch (e) { console.error('Like error', e); }
        });
      }

      const commentForm = document.getElementById('commentForm');
      if (commentForm) {
        commentForm.addEventListener('submit', async (e) => {
          e.preventDefault();
          const formData = new FormData(commentForm);
          const btn = commentForm.querySelector('button');
          btn.disabled = true;
          // btn.textContent = 'Posting...';

          try {
            const req = await fetch('ajax/add_comment.php', { method: 'POST', body: formData });
            const res = await req.json();
            if (res.success) {
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
          } catch (e) { console.error('Comment error', e); }
          btn.disabled = false;
          // btn.innerHTML = "<svg class=comment-icon fill=none stroke=currentColor stroke-linecap=round stroke-linejoin=round viewBox=0 0 24 24><path d=M10 14 21 3></path> <path d=m21 3-6.5 18a.55.55 0 0 1-1 0L10 14l-7-3.5a.55.55 0 0 1 0-1L21 3Z></path></svg>"
        });
      };

      // Copy Link sharing feature
      const copyBtn = document.getElementById('copyShareLink');
      const copyTooltip = document.getElementById('copyTooltip');
      if (copyBtn) {
        copyBtn.addEventListener('click', async () => {
          try {
            await navigator.clipboard.writeText(window.location.href);
            copyBtn.classList.add('copied');
            copyTooltip.textContent = 'Copied!';
            setTimeout(() => {
              copyBtn.classList.remove('copied');
              copyTooltip.textContent = 'Copy Link';
            }, 2000);
          } catch (err) {
            console.error('Failed to copy link: ', err);
            // Fallback for browsers not supporting navigator.clipboard
            const tempInput = document.createElement('input');
            tempInput.value = window.location.href;
            document.body.appendChild(tempInput);
            tempInput.select();
            document.execCommand('copy');
            document.body.removeChild(tempInput);
            copyBtn.classList.add('copied');
            copyTooltip.textContent = 'Copied!';
            setTimeout(() => {
              copyBtn.classList.remove('copied');
              copyTooltip.textContent = 'Copy Link';
            }, 2000);
          }
        });
      }
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
            <?php
            $relImg = !empty($related['cover_image'])
              ? $related['cover_image']
              : getFirstBodyImage($related['body'] ?? '');
            $relImg = $relImg ?: 'assets/public/placeholder.jpg';
            ?>
            <img src="<?= htmlspecialchars($relImg) ?>" alt="<?= htmlspecialchars($related['title']) ?>">
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
            $gStmt = $conn->prepare('SELECT group_name FROM categories WHERE name = ? LIMIT 1');
            $gName = '';
            if ($gStmt) {
              $gStmt->bind_param('s', $cat);
              $gStmt->execute();
              $gRes = $gStmt->get_result();
              if ($gRow = $gRes->fetch_assoc())
                $gName = $gRow['group_name'];
            }
            ?>
            <a
              href="blog.php?category=<?= urlencode($gName ?: 'All') ?>&sub=<?= urlencode($cat) ?>"><?= htmlspecialchars($cat) ?></a>
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
                <span
                  style="display:block;font-size:12px;opacity:.6;margin-top:2px;"><?= htmlspecialchars($event['event_date']) ?></span>
              <?php endif; ?>
            </p>
          </a>
        <?php endforeach; ?>
      <?php else: ?>
        <p style="opacity:.5;font-size:14px;">No upcoming events at the moment.</p>
      <?php endif; ?>
    </div>

  </aside>
</main>
</body>
<?php require_once 'includes/footer.php'; ?>