<?php
require_once __DIR__ . '/../includes/session_config.php';
require_once __DIR__ . '/../includes/db.php';

$error = '';
$success = '';
$token = $_GET['token'] ?? '';
$validToken = false;

if (empty($token)) {
    $error = 'Invalid or missing reset token.';
} else {
    // Validate token and check if it has not expired
    $stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expires > ?");
    if ($stmt) {
        $now = date('Y-m-d H:i:s');
        $stmt->bind_param("ss", $token, $now);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $validToken = true;
        } else {
            $error = 'Invalid or expired reset token.';
        }
    } else {
        $error = 'An error occurred. Please try again.';
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
        $upd = $conn->prepare("UPDATE users SET password_hash = ?, reset_token = NULL, reset_token_expires = NULL WHERE reset_token = ?");
        if ($upd) {
            $upd->bind_param("ss", $hash, $token);
            if ($upd->execute()) {
                $success = 'Password successfully reset! You can now <a href="login.php" style="color:var(--accent); text-decoration:underline;">login</a>.';
                $validToken = false; // Hide form on success
            } else {
                $error = 'An error occurred. Please try again.';
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
  <title>Reset Password - Wakaabout</title>
  <link rel="stylesheet" href="../assets/required.css" />
  <link rel="stylesheet" href="auth.css">
</head>
<body>

  <form method="POST" class="auth-form" id="resetPasswordForm">
    <div class="brand-logo">
      <a href="../index.php"><img src="../assets/public/footerlogo.png" alt="Wakabout Logo"></a>
    </div>
    
    <h3 style="text-align: center; margin-top: 15px; margin-bottom: 5px; font-weight: 600; font-size: 20px;">Set New Password</h3>
    <p class="auth-form-subtitle" style="text-align: center; margin-bottom: 20px; font-size: 14px;">
      Choose a strong password to secure your account.
    </p>

    <?php if ($error): ?>
      <div class="auth-alert error" style="height: auto; min-height: 10vh; padding: 15px; box-sizing: border-box;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
      <div class="auth-alert success" style="height: auto; min-height: 10vh; padding: 20px; box-sizing: border-box; flex-direction: column; align-items: flex-start; justify-content: center;"><?= $success ?></div>
    <?php endif; ?>

    <?php if ($validToken): ?>
      <div class="auth-ctn">
        
        <!-- New Password -->
        <div class="auth-inp-block password-wrapper">
          <input type="password" id="password" name="password" placeholder="New Password (min. 6 chars)" required>
          <button type="button" class="password-toggle" aria-label="Toggle password visibility">
            <svg class="eye-on" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
            </svg>
            <svg class="eye-off" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12c1.292 4.338 5.31 7.5 10.066 7.5.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
            </svg>
          </button>
        </div>

        <div class="password-strength"><div class="password-strength-bar" id="strengthBar"></div></div>
        <div class="password-hint" id="strengthHint" style="margin-bottom: 20px;">Use at least 6 characters</div>

        <!-- Confirm Password -->
        <div class="auth-inp-block password-wrapper">
          <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
          <button type="button" class="password-toggle" aria-label="Toggle password visibility">
            <svg class="eye-on" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
            </svg>
            <svg class="eye-off" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12c1.292 4.338 5.31 7.5 10.066 7.5.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
            </svg>
          </button>
        </div>

        <button type="submit" class="auth-btn">Reset Password</button>
      </div>
    <?php endif; ?>

    <div class="auth-links">
      <p><a href="login.php">Back to Login</a></p>
    </div>   
  </form>

  <script>
  // Password toggle
  document.querySelectorAll('.password-toggle').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const input = btn.closest('.password-wrapper').querySelector('input');
      const isPassword = input.type === 'password';
      input.type = isPassword ? 'text' : 'password';
      btn.classList.toggle('active', isPassword);
    });
  });

  // Password strength indicator
  const passInput = document.getElementById('password');
  const strengthBar = document.getElementById('strengthBar');
  const strengthHint = document.getElementById('strengthHint');

  if (passInput && strengthBar) {
    passInput.addEventListener('input', () => {
      const val = passInput.value;
      let strength = 0;
      if (val.length >= 6) strength++;
      if (val.length >= 10) strength++;
      if (/[A-Z]/.test(val) && /[0-9]/.test(val)) strength++;
      if (/[^A-Za-z0-9]/.test(val)) strength++;

      strengthBar.className = 'password-strength-bar';
      if (val.length === 0) {
        strengthBar.style.width = '0';
        strengthHint.textContent = 'Use at least 6 characters';
      } else if (strength <= 1) {
        strengthBar.classList.add('weak');
        strengthHint.textContent = 'Weak — try adding numbers & symbols';
      } else if (strength <= 2) {
        strengthBar.classList.add('medium');
        strengthHint.textContent = 'Getting better — add more variety';
      } else {
        strengthBar.classList.add('strong');
        strengthHint.textContent = 'Strong password!';
      }
    });
  }
  </script>
</body>
</html>
