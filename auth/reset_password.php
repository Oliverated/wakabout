<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

$error = '';
$success = '';
$token = $_GET['token'] ?? '';
$validToken = false;

if (empty($token)) {
    $error = 'Invalid or missing reset token.';
} else {
    // Validate token
    $stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ?");
    if ($stmt) {
        $stmt->bind_param("s", $token);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $validToken = true;
        } else {
            $error = 'Invalid or expired reset token.';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    
    if (empty($password) || empty($confirm)) {
        $error = 'Please fill out all fields.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $upd = $conn->prepare("UPDATE users SET password_hash = ?, reset_token = NULL WHERE reset_token = ?");
        if ($upd) {
            $upd->bind_param("ss", $hash, $token);
            if ($upd->execute()) {
                $success = 'Password successfully reset! You can now <a href="login.php">login</a>.';
                $validToken = false; // Hide form
            } else {
                $error = 'An error occurred. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reset Password - Wakabout</title>
  <link rel="stylesheet" href="auth.css">
</head>
<body>
  <div class="auth-container">
    <div class="auth-brand">
      <a href="../index.php" style="text-decoration:none;"><h2>Waka<span>bout</span></h2></a>
    </div>
    <h3 class="auth-form-title">Enter New Password</h3>
    
    <?php if ($error): ?>
      <div class="auth-alert error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
      <div class="auth-alert success"><?= $success ?></div>
    <?php endif; ?>

    <?php if ($validToken): ?>
      <form method="POST">
        <div class="auth-input-group">
          <label for="password">New Password</label>
          <input type="password" id="password" name="password" required>
        </div>
        <div class="auth-input-group">
          <label for="confirm_password">Confirm New Password</label>
          <input type="password" id="confirm_password" name="confirm_password" required>
        </div>
        <button type="submit" class="auth-btn">Reset Password</button>
      </form>
    <?php endif; ?>

    <div class="auth-links">
      <p><a href="login.php">Back to Login</a></p>
    </div>
  </div>
</body>
</html>
