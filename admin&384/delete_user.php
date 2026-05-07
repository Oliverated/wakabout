<?php
require_once '../includes/db.php';

$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    // Delete user. This automatically cascades to post_comments and post_likes due to DB foreign keys.
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            header("Location: manage_users.php?msg=" . urlencode("User deleted successfully."));
            exit;
        }
    }
}

header("Location: manage_users.php?err=" . urlencode("Could not delete user."));
exit;
