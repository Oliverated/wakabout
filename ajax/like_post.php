<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to like a post.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_id = intval($_POST['post_id'] ?? 0);
    $user_id = $_SESSION['user_id'];

    if ($post_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid post.']);
        exit;
    }

    // Check if the like already exists
    $stmt = $conn->prepare("SELECT id FROM post_likes WHERE post_id = ? AND user_id = ?");
    if ($stmt) {
        $stmt->bind_param("ii", $post_id, $user_id);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            // Unlike
            $del = $conn->prepare("DELETE FROM post_likes WHERE post_id = ? AND user_id = ?");
            $del->bind_param("ii", $post_id, $user_id);
            $del->execute();
            $action = 'unliked';
        } else {
            // Like
            $ins = $conn->prepare("INSERT INTO post_likes (post_id, user_id) VALUES (?, ?)");
            $ins->bind_param("ii", $post_id, $user_id);
            $ins->execute();
            $action = 'liked';
        }

        // Get total like count
        $countStmt = $conn->prepare("SELECT COUNT(*) AS total FROM post_likes WHERE post_id = ?");
        $countStmt->bind_param("i", $post_id);
        $countStmt->execute();
        $total = $countStmt->get_result()->fetch_assoc()['total'];

        echo json_encode([
            'success' => true,
            'action' => $action,
            'total_likes' => $total
        ]);
        exit;
    }
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);
