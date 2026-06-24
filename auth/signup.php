<?php
require_once __DIR__ . '/../includes/session_config.php';
require_once __DIR__ . '/../includes/db.php';

if (isset($_SESSION['user_id'])) {
  header('Location: ../index.php');
  exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $first_name = trim($_POST['first_name'] ?? '');
  $last_name  = trim($_POST['last_name']  ?? '');
  $username   = trim($_POST['username']   ?? '');
  $email      = trim($_POST['email']      ?? '');
  $password   = $_POST['password']        ?? '';

  if (empty($first_name) || empty($last_name) || empty($username) || empty($email) || empty($password)) {
    $error = 'All fields are required.';
  } elseif (strlen($password) < 6) {
    $error = 'Password must be at least 6 characters.';
  } else {
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    if ($stmt) {
      $stmt->bind_param("ss", $email, $username);
      $stmt->execute();
      if ($stmt->get_result()->num_rows > 0) {
        $error = 'Email or Username already exists.';
      } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $insStmt = $conn->prepare("INSERT INTO users (first_name, last_name, username, email, password_hash) VALUES (?, ?, ?, ?, ?)");
        if ($insStmt) {
          $insStmt->bind_param("sssss", $first_name, $last_name, $username, $email, $hash);
          if ($insStmt->execute()) {
            $_SESSION['user_id']    = $conn->insert_id;
            $_SESSION['username']   = $username;
            $_SESSION['first_name'] = $first_name;
            $_SESSION['last_name']  = $last_name;
            header('Location: ../index.php');
            exit;
          } else {
            $error = 'Oops, something went wrong. Please try again.';
          }
        }
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign Up - Wakabout</title>
  <link rel="stylesheet" href="../assets/required.css" />
  <link rel="stylesheet" href="auth.css">
</head>
<body>

  <form method="POST" id="signupForm" class="auth-form">

    <div class="brand-logo">
      <a href="../index.php"><img src="../assets/public/footerlogo.png" alt="Wakabout Logo"></a>
    </div>

    <?php if ($error): ?>
      <div class="auth-alert error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="auth-ctn">
<div class="name-colunm">
      <!-- First Name -->
      <div class="auth-inp-block">
        <input type="text" id="first_name" name="first_name" placeholder="First Name" required value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
        </svg>
      </div>

      <!-- Last Name -->
      <div class="auth-inp-block">
        <input type="text" id="last_name" name="last_name" placeholder="Last Name" required value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
        </svg>
      </div>
</div>
      <!-- Username -->
      <div class="auth-inp-block">
        <input type="text" id="username" name="username" placeholder="Username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
        </svg>
      </div>

      <!-- Email -->
      <div class="auth-inp-block">
        <input type="email" id="email" name="email" placeholder="Email Address" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
        </svg>
      </div>

      <!-- Password -->
      <div class="auth-inp-block password-wrapper">
        <input type="password" id="password" name="password" placeholder="Password (min. 6 characters)" required>
        <button type="button" class="password-toggle" aria-label="Toggle password visibility">
          <svg class="eye-on" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
          </svg>
          <svg class="eye-off" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12c1.292 4.338 5.31 7.5 10.066 7.5.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
          </svg>
        </button>
      </div>

      <div class="password-strength"><div class="password-strength-bar" id="strengthBar"></div>
    
    </div>
      <div class="password-hint" id="strengthHint">Use at least 6 characters</div>

      <button type="submit" class="auth-btn">Create Account</button>

    </div>

    <div class="auth-links">
      <p>Already have an account? <a href="login.php">Login</a></p>
    </div>

  </form>

<script>
// Password toggle
document.querySelectorAll('.password-toggle').forEach(btn => {
  btn.addEventListener('click', () => {
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