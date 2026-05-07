<?php
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');

// Ensure the subscribers table exists
$conn->query("
CREATE TABLE IF NOT EXISTS subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Please provide a valid email address.']);
        exit;
    }

    try {
        $stmt = $conn->prepare("INSERT INTO subscribers (email) VALUES (?)");
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            if ($stmt->error) {
                throw new Exception($stmt->error, $conn->errno);
            }
            echo json_encode(['success' => true, 'message' => 'Thank you for subscribing!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        }
    } catch (Exception $e) {
        if ($conn->errno == 1062 || $e->getCode() == 1062) {
            echo json_encode(['success' => false, 'message' => 'You are already subscribed.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
        }
    }
}
