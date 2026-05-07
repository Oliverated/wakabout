<?php
require_once __DIR__ . '/../includes/db.php';

$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    // Optionally delete the cover image file
    $stmt = $conn->prepare("SELECT cover_image FROM posts WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $post = $result ? $result->fetch_assoc() : null;

        if ($post && !empty($post['cover_image'])) {
            $imgPath = __DIR__ . '/../' . $post['cover_image'];
            if (file_exists($imgPath)) {
                unlink($imgPath);
            }
        }
    }

    // Delete the post
    $stmt2 = $conn->prepare("DELETE FROM posts WHERE id = ?");
    if ($stmt2) {
        $stmt2->bind_param("i", $id);
        $stmt2->execute();
    }
}

header('Location: manage_posts.php?msg=deleted');
exit;
