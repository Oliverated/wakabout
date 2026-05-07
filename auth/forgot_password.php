<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $error = 'Please enter your email.';
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                // Generate a simple token
                $token = bin2hex(random_bytes(32));
                
                // Update DB with the token
                $upd = $conn->prepare("UPDATE users SET reset_token = ? WHERE email = ?");
                if ($upd) {
                    $upd->bind_param("ss", $token, $email);
                    $upd->execute();
                    
                    // In a real app we'd email this link. For local dev we'll just show it.
                    $resetLink = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=" . $token;
                    $success = "Password reset instructions theoretically sent. For demo purposes, here is the link: <br><br><a href='$resetLink' style='color:red;text-decoration:underline;'>Reset Password Here</a>";
                }
            } else {
                // Return success anyway to prevent email enumeration
                $success = "If your email is in our system, you will receive a reset link shortly.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Forgot Password - Wakabout</title>
  <link rel="stylesheet" href="auth.css">
</head>
<body>
  <div class="auth-container">
    <div class="auth-brand">
      <a href="../index.php" style="text-decoration:none;"><h2>Waka<span>bout</span></h2></a>
    </div>
    <h3 class="auth-form-title">Reset Password</h3>
    
    <?php if ($error): ?>
      <div class="auth-alert error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
      <div class="auth-alert success"><?= $success ?></div>
    <?php else: ?>
      <form method="POST">
        <div class="auth-input-group">
          <label for="email">Email Address</label>
          <input type="email" id="email" name="email" required>
        </div>
        <button type="submit" class="auth-btn">Send Reset Link</button>
      </form>
    <?php endif; ?>

    <div class="auth-links">
      <p>Remembered your password? <a href="login.php">Login</a></p>
    </div>
  </div>
</body>
</html>
