<?php
require_once '../includes/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM subscribers WHERE email = ?");
    if ($stmt) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'You have been successfully unsubscribed from our newsletter.']);
        } else {
            echo json_encode(['success' => false, 'message' => "We couldn't find that email address in our subscriber list."]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'An internal error occurred. Please try again later.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
