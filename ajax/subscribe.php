<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/mailer.php';
require_once __DIR__ . '/../includes/emailTemplates.php';

header('Content-Type: application/json');

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

            // ── Send welcome email ──────────────────────────────
 try {
    $htmlBody = welcomeEmailTemplate($email);
    sendMail($email, 'Welcome to WakaAbout! 🌍', $htmlBody);
} catch (\Throwable $mailErr) {
    error_log('[Wakabout] Welcome email failed for ' . $email . ': ' . $mailErr->getMessage());
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
