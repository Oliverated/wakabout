<?php
require_once __DIR__ . '/../includes/session_config.php';
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to comment.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_id = intval($_POST['post_id'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');
    $user_id = $_SESSION['user_id'];

    if ($post_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid post.']);
        exit;
    }

    if (empty($comment)) {
        echo json_encode(['success' => false, 'message' => 'Comment cannot be empty.']);
        exit;
    }

    $ins = $conn->prepare("INSERT INTO post_comments (post_id, user_id, comment) VALUES (?, ?, ?)");
    if ($ins) {
        $ins->bind_param("iis", $post_id, $user_id, $comment);
        if ($ins->execute()) {
            
            // fetch the newly added comment data (with username) to return
            $c_id = $conn->insert_id;
            $sel = $conn->prepare("SELECT c.id, c.comment, c.created_at, u.username FROM post_comments c JOIN users u ON c.user_id = u.id WHERE c.id = ?");
            $sel->bind_param("i", $c_id);
            $sel->execute();
            $newComment = $sel->get_result()->fetch_assoc();

            echo json_encode([
                'success' => true,
                'comment' => [
                    'id' => $newComment['id'],
                    'username' => htmlspecialchars($newComment['username']),
                    'text' => nl2br(htmlspecialchars($newComment['comment'])),
                    'date' => date('M j, Y \a\t g:i A', strtotime($newComment['created_at']))
                ]
            ]);
            exit;
        } else {
            echo json_encode(['success' => false, 'message' => 'Error saving comment.']);
            exit;
        }
    }
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);
