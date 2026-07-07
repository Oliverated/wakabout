<?php
require_once __DIR__ . '/../includes/session_config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/mailer.php';
require_once __DIR__ . '/../includes/emailTemplates.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                // Generate a secure token and set expiration to 30 minutes
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+30 minutes'));
                
                // Update DB with the token and its expiration
                $upd = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE email = ?");
                if ($upd) {
                    $upd->bind_param("sss", $token, $expires, $email);
                    if ($upd->execute()) {
                        // Build the reset link
                        $dir = str_replace('\\', '/', dirname($_SERVER['PHP_SELF']));
                        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
                        $resetLink = $protocol . "://" . $_SERVER['HTTP_HOST'] . $dir . "/reset_password.php?token=" . $token;
                        
                        // Try to send email
                        $emailBody = resetPasswordEmailTemplate($resetLink);
                        $mailSent = sendMail($email, "Reset Your Password - WakaAbout", $emailBody);                       
                        $isLocal = in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1']) || strpos($_SERVER['HTTP_HOST'], '192.168.') === 0; 
                        $success = "Reset link sent check your mail for your link, check". $mailSent . "for link" ;
                        if ($isLocal || !$mailSent) {
                            $success .= "  Email send " . ($mailSent ? "succeeded" : "failed/skipped") . ". You can use the link below to reset the password:<br>
   </div>";
                        }
                    } else {
                        $error = 'An error occurred. Please try again.';
                    }
                } else {
                    $error = 'An error occurred. Please try again.';
                }
            } else {
                // Prevent email enumeration
                $success = "Check your email" . htmlspecialchars($email) . "your reset link has been sent";
                $isLocal = in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1']) || strpos($_SERVER['HTTP_HOST'], '192.168.') === 0;
                if ($isLocal) {
                    $success .= "<br><br><span style='color: #a3a3a3; font-size: 13px;'>Note: The email \"<b>" . htmlspecialchars($email) . "</b>\" does not exist in the database, so no reset link was generated.</span>";
                }
            }
        } else {
            $error = 'An error occurred. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password - Wakaabout</title>
  <link rel="stylesheet" href="../assets/required.css" />
  <link rel="stylesheet" href="auth.css">
</head>
<body>

  <form method="POST" class="auth-form" id="forgotPasswordForm">
    <div class="brand-logo">
      <a href="../index.php"><img src="../assets/public/footerlogo.png" alt="Wakabout Logo"></a>
    </div>
    
    <p class="auth-form-subtitle" style="text-align: center; margin-bottom: 20px;">
      Enter your email address and we'll send you a link to reset your password.
    </p>

    <?php if ($error): ?>
      <div class="auth-alert error" style="height: auto; min-height: 10vh; padding: 15px; box-sizing: border-box;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
      <div class="auth-alert success" style="height: auto; min-height: 10vh; padding: 20px; box-sizing: border-box; flex-direction: column; align-items: flex-start; justify-content: center;"><?= $success ?></div>
    <?php endif; ?>

    <?php if (!$success): ?>
      <div class="auth-ctn">
        <div class="auth-inp-block">
          <input type="email" id="email" name="email" placeholder="Email Address" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
          <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
          </svg>
        </div>

        <button type="submit" class="auth-btn">Send Reset Link</button>
      </div>
    <?php endif; ?>

    <div class="auth-links">
      <p><a href="login.php">Back to Login</a></p>
    </div>   
  </form>

</body>
</html>
